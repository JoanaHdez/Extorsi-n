<?php

namespace App\Modules\CorreoRegistroAPI\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Email\Email;
use CodeIgniter\HTTP\ResponseInterface;

class CorreoRegistroAPI_Controller extends BaseController
{
    private const REMITENTE_INSTITUCIONAL = 'eventos-capacitaciones-cgsc@seguridadneza.gob.mx';

    public function estado(): ResponseInterface
    {
        return $this->respuesta([
            'ok' => true,
            'mensaje' => 'API de correos de registro disponible.',
            'endpoints' => [
                'individual' => 'api/correos/registro',
                'masivo' => 'api/correos/registro/masivo',
            ],
        ]);
    }

    public function preflight(): ResponseInterface
    {
        return $this->conCabecerasApi($this->response->setStatusCode(204));
    }

    public function enviarCorreo(): ResponseInterface
    {
        if (! $this->apiKeyValida()) {
            return $this->respuestaError('API key invalida o no enviada.', 401);
        }

        $registro = $this->normalizarRegistro($this->obtenerDatos());
        $errores = $this->validarRegistro($registro);

        if ($errores !== []) {
            return $this->respuesta([
                'ok' => false,
                'mensaje' => 'La informacion del registro no es valida.',
                'errores' => $errores,
            ], 422);
        }

        $resultado = $this->enviarRegistro($registro);

        if (! $resultado['ok']) {
            return $this->respuesta([
                'ok' => false,
                'mensaje' => 'No se pudo enviar el correo de confirmacion. Revise los logs del servidor.',
                'fallidos' => [[
                    'id' => $registro['id'],
                    'correo' => $registro['correo'],
                ]],
            ], 500);
        }

        return $this->respuesta([
            'ok' => true,
            'mensaje' => 'Correo de confirmacion enviado correctamente.',
            'enviados' => 1,
            'fallidos' => [],
            'registro' => [
                'id' => $registro['id'],
                'correo' => $registro['correo'],
            ],
        ]);
    }

    public function enviarMasivo(): ResponseInterface
    {
        if (! $this->apiKeyValida()) {
            return $this->respuestaError('API key invalida o no enviada.', 401);
        }

        $registros = $this->normalizarRegistrosMasivos($this->obtenerDatos());

        if ($registros === []) {
            return $this->respuestaError('Debe enviar al menos un registro.', 422);
        }

        $maximoRegistros = (int) $this->envTexto('correoRegistro.maxCorreos', '200');

        if (count($registros) > $maximoRegistros) {
            return $this->respuesta([
                'ok' => false,
                'mensaje' => 'La peticion supera el maximo permitido de registros.',
                'maximo' => $maximoRegistros,
                'total_recibido' => count($registros),
            ], 422);
        }

        $registrosValidos = [];
        $erroresValidacion = [];

        foreach ($registros as $indice => $registro) {
            $registroNormalizado = $this->normalizarRegistro($registro);
            $errores = $this->validarRegistro($registroNormalizado);

            if ($errores !== []) {
                $erroresValidacion[] = [
                    'indice' => $indice,
                    'id' => $registroNormalizado['id'],
                    'correo' => $registroNormalizado['correo'],
                    'errores' => $errores,
                ];

                continue;
            }

            $registrosValidos[] = $registroNormalizado;
        }

        if ($erroresValidacion !== []) {
            return $this->respuesta([
                'ok' => false,
                'mensaje' => 'Uno o mas registros no son validos.',
                'errores' => $erroresValidacion,
            ], 422);
        }

        $enviados = [];
        $fallidos = [];

        foreach ($registrosValidos as $registro) {
            if ($this->enviarRegistro($registro)['ok']) {
                $enviados[] = [
                    'id' => $registro['id'],
                    'correo' => $registro['correo'],
                ];

                continue;
            }

            $fallidos[] = [
                'id' => $registro['id'],
                'correo' => $registro['correo'],
            ];
        }

        if ($enviados === []) {
            return $this->respuesta([
                'ok' => false,
                'mensaje' => 'No se pudo enviar ningun correo de confirmacion. Revise los logs del servidor.',
                'enviados' => 0,
                'fallidos' => $fallidos,
            ], 500);
        }

        return $this->respuesta([
            'ok' => $fallidos === [],
            'mensaje' => $fallidos === []
                ? 'Correos de confirmacion enviados correctamente.'
                : 'Algunos correos de confirmacion no pudieron enviarse.',
            'enviados' => count($enviados),
            'fallidos' => $fallidos,
        ], $fallidos === [] ? 200 : 207);
    }

    private function enviarRegistro(array $registro): array
    {
        $configuraciones = $this->configuracionesCorreo();

        if ($configuraciones === []) {
            log_message('error', 'ERROR ENVIO REGISTRO: configuracion de correo incompleta.');

            return ['ok' => false];
        }

        foreach ($configuraciones as $configuracion) {
            $email = $this->crearCorreo($configuracion);
            $email->setFrom($this->remitenteCorreo(), $this->remitenteNombre());
            $email->setTo($registro['correo']);
            $email->setSubject($this->asunto());

            $heroCid = $this->adjuntarImagenInline($email, FCPATH . 'assets/img/OCTAVO_CONGRESO2026.png', 'hero congreso');
            $logoCid = $this->adjuntarImagenInline($email, FCPATH . 'assets/img/GOBIERNO_NEZA_LOGO.png', 'logo gobierno');
            $email->setMessage($this->cuerpoCorreo($registro, $heroCid, $logoCid));

            try {
                if ($email->send()) {
                    log_message(
                        'info',
                        'ENVIO CONFIRMACION REGISTRO OK id=' . $registro['id']
                            . ' correo=' . $registro['correo']
                            . ' usando ' . $this->descripcionCorreo($configuracion)
                    );

                    return ['ok' => true];
                }

                log_message(
                    'error',
                    'ERROR ENVIO CONFIRMACION REGISTRO id=' . $registro['id']
                        . ' correo=' . $registro['correo']
                        . ' usando ' . $this->descripcionCorreo($configuracion)
                        . ': ' . strip_tags($email->printDebugger(['headers', 'subject']))
                );
            } catch (\Throwable $exception) {
                log_message(
                    'error',
                    'ERROR ENVIO CONFIRMACION REGISTRO EXCEPTION id=' . $registro['id']
                        . ' correo=' . $registro['correo']
                        . ' usando ' . $this->descripcionCorreo($configuracion)
                        . ': ' . $exception->getMessage()
                );
            }
        }

        return ['ok' => false];
    }

    private function apiKeyValida(): bool
    {
        $apiKey = $this->envTexto('correoRegistro.apiToken', $this->envTexto('correoInvitacion.apiToken'));

        if ($apiKey === '') {
            log_message('error', 'Falta configurar correoRegistro.apiToken o correoInvitacion.apiToken en .env.');

            return false;
        }

        $recibida = trim($this->request->getHeaderLine('X-API-KEY'));

        if ($recibida === '') {
            $authorization = trim($this->request->getHeaderLine('Authorization'));

            if (stripos($authorization, 'Bearer ') === 0) {
                $recibida = trim(substr($authorization, 7));
            }
        }

        if ($recibida === '') {
            $recibida = $this->datoTexto($this->request->getPost() ?: [], [
                'api_key',
                'apiToken',
                'token',
            ]);
        }

        return $recibida !== '' && hash_equals($apiKey, $recibida);
    }

    private function obtenerDatos(): array
    {
        $contentType = strtolower($this->request->getHeaderLine('Content-Type'));

        if (str_contains($contentType, 'application/json')) {
            try {
                $json = $this->request->getJSON(true);

                return is_array($json) ? $json : [];
            } catch (\Throwable $exception) {
                log_message('error', 'JSON invalido en API registro: ' . $exception->getMessage());

                return [];
            }
        }

        $post = $this->request->getPost();

        if (! is_array($post)) {
            return [];
        }

        if (isset($post['payload']) && is_string($post['payload'])) {
            $payload = json_decode($post['payload'], true);

            if (is_array($payload)) {
                return array_merge($payload, $post);
            }
        }

        return $post;
    }

    private function normalizarRegistrosMasivos(array $datos): array
    {
        if ($this->esLista($datos)) {
            return array_values(array_filter($datos, 'is_array'));
        }

        foreach (['registros', 'personas', 'destinatarios', 'datos'] as $llave) {
            if (isset($datos[$llave]) && is_array($datos[$llave])) {
                return $this->esLista($datos[$llave])
                    ? array_values(array_filter($datos[$llave], 'is_array'))
                    : [$datos[$llave]];
            }
        }

        return $this->tieneCamposRegistro($datos) ? [$datos] : [];
    }

    private function normalizarRegistro(array $datos): array
    {
        return [
            'id' => $this->datoTexto($datos, ['id']),
            'nombre' => $this->datoTexto($datos, ['nombre']),
            'appat' => $this->datoTexto($datos, ['appat', 'apellido_p', 'apellidoPaterno']),
            'apmat' => $this->datoTexto($datos, ['apmat', 'apellido_m', 'apellidoMaterno']),
            'correo' => strtolower($this->datoTexto($datos, ['correo', 'email'])),
            'clave' => $this->datoTexto($datos, ['clave']),
        ];
    }

    private function validarRegistro(array $registro): array
    {
        $errores = [];

        foreach (['id', 'nombre', 'appat', 'apmat', 'correo', 'clave'] as $campo) {
            if (($registro[$campo] ?? '') === '') {
                $errores[$campo] = 'El campo ' . $campo . ' es obligatorio.';
            }
        }

        if (($registro['correo'] ?? '') !== '' && ! filter_var($registro['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'El correo no tiene un formato valido.';
        }

        return $errores;
    }

    private function tieneCamposRegistro(array $datos): bool
    {
        foreach (['id', 'nombre', 'appat', 'apmat', 'correo', 'clave'] as $campo) {
            if (array_key_exists($campo, $datos)) {
                return true;
            }
        }

        return false;
    }

    private function esLista(array $datos): bool
    {
        if ($datos === []) {
            return true;
        }

        return array_keys($datos) === range(0, count($datos) - 1);
    }

    private function crearCorreo(array $configuracion): Email
    {
        $email = \Config\Services::email();
        $email->clear(true);
        $email->initialize($configuracion);

        return $email;
    }

    private function adjuntarImagenInline(Email $email, string $ruta, string $descripcion): string
    {
        if (! is_file($ruta)) {
            log_message('error', 'No existe imagen inline para correo de registro (' . $descripcion . '): ' . $ruta);

            return '';
        }

        $email->attach($ruta, 'inline', basename($ruta));
        $cid = (string) ($email->setAttachmentCID($ruta) ?: '');

        if ($cid === '') {
            log_message('error', 'No se pudo generar CID para correo de registro (' . $descripcion . '): ' . $ruta);
        }

        return $cid;
    }
    private function configuracionesCorreo(): array
    {
        $smtpBase = [
            'protocol' => 'smtp',
            'SMTPHost' => $this->envTexto('correoRegistro.SMTPHost', $this->envTexto('email.SMTPHost')),
            'SMTPUser' => $this->envTexto('correoRegistro.SMTPUser', $this->envTexto('email.SMTPUser', self::REMITENTE_INSTITUCIONAL)),
            'SMTPPass' => $this->smtpPassword(),
            'SMTPPort' => (int) $this->envTexto('correoRegistro.SMTPPort', $this->envTexto('email.SMTPPort', '587')),
            'SMTPTimeout' => (int) $this->envTexto('correoRegistro.SMTPTimeout', $this->envTexto('email.SMTPTimeout', '20')),
            'SMTPCrypto' => $this->envTexto('correoRegistro.SMTPCrypto', $this->envTexto('email.SMTPCrypto', 'tls')),
            'SMTPAuthMethod' => $this->envTexto('correoRegistro.SMTPAuthMethod', $this->envTexto('email.SMTPAuthMethod', 'login')),
            'mailType' => $this->envTexto('correoRegistro.mailType', $this->envTexto('email.mailType', 'html')),
            'charset' => $this->envTexto('correoRegistro.charset', $this->envTexto('email.charset', 'UTF-8')),
            'newline' => "\r\n",
            'CRLF' => "\r\n",
        ];

        $configuraciones = [];

        if (
            $this->envTexto('correoRegistro.useSMTP', 'true') !== 'false'
            && $smtpBase['SMTPHost'] !== ''
            && $smtpBase['SMTPUser'] !== ''
            && $smtpBase['SMTPPass'] !== ''
            && $smtpBase['SMTPPort'] > 0
        ) {
            $configuraciones[] = $smtpBase;
            $fallbacks = array_filter(array_map('trim', explode(',', $this->envTexto('correoRegistro.SMTPFallbacks', '587:tls,465:ssl'))));

            foreach ($fallbacks as $fallback) {
                [$puerto, $crypto] = array_pad(explode(':', $fallback, 2), 2, '');

                if (! ctype_digit($puerto)) {
                    continue;
                }

                $configuracionFallback = $smtpBase;
                $configuracionFallback['SMTPPort'] = (int) $puerto;
                $configuracionFallback['SMTPCrypto'] = trim($crypto);
                $configuraciones[] = $configuracionFallback;
            }
        }

        if ($this->envTexto('correoRegistro.mailFallback', 'true') !== 'false') {
            $configuraciones[] = [
                'protocol' => 'mail',
                'mailType' => $this->envTexto('correoRegistro.mailType', $this->envTexto('email.mailType', 'html')),
                'charset' => $this->envTexto('correoRegistro.charset', $this->envTexto('email.charset', 'UTF-8')),
                'newline' => "\r\n",
                'CRLF' => "\r\n",
            ];
        }

        $unicas = [];

        foreach ($configuraciones as $configuracion) {
            $unicas[$this->descripcionCorreo($configuracion)] = $configuracion;
        }

        return array_values($unicas);
    }

    private function smtpPassword(): string
    {
        $smtpPassB64 = $this->envTexto('correoRegistro.SMTPPassB64', $this->envTexto('email.SMTPPassB64'));

        if ($smtpPassB64 !== '') {
            $smtpPass = base64_decode($smtpPassB64, true);

            if ($smtpPass !== false) {
                return $smtpPass;
            }

            log_message('error', 'correoRegistro.SMTPPassB64/email.SMTPPassB64 no es base64 valido.');
        }

        return $this->envTexto('correoRegistro.SMTPPass', $this->envTexto('email.SMTPPass'));
    }

    private function remitenteCorreo(): string
    {
        return $this->envTexto('correoRegistro.fromEmail', $this->envTexto('email.fromEmail', self::REMITENTE_INSTITUCIONAL));
    }

    private function remitenteNombre(): string
    {
        return $this->envTexto('correoRegistro.fromName', 'Registro Exitoso');
    }

    private function asunto(): string
    {
        return $this->envTexto('correoRegistro.asunto', 'Registro Exitoso');
    }

    private function cuerpoCorreo(array $registro, string $heroCid = '', string $logoCid = ''): string
    {
        return view('App\Modules\CorreoRegistroAPI\Views\ConfirmacionRegistro', [
            'id' => $registro['id'],
            'nombre' => $registro['nombre'],
            'appat' => $registro['appat'],
            'apmat' => $registro['apmat'],
            'correo' => $registro['correo'],
            'clave' => $registro['clave'],
            'nombre_completo' => trim($registro['nombre'] . ' ' . $registro['appat'] . ' ' . $registro['apmat']),
            'hero_cid' => $heroCid,
            'logo_cid' => $logoCid,
        ]);
    }

    private function datoTexto(array $datos, array $llaves): string
    {
        foreach ($llaves as $llave) {
            if (! array_key_exists($llave, $datos)) {
                continue;
            }

            $valor = $datos[$llave];

            if (is_array($valor) || is_object($valor)) {
                continue;
            }

            $valor = trim((string) $valor);

            if ($valor !== '') {
                return $valor;
            }
        }

        return '';
    }

    private function envTexto(string $llave, string $valorPorDefecto = ''): string
    {
        $valor = env($llave);

        if ($valor === null || $valor === '') {
            return $valorPorDefecto;
        }

        if ($valor === false) {
            return 'false';
        }

        if ($valor === true) {
            return 'true';
        }

        return trim((string) $valor);
    }

    private function descripcionCorreo(array $configuracion): string
    {
        if (($configuracion['protocol'] ?? '') === 'mail') {
            return 'protocol=mail';
        }

        return 'host=' . (($configuracion['SMTPHost'] ?? '') ?: 'N/A')
            . ' port=' . (($configuracion['SMTPPort'] ?? '') ?: 'N/A')
            . ' crypto=' . (($configuracion['SMTPCrypto'] ?? '') ?: 'N/A');
    }

    private function respuestaError(string $mensaje, int $codigo): ResponseInterface
    {
        return $this->respuesta([
            'ok' => false,
            'mensaje' => $mensaje,
        ], $codigo);
    }

    private function respuesta(array $datos, int $codigo = 200): ResponseInterface
    {
        return $this->conCabecerasApi(
            $this->response
                ->setStatusCode($codigo)
                ->setJSON($datos)
        );
    }

    private function conCabecerasApi(ResponseInterface $response): ResponseInterface
    {
        $origenPermitido = $this->envTexto('correoRegistro.allowedOrigin', $this->envTexto('correoInvitacion.allowedOrigin', '*'));

        return $response
            ->setHeader('Access-Control-Allow-Origin', $origenPermitido)
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, X-API-KEY, Authorization')
            ->setHeader('Access-Control-Max-Age', '86400');
    }
}
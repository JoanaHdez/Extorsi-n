<?php

namespace App\Controllers;

use CodeIgniter\Email\Email;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;

class CorreoInvitacion_Controller extends BaseController
{
    /**
     * Responde preflight CORS cuando el API se consume desde navegador.
     */
    public function preflight(): ResponseInterface
    {
        return $this->conCabecerasApi($this->response->setStatusCode(204));
    }

    /**
     * Envia una invitacion a un solo correo.
     *
     * Espera JSON o form-data:
     * - cc: correo o lista de correos que recibira la invitacion.
     * - adjunto o adjuntos[]: archivos opcionales enviados por multipart/form-data.
     * - body, cuerpo, mensaje, html, contenido o mensaje_adicional: cuerpo del correo.
     * - smtp_usuario/smtp_password opcionales; si no se envian, usa .env.
     */
    public function enviarCorreo(): ResponseInterface
    {
        // El API usa llave propia por header, no token CSRF de CodeIgniter.
        if (! $this->apiKeyValida()) {
            return $this->respuestaError('API key invalida o no enviada.', 401);
        }

        // Se toma la informacion enviada por JSON o por form-data.
        $datos = $this->obtenerDatos();

        // Se toma la lista de destinatarios desde cc.
        $correos = $this->destinatariosCc($datos);

        if ($correos === []) {
            return $this->respuestaError('Debe enviar al menos un correo en cc.', 422);
        }

        $invalidos = $this->correosInvalidos($correos);

        if ($invalidos !== []) {
            return $this->respuesta([
                'ok' => false,
                'mensaje' => 'Uno o mas correos en cc no tienen formato valido.',
                'correos_invalidos' => $invalidos,
            ], 422);
        }

        // Se leen y validan archivos adjuntos enviados por multipart/form-data.
        $adjuntos = $this->obtenerAdjuntos();

        if ($adjuntos['errores'] !== []) {
            return $this->respuesta([
                'ok' => false,
                'mensaje' => 'Uno o mas adjuntos no son validos.',
                'errores' => $adjuntos['errores'],
            ], 422);
        }

        // Se envia usando el cuerpo recibido por parametro.
        $resultado = $this->enviarInvitacion($correos, $datos, $adjuntos['archivos']);

        if ($resultado['enviados'] === []) {
            return $this->respuesta([
                'ok' => false,
                'mensaje' => 'No se pudo enviar la invitacion. Revise los logs del servidor.',
                'fallidos' => $resultado['fallidos'],
            ], 500);
        }

        // Se responde JSON para que cualquier cliente del API pueda interpretar el resultado.
        return $this->respuesta([
            'ok' => $resultado['fallidos'] === [],
            'mensaje' => $resultado['fallidos'] === []
                ? 'Invitacion enviada correctamente.'
                : 'Algunas invitaciones no pudieron enviarse.',
            'cc' => $correos,
            'enviados' => count($resultado['enviados']),
            'fallidos' => $resultado['fallidos'],
            'adjuntos' => count($adjuntos['archivos']),
        ], $resultado['fallidos'] === [] ? 200 : 207);
    }

    /**
     * Envia la misma invitacion a diferentes correos.
     *
     * Espera JSON o form-data:
     * - cc: correo o lista de correos que recibira la invitacion.
     * - adjunto o adjuntos[]: archivos opcionales enviados por multipart/form-data.
     * - body, cuerpo, mensaje, html, contenido o mensaje_adicional: cuerpo del correo.
     * - smtp_usuario/smtp_password opcionales; si no se envian, usa .env.
     */
    public function enviarMasivo(): ResponseInterface
    {
        // El API usa llave propia por header, no token CSRF de CodeIgniter.
        if (! $this->apiKeyValida()) {
            return $this->respuestaError('API key invalida o no enviada.', 401);
        }

        // Se toma la informacion enviada por JSON o por form-data.
        $datos = $this->obtenerDatos();

        // Se toma la lista de destinatarios desde cc.
        $correos = $this->destinatariosCc($datos);

        // Se valida que la peticion tenga al menos un correo correcto.
        if ($correos === []) {
            return $this->respuestaError('Debe enviar al menos un correo en cc.', 422);
        }

        // Se revisa si algun correo tiene formato invalido antes de intentar enviar.
        $invalidos = $this->correosInvalidos($correos);

        if ($invalidos !== []) {
            return $this->respuesta([
                'ok' => false,
                'mensaje' => 'Uno o mas correos en cc no tienen formato valido.',
                'correos_invalidos' => $invalidos,
            ], 422);
        }

        // Se limita el envio masivo para evitar errores humanos o abuso del endpoint.
        $maximoCorreos = (int) (env('correoInvitacion.maxCorreos') ?: 200);

        if (count($correos) > $maximoCorreos) {
            return $this->respuesta([
                'ok' => false,
                'mensaje' => 'La peticion supera el maximo permitido de correos.',
                'maximo' => $maximoCorreos,
                'total_recibido' => count($correos),
            ], 422);
        }

        // Se leen una sola vez los archivos para reutilizarlos en cada correo del envio masivo.
        $adjuntos = $this->obtenerAdjuntos();

        if ($adjuntos['errores'] !== []) {
            return $this->respuesta([
                'ok' => false,
                'mensaje' => 'Uno o mas adjuntos no son validos.',
                'errores' => $adjuntos['errores'],
            ], 422);
        }

        $resultado = $this->enviarInvitacion($correos, $datos, $adjuntos['archivos']);

        if ($resultado['enviados'] === []) {
            return $this->respuesta([
                'ok' => false,
                'mensaje' => 'No se pudo enviar ninguna invitacion. Revise los logs del servidor.',
                'fallidos' => $resultado['fallidos'],
            ], 500);
        }

        return $this->respuesta([
            'ok' => $resultado['fallidos'] === [],
            'mensaje' => $resultado['fallidos'] === []
                ? 'Invitaciones enviadas correctamente.'
                : 'Algunas invitaciones no pudieron enviarse.',
            'cc' => $correos,
            'enviados' => count($resultado['enviados']),
            'fallidos' => $resultado['fallidos'],
            'adjuntos' => count($adjuntos['archivos']),
        ], $resultado['fallidos'] === [] ? 200 : 207);
    }

    /**
     * Envia la invitacion con el cuerpo recibido por parametro.
     */
    private function enviarInvitacion(array $correos, array $datos, array $adjuntos = []): array
    {
        $configuraciones = $this->configuracionesCorreo($datos);

        if ($configuraciones === []) {
            log_message('error', 'ERROR ENVIO INVITACION CONGRESO: configuracion SMTP incompleta.');

            return [
                'ok' => false,
                'enviados' => [],
                'fallidos' => $correos,
            ];
        }

        foreach ($configuraciones as $configuracion) {
            $email = $this->crearCorreo($configuracion);

            $email->setFrom($this->remitenteCorreo(), $this->remitenteNombre());

            $email->setTo($correos);
            $email->setSubject($this->asunto($datos));

            foreach ($adjuntos as $adjunto) {
                $email->attach($adjunto['ruta'], 'attachment', $adjunto['nombre']);
            }

            $hero = FCPATH . 'assets/img/OCTAVO_CONGRESO2026.png';
            $logo = FCPATH . 'assets/img/GOBIERNO_NEZA_LOGO.png';
            $heroCid = '';
            $logoCid = '';

            if (is_file($hero)) {
                $email->attach($hero, 'inline', 'OCTAVO_CONGRESO2026.png');
                $heroCid = (string) ($email->setAttachmentCID($hero) ?: '');

                if ($heroCid === '') {
                    log_message('error', 'No se pudo generar CID para imagen hero: ' . $hero);
                }
            } else {
                log_message('error', 'No existe imagen hero: ' . $hero);
            }

            if (is_file($logo)) {
                $email->attach($logo, 'inline', 'GOBIERNO_NEZA_LOGO.png');
                $logoCid = (string) ($email->setAttachmentCID($logo) ?: '');

                if ($logoCid === '') {
                    log_message('error', 'No se pudo generar CID para imagen logo: ' . $logo);
                }
            } else {
                log_message('error', 'No existe imagen logo: ' . $logo);
            }

            $email->setMessage($this->cuerpoCorreo($datos, $heroCid, $logoCid));

            try {
                if ($email->send()) {
                    log_message(
                        'info',
                        'ENVIO INVITACION CONGRESO OK A LISTA usando ' . $this->descripcionCorreo($configuracion)
                            . ' destinatarios=' . count($correos)
                    );

                    return [
                        'ok' => true,
                        'enviados' => $correos,
                        'fallidos' => [],
                    ];
                }

                log_message(
                    'error',
                    'ERROR ENVIO INVITACION CONGRESO A LISTA CC usando ' . $this->descripcionCorreo($configuracion)
                        . ': ' . strip_tags($email->printDebugger(['headers', 'subject']))
                );
            } catch (\Throwable $exception) {
                log_message(
                    'error',
                    'ERROR ENVIO INVITACION CONGRESO EXCEPTION A LISTA CC usando ' . $this->descripcionCorreo($configuracion)
                        . ': ' . $exception->getMessage()
                );
            }
        }

        return [
            'ok' => false,
            'enviados' => [],
            'fallidos' => $correos,
        ];
    }

    /**
     * Valida la llave del API enviada por X-API-KEY o Authorization: Bearer.
     */
    private function apiKeyValida(): bool
    {
        // La llave real se configura en .env y nunca debe escribirse fija en el controlador.
        $apiKey = trim((string) env('correoInvitacion.apiToken'));

        if ($apiKey === '') {
            log_message('error', 'Falta configurar correoInvitacion.apiToken en .env.');

            return false;
        }

        // Primero se lee X-API-KEY, que es el header mas simple para consumidores del API.
        $recibida = trim($this->request->getHeaderLine('X-API-KEY'));

        // Tambien se acepta Authorization: Bearer para clientes que ya trabajan con bearer tokens.
        if ($recibida === '') {
            $authorization = trim($this->request->getHeaderLine('Authorization'));

            if (stripos($authorization, 'Bearer ') === 0) {
                $recibida = trim(substr($authorization, 7));
            }
        }

        // Respaldo para clientes que no pueden enviar headers personalizados.
        if ($recibida === '') {
            $recibida = trim((string) (
                $this->request->getPost('api_key')
                ?? $this->request->getPost('apiToken')
                ?? $this->request->getPost('token')
                ?? ''
            ));
        }

        // hash_equals evita comparar secretos con operaciones vulnerables por tiempo.
        return $recibida !== '' && hash_equals($apiKey, $recibida);
    }

    /**
     * Obtiene datos desde JSON o form-data.
     */
    private function obtenerDatos(): array
    {
        $contentType = strtolower($this->request->getHeaderLine('Content-Type'));

        // Si el cliente envia JSON, CodeIgniter lo convierte a arreglo.
        if (str_contains($contentType, 'application/json')) {
            try {
                $json = $this->request->getJSON(true);

                if (is_array($json)) {
                    return $json;
                }
            } catch (\Throwable $exception) {
                log_message('error', 'JSON invalido en API invitacion: ' . $exception->getMessage());

                return [];
            }
        }

        // Si no hay JSON, se intenta leer como formulario tradicional.
        $post = $this->request->getPost();

        if (! is_array($post)) {
            return [];
        }

        // Si el hosting bloquea application/json, se puede enviar JSON en un campo payload.
        if (isset($post['payload']) && is_string($post['payload'])) {
            $payload = json_decode($post['payload'], true);

            if (is_array($payload)) {
                return array_merge($payload, $post);
            }
        }

        return $post;
    }

    /**
     * Obtiene y valida archivos adjuntos enviados por multipart/form-data.
     */
    private function obtenerAdjuntos(): array
    {
        // Se aceptan extensiones comunes; pueden cambiarse desde .env.
        $extensionesPermitidas = array_filter(array_map('trim', explode(
            ',',
            (string) (env('correoInvitacion.extensionesAdjuntos') ?: 'pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx')
        )));

        // Se limita el peso por archivo para proteger memoria, SMTP y tiempos de respuesta.
        $maximoMb = (int) (env('correoInvitacion.maxAdjuntoMB') ?: 10);
        $maximoBytes = $maximoMb * 1024 * 1024;

        // CodeIgniter entrega todos los archivos subidos desde la peticion.
        $archivosRequest = $this->request->getFiles();
        $archivosSubidos = [];

        // Se aceptan ambos nombres de campo: adjunto y adjuntos.
        foreach (['adjunto', 'adjuntos'] as $campo) {
            if (isset($archivosRequest[$campo])) {
                $archivosSubidos = array_merge($archivosSubidos, $this->aplanarAdjuntos($archivosRequest[$campo]));
            }
        }

        $adjuntos = [];
        $errores = [];

        foreach ($archivosSubidos as $archivo) {
            // Si el campo existe pero no se eligio archivo, se ignora.
            if ($archivo->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            // Si PHP reporta error de subida, se informa al consumidor del API.
            if (! $archivo->isValid()) {
                $errores[] = $archivo->getClientName() . ': ' . $archivo->getErrorString();
                continue;
            }

            // Se valida extension para no aceptar archivos ejecutables o inesperados.
            $extension = mb_strtolower($archivo->getClientExtension(), 'UTF-8');

            if (! in_array($extension, $extensionesPermitidas, true)) {
                $errores[] = $archivo->getClientName() . ': extension no permitida.';
                continue;
            }

            // Se valida tamano por archivo.
            if ($archivo->getSize() > $maximoBytes) {
                $errores[] = $archivo->getClientName() . ': supera el maximo de ' . $maximoMb . ' MB.';
                continue;
            }

            // Se guarda la ruta temporal para adjuntarla sin mover el archivo.
            $adjuntos[] = [
                'ruta' => $archivo->getTempName(),
                'nombre' => $archivo->getClientName(),
                'mime' => $archivo->getClientMimeType() ?: 'application/octet-stream',
            ];
        }

        return [
            'archivos' => $adjuntos,
            'errores' => $errores,
        ];
    }

    /**
     * Convierte adjuntos simples o arreglos de adjuntos en una lista plana.
     */
    private function aplanarAdjuntos($valor): array
    {
        // Un campo de archivo simple llega como UploadedFile.
        if ($valor instanceof UploadedFile) {
            return [$valor];
        }

        // Un campo adjuntos[] llega como arreglo, asi que se recorre recursivamente.
        if (is_array($valor)) {
            $archivos = [];

            foreach ($valor as $item) {
                $archivos = array_merge($archivos, $this->aplanarAdjuntos($item));
            }

            return $archivos;
        }

        // Cualquier otro valor se ignora porque no representa un archivo subido.
        return [];
    }

    /**
     * Crea el servicio SMTP con las credenciales ya existentes del proyecto.
     */
    private function crearCorreo(array $configuracion): Email
    {
        // Se limpia la instancia para que no conserve destinatarios o adjuntos anteriores.
        $email = \Config\Services::email();
        $email->clear(true);

        $email->initialize($configuracion);

        return $email;
    }

    /**
     * Construye la configuracion de correo principal y sus fallbacks para el API.
     */
    private function configuracionesCorreo(array $datos = []): array
    {
        $smtpBase = [
            'protocol'       => 'smtp',
            'SMTPHost'       => $this->envTexto('correoInvitacion.SMTPHost', $this->envTexto('email.SMTPHost')),
            'SMTPUser'       => $this->smtpUsuario($datos),
            'SMTPPass'       => $this->smtpPassword($datos),
            'SMTPPort'       => (int) $this->envTexto('correoInvitacion.SMTPPort', $this->envTexto('email.SMTPPort', '25')),
            'SMTPTimeout'    => (int) $this->envTexto('correoInvitacion.SMTPTimeout', $this->envTexto('email.SMTPTimeout', '20')),
            'SMTPCrypto'     => $this->envTexto('correoInvitacion.SMTPCrypto', $this->envTexto('email.SMTPCrypto')),
            'SMTPAuthMethod' => $this->envTexto('correoInvitacion.SMTPAuthMethod', $this->envTexto('email.SMTPAuthMethod', 'login')),
            'mailType'       => $this->envTexto('correoInvitacion.mailType', $this->envTexto('email.mailType', 'html')),
            'charset'        => $this->envTexto('correoInvitacion.charset', $this->envTexto('email.charset', 'UTF-8')),
            'newline'        => "\r\n",
            'CRLF'           => "\r\n",
        ];

        $configuraciones = [];

        if (
            $this->envTexto('correoInvitacion.useSMTP', 'true') !== 'false'
            && $smtpBase['SMTPHost'] !== ''
            && $smtpBase['SMTPUser'] !== ''
            && $smtpBase['SMTPPass'] !== ''
            && $smtpBase['SMTPPort'] > 0
        ) {
            $configuraciones[] = $smtpBase;
            $fallbacks = array_filter(array_map('trim', explode(',', $this->envTexto('correoInvitacion.SMTPFallbacks'))));

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

        if ($this->envTexto('correoInvitacion.mailFallback', 'true') !== 'false') {
            $configuraciones[] = [
                'protocol' => 'mail',
                'mailType' => $this->envTexto('correoInvitacion.mailType', $this->envTexto('email.mailType', 'html')),
                'charset'  => $this->envTexto('correoInvitacion.charset', $this->envTexto('email.charset', 'UTF-8')),
                'newline'  => "\r\n",
                'CRLF'     => "\r\n",
            ];
        }

        $unicas = [];

        foreach ($configuraciones as $configuracion) {
            $clave = $this->descripcionCorreo($configuracion);
            $unicas[$clave] = $configuracion;
        }

        return array_values($unicas);
    }

    /**
     * Obtiene el usuario SMTP enviado por API o el configurado en .env.
     */
    private function smtpUsuario(array $datos = []): string
    {
        $smtpUsuario = $this->datoTexto($datos, [
            'smtp_usuario',
            'smtpUsuario',
            'smtp_user',
            'smtpUser',
            'SMTPUser',
            'usuario_smtp',
            'usuarioCorreo',
            'usuario',
        ]);

        if ($smtpUsuario !== '') {
            return $smtpUsuario;
        }

        return $this->envTexto('correoInvitacion.SMTPUser', $this->envTexto('email.SMTPUser'));
    }

    /**
     * Obtiene el password SMTP, preferentemente codificado en base64.
     */
    private function smtpPassword(array $datos = []): string
    {
        $smtpPass = $this->datoTexto($datos, [
            'smtp_password',
            'smtpPassword',
            'smtp_pass',
            'smtpPass',
            'SMTPPass',
            'smtp_contrasena',
            'smtpContrasena',
            'smtp_contraseña',
            'smtpContraseña',
            'password_smtp',
            'contrasena_smtp',
            'contraseña_smtp',
            'contrasena',
            'contraseña',
            'password',
        ]);

        if ($smtpPass !== '') {
            return $smtpPass;
        }

        $smtpPassB64Request = $this->datoTexto($datos, [
            'smtp_password_b64',
            'smtpPasswordB64',
            'smtp_pass_b64',
            'smtpPassB64',
            'SMTPPassB64',
        ]);

        if ($smtpPassB64Request !== '') {
            $smtpPassDecodificado = base64_decode($smtpPassB64Request, true);

            if ($smtpPassDecodificado !== false) {
                return $smtpPassDecodificado;
            }

            log_message('error', 'El password SMTP enviado por API no es base64 valido.');
        }

        $smtpPassB64 = $this->envTexto('correoInvitacion.SMTPPassB64', $this->envTexto('email.SMTPPassB64'));

        if ($smtpPassB64 !== '') {
            $smtpPass = base64_decode($smtpPassB64, true);

            if ($smtpPass !== false) {
                return $smtpPass;
            }

            log_message('error', 'correoInvitacion.SMTPPassB64/email.SMTPPassB64 no es base64 valido.');
        }

        return $this->envTexto('correoInvitacion.SMTPPass', $this->envTexto('email.SMTPPass'));
    }

    /**
     * Lee el primer valor de texto no vacio desde varios alias de la peticion.
     */
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


    private function remitenteCorreo(): string
    {
        return $this->envTexto('correoInvitacion.fromEmail', $this->envTexto('email.fromEmail'));
    }

    private function remitenteNombre(): string
    {
        return $this->envTexto(
            'correoInvitacion.fromName',
            $this->envTexto('email.fromName2', 'Invitacion Congreso')
        );
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

    /**
     * Obtiene la lista de destinatarios desde el parametro cc.
     */
    private function destinatariosCc(array $datos): array
    {
        return $this->normalizarCorreos($datos['cc'] ?? []);
    }

    /**
     * Normaliza correos desde string o arreglo.
     */
    private function normalizarCorreos($valor): array
    {
        // Un texto se puede separar por coma, punto y coma o salto de linea.
        if (is_string($valor)) {
            $valor = preg_split('/[,;\r\n]+/', $valor) ?: [];
        }

        if (! is_array($valor)) {
            return [];
        }

        // Se limpian espacios y se evitan repetidos.
        $correos = [];

        foreach ($valor as $correo) {
            // Si dentro del arreglo llega un texto con varios correos, tambien se separa.
            if (is_string($correo)) {
                $partes = preg_split('/[,;\r\n]+/', $correo) ?: [];

                foreach ($partes as $parte) {
                    if (trim($parte) !== '') {
                        $correos[] = mb_strtolower(trim($parte), 'UTF-8');
                    }
                }
            }
        }

        return array_values(array_unique($correos));
    }

    /**
     * Detecta correos con formato invalido.
     */
    private function correosInvalidos(array $correos): array
    {
        // Se usa la validacion nativa de PHP para correos.
        return array_values(array_filter($correos, static function (string $correo): bool {
            return ! filter_var($correo, FILTER_VALIDATE_EMAIL);
        }));
    }

    /**
     * Obtiene el cuerpo del correo enviado por quien consume el API.
     * El equipo que entregue el diseno final debe modificar este metodo o enviar body/html.
     */
    private function cuerpoCorreo(array $datos, string $heroCid = '', string $logoCid = ''): string
    {
        return view('App\Modules\CorreosAPI\Views\Invitacion', [
            'nombre_completo' => esc(trim((string) ($datos['nombre'] ?? $datos['nombre_completo'] ?? ''))),
            'nombramiento'   => esc(trim((string) ($datos['nombramiento'] ?? ''))),
            'hero_cid'        => $heroCid,
            'logo_cid'        => $logoCid,
        ]);
    }
    /**
     * Regresa el asunto del correo.
     */
    private function asunto(array $datos): string
    {
        // Se permite personalizar el asunto, pero se deja uno institucional por defecto.
        return trim((string) ($datos['asunto'] ?? 'Invitacion al Octavo Congreso Internacional de Seguridad y Proximidad Social'));
    }

    /**
     * Crea una respuesta de error uniforme para el API.
     */
    private function respuestaError(string $mensaje, int $codigo): ResponseInterface
    {
        return $this->respuesta([
            'ok' => false,
            'mensaje' => $mensaje,
        ], $codigo);
    }

    /**
     * Crea una respuesta JSON uniforme para el API.
     */
    private function respuesta(array $datos, int $codigo = 200): ResponseInterface
    {
        return $this->conCabecerasApi(
            $this->response
                ->setStatusCode($codigo)
                ->setJSON($datos)
        );
    }

    /**
     * Agrega headers necesarios para clientes externos del API.
     */
    private function conCabecerasApi(ResponseInterface $response): ResponseInterface
    {
        $origenPermitido = $this->envTexto('correoInvitacion.allowedOrigin', '*');

        return $response
            ->setHeader('Access-Control-Allow-Origin', $origenPermitido)
            ->setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, X-API-KEY, Authorization')
            ->setHeader('Access-Control-Max-Age', '86400');
    }
}

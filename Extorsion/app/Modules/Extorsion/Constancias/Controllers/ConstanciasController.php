<?php

namespace App\Modules\Extorsion\Constancias\Controllers;

use App\Controllers\BaseController;
use App\Modules\Extorsion\Constancias\Services\ConstanciaPdfService;
use App\Modules\Extorsion\Constancias\Services\ConstanciaRegistroService;
use App\Modules\Extorsion\Constancias\Services\ConstanciasEstadoService;
use App\Modules\Extorsion\Constancias\Services\ConstanciaTokenService;
use App\Modules\Extorsion\Constancias\Services\CuestionarioConstanciaService;
use App\Modules\Extorsion\Jornadas\Services\JornadaRegistroService;
use CodeIgniter\Exceptions\PageNotFoundException;

class ConstanciasController extends BaseController
{
    public function constancia(string $token)
    {
        $datosToken = $this->validarTokenConstancia($token);

        if ($datosToken === null) {
            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', 'text/html; charset=UTF-8')
                ->setBody($this->mensajeLigaConstanciaNoValida());
        }

        $registro = $this->obtenerRegistroConstancia($datosToken['tipo'], (int) $datosToken['id']);

        if ($registro === null) {
            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', 'text/html; charset=UTF-8')
                ->setBody($this->mensajeRegistroConstanciaNoEncontrado());
        }

        if (! $this->constanciasHabilitadas()) {
            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', 'text/html; charset=UTF-8')
                ->setBody($this->mensajeConstanciaNoDisponible());
        }

        $plantillaPath = $this->plantillaConstanciaPath($registro);

        if ($plantillaPath === null) {
            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', 'text/html; charset=UTF-8')
                ->setBody($this->mensajeConstanciaFueraDeFecha());
        }

        if ($this->cuestionarioConstanciaHabilitado() && ! $this->cuestionarioConstanciaRespondido($datosToken['tipo'], (int) $datosToken['id'])) {
            return view('App\Modules\Extorsion\Constancias\Views\CuestionarioConstancia', [
                'registro' => $registro,
                'token' => $token,
                'preguntas' => $this->preguntasCuestionarioConstancia(),
            ]);
        }

        return $this->descargarConstanciaPdf($registro, $plantillaPath);
    }

    public function guardarCuestionarioConstancia(string $token)
    {
        $datosToken = $this->validarTokenConstancia($token);

        if ($datosToken === null) {
            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', 'text/html; charset=UTF-8')
                ->setBody($this->mensajeLigaConstanciaNoValida());
        }

        $registro = $this->obtenerRegistroConstancia($datosToken['tipo'], (int) $datosToken['id']);

        if ($registro === null) {
            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', 'text/html; charset=UTF-8')
                ->setBody($this->mensajeRegistroConstanciaNoEncontrado());
        }

        if (! $this->constanciasHabilitadas()) {
            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', 'text/html; charset=UTF-8')
                ->setBody($this->mensajeConstanciaNoDisponible());
        }

        $plantillaPath = $this->plantillaConstanciaPath($registro);

        if ($plantillaPath === null) {
            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', 'text/html; charset=UTF-8')
                ->setBody($this->mensajeConstanciaFueraDeFecha());
        }

        $respuestas = $this->request->getPost('respuestas');

        if (! is_array($respuestas)) {
            return redirect()->to(base_url('constancia/' . $token))
                ->with('errors', [
                    'cuestionario' => 'Responda el cuestionario para descargar su constancia.'
                ]);
        }

        $this->guardarRespuestasCuestionario($datosToken['tipo'], (int) $datosToken['id'], $respuestas);

        return view('App\Modules\Extorsion\Constancias\Views\CuestionarioExito', [
            'downloadUrl' => base_url('constancia/' . $token),
        ]);
    }

    public function controlConstancias()
    {
        $token = (string) $this->request->getGet('token');

        if (! $this->tokenControlValido($token)) {
            throw PageNotFoundException::forPageNotFound('Panel no encontrado.');
        }

        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setBody($this->vistaControlConstancias($token));
    }

    public function actualizarControlConstancias()
    {
        $token = (string) $this->request->getPost('token');

        if (! $this->tokenControlValido($token)) {
            throw PageNotFoundException::forPageNotFound('Panel no encontrado.');
        }

        if ($this->request->getPost('tipo') === 'jornada') {
            $jornada = (string) $this->request->getPost('jornada');
            $this->guardarJornadaActiva($jornada);
        } else {
            $habilitar = $this->request->getPost('estado') === '1';
            $this->guardarEstadoConstancias($habilitar);
        }

        return redirect()->to(base_url('constancias/control?token=' . rawurlencode($token)));
    }

    private function descargarConstanciaPdf(array $registro, string $plantillaPath)
    {
        $pdf = (new ConstanciaPdfService())->generar($registro, $plantillaPath);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $pdf['filename'] . '"')
            ->setBody($pdf['content']);
    }

    private function preguntasCuestionarioConstancia(): array
    {
        return (new CuestionarioConstanciaService())->preguntas();
    }

    private function cuestionarioConstanciaHabilitado(): bool
    {
        return (new CuestionarioConstanciaService())->habilitado();
    }

    private function cuestionarioConstanciaRespondido(string $tipoRegistro, int $idRegistro): bool
    {
        return (new CuestionarioConstanciaService())->respondido($tipoRegistro, $idRegistro);
    }

    private function guardarRespuestasCuestionario(string $tipoRegistro, int $idRegistro, array $respuestas): void
    {
        (new CuestionarioConstanciaService())->guardarRespuestas($tipoRegistro, $idRegistro, $respuestas);
    }

    private function obtenerRegistroConstancia(string $tipo, int $id): ?array
    {
        return (new ConstanciaRegistroService())->obtener($tipo, $id);
    }

    private function validarTokenConstancia(string $token): ?array
    {
        return (new ConstanciaTokenService())->validar($token);
    }

    private function constanciasHabilitadas(): bool
    {
        return (new ConstanciasEstadoService())->habilitadas();
    }

    private function guardarEstadoConstancias(bool $habilitadas): void
    {
        (new ConstanciasEstadoService())->guardar($habilitadas);
    }

    private function jornadaActiva(): string
    {
        return (new JornadaRegistroService())->activa();
    }

    private function jornadasRegistro(): array
    {
        return (new JornadaRegistroService())->jornadas();
    }

    private function guardarJornadaActiva(string $jornada): void
    {
        (new JornadaRegistroService())->guardarActiva($jornada);
    }

    private function tokenControlValido(string $token): bool
    {
        $tokenConfigurado = (string) env('registro.constanciasControlToken', '');

        return $tokenConfigurado !== '' && hash_equals($tokenConfigurado, $token);
    }

    private function vistaControlConstancias(string $token): string
    {
        $habilitadas = $this->constanciasHabilitadas();
        $estadoTexto = $habilitadas ? 'Habilitadas' : 'Deshabilitadas';
        $accionTexto = $habilitadas ? 'Deshabilitar constancias' : 'Habilitar constancias';
        $accionValor = $habilitadas ? '0' : '1';
        $color = $habilitadas ? '#15803d' : '#8a1538';
        $csrf = function_exists('csrf_field') ? csrf_field() : '';
        $jornadaActiva = $this->jornadaActiva();
        $jornadasBotones = '';

        foreach ($this->jornadasRegistro() as $slug => $fecha) {
            $activa = $slug === $jornadaActiva;
            $jornadasBotones .= '<form method="post" action="' . esc(base_url('constancias/control'), 'attr') . '" class="mini-form">
                    ' . $csrf . '
                    <input type="hidden" name="token" value="' . esc($token, 'attr') . '">
                    <input type="hidden" name="tipo" value="jornada">
                    <input type="hidden" name="jornada" value="' . esc($slug, 'attr') . '">
                    <button type="submit" class="' . ($activa ? 'secondary active' : 'secondary') . '">' . esc($slug) . '</button>
                </form>';
        }

        $jornadasBotones .= '<form method="post" action="' . esc(base_url('constancias/control'), 'attr') . '" class="mini-form">
                ' . $csrf . '
                <input type="hidden" name="token" value="' . esc($token, 'attr') . '">
                <input type="hidden" name="tipo" value="jornada">
                <input type="hidden" name="jornada" value="">
                <button type="submit" class="secondary danger">Cerrar registro</button>
            </form>';

        return '<!doctype html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Control de constancias</title>
            <style>
                body {
                    margin: 0;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 22px;
                    background: #eef2f7;
                    color: #1f2933;
                    font-family: Arial, Helvetica, sans-serif;
                }
                .panel {
                    width: min(420px, 100%);
                    padding: 28px 24px;
                    border-top: 8px solid ' . $color . ';
                    background: #ffffff;
                    box-shadow: 0 14px 34px rgba(20, 36, 64, 0.14);
                    text-align: center;
                }
                h1 {
                    margin: 0 0 12px;
                    color: #243b6b;
                    font-size: 26px;
                    line-height: 1.25;
                }
                .status {
                    margin: 0 0 24px;
                    color: ' . $color . ';
                    font-size: 22px;
                    font-weight: 700;
                }
                button {
                    width: 100%;
                    border: 0;
                    border-radius: 8px;
                    padding: 16px 18px;
                    background: ' . $color . ';
                    color: #ffffff;
                    font-size: 17px;
                    font-weight: 700;
                }
                .section {
                    margin-top: 28px;
                    padding-top: 22px;
                    border-top: 1px solid #e5e9ef;
                }
                .section-title {
                    margin: 0 0 12px;
                    color: #243b6b;
                    font-size: 18px;
                    font-weight: 700;
                }
                .mini-form {
                    margin-top: 10px;
                }
                .secondary {
                    background: #243b6b;
                    padding: 13px 16px;
                    font-size: 15px;
                }
                .secondary.active {
                    background: #15803d;
                }
                .secondary.danger {
                    background: #8a1538;
                }
                .note {
                    margin: 18px 0 0;
                    color: #667085;
                    font-size: 14px;
                    line-height: 1.5;
                }
            </style>
        </head>
        <body>
            <main class="panel">
                <h1>Control de constancias</h1>
                <p class="status">' . esc($estadoTexto) . '</p>
                <form method="post" action="' . esc(base_url('constancias/control'), 'attr') . '">
                    ' . $csrf . '
                    <input type="hidden" name="token" value="' . esc($token, 'attr') . '">
                    <input type="hidden" name="tipo" value="constancias">
                    <input type="hidden" name="estado" value="' . esc($accionValor, 'attr') . '">
                    <button type="submit">' . esc($accionTexto) . '</button>
                </form>
                <section class="section">
                    <p class="section-title">Jornada activa</p>
                    <p class="note">' . ($jornadaActiva === '' ? 'Registro cerrado' : 'Activa: ' . esc($jornadaActiva)) . '</p>
                    ' . $jornadasBotones . '
                </section>
                <p class="note">Use este panel para abrir o cerrar descargas y cambiar la URL de registro activa.</p>
            </main>
        </body>
        </html>';
    }

    private function mensajeLigaConstanciaNoValida(): string
    {
        return $this->mensajeConstanciaSimple(
            'Liga de constancia no valida',
            'La liga de constancia esta incompleta o fue alterada por el correo. Abra el enlace original completo o copie y pegue toda la URL en el navegador.'
        );
    }

    private function mensajeRegistroConstanciaNoEncontrado(): string
    {
        return $this->mensajeConstanciaSimple(
            'Registro no encontrado',
            'No se encontro el registro asociado a esta constancia. Verifique que esta usando la liga completa recibida por correo.'
        );
    }

    private function mensajeConstanciaSimple(string $titulo, string $mensaje): string
    {
        $tituloSeguro = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
        $mensajeSeguro = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');

        return '<!doctype html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . $tituloSeguro . '</title>
            <style>
                body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; background: #eef2f7; color: #1f2933; font-family: Arial, Helvetica, sans-serif; }
                .message { width: min(580px, 100%); padding: 34px 30px; border-top: 8px solid #8a1538; background: #ffffff; box-shadow: 0 14px 34px rgba(20, 36, 64, 0.14); text-align: center; }
                h1 { margin: 0 0 12px; color: #243b6b; font-size: 28px; line-height: 1.25; }
                p { margin: 0; color: #344054; font-size: 18px; line-height: 1.6; }
            </style>
        </head>
        <body>
            <main class="message">
                <h1>' . $tituloSeguro . '</h1>
                <p>' . $mensajeSeguro . '</p>
            </main>
        </body>
        </html>';
    }

    private function mensajeConstanciaNoDisponible(): string
    {
        return '<!doctype html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Constancia no disponible</title>
            <style>
                body {
                    margin: 0;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 24px;
                    background: #eef2f7;
                    color: #1f2933;
                    font-family: Arial, Helvetica, sans-serif;
                }
                .message {
                    width: min(560px, 100%);
                    padding: 34px 30px;
                    border-top: 8px solid #8a1538;
                    background: #ffffff;
                    box-shadow: 0 14px 34px rgba(20, 36, 64, 0.14);
                    text-align: center;
                }
                h1 {
                    margin: 0 0 12px;
                    color: #243b6b;
                    font-size: 28px;
                    line-height: 1.25;
                }
                p {
                    margin: 0;
                    color: #344054;
                    font-size: 18px;
                    line-height: 1.6;
                }
            </style>
        </head>
        <body>
            <main class="message">
                <h1>Constancia no disponible</h1>
                <p>La descarga de su constancia se habilitar&aacute; una vez que termine la pl&aacute;tica.</p>
            </main>
        </body>
        </html>';
    }

    private function mensajeConstanciaFueraDeFecha(): string
    {
        return '<!doctype html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Constancia no disponible</title>
            <style>
                body {
                    margin: 0;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 24px;
                    background: #eef2f7;
                    color: #1f2933;
                    font-family: Arial, Helvetica, sans-serif;
                }
                .message {
                    width: min(580px, 100%);
                    padding: 34px 30px;
                    border-top: 8px solid #8a1538;
                    background: #ffffff;
                    box-shadow: 0 14px 34px rgba(20, 36, 64, 0.14);
                    text-align: center;
                }
                h1 {
                    margin: 0 0 12px;
                    color: #243b6b;
                    font-size: 28px;
                    line-height: 1.25;
                }
                p {
                    margin: 0;
                    color: #344054;
                    font-size: 18px;
                    line-height: 1.6;
                }
                .small {
                    margin-top: 14px;
                    color: #667085;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <main class="message">
                <h1>Constancia no disponible</h1>
                <p>La constancia de este registro solo podr&aacute; generarse cuando corresponda a una de las fechas oficiales de la pl&aacute;tica.</p>
                <p class="small">Si considera que esto es un error, verifique el d&iacute;a de registro o comun&iacute;quese con el personal organizador.</p>
            </main>
        </body>
        </html>';
    }

    private function plantillaConstanciaPath(array $registro): ?string
    {
        return (new ConstanciaRegistroService())->plantillaPath($registro);
    }
}
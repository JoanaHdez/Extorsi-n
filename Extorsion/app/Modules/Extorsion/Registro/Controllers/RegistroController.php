<?php

namespace App\Modules\Extorsion\Registro\Controllers;

use App\Controllers\BaseController;
use App\Models\Dato_Model;
use App\Models\Dependencia_Model;
use App\Models\General_Model;
use App\Models\Personal_Model;
use App\Models\Sexo_Model;
use App\Modules\Extorsion\Jornadas\Services\JornadaRegistroService;
use App\Modules\Extorsion\Registro\Services\RegistroCorreoService;

class RegistroController extends BaseController
{
    public function index(?string $jornada = null)
    {
        if (! $this->jornadaRegistroDisponible($jornada)) {
            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', 'text/html; charset=UTF-8')
                ->setBody($this->mensajeJornadaNoDisponible());
        }

        $sexo = new Sexo_Model();
        $dependencia = new Dependencia_Model();

        $data['sexos'] = $sexo->orderBy('sexo', 'ASC')->findAll();
        $data['dependencias'] = $dependencia->orderBy('id_dependencia', 'ASC')->findAll();

        $data['style'] = 'assets/Css/registro.css';
        $data['jornada'] = $jornada;
        $data['guardarUrl'] = $jornada === null
            ? base_url('registro/guardar')
            : base_url('registro/' . $jornada . '/guardar');
        $data['guardarPersonalUrl'] = $jornada === null
            ? base_url('registro/guardar-personal')
            : base_url('registro/' . $jornada . '/guardar-personal');
        $data['buscarNominaUrl'] = base_url('registro/buscar-nomina');
        $data['exitoUrl'] = base_url('registro/exito' . ($jornada === null ? '' : '?jornada=' . rawurlencode($jornada)));

        return view('head', $data)
            . view('Registro', $data);
    }

    public function guardar(?string $jornada = null)
    {
        if (! $this->jornadaRegistroDisponible($jornada)) {
            return redirect()->to($jornada === null ? '/registro' : '/registro/' . $jornada)
                ->withInput()
                ->with('errors', [
                    'jornada' => 'El registro para esta plática no se encuentra disponible.'
                ]);
        }

        $rules = [
            'nombre' => 'required|max_length[100]',
            'apellido_p' => 'required|max_length[100]',
            'apellido_m' => 'required|max_length[100]',
            'correo' => 'required|valid_email',
            'id_sexo' => 'required|integer',
            'id_dependencia' => 'required|integer',
            'dependencia_otro' => 'permit_empty|max_length[150]',
        ];

        $messages = [
            'nombre' => [
                'required' => 'El campo nombre es obligatorio.',
            ],
            'apellido_p' => [
                'required' => 'El campo apellido paterno es obligatorio.',
            ],
            'apellido_m' => [
                'required' => 'El campo apellido materno es obligatorio.',
            ],
            'correo' => [
                'required' => 'El campo correo es obligatorio.',
            ],
            'id_sexo' => [
                'required' => 'El campo sexo es obligatorio.',
            ],
            'id_dependencia' => [
                'required' => 'El campo dependencia es obligatorio.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();

        $totalRegistros = $db->table('general')->countAll();

        if ($totalRegistros >= 600) {
            return redirect()->back()
                ->withInput()
                ->with('errors', [
                    'limite' => 'El sistema ha alcanzado el límite máximo de 600 registros.'
                ]);
        }

        $dependenciaModel = new Dependencia_Model();
        $dependenciaSeleccionada = $dependenciaModel->find($this->request->getPost('id_dependencia'));

        if (! $dependenciaSeleccionada) {
            return redirect()->back()
                ->withInput()
                ->with('errors', [
                    'dependencia' => 'La dependencia seleccionada no es valida.'
                ]);
        }

        $dependenciaTexto = $dependenciaSeleccionada['dependencia'];

        if (mb_strtolower($dependenciaTexto, 'UTF-8') === 'otro') {
            $dependenciaOtro = trim($this->request->getPost('dependencia_otro'));

            if ($dependenciaOtro === '') {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', [
                        'dependencia_otro' => 'Especifique la dependencia.'
                    ]);
            }

            $dependenciaTexto = $dependenciaOtro;
        }

        $nombreNormalizado = mb_strtoupper($this->sinAcentos(trim($this->request->getPost('nombre'))));
        $apellidoPNormalizado = mb_strtoupper($this->sinAcentos(trim($this->request->getPost('apellido_p'))));
        $apellidoMNormalizado = mb_strtoupper($this->sinAcentos(trim($this->request->getPost('apellido_m'))));
        $correo = trim($this->request->getPost('correo'));
        $correoNormalizado = mb_strtoupper($correo);

        $registroDuplicado = $db->table('general g')
            ->join('dato d', 'g.id_dato = d.id_dato')
            ->groupStart()
                ->where('d.correo', $correoNormalizado)
                ->orGroupStart()
                    ->where('d.nombre', $nombreNormalizado)
                    ->where('d.apellido_p', $apellidoPNormalizado)
                    ->where('d.apellido_m', $apellidoMNormalizado)
                ->groupEnd()
            ->groupEnd()
            ->countAllResults() > 0;

        if ($registroDuplicado) {
            return redirect()->back()
                ->withInput()
                ->with('errors', [
                    'duplicado' => 'Ya existe un registro con esos datos. Si ya se registró previamente, no es necesario volver a enviar el formulario.'
                ]);
        }

        $dato = new Dato_Model();
        $general = new General_Model();
        $idDato = $dato->insert([
            'nombre' => $nombreNormalizado,
            'apellido_p' => $apellidoPNormalizado,
            'apellido_m' => $apellidoMNormalizado,
            'correo' => $correoNormalizado,
        ]);

        $idGeneral = $general->insert([
            'id_dato' => $idDato,
            'id_sexo' => $this->request->getPost('id_sexo'),
            'dependencia' => mb_strtoupper(
                $this->sinAcentos(
                    trim($dependenciaTexto)
                )
            ),
        ]);

        (new RegistroCorreoService())->enviar($correo, 'externo', (int) $idGeneral);

        return redirect()->to('/registro/exito' . ($jornada === null ? '' : '?jornada=' . rawurlencode($jornada)));
    }

    public function exito()
    {
        $jornada = trim((string) $this->request->getGet('jornada'));
        if ($jornada === '' || ! isset($this->jornadasRegistro()[$jornada])) {
            $jornada = null;
        }

        return view('Reg_Exitoso', [
            'formularioUrl' => $jornada === null
                ? base_url('registro')
                : base_url('registro/' . $jornada),
        ]);
    }

    public function listado()
    {
        $db = \Config\Database::connect();
        $buscar = $this->request->getGet('buscar');

        $sql = "
        SELECT 
        d.nombre, 
        d.apellido_p, 
        d.apellido_m,
        d.correo,
        s.sexo,
        g.dependencia,
        g.fecha_registro

        FROM general g

        inner join dato d on g.id_dato = d.id_dato
        inner join sexo s on g.id_sexo = s.id_sexo

                ";

        if (! empty($buscar)) {
            $sql .= " 
            WHERE d.nombre LIKE '%$buscar%'
            OR d.correo LIKE '%$buscar%'
            ";
        }

        $query = $db->query($sql);
        $data['registros'] = $query->getResultArray();

        return view('Listado', $data);
    }

    public function guardarPersonal(?string $jornada = null)
    {
        if (! $this->jornadaRegistroDisponible($jornada)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El registro para esta plática no se encuentra disponible.'
            ]);
        }

        $rules = [
            'nomina' => 'required|integer',
            'correo' => 'required|valid_email'
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos inválidos'
            ]);
        }

        $personal = new Personal_Model();

        $empleado = $personal
            ->where('nomina', $this->request->getPost('nomina'))
            ->first();

        if (! $empleado) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'La nómina no existe'
            ]);
        }

        $db = \Config\Database::connect();
        $nomina = (string) $this->request->getPost('nomina');
        $correoNormalizado = strtoupper(trim((string) $this->request->getPost('correo')));
        $registroDuplicado = $db->table('general_personal')
            ->groupStart()
                ->where('nomina', $nomina)
                ->orWhere('correo', $correoNormalizado)
            ->groupEnd()
            ->countAllResults() > 0;

        if ($registroDuplicado) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ya existe un registro con esos datos. Si ya se registró previamente, no es necesario volver a enviar el formulario.'
            ]);
        }

        $generalPersonal = new \App\Models\General_Personal_Model();

        log_message('error', json_encode([
            'nomina' => $this->request->getPost('nomina'),
            'correo' => $this->request->getPost('correo')
        ]));

        $idGeneralPersonal = $generalPersonal->insert([
            'nomina' => $nomina,
            'correo' => $correoNormalizado
        ]);

        (new RegistroCorreoService())->enviar($this->request->getPost('correo'), 'personal', (int) $idGeneralPersonal);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Registro guardado correctamente'
        ]);
    }

    public function buscarNomina($nomina)
    {
        $personal = new Personal_Model();

        $registro = $personal
            ->select('
            personal.nomina,
            personal.nombre,
            personal.apellido_p,
            personal.apellido_m,
            personal.area,
            personal.funcion,
            sexo.sexo
        ')
            ->join('sexo', 'sexo.id_sexo = personal.id_sexo', 'left')
            ->where('personal.nomina', $nomina)
            ->first();

        if (! $registro) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se encontró la nómina.'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $registro
        ]);
    }

    private function jornadaRegistroDisponible(?string $jornada): bool
    {
        return (new JornadaRegistroService())->disponible($jornada);
    }

    private function jornadasRegistro(): array
    {
        return (new JornadaRegistroService())->jornadas();
    }

    private function mensajeJornadaNoDisponible(): string
    {
        return '<!doctype html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Registro no disponible</title>
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
                <h1>Registro no disponible</h1>
                <p>El registro para esta pl&aacute;tica no se encuentra disponible. Verifique el enlace correspondiente al d&iacute;a de su asistencia.</p>
            </main>
        </body>
        </html>';
    }

    private function sinAcentos($cadena)
    {
        return strtr($cadena, [
            "\u{00E1}" => 'a',
            "\u{00E9}" => 'e',
            "\u{00ED}" => 'i',
            "\u{00F3}" => 'o',
            "\u{00FA}" => 'u',
            "\u{00C1}" => 'A',
            "\u{00C9}" => 'E',
            "\u{00CD}" => 'I',
            "\u{00D3}" => 'O',
            "\u{00DA}" => 'U',
        ]);
    }
}
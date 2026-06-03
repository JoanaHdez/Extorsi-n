<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Dato_Model;
use App\Models\Dependencia_Model;
use App\Models\General_Model;
use App\Models\General_Personal_Model;
use App\Models\Personal_Model;
use App\Models\Sexo_Model;


class Registro_Controller extends BaseController
{
    public function index()
    {

        $sexo = new Sexo_Model();
        $dependencia = new Dependencia_Model();

        $data['sexos'] = $sexo->orderBy('sexo', 'ASC')->findAll();
        $data['dependencias'] = $dependencia->orderBy('id_dependencia', 'ASC')->findAll();

        $data['style'] = 'assets/Css/registro.css';

        return view('head', $data)
            .   view('Registro', $data);
    }

    public function guardar()
    {
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

        if (!$this->validate($rules, $messages)) {

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

        if (!$dependenciaSeleccionada) {
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

        $correo = trim($this->request->getPost('correo'));

        $dato = new Dato_Model();
        $general = new General_Model();
        $idDato = $dato->insert([
            'nombre' => mb_strtoupper($this->sinAcentos(trim($this->request->getPost('nombre')))),
            'apellido_p' => mb_strtoupper($this->sinAcentos(trim($this->request->getPost('apellido_p')))),
            'apellido_m' => mb_strtoupper($this->sinAcentos(trim($this->request->getPost('apellido_m')))),
            'correo' => mb_strtoupper($correo),
        ]);

        $general->insert([
            'id_dato' => $idDato,
            'id_sexo' => $this->request->getPost('id_sexo'),

            'dependencia' => mb_strtoupper(
                $this->sinAcentos(
                    trim($dependenciaTexto)
                )
            ),

        ]);
        $this->enviarCorreoRegistro($correo);

        return redirect()->to('/registro/exito');
    }

    public function exito()
    {
        return view('Reg_Exitoso');
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

        if (!empty($buscar)) {
            $sql .= " 
            WHERE d.nombre LIKE '%$buscar%'
            OR d.correo LIKE '%$buscar%'
            ";
        }

        $query = $db->query($sql);
        $data['registros'] = $query->getResultArray();
        return view('Listado', $data);
    }

    public function guardarPersonal()
    {

        $rules = [
            'nomina' => 'required|integer',
            'correo' => 'required|valid_email'
        ];

        if (!$this->validate($rules)) {

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos inválidos'
            ]);
        }

        $personal = new Personal_Model();

        $empleado = $personal
            ->where('nomina', $this->request->getPost('nomina'))
            ->first();

        if (!$empleado) {

            return $this->response->setJSON([
                'success' => false,
                'message' => 'La nómina no existe'
            ]);
        }

        $generalPersonal = new \App\Models\General_Personal_Model();

        log_message('error', json_encode([
            'nomina' => $this->request->getPost('nomina'),
            'correo' => $this->request->getPost('correo')
        ]));

        $generalPersonal->insert([
            'nomina' => $this->request->getPost('nomina'),
            'correo' => strtoupper(trim($this->request->getPost('correo')))
        ]);

        $this->enviarCorreoRegistro($this->request->getPost('correo'));

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

        if (!$registro) {
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

    public function reporte()
    {
        $db = \Config\Database::connect();
        $dependenciaModel = new Dependencia_Model();
        $personalTieneFecha = $db->fieldExists('fecha_registro', 'general_personal');
        $fechaPersonal = $personalTieneFecha ? 'DATE(gp.fecha_registro)' : 'NULL';
        $fechaRegistroPersonal = $personalTieneFecha ? 'gp.fecha_registro' : 'NULL';

        $total = $db->query("
        SELECT
            (
                SELECT COUNT(*)
                FROM general
            ) +
            (
                SELECT COUNT(*)
                FROM general_personal
            ) AS total");

        $dias = $db->query("
        SELECT fecha, COUNT(*) AS total
        FROM (
            SELECT DATE(g.fecha_registro) AS fecha
            FROM general g

            UNION ALL

            SELECT {$fechaPersonal} AS fecha
            FROM general_personal gp
        ) registros
        WHERE fecha IS NOT NULL
        GROUP BY fecha
        ORDER BY fecha
        ");

        $registros = $db->query("
        SELECT 
            d.nombre,
            d.apellido_p,
            d.apellido_m,
            d.correo,
            'Externo' AS tipo_registro,
            '' AS area,
            g.dependencia
        FROM general g
        INNER JOIN dato d ON g.id_dato = d.id_dato

        UNION ALL

        SELECT
            p.nombre,
            p.apellido_p,
            p.apellido_m,
            gp.correo,
            'Comisaria' AS tipo_registro,
            p.area,
            '' AS dependencia
        FROM general_personal gp
        INNER JOIN personal p ON gp.nomina = p.nomina
        ");

        $dashboard = $db->query("
        SELECT
            g.id_general,
            s.sexo,
            DATE(g.fecha_registro) AS fecha,
            g.fecha_registro,
            'Externo' AS tipo_registro,
            '' AS area,
            '' AS funcion,
            g.dependencia
        FROM general g
        INNER JOIN sexo s ON g.id_sexo = s.id_sexo

        UNION ALL

        SELECT
            gp.id_general_personal AS id_general,
            s.sexo,
            {$fechaPersonal} AS fecha,
            {$fechaRegistroPersonal} AS fecha_registro,
            'Comisaria' AS tipo_registro,
            p.area,
            p.funcion,
            '' AS dependencia
        FROM general_personal gp
        INNER JOIN personal p ON gp.nomina = p.nomina
        LEFT JOIN sexo s ON p.id_sexo = s.id_sexo
                ");

        $data['total'] = $total->getRow()->total;
        $data['dias'] = $dias->getResultArray();
        $data['registros'] = $registros->getResultArray();
        $data['dashboard'] = $dashboard->getResultArray();
        $data['dependencias'] = $dependenciaModel->orderBy('id_dependencia', 'ASC')->findAll();

        $data['style'] = 'assets/Css/reporte.css';

        return view('head', $data)
            . view('Reporte', $data);
    }

    public function exportar()
    {
        $db = \Config\Database::connect();

        $query = $db->query("
        SELECT 
        d.nombre, 
        d.apellido_p, 
        d.apellido_m,
        d.correo,
        s.sexo,
        g.dependencia,
                g.fecha_registro

        FROM general g

        INNER JOIN dato d ON g.id_dato = d.id_dato
        INNER JOIN sexo s ON g.id_sexo = s.id_sexo");


        $registros = $query->getResultArray();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=registro.csv');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Correo',
            'Sexo',
            'Dependencia',
            'Fecha de Registro'
        ]);

        foreach ($registros as $fila) {
            fputcsv($output, [
                $fila['nombre'],
                $fila['apellido_p'],
                $fila['apellido_m'],
                $fila['correo'],
                $fila['sexo'],
                $fila['dependencia'],
                $fila['fecha_registro']
            ]);
        }

        fclose($output);
        exit;
    }

    private function enviarCorreoRegistro(string $correo): void
    {
        $config = config('Email');

        if ($config->SMTPHost === '' || $config->fromEmail === '') {
            log_message('info', 'Correo de registro no enviado: SMTP no configurado.');
            return;
        }

        $ligaConstancia = env('registro.constanciaUrl') ?: base_url('registro/exito');

        $email = \Config\Services::email();
        $email->setFrom($config->fromEmail, $config->fromName ?: $config->fromEmail);
        $email->setTo($correo);
        $email->setSubject('Registro exitoso');
        $email->setMessage(
            "Su registro fue exitoso.\n\n" .
            "Podra descargar su constancia en la siguiente liga:\n" .
            $ligaConstancia . "\n"
        );

        if (!$email->send(false)) {
            log_message('error', 'No se pudo enviar correo de registro: ' . print_r($email->printDebugger(['headers']), true));
        }
    }

    private function sinAcentos($cadena)
    {
        $buscar = ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'];
        return str_replace($buscar, $reemplazar, $cadena);
    }
}

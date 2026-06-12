<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Dato_Model;
use App\Models\Dependencia_Model;
use App\Models\General_Model;
use App\Models\General_Personal_Model;
use App\Models\Personal_Model;
use App\Models\Sexo_Model;
use Dompdf\Dompdf;
use Dompdf\Options;


class Registro_Controller extends BaseController
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
            .   view('Registro', $data);
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
        $this->enviarCorreoRegistro($correo, 'externo', (int) $idGeneral);

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

        $this->enviarCorreoRegistro($this->request->getPost('correo'), 'personal', (int) $idGeneralPersonal);

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
            g.dependencia,
            g.fecha_registro
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
            '' AS dependencia,
            {$fechaRegistroPersonal} AS fecha_registro
        FROM general_personal gp
        INNER JOIN personal p ON gp.nomina = p.nomina
        ORDER BY fecha_registro ASC
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
        ORDER BY fecha_registro ASC
                ");

        $data['total'] = $total->getRow()->total;
        $data['dias'] = $dias->getResultArray();
        $data['registros'] = $registros->getResultArray();
        $data['dashboard'] = $dashboard->getResultArray();
        $data['dependencias'] = $dependenciaModel->orderBy('id_dependencia', 'ASC')->findAll();
        $data['cuestionarioResumen'] = $this->resumenCuestionarioConstancia();
        $totalesCuestionario = array_column($data['cuestionarioResumen'], 'total_respuestas');
        $data['cuestionarioTotal'] = empty($totalesCuestionario) ? 0 : max($totalesCuestionario);
        $data['mostrarCuestionarioDashboard'] = filter_var(env('registro.mostrarCuestionarioDashboard', false), FILTER_VALIDATE_BOOLEAN);

        $data['style'] = 'assets/Css/reporte.css';

        return view('head', $data)
            . view('Reporte', $data);
    }

    public function reporteCuestionario()
    {
        $fechaFiltro = trim((string) $this->request->getGet('dia'));
        if ($fechaFiltro !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFiltro)) {
            $fechaFiltro = '';
        }

        $data['cuestionarioResumen'] = $this->resumenCuestionarioConstancia($fechaFiltro ?: null);
        $totalesCuestionario = array_column($data['cuestionarioResumen'], 'total_respuestas');
        $data['cuestionarioTotal'] = empty($totalesCuestionario) ? 0 : max($totalesCuestionario);
        $data['cuestionarioDias'] = $this->diasCuestionarioConstancia();
        $data['cuestionarioFechaFiltro'] = $fechaFiltro;
        $data['style'] = 'assets/Css/reporte.css';

        return view('head', $data)
            . view('ReporteCuestionario', $data);
    }

    public function exportar()
    {
        $db = \Config\Database::connect();
        $personalTieneFecha = $db->fieldExists('fecha_registro', 'general_personal');
        $fechaRegistroPersonal = $personalTieneFecha ? 'gp.fecha_registro' : 'NULL';

        $query = $db->query("
            SELECT
                d.nombre,
                d.apellido_p,
                d.apellido_m,
                d.correo,
                s.sexo,
                'Externo' AS tipo_registro,
                '' AS area,
                g.dependencia,
                g.fecha_registro
            FROM general g
            INNER JOIN dato d ON g.id_dato = d.id_dato
            LEFT JOIN sexo s ON g.id_sexo = s.id_sexo

            UNION ALL

            SELECT
                p.nombre,
                p.apellido_p,
                p.apellido_m,
                gp.correo,
                s.sexo,
                'Comisaria' AS tipo_registro,
                p.area,
                '' AS dependencia,
                {$fechaRegistroPersonal} AS fecha_registro
            FROM general_personal gp
            INNER JOIN personal p ON gp.nomina = p.nomina
            LEFT JOIN sexo s ON p.id_sexo = s.id_sexo

            ORDER BY fecha_registro ASC
        ");

        $registros = $query->getResultArray();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=registros.csv');

        $output = fopen('php://output', 'w');

        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, [
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Correo',
            'Sexo',
            'Tipo',
            'Area',
            'Dependencia',
            'Fecha de Registro'
        ], ';');

        foreach ($registros as $fila) {
            fputcsv($output, [
                $fila['nombre'],
                $fila['apellido_p'],
                $fila['apellido_m'],
                $fila['correo'],
                $fila['sexo'],
                $fila['tipo_registro'],
                $fila['area'],
                $fila['dependencia'],
                $fila['fecha_registro']
            ], ';');
        }

        fclose($output);
        exit;
    }

    public function exportarComentariosCuestionario()
    {
        $fechaFiltro = trim((string) $this->request->getGet('dia'));
        if ($fechaFiltro !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFiltro)) {
            $fechaFiltro = '';
        }

        $db = \Config\Database::connect();
        $this->asegurarTablaCuestionario($db);

        $filas = $db->table('cuestionario_constancia')
            ->select('tipo_registro, id_registro, respuestas, fecha_respuesta')
            ->orderBy('fecha_respuesta', 'ASC')
            ->get()
            ->getResultArray();

        if ($fechaFiltro !== '') {
            $filas = array_values(array_filter($filas, static function (array $fila) use ($fechaFiltro): bool {
                return substr((string) ($fila['fecha_respuesta'] ?? ''), 0, 10) === $fechaFiltro;
            }));
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=comentarios-cuestionario.csv');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, [
            'Fecha de respuesta',
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Correo',
            'Tipo de registro',
            'Comentario',
        ], ';');

        foreach ($filas as $fila) {
            $respuestas = json_decode((string) ($fila['respuestas'] ?? ''), true);

            if (! is_array($respuestas)) {
                continue;
            }

            $comentario = trim((string) ($respuestas['comentarios'] ?? ''));

            if ($comentario === '') {
                continue;
            }

            $registro = $this->obtenerRegistroConstancia(
                (string) ($fila['tipo_registro'] ?? ''),
                (int) ($fila['id_registro'] ?? 0)
            ) ?? [];

            fputcsv($output, [
                $fila['fecha_respuesta'] ?? '',
                $registro['nombre'] ?? '',
                $registro['apellido_p'] ?? '',
                $registro['apellido_m'] ?? '',
                $registro['correo'] ?? '',
                $registro['tipo_registro'] ?? ($fila['tipo_registro'] ?? ''),
                $comentario,
            ], ';');
        }

        fclose($output);
        exit;
    }

    public function constancia(string $token)
    {
        $datosToken = $this->validarTokenConstancia($token);

        if ($datosToken === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Constancia no valida.');
        }

        $registro = $this->obtenerRegistroConstancia($datosToken['tipo'], (int) $datosToken['id']);

        if ($registro === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Registro no encontrado.');
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
            return view('CuestionarioConstancia', [
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
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Constancia no valida.');
        }

        $registro = $this->obtenerRegistroConstancia($datosToken['tipo'], (int) $datosToken['id']);

        if ($registro === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Registro no encontrado.');
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

        return view('CuestionarioExito', [
            'downloadUrl' => base_url('constancia/' . $token),
        ]);
    }

    private function descargarConstanciaPdf(array $registro, string $plantillaPath)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $html = view('ConstanciaPdf', [
            'registro' => $registro,
            'plantilla' => $this->imageDataUri($plantillaPath, 'image/png'),
        ]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->render();

        $nombreArchivo = 'constancia-' . strtolower($registro['folio']) . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"')
            ->setBody($dompdf->output());
    }

    private function preguntasCuestionarioConstancia(): array
    {
        $escala = ['1', '2', '3', '4', '5'];

        return [
            [
                'id' => 'pregunta_1',
                'texto' => '¿Qué te pareció la Plática de Medidas Preventivas en Caso de Extorsión?',
                'tipo' => 'radio',
                'opciones' => $escala,
                'required' => true,
            ],
            [
                'id' => 'pregunta_2',
                'texto' => '¿El tema de la conferencia fue interesante para ti?',
                'tipo' => 'radio',
                'opciones' => $escala,
                'required' => true,
            ],
            [
                'id' => 'pregunta_3',
                'texto' => '¿La información presentada fue clara y fácil de entender?',
                'tipo' => 'radio',
                'opciones' => $escala,
                'required' => true,
            ],
            [
                'id' => 'pregunta_4',
                'texto' => '¿El ponente explicó el tema de manera adecuada?',
                'tipo' => 'radio',
                'opciones' => $escala,
                'required' => true,
            ],
            [
                'id' => 'pregunta_5',
                'texto' => '¿La conferencia mantuvo tu atención durante la mayor parte del tiempo?',
                'tipo' => 'radio',
                'opciones' => $escala,
                'required' => true,
            ],
            [
                'id' => 'comentarios',
                'texto' => 'Comentarios adicionales',
                'tipo' => 'textarea',
                'required' => false,
            ],
        ];
    }

    private function cuestionarioConstanciaHabilitado(): bool
    {
        return filter_var(env('registro.cuestionarioConstanciaHabilitado', false), FILTER_VALIDATE_BOOLEAN);
    }

    private function cuestionarioConstanciaRespondido(string $tipoRegistro, int $idRegistro): bool
    {
        $db = \Config\Database::connect();
        $this->asegurarTablaCuestionario($db);

        return $db->table('cuestionario_constancia')
            ->where('tipo_registro', $tipoRegistro)
            ->where('id_registro', $idRegistro)
            ->countAllResults() > 0;
    }

    private function guardarRespuestasCuestionario(string $tipoRegistro, int $idRegistro, array $respuestas): void
    {
        $db = \Config\Database::connect();
        $this->asegurarTablaCuestionario($db);

        $limpias = [];

        foreach ($this->preguntasCuestionarioConstancia() as $pregunta) {
            $id = $pregunta['id'];
            $valor = $respuestas[$id] ?? '';
            $limpias[$id] = is_array($valor) ? implode(', ', $valor) : trim((string) $valor);
        }

        $existente = $db->table('cuestionario_constancia')
            ->select('id_cuestionario')
            ->where('tipo_registro', $tipoRegistro)
            ->where('id_registro', $idRegistro)
            ->get()
            ->getRowArray();

        $datos = [
            'tipo_registro' => $tipoRegistro,
            'id_registro' => $idRegistro,
            'respuestas' => json_encode($limpias, JSON_UNESCAPED_UNICODE),
        ];

        if ($existente) {
            $db->table('cuestionario_constancia')
                ->where('id_cuestionario', $existente['id_cuestionario'])
                ->update($datos);
            return;
        }

        $db->table('cuestionario_constancia')->insert($datos);
    }

    private function asegurarTablaCuestionario($db): void
    {
        $db->query("
            CREATE TABLE IF NOT EXISTS cuestionario_constancia (
                id_cuestionario INT AUTO_INCREMENT PRIMARY KEY,
                tipo_registro VARCHAR(20) NOT NULL,
                id_registro INT NOT NULL,
                respuestas LONGTEXT NOT NULL,
                fecha_respuesta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_cuestionario_registro (tipo_registro, id_registro)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function resumenCuestionarioConstancia(?string $fecha = null): array
    {
        $db = \Config\Database::connect();
        $this->asegurarTablaCuestionario($db);

        $builder = $db->table('cuestionario_constancia')
            ->select('respuestas, fecha_respuesta')
            ->orderBy('fecha_respuesta', 'ASC');

        $filas = $builder->get()->getResultArray();

        if ($fecha !== null && $fecha !== '') {
            $filas = array_values(array_filter($filas, static function (array $fila) use ($fecha): bool {
                return substr((string) ($fila['fecha_respuesta'] ?? ''), 0, 10) === $fecha;
            }));
        }

        $resumen = [];

        foreach ($this->preguntasCuestionarioConstancia() as $pregunta) {
            $id = $pregunta['id'];
            $resumen[$id] = [
                'id' => $id,
                'texto' => $pregunta['texto'],
                'tipo' => $pregunta['tipo'] ?? 'text',
                'conteos' => [],
                'respuestas_abiertas' => [],
                'total_respuestas' => 0,
            ];

            foreach (($pregunta['opciones'] ?? []) as $opcion) {
                $resumen[$id]['conteos'][$opcion] = 0;
            }
        }

        foreach ($filas as $fila) {
            $respuestas = json_decode((string) $fila['respuestas'], true);

            if (! is_array($respuestas)) {
                continue;
            }

            foreach ($resumen as $id => &$preguntaResumen) {
                $valor = trim((string) ($respuestas[$id] ?? ''));

                if ($valor === '') {
                    continue;
                }

                $preguntaResumen['total_respuestas']++;

                if ($preguntaResumen['tipo'] === 'textarea') {
                    $preguntaResumen['respuestas_abiertas'][] = $valor;
                    continue;
                }

                if (! array_key_exists($valor, $preguntaResumen['conteos'])) {
                    $preguntaResumen['conteos'][$valor] = 0;
                }

                $preguntaResumen['conteos'][$valor]++;
            }
            unset($preguntaResumen);
        }

        return array_values($resumen);
    }

    private function diasCuestionarioConstancia(): array
    {
        $db = \Config\Database::connect();
        $this->asegurarTablaCuestionario($db);

        return $db->table('cuestionario_constancia')
            ->select('DATE(fecha_respuesta) AS fecha, COUNT(*) AS total', false)
            ->groupBy('DATE(fecha_respuesta)')
            ->orderBy('fecha', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function controlConstancias()
    {
        $token = (string) $this->request->getGet('token');

        if (! $this->tokenControlValido($token)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Panel no encontrado.');
        }

        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setBody($this->vistaControlConstancias($token));
    }

    public function actualizarControlConstancias()
    {
        $token = (string) $this->request->getPost('token');

        if (! $this->tokenControlValido($token)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Panel no encontrado.');
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

    private function enviarCorreoRegistro(string $correo, string $tipoRegistro, int $idRegistro): void
    {
        $ligaConstancia = base_url('constancia/' . $this->generarTokenConstancia($tipoRegistro, $idRegistro));
        $registroCorreo = $this->obtenerRegistroConstancia($tipoRegistro, $idRegistro);
        $nombreCompleto = $registroCorreo === null
            ? ''
            : trim(($registroCorreo['nombre'] ?? '') . ' ' . ($registroCorreo['apellido_p'] ?? '') . ' ' . ($registroCorreo['apellido_m'] ?? ''));

        $logoAyuntamientoPath = FCPATH . 'assets/img/ayun.png';
        $logoComisariaPath = FCPATH . 'assets/img/comisaria.png';
        $iconoExitoPath = FCPATH . 'assets/img/bien.png';

        $email = \Config\Services::email();
        $smtpPassB64 = env('email.SMTPPassB64');
        $smtpPass = $smtpPassB64 !== null && $smtpPassB64 !== ''
            ? (base64_decode($smtpPassB64) ?: '')
            : env('email.SMTPPass', '');

        $emailConfig = [
            'protocol'    => env('email.protocol', 'smtp'),
            'SMTPHost'    => env('email.SMTPHost'),
            'SMTPUser'    => env('email.SMTPUser'),
            'SMTPPass'    => $smtpPass,
            'SMTPPort'    => (int) env('email.SMTPPort'),
            'SMTPTimeout' => (int) env('email.SMTPTimeout', 10),
            'SMTPCrypto'  => env('email.SMTPCrypto', ''),
            'mailType'    => env('email.mailType', 'html'),
            'charset'     => env('email.charset', 'UTF-8'),
            'newline'     => "\r\n",
            'CRLF'        => "\r\n",
        ];

        $email->initialize($emailConfig);

        $email->setFrom(
            env('email.fromEmail'),
            env('email.fromName')
        );

        $email->setTo($correo);
        $email->setSubject('Registro exitoso');

        $email->attach($logoAyuntamientoPath, 'inline');
        $email->attach($logoComisariaPath, 'inline');
        $email->attach($iconoExitoPath, 'inline');

        $logoAyuntamiento = $email->setAttachmentCID($logoAyuntamientoPath);
        $logoComisaria = $email->setAttachmentCID($logoComisariaPath);
        $iconoExito = $email->setAttachmentCID($iconoExitoPath);

        $logoAyuntamientoSrc = $logoAyuntamiento ? 'cid:' . $logoAyuntamiento : base_url('assets/img/ayun.png');
        $logoComisariaSrc = $logoComisaria ? 'cid:' . $logoComisaria : base_url('assets/img/comisaria.png');
        $iconoExitoSrc = $iconoExito ? 'cid:' . $iconoExito : base_url('assets/img/bien.png');

        $mensaje = '
            <!doctype html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Registro exitoso</title>
            </head>
            <body style="margin:0; padding:0; background:#eef2f7; font-family:Arial, Helvetica, sans-serif; color:#1f2933;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#eef2f7; padding:28px 12px;">
                    <tr>
                        <td align="center">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:660px; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 14px 34px rgba(20, 36, 64, 0.14);">
                                <tr>
                                    <td style="height:8px; background:#8a1538; line-height:8px; font-size:1px;">&nbsp;</td>
                                </tr>

                                <tr>
                                    <td style="background:#ffffff; padding:18px 30px; border-bottom:1px solid #e6ebf2;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td align="left" width="50%" style="vertical-align:middle;">
                                                    <img src="' . esc($logoAyuntamientoSrc, 'attr') . '" alt="Ayuntamiento" width="132" style="display:block; width:132px; max-width:132px; height:auto; border:0;">
                                                </td>
                                                <td align="right" width="50%" style="vertical-align:middle;">
                                                    <img src="' . esc($logoComisariaSrc, 'attr') . '" alt="Comisaría" width="128" style="display:block; width:128px; max-width:128px; height:auto; border:0;">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="background:#243b6b; padding:34px 36px 34px; text-align:center;">
                                        <p style="margin:0; color:#ffffff; font-size:22px; line-height:1.35; font-weight:700;">
                                            Pl&aacute;tica de Medidas Preventivas en Casos de Extorsi&oacute;n
                                        </p>
                                        <p style="margin:8px 0 22px; color:#dbe7ff; font-size:16px; line-height:1.45; font-weight:700;">
                                            Comisar&iacute;a General de Seguridad Ciudadana
                                        </p>
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:0 auto 18px;">
                                            <tr>
                                                <td style="background:#ffffff; border-radius:50%; padding:12px;">
                                                    <img src="' . esc($iconoExitoSrc, 'attr') . '" alt="" width="54" style="display:block; width:54px; height:auto; border:0;">
                                                </td>
                                            </tr>
                                        </table>
                                        <h1 style="margin:0; color:#ffffff; font-size:30px; line-height:1.25; font-weight:700;">
                                            Registro exitoso
                                        </h1>
                                        <p style="margin:12px 0 0; color:#dbe7ff; font-size:17px; line-height:1.5;">
                                            Su asistencia quedó registrada correctamente.
                                        </p>
                                        <p style="margin:18px 0 0; color:#ffffff; font-size:20px; line-height:1.35; font-weight:700; text-transform:uppercase;">
                                            ' . esc($nombreCompleto) . '
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:34px 38px 8px;">
                                        <p style="margin:0 0 22px; color:#344054; font-size:22px; line-height:1.4; font-weight:700; text-align:center;">
                                            Gracias por registrarse
                                        </p>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f8fafc; border:1px solid #e6ebf2; border-radius:10px;">
                                            <tr>
                                                <td style="padding:18px 20px;">
                                                    <p style="margin:0; color:#243b6b; font-size:14px; line-height:1.5; font-weight:700; text-transform:uppercase;">
                                                        Próximo paso
                                                    </p>
                                                    <p style="margin:6px 0 0; color:#344054; font-size:16px; line-height:1.6;">
                                                        Conserve este correo para poder descargar su constancia al concluir el evento.
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin:22px 0 0; color:#344054; font-size:16px; line-height:1.65;">
                                            Puede descargar su constancia desde el siguiente enlace:
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="center" style="padding:24px 38px 34px;">
                                        <a href="' . esc($ligaConstancia, 'attr') . '" style="display:inline-block; background:#8a1538; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; padding:15px 30px; border-radius:8px;">
                                            Descargar constancia
                                        </a>
                                        <p style="margin:18px 0 0; color:#667085; font-size:13px; line-height:1.5;">
                                            Si el botón no funciona, copie y pegue esta liga en su navegador:
                                        </p>
                                        <p style="margin:8px 0 0; color:#243b6b; font-size:13px; line-height:1.5; word-break:break-all;">
                                            <a href="' . esc($ligaConstancia, 'attr') . '" style="color:#243b6b; text-decoration:underline;">' . esc($ligaConstancia) . '</a>
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="background:#f8fafc; padding:22px 34px; border-top:1px solid #e5e9ef; text-align:center;">
                                        <p style="margin:0; color:#667085; font-size:12px; line-height:1.5;">
                                            Secretaría de Seguridad y Protección Ciudadana
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
        ';

        $email->setMessage($mensaje);

        try {
            if (! $email->send()) {
                log_message(
                    'error',
                    'ERROR ENVIO CORREO: protocol=' . $emailConfig['protocol']
                        . ' host=' . ($emailConfig['SMTPHost'] ?: 'N/A')
                        . ' port=' . ($emailConfig['SMTPPort'] ?: 'N/A')
                        . ' crypto=' . ($emailConfig['SMTPCrypto'] ?: 'N/A')
                        . ' detalle=' . $email->printDebugger(['headers', 'subject'])
                );
            }
        } catch (\Throwable $exception) {
            log_message(
                'error',
                'ERROR ENVIO CORREO EXCEPTION: protocol=' . $emailConfig['protocol']
                    . ' host=' . ($emailConfig['SMTPHost'] ?: 'N/A')
                    . ' port=' . ($emailConfig['SMTPPort'] ?: 'N/A')
                    . ' crypto=' . ($emailConfig['SMTPCrypto'] ?: 'N/A')
                    . ' detalle=' . $exception->getMessage()
            );
        }
    }

    private function obtenerRegistroConstancia(string $tipo, int $id): ?array
    {
        $db = \Config\Database::connect();

        if ($tipo === 'externo') {
            $registro = $db->table('general g')
                ->select("
                    g.id_general,
                    d.nombre,
                    d.apellido_p,
                    d.apellido_m,
                    d.correo,
                    s.sexo,
                    g.dependencia,
                    g.fecha_registro,
                    'Externo' AS tipo_registro,
                    '' AS nomina,
                    '' AS area,
                    '' AS funcion
                ")
                ->join('dato d', 'd.id_dato = g.id_dato')
                ->join('sexo s', 's.id_sexo = g.id_sexo', 'left')
                ->where('g.id_general', $id)
                ->get()
                ->getRowArray();

            if (! $registro) {
                return null;
            }

            $registro['folio'] = 'EXT-' . str_pad((string) $registro['id_general'], 5, '0', STR_PAD_LEFT);

            return $registro;
        }

        if ($tipo === 'personal') {
            $personalTieneFecha = $db->fieldExists('fecha_registro', 'general_personal');
            $fechaSelect = $personalTieneFecha ? 'gp.fecha_registro' : 'NULL AS fecha_registro';

            $registro = $db->table('general_personal gp')
                ->select("
                    gp.id_general_personal AS id_general,
                    p.nomina,
                    p.nombre,
                    p.apellido_p,
                    p.apellido_m,
                    gp.correo,
                    s.sexo,
                    p.area,
                    p.funcion,
                    {$fechaSelect},
                    'Comisaría' AS tipo_registro,
                    '' AS dependencia
                ")
                ->join('personal p', 'p.nomina = gp.nomina')
                ->join('sexo s', 's.id_sexo = p.id_sexo', 'left')
                ->where('gp.id_general_personal', $id)
                ->get()
                ->getRowArray();

            if (! $registro) {
                return null;
            }

            $registro['folio'] = 'COM-' . str_pad((string) $registro['id_general'], 5, '0', STR_PAD_LEFT);

            return $registro;
        }

        return null;
    }

    private function generarTokenConstancia(string $tipo, int $id): string
    {
        $payload = $this->base64UrlEncode(json_encode([
            'tipo' => $tipo,
            'id' => $id,
        ]));

        $firma = $this->base64UrlEncode(hash_hmac('sha256', $payload, $this->claveConstancia(), true));

        return $payload . '.' . $firma;
    }

    private function validarTokenConstancia(string $token): ?array
    {
        $partes = explode('.', $token, 2);

        if (count($partes) !== 2) {
            return null;
        }

        [$payload, $firma] = $partes;
        $firmaEsperada = $this->base64UrlEncode(hash_hmac('sha256', $payload, $this->claveConstancia(), true));

        if (! hash_equals($firmaEsperada, $firma)) {
            return null;
        }

        $datos = json_decode($this->base64UrlDecode($payload), true);

        if (! is_array($datos) || empty($datos['tipo']) || empty($datos['id'])) {
            return null;
        }

        if (! in_array($datos['tipo'], ['externo', 'personal'], true)) {
            return null;
        }

        return $datos;
    }

    private function claveConstancia(): string
    {
        return env('encryption.key')
            ?: env('email.SMTPPassB64')
            ?: 'ExtorsionF-constancia';
    }

    private function jornadaRegistroDisponible(?string $jornada): bool
    {
        $jornadaActiva = $this->jornadaActiva();

        if ($jornadaActiva === '') {
            return false;
        }

        if ($jornada === null || $jornada === '') {
            return false;
        }

        return isset($this->jornadasRegistro()[$jornada]) && hash_equals($jornadaActiva, $jornada);
    }

    private function jornadasRegistro(): array
    {
        return [
            '09-junio' => '2026-06-09',
            '10-junio' => '2026-06-10',
            '17-junio' => '2026-06-17',
            '12-junio' => '2026-06-12',
        ];
    }

    private function jornadaActiva(): string
    {
        $estadoPath = $this->estadoJornadaPath();

        if (is_file($estadoPath)) {
            $estado = json_decode((string) file_get_contents($estadoPath), true);

            if (is_array($estado) && array_key_exists('jornada', $estado)) {
                return (string) $estado['jornada'];
            }
        }

        return trim((string) env('registro.jornadaActiva', ''));
    }

    private function guardarJornadaActiva(string $jornada): void
    {
        if ($jornada !== '' && ! isset($this->jornadasRegistro()[$jornada])) {
            return;
        }

        file_put_contents(
            $this->estadoJornadaPath(),
            json_encode(['jornada' => $jornada], JSON_PRETTY_PRINT)
        );
    }

    private function estadoJornadaPath(): string
    {
        return WRITEPATH . 'registro_jornada.json';
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

    private function constanciasHabilitadas(): bool
    {
        $estadoPath = $this->estadoConstanciasPath();

        if (is_file($estadoPath)) {
            $estado = json_decode((string) file_get_contents($estadoPath), true);

            if (is_array($estado) && array_key_exists('habilitadas', $estado)) {
                return (bool) $estado['habilitadas'];
            }
        }

        return filter_var(env('registro.constanciasHabilitadas', false), FILTER_VALIDATE_BOOLEAN);
    }

    private function guardarEstadoConstancias(bool $habilitadas): void
    {
        file_put_contents(
            $this->estadoConstanciasPath(),
            json_encode(['habilitadas' => $habilitadas], JSON_PRETTY_PRINT)
        );
    }

    private function estadoConstanciasPath(): string
    {
        return WRITEPATH . 'constancias_estado.json';
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
        $plantillaPorFecha = [
            '2026-06-09' => 'junio_09.png',
            '2026-06-10' => 'junio_10.png',
            '2026-06-17' => 'junio_17.png',
            '2026-06-12' => 'junio_12.png',
        ];

        if (empty($registro['fecha_registro'])) {
            return null;
        }

        $fecha = date('Y-m-d', strtotime($registro['fecha_registro']));

        if (! isset($plantillaPorFecha[$fecha])) {
            return null;
        }

        $archivo = $plantillaPorFecha[$fecha];

        $archivo = basename((string) $archivo);
        $plantillaPath = FCPATH . 'assets/img/' . $archivo;

        return is_file($plantillaPath) ? $plantillaPath : null;
    }

    private function base64UrlEncode(string $valor): string
    {
        return rtrim(strtr(base64_encode($valor), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $valor): string
    {
        return base64_decode(strtr($valor, '-_', '+/')) ?: '';
    }

    private function imageDataUri(string $path, string $mime): string
    {
        if (! is_file($path)) {
            return '';
        }

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    private function sinAcentos($cadena)
    {
        $cadena = str_replace(['ñ', 'Ñ'], ['__ENE_MINUS__', '__ENE_MAYUS__'], $cadena);
        $buscar = ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'];
        return str_replace(
            ['__ENE_MINUS__', '__ENE_MAYUS__'],
            ['ñ', 'Ñ'],
            str_replace($buscar, $reemplazar, $cadena)
        );
    }
}

<?php

namespace App\Modules\Extorsion\Reportes\Controllers;

use App\Controllers\BaseController;
use App\Models\Dependencia_Model;
use App\Modules\Extorsion\Constancias\Services\ConstanciaRegistroService;
use App\Modules\Extorsion\Constancias\Services\CuestionarioConstanciaService;

class ReportesController extends BaseController
{
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
            . view('App\Modules\Extorsion\Constancias\Views\ReporteCuestionario', $data);
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
        (new CuestionarioConstanciaService())->asegurarTabla($db);

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

            $registro = (new ConstanciaRegistroService())->obtener(
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

    private function resumenCuestionarioConstancia(?string $fecha = null): array
    {
        return (new CuestionarioConstanciaService())->resumen($fecha);
    }

    private function diasCuestionarioConstancia(): array
    {
        return (new CuestionarioConstanciaService())->dias();
    }
}
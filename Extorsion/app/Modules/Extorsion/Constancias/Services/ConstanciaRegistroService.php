<?php

namespace App\Modules\Extorsion\Constancias\Services;

class ConstanciaRegistroService
{
    public function obtener(string $tipo, int $id): ?array
    {
        $db = \Config\Database::connect();

        if ($tipo === 'externo') {
            return $this->obtenerExterno($db, $id);
        }

        if ($tipo === 'personal') {
            return $this->obtenerPersonal($db, $id);
        }

        return null;
    }

    public function plantillaPath(array $registro): ?string
    {
        $plantillaPorFecha = [
            '2026-06-09' => 'junio_09.png',
            '2026-06-10' => 'junio_10.png',
            '2026-06-17' => 'junio_17.png',
            '2026-06-12' => 'junio_12.png',
        ];

        $archivo = 'junio_17.png';

        if (! empty($registro['fecha_registro'])) {
            $fecha = date('Y-m-d', strtotime($registro['fecha_registro']));
            $archivo = $plantillaPorFecha[$fecha] ?? $archivo;
        }

        $archivo = basename((string) $archivo);
        $plantillaPath = FCPATH . 'assets/img/' . $archivo;

        return is_file($plantillaPath) ? $plantillaPath : null;
    }

    private function obtenerExterno($db, int $id): ?array
    {
        $registro = $this->consultaExterno($db)
            ->where('g.id_general', $id)
            ->get()
            ->getRowArray();

        if (! $registro) {
            $registro = $this->consultaExterno($db)
                ->where('d.id_dato', $id)
                ->get()
                ->getRowArray();
        }

        if (! $registro) {
            return null;
        }

        $registro['folio'] = 'EXT-' . str_pad((string) $registro['id_general'], 5, '0', STR_PAD_LEFT);

        return $registro;
    }

    private function obtenerPersonal($db, int $id): ?array
    {
        $registro = $this->consultaPersonal($db)
            ->where('gp.id_general_personal', $id)
            ->get()
            ->getRowArray();

        if (! $registro) {
            $registro = $this->consultaPersonal($db)
                ->where('gp.nomina', (string) $id)
                ->get()
                ->getRowArray();
        }

        if (! $registro && $db->fieldExists('id_personal', 'personal')) {
            $registro = $this->consultaPersonal($db)
                ->where('p.id_personal', $id)
                ->get()
                ->getRowArray();
        }

        if (! $registro) {
            return null;
        }

        $registro['folio'] = 'COM-' . str_pad((string) $registro['id_general'], 5, '0', STR_PAD_LEFT);

        return $registro;
    }

    private function consultaExterno($db)
    {
        return $db->table('general g')
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
            ->join('sexo s', 's.id_sexo = g.id_sexo', 'left');
    }

    private function consultaPersonal($db)
    {
        $fechaSelect = $db->fieldExists('fecha_registro', 'general_personal')
            ? 'gp.fecha_registro'
            : 'NULL AS fecha_registro';

        return $db->table('general_personal gp')
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
                'Comisaria' AS tipo_registro,
                '' AS dependencia
            ")
            ->join('personal p', 'p.nomina = gp.nomina')
            ->join('sexo s', 's.id_sexo = p.id_sexo', 'left');
    }
}
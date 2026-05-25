<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EstadosMunicipiosSeeder extends Seeder
{
    public function run()
    {
        $rutaCsv = APPPATH . 'Database/Seeds/estados_municipios.csv';
        $rutaJson = APPPATH . 'Database/Seeds/estados-municipios.json';

        if (is_file($rutaJson)) {
            $this->cargarDesdeJson($rutaJson);
            return;
        }

        if (!is_file($rutaCsv)) {
            echo "No se encontro el archivo JSON: {$rutaJson}" . PHP_EOL;
            echo "No se encontro el archivo CSV: {$rutaCsv}" . PHP_EOL;
            echo "Crea uno de estos archivos:" . PHP_EOL;
            echo "- estados-municipios.json del repositorio cisnerosnow/json-estados-municipios-mexico" . PHP_EOL;
            echo "- estados_municipios.csv con columnas: estado,municipio" . PHP_EOL;
            return;
        }

        $this->cargarDesdeCsv($rutaCsv);
    }

    private function cargarDesdeCsv(string $rutaCsv): void
    {
        $archivo = fopen($rutaCsv, 'r');

        if ($archivo === false) {
            echo "No se pudo abrir el archivo: {$rutaCsv}" . PHP_EOL;
            return;
        }

        $encabezados = fgetcsv($archivo);
        $insertados = 0;

        while (($fila = fgetcsv($archivo)) !== false) {
            $datos = array_combine($encabezados, $fila);

            if ($datos === false) {
                continue;
            }

            $estado = trim((string) ($datos['estado'] ?? ''));
            $municipio = trim((string) ($datos['municipio'] ?? ''));

            if ($this->insertarEstadoMunicipio($estado, $municipio)) {
                $insertados++;
            }
        }

        fclose($archivo);

        echo "Municipios insertados: {$insertados}" . PHP_EOL;
    }

    private function cargarDesdeJson(string $rutaJson): void
    {
        $contenido = file_get_contents($rutaJson);
        $estados = json_decode($contenido, true);

        if (!is_array($estados)) {
            echo "El JSON no tiene un formato valido: {$rutaJson}" . PHP_EOL;
            return;
        }

        $insertados = 0;

        foreach ($estados as $estado => $municipios) {
            $estado = trim((string) $estado);

            if ($estado === '' || !is_array($municipios)) {
                continue;
            }

            foreach ($municipios as $municipio) {
                if ($this->insertarEstadoMunicipio($estado, trim((string) $municipio))) {
                    $insertados++;
                }
            }
        }

        echo "Municipios insertados: {$insertados}" . PHP_EOL;
    }

    private function insertarEstadoMunicipio(string $estado, string $municipio): bool
    {
        if ($estado === '' || $municipio === '') {
            return false;
        }

        $estadoExistente = $this->db->table('estado')
            ->where('estado', $estado)
            ->get()
            ->getRowArray();

        if ($estadoExistente) {
            $idEstado = $estadoExistente['id_estado'];
        } else {
            $this->db->table('estado')->insert([
                'estado' => $estado,
            ]);
            $idEstado = $this->db->insertID();
        }

        $municipioExistente = $this->db->table('municipio')
            ->where('id_estado', $idEstado)
            ->where('municipio', $municipio)
            ->get()
            ->getRowArray();

        if ($municipioExistente) {
            return false;
        }

        $this->db->table('municipio')->insert([
            'id_estado' => $idEstado,
            'municipio' => $municipio,
        ]);

        return true;
    }
}

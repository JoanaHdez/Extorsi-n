<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EstadosMunicipiosSeeder extends Seeder
{
    public function run()
    {
        $rutaCsv = APPPATH . 'Database/Seeds/estados_municipios.csv';

        if (!is_file($rutaCsv)) {
            echo "No se encontro el archivo: {$rutaCsv}" . PHP_EOL;
            echo "Crea un CSV con columnas: estado,municipio" . PHP_EOL;
            return;
        }

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

            if ($estado === '' || $municipio === '') {
                continue;
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
                continue;
            }

            $this->db->table('municipio')->insert([
                'id_estado' => $idEstado,
                'municipio' => $municipio,
            ]);

            $insertados++;
        }

        fclose($archivo);

        echo "Municipios insertados: {$insertados}" . PHP_EOL;
    }
}

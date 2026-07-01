<?php

namespace App\Modules\Extorsion\Constancias\Services;

class CuestionarioConstanciaService
{
    public function preguntas(): array
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

    public function habilitado(): bool
    {
        return filter_var(env('registro.cuestionarioConstanciaHabilitado', false), FILTER_VALIDATE_BOOLEAN);
    }

    public function respondido(string $tipoRegistro, int $idRegistro): bool
    {
        $db = \Config\Database::connect();
        $this->asegurarTabla($db);

        return $db->table('cuestionario_constancia')
            ->where('tipo_registro', $tipoRegistro)
            ->where('id_registro', $idRegistro)
            ->countAllResults() > 0;
    }

    public function guardarRespuestas(string $tipoRegistro, int $idRegistro, array $respuestas): void
    {
        $db = \Config\Database::connect();
        $this->asegurarTabla($db);

        $limpias = [];

        foreach ($this->preguntas() as $pregunta) {
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

    public function asegurarTabla($db = null): void
    {
        $db ??= \Config\Database::connect();

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

    public function resumen(?string $fecha = null): array
    {
        $db = \Config\Database::connect();
        $this->asegurarTabla($db);

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

        foreach ($this->preguntas() as $pregunta) {
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

    public function dias(): array
    {
        $db = \Config\Database::connect();
        $this->asegurarTabla($db);

        return $db->table('cuestionario_constancia')
            ->select('DATE(fecha_respuesta) AS fecha, COUNT(*) AS total', false)
            ->groupBy('DATE(fecha_respuesta)')
            ->orderBy('fecha', 'ASC')
            ->get()
            ->getResultArray();
    }
}
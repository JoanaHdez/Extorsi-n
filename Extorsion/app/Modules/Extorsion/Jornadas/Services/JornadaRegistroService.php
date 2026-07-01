<?php

namespace App\Modules\Extorsion\Jornadas\Services;

class JornadaRegistroService
{
    public function disponible(?string $jornada): bool
    {
        $jornadaActiva = $this->activa();

        if ($jornadaActiva === '') {
            return false;
        }

        if ($jornada === null || $jornada === '') {
            return false;
        }

        return isset($this->jornadas()[$jornada]) && hash_equals($jornadaActiva, $jornada);
    }

    public function jornadas(): array
    {
        return [
            '09-junio' => '2026-06-09',
            '10-junio' => '2026-06-10',
            '17-junio' => '2026-06-17',
            '12-junio' => '2026-06-12',
        ];
    }

    public function activa(): string
    {
        $estadoPath = $this->estadoPath();

        if (is_file($estadoPath)) {
            $estado = json_decode((string) file_get_contents($estadoPath), true);

            if (is_array($estado) && array_key_exists('jornada', $estado)) {
                return (string) $estado['jornada'];
            }
        }

        return trim((string) env('registro.jornadaActiva', ''));
    }

    public function guardarActiva(string $jornada): void
    {
        if ($jornada !== '' && ! isset($this->jornadas()[$jornada])) {
            return;
        }

        file_put_contents(
            $this->estadoPath(),
            json_encode(['jornada' => $jornada], JSON_PRETTY_PRINT)
        );
    }

    public function estadoPath(): string
    {
        return WRITEPATH . 'registro_jornada.json';
    }
}
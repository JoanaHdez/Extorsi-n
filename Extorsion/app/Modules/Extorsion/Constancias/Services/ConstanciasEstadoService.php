<?php

namespace App\Modules\Extorsion\Constancias\Services;

class ConstanciasEstadoService
{
    public function habilitadas(): bool
    {
        $estadoPath = $this->estadoPath();

        if (is_file($estadoPath)) {
            $estado = json_decode((string) file_get_contents($estadoPath), true);

            if (is_array($estado) && array_key_exists('habilitadas', $estado)) {
                return (bool) $estado['habilitadas'];
            }
        }

        return filter_var(env('registro.constanciasHabilitadas', false), FILTER_VALIDATE_BOOLEAN);
    }

    public function guardar(bool $habilitadas): void
    {
        file_put_contents(
            $this->estadoPath(),
            json_encode(['habilitadas' => $habilitadas], JSON_PRETTY_PRINT)
        );
    }

    public function estadoPath(): string
    {
        return WRITEPATH . 'constancias_estado.json';
    }
}

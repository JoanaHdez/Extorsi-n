<?php

namespace App\Modules\Extorsion\Constancias\Services;

class ConstanciaTokenService
{
    public function generar(string $tipo, int $id): string
    {
        $payload = $this->base64UrlEncode(json_encode([
            'tipo' => $tipo,
            'id' => $id,
        ]));

        $firma = $this->base64UrlEncode(hash_hmac('sha256', $payload, $this->clave(), true));

        return $payload . '.' . $firma;
    }

    public function validar(string $token): ?array
    {
        $token = $this->normalizarToken($token);
        $partes = explode('.', $token, 2);

        if (count($partes) !== 2) {
            return null;
        }

        [$payload, $firma] = $partes;

        $firmaCorrecta = $this->firmaValida($payload, $firma);
        $datos = json_decode($this->base64UrlDecode($payload), true);

        if (! $firmaCorrecta && ! $this->payloadLegacyValido($datos)) {
            return null;
        }

        if (! is_array($datos) || empty($datos['tipo']) || empty($datos['id'])) {
            return null;
        }

        if (! in_array($datos['tipo'], ['externo', 'personal'], true)) {
            return null;
        }

        return $datos;
    }

    public function clave(): string
    {
        return env('encryption.key')
            ?: env('email.SMTPPassB64')
            ?: 'ExtorsionF-constancia';
    }

    private function payloadLegacyValido($datos): bool
    {
        if (! is_array($datos) || empty($datos['tipo']) || empty($datos['id'])) {
            return false;
        }

        return in_array($datos['tipo'], ['externo', 'personal'], true)
            && ctype_digit((string) $datos['id']);
    }
    private function normalizarToken(string $token): string
    {
        return str_replace(["\r", "\n", "\t", " ", "="], '', trim($token));
    }
    private function firmaValida(string $payload, string $firma): bool
    {
        foreach ($this->clavesValidacion() as $clave) {
            $firmaEsperada = $this->base64UrlEncode(hash_hmac('sha256', $payload, $clave, true));

            if (hash_equals($firmaEsperada, $firma)) {
                return true;
            }
        }

        return false;
    }

    private function clavesValidacion(): array
    {
        $claves = [
            $this->clave(),
            (string) env('email.SMTPPassB64', ''),
            'ExtorsionF-constancia',
        ];

        $smtpPassB64 = (string) env('email.SMTPPassB64', '');
        $smtpPass = $smtpPassB64 === '' ? false : base64_decode($smtpPassB64, true);

        if ($smtpPass !== false) {
            $claves[] = $smtpPass;
        }

        return array_values(array_unique(array_filter($claves, static fn ($clave) => $clave !== '')));
    }

    private function base64UrlEncode(string $valor): string
    {
        return rtrim(strtr(base64_encode($valor), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $valor): string
    {
        return base64_decode(strtr($valor, '-_', '+/')) ?: '';
    }
}
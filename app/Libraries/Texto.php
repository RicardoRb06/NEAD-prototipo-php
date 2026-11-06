<?php

declare(strict_types=1);

namespace App\Libraries;

use Normalizer;

/**
 * Normalização de texto usada na busca e na gravação das colunas de busca.
 */
final class Texto
{
    /**
     * Minúsculas e sem acentos: "Úrsula" vira "ursula".
     */
    public static function paraBusca(string $texto): string
    {
        $decomposto = Normalizer::normalize(mb_strtolower(trim($texto), 'UTF-8'), Normalizer::FORM_D);

        return preg_replace('/\p{Mn}+/u', '', (string) $decomposto) ?? '';
    }

    public static function somenteDigitos(string $texto): string
    {
        return preg_replace('/\D+/', '', $texto) ?? '';
    }

    /**
     * Formata 11 dígitos como 000.000.000-00.
     */
    public static function formatarCpf(string $digitos): string
    {
        return preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $digitos) ?? $digitos;
    }
}

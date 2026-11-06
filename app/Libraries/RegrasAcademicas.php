<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Regras de aprovação: fonte única para o seeder, as telas e os relatórios.
 */
final class RegrasAcademicas
{
    public const NOTA_MINIMA = 60;

    public const FREQUENCIA_MINIMA = 75;

    public const APROVADO = 'Aprovado';

    public const REPROVADO = 'Reprovado';

    public static function aprovado(int $nota, int $frequencia): bool
    {
        return $nota >= self::NOTA_MINIMA && $frequencia >= self::FREQUENCIA_MINIMA;
    }

    public static function situacao(int $nota, int $frequencia): string
    {
        return self::aprovado($nota, $frequencia) ? self::APROVADO : self::REPROVADO;
    }
}

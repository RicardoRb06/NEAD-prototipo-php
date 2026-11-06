<?php

declare(strict_types=1);

namespace App\Database\Seeds\Demo;

/**
 * Gerador pseudoaleatório com semente (Mulberry32).
 *
 * Reproduz a aritmética de 32 bits do JavaScript para que os dados de
 * demonstração sejam idênticos aos do protótipo original.
 */
final class GeradorDeterministico
{
    public function __construct(private int $semente)
    {
    }

    /**
     * Próximo número em [0, 1).
     */
    public function proximo(): float
    {
        $this->semente = self::int32(self::int32($this->semente) + 0x6D2B79F5);
        $s             = $this->semente;

        $t = self::multiplicar($s ^ self::deslocar($s, 15), 1 | $s);
        $t = self::int32(self::int32($t + self::multiplicar($t ^ self::deslocar($t, 7), 61 | $t)) ^ $t);

        return (($t ^ self::deslocar($t, 14)) & 0xFFFFFFFF) / 4294967296;
    }

    /**
     * Inteiro em [$minimo, $maximo].
     */
    public function inteiro(int $minimo, int $maximo): int
    {
        return (int) floor($this->proximo() * ($maximo - $minimo + 1)) + $minimo;
    }

    /**
     * Equivalente a `valor | 0` no JavaScript.
     */
    private static function int32(int $valor): int
    {
        $valor &= 0xFFFFFFFF;

        return $valor >= 0x80000000 ? $valor - 0x100000000 : $valor;
    }

    /**
     * Equivalente a `valor >>> bits` no JavaScript.
     */
    private static function deslocar(int $valor, int $bits): int
    {
        return ($valor & 0xFFFFFFFF) >> $bits;
    }

    /**
     * Equivalente a `Math.imul(a, b)`: o produto de dois int32 cabe em 64 bits.
     */
    private static function multiplicar(int $a, int $b): int
    {
        return self::int32(self::int32($a) * self::int32($b));
    }
}

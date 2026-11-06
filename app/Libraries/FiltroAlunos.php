<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Critérios da busca de alunos, já saneados a partir da query string.
 */
final readonly class FiltroAlunos
{
    public const TIPO_INGRESSO = 'ingresso';

    public const TIPO_FORMACAO = 'formacao';

    private function __construct(
        public string $matricula,
        public string $cpf,
        public string $nome,
        public ?int $cursoId,
        public ?int $campusId,
        public string $tipoAno,
        public ?int $ano,
    ) {
    }

    /**
     * Valores malformados são ignorados: um parâmetro inválido não impede a busca.
     *
     * @param array<string, mixed> $dados
     */
    public static function deArray(array $dados): self
    {
        $ano     = self::inteiro($dados['ano'] ?? null, 9999);
        $tipoAno = self::texto($dados['tipo_ano'] ?? null);

        if (! in_array($tipoAno, [self::TIPO_INGRESSO, self::TIPO_FORMACAO], true)) {
            // Ano sem tipo equivale a ano de ingresso.
            $tipoAno = $ano !== null ? self::TIPO_INGRESSO : '';
        }

        return new self(
            self::texto($dados['matricula'] ?? null),
            self::texto($dados['cpf'] ?? null),
            self::texto($dados['nome'] ?? null),
            self::inteiro($dados['curso'] ?? null),
            self::inteiro($dados['campus'] ?? null),
            $tipoAno,
            $ano,
        );
    }

    public function cpfDigitos(): string
    {
        return Texto::somenteDigitos($this->cpf);
    }

    public function estaAtivo(): bool
    {
        return $this->matricula !== ''
            || $this->cpfDigitos() !== ''
            || $this->nome !== ''
            || $this->cursoId !== null
            || $this->campusId !== null
            || $this->tipoAno !== ''
            || $this->ano !== null;
    }

    private static function texto(mixed $valor): string
    {
        return is_string($valor) ? trim($valor) : '';
    }

    private static function inteiro(mixed $valor, int $maximo = PHP_INT_MAX): ?int
    {
        $inteiro = is_string($valor)
            ? filter_var(trim($valor), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => $maximo]])
            : false;

        return $inteiro === false ? null : $inteiro;
    }
}

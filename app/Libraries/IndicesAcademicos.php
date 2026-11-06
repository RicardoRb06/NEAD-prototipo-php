<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Entities\RegistroHistorico;

/**
 * Cargas horárias e IRA calculados a partir do histórico de um aluno.
 */
final readonly class IndicesAcademicos
{
    private function __construct(
        public int $cargaHorariaCursada,
        public int $cargaHorariaAprovada,
        private ?float $mediaPonderada,
    ) {
    }

    /**
     * @param list<RegistroHistorico> $registros
     */
    public static function calcular(array $registros): self
    {
        $somaNotas = 0;
        $cursada   = 0;
        $aprovada  = 0;

        foreach ($registros as $registro) {
            $somaNotas += $registro->nota * $registro->carga_horaria;
            $cursada   += $registro->carga_horaria;

            if ($registro->aprovado()) {
                $aprovada += $registro->carga_horaria;
            }
        }

        return new self($cursada, $aprovada, $cursada > 0 ? $somaNotas / $cursada : null);
    }

    /**
     * Média das notas ponderada pela carga horária; "-" quando não há disciplinas.
     * Média geral e IRA usam hoje a mesma fórmula, como no protótipo original.
     */
    public function mediaFormatada(): string
    {
        return $this->mediaPonderada === null
            ? '-'
            : number_format($this->mediaPonderada, 1, ',', '');
    }
}

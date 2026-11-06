<?php

declare(strict_types=1);

namespace App\Database\Seeds\Demo;

use App\Libraries\RegrasAcademicas;
use App\Libraries\Texto;

/**
 * Monta os alunos fictícios de demonstração a partir de DadosBase.
 *
 * Cada aluno tem sua própria semente; por isso a ordem das chamadas ao gerador
 * importa e segue a do protótipo original.
 */
final class GeradorAlunos
{
    /** Período letivo de referência do protótipo: fixo para os dados não mudarem. */
    private const ANO_ATUAL = 2026;

    private const SEMESTRE_ATUAL = 1;

    private const DOMINIO_EMAIL = 'estudante.ifsudestemg.edu.br';

    /**
     * @return list<array<string, mixed>>
     */
    public function gerar(): array
    {
        $alunos = [];

        foreach (DadosBase::ALUNOS as $indice => $base) {
            $alunos[] = $this->gerarAluno($indice, ...$base);
        }

        return $alunos;
    }

    /**
     * @return array<string, mixed>
     */
    private function gerarAluno(int $indice, string $nome, string $curso, int $ano, int $semestre, string $nascimento): array
    {
        $gerador   = new GeradorDeterministico(1000 + $indice * 37);
        $historico = $this->gerarHistorico($gerador, $curso, $ano, $semestre);
        $cpf       = $this->gerarCpf($gerador);
        $telefone  = sprintf('(32) 9%d-%04d', $gerador->inteiro(8000, 9999), $gerador->inteiro(0, 9999));
        $campus    = DadosBase::CAMPI[$gerador->inteiro(0, count(DadosBase::CAMPI) - 1)];

        $concluiu = $this->periodosCursados($ano, $semestre) >= count(DadosBase::GRADES[$curso]);
        $aprovado = array_filter($historico, static fn (array $d): bool => ! RegrasAcademicas::aprovado($d['nota'], $d['frequencia'])) === [];
        $formado  = $concluiu && $aprovado;

        $partes = explode(' ', Texto::paraBusca($nome));

        return [
            'nome'             => $nome,
            'curso'            => $curso,
            'anoIngresso'      => $ano,
            'semestreIngresso' => $semestre,
            'nascimento'       => $nascimento,
            'matricula'        => sprintf('%d%d%s%04d', $ano, $semestre, DadosBase::CODIGOS_CURSO[$curso], $indice + 1),
            'cpf'              => $cpf,
            'email'            => $partes[0] . '.' . $partes[array_key_last($partes)] . '@' . self::DOMINIO_EMAIL,
            'telefone'         => $telefone,
            'campus'           => $campus,
            'vinculo'          => $formado ? 'Formado' : 'Ativo',
            'anoFormacao'      => $formado ? (int) substr($historico[array_key_last($historico)]['periodo'], 0, 4) : null,
            'historico'        => $historico,
        ];
    }

    private function periodosCursados(int $ano, int $semestre): int
    {
        return (self::ANO_ATUAL - $ano) * 2 + (self::SEMESTRE_ATUAL - $semestre) + 1;
    }

    /**
     * @return list<array{periodo: string, numeroPeriodo: int, disciplina: string, cargaHoraria: int, nota: int, frequencia: int}>
     */
    private function gerarHistorico(GeradorDeterministico $gerador, string $curso, int $ano, int $semestre): array
    {
        $grade     = DadosBase::GRADES[$curso];
        $total     = min($this->periodosCursados($ano, $semestre), count($grade));
        $historico = [];

        for ($periodo = 0; $periodo < $total; $periodo++) {
            foreach ($grade[$periodo] as [$disciplina, $cargaHoraria]) {
                $nota = $gerador->inteiro(0, 99) < 12
                    ? $gerador->inteiro(25, 59)
                    : $gerador->inteiro(60, 100);
                $frequencia = $gerador->inteiro(0, 99) < 6
                    ? $gerador->inteiro(55, 74)
                    : $gerador->inteiro(75, 100);

                $historico[] = [
                    'periodo'       => $ano . '.' . $semestre,
                    'numeroPeriodo' => $periodo + 1,
                    'disciplina'    => $disciplina,
                    'cargaHoraria'  => $cargaHoraria,
                    'nota'          => $nota,
                    'frequencia'    => $frequencia,
                ];
            }

            if ($semestre === 1) {
                $semestre = 2;
            } else {
                $semestre = 1;
                $ano++;
            }
        }

        return $historico;
    }

    /**
     * CPF sintaticamente válido, somente com dígitos.
     */
    private function gerarCpf(GeradorDeterministico $gerador): string
    {
        $digitos = [];

        for ($i = 0; $i < 9; $i++) {
            $digitos[] = $gerador->inteiro(0, 9);
        }

        for ($verificador = 0; $verificador < 2; $verificador++) {
            $peso = count($digitos) + 1;
            $soma = 0;

            foreach ($digitos as $posicao => $digito) {
                $soma += $digito * ($peso - $posicao);
            }

            $resto     = ($soma * 10) % 11;
            $digitos[] = $resto === 10 ? 0 : $resto;
        }

        return implode('', $digitos);
    }
}

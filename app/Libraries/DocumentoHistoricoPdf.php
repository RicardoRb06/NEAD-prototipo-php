<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\I18n\Time;
use TCPDF;

/**
 * Documento A4 com o layout dos históricos: cabeçalho por aluno e rodapé por página.
 */
final class DocumentoHistoricoPdf extends TCPDF
{
    private const MARGEM = 15;

    private const INICIO_CONTEUDO = 31;

    private readonly string $emissao;

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8');

        $this->emissao = 'Emitido em ' . Time::now()->format('d/m/Y \à\s H:i');

        $this->SetCreator('NEAD - Interface de Consulta Centralizada');
        $this->SetTitle('Histórico Escolar');
        $this->setPrintHeader(false);
        $this->SetMargins(self::MARGEM, 20, self::MARGEM);
        $this->SetAutoPageBreak(true, 20);
    }

    /**
     * Começa uma nova página com o cabeçalho e o conteúdo de um aluno.
     */
    public function adicionarAluno(string $html): void
    {
        $this->AddPage();

        $this->SetFont('helvetica', 'B', 14);
        $this->SetXY(self::MARGEM, 13);
        $this->Cell(0, 6, 'IF Sudeste MG - SIGAA', 0, 1);

        $this->SetFont('helvetica', '', 9);
        $this->SetX(self::MARGEM);
        $this->Cell(0, 4, 'Instituto Federal do Sudeste de Minas Gerais - Histórico Escolar', 0, 1);

        $largura = $this->getPageWidth();
        $this->SetDrawColor(64, 78, 130);
        $this->SetLineWidth(0.6);
        $this->Line(self::MARGEM, 26, $largura - self::MARGEM, 26);
        $this->SetLineWidth(0.2);

        $this->SetY(self::INICIO_CONTEUDO);
        $this->SetFont('helvetica', '', 8.5);
        $this->writeHTML($html, false, false, true);
    }

    public function Footer(): void
    {
        $regra = sprintf(
            'Notas de 0 a 100. Aprovação com nota mínima %d e frequência mínima de %d%%. '
            . 'IRA: média das notas ponderada pela carga horária de todas as disciplinas cursadas.',
            RegrasAcademicas::NOTA_MINIMA,
            RegrasAcademicas::FREQUENCIA_MINIMA,
        );

        $this->SetFont('helvetica', '', 7);
        $this->SetTextColor(80);

        $this->SetY(-15);
        $this->MultiCell(0, 3, $regra, 0, 'L');

        $this->SetY(-8);
        $this->Cell(90, 3, $this->emissao);
        $this->Cell(
            0,
            3,
            // Os apelidos são trocados depois; o ajuste corrige o alinhamento à direita.
            $this->getAliasRightShift() . 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(),
            0,
            0,
            'R',
        );
    }
}

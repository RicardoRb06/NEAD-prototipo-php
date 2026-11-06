<?php
/**
 * Histórico de um aluno, em HTML restrito ao subconjunto suportado pelo TCPDF.
 *
 * @var App\Entities\Aluno                   $aluno
 * @var list<App\Entities\RegistroHistorico> $registros
 * @var App\Libraries\IndicesAcademicos      $indices
 */

use App\Libraries\RegrasAcademicas;

$borda   = 'border:0.2mm solid #B8C4D8;';
$titulo  = $borda . 'background-color:#C4D2EB;font-weight:bold;text-align:center;';
$rotulo  = $borda . 'background-color:#EFF3FA;font-weight:bold;';
$coluna  = $borda . 'background-color:#DEDFE3;font-weight:bold;';
$centro  = 'text-align:center;';
$periodo = null;
?>
<table cellpadding="4">
    <tr><td colspan="4" width="100%" style="<?= $titulo ?>">Dados Pessoais</td></tr>
    <tr>
        <td width="22%" style="<?= $rotulo ?>">Nome</td>
        <td width="78%" colspan="3" style="<?= $borda ?>"><?= esc($aluno->nome) ?></td>
    </tr>
    <tr>
        <td width="22%" style="<?= $rotulo ?>">Matrícula</td>
        <td width="28%" style="<?= $borda ?>"><?= esc($aluno->matricula) ?></td>
        <td width="22%" style="<?= $rotulo ?>">CPF</td>
        <td width="28%" style="<?= $borda ?>"><?= esc($aluno->cpfFormatado()) ?></td>
    </tr>
    <tr>
        <td width="22%" style="<?= $rotulo ?>">Data de nascimento</td>
        <td width="28%" style="<?= $borda ?>"><?= esc($aluno->nascimentoFormatado()) ?></td>
        <td width="22%" style="<?= $rotulo ?>">Telefone</td>
        <td width="28%" style="<?= $borda ?>"><?= esc($aluno->telefone) ?></td>
    </tr>
    <tr>
        <td width="22%" style="<?= $rotulo ?>">E-mail</td>
        <td width="78%" colspan="3" style="<?= $borda ?>"><?= esc($aluno->email) ?></td>
    </tr>
</table>
<div style="font-size:6pt;">&nbsp;</div>
<table cellpadding="4">
    <tr><td colspan="4" width="100%" style="<?= $titulo ?>">Dados do Curso</td></tr>
    <tr>
        <td width="22%" style="<?= $rotulo ?>">Curso</td>
        <td width="78%" colspan="3" style="<?= $borda ?>"><?= esc($aluno->curso) ?></td>
    </tr>
    <tr>
        <td width="22%" style="<?= $rotulo ?>">Campus</td>
        <td width="28%" style="<?= $borda ?>"><?= esc($aluno->campus) ?></td>
        <td width="22%" style="<?= $rotulo ?>">Situação do vínculo</td>
        <td width="28%" style="<?= $borda ?>"><?= esc($aluno->vinculo) ?></td>
    </tr>
    <tr>
        <td width="22%" style="<?= $rotulo ?>">Ano/semestre de ingresso</td>
        <td width="28%" style="<?= $borda ?>"><?= esc($aluno->periodoIngresso()) ?></td>
        <td width="22%" style="<?= $rotulo ?>">Ano de formação</td>
        <td width="28%" style="<?= $borda ?>"><?= esc($aluno->ano_formacao ?? '-') ?></td>
    </tr>
    <tr>
        <td width="22%" style="<?= $rotulo ?>">Carga horária cursada</td>
        <td width="28%" style="<?= $borda ?>"><?= $indices->cargaHorariaCursada ?>h</td>
        <td width="22%" style="<?= $rotulo ?>">Carga horária aprovada</td>
        <td width="28%" style="<?= $borda ?>"><?= $indices->cargaHorariaAprovada ?>h</td>
    </tr>
    <tr>
        <td width="22%" style="<?= $rotulo ?>">Média geral</td>
        <td width="28%" style="<?= $borda ?>"><?= esc($indices->mediaFormatada()) ?></td>
        <td width="22%" style="<?= $rotulo ?>">IRA</td>
        <td width="28%" style="<?= $borda ?>"><?= esc($indices->mediaFormatada()) ?></td>
    </tr>
</table>
<div style="font-size:6pt;">&nbsp;</div>
<table cellpadding="4">
    <thead>
        <tr><td colspan="6" width="100%" style="<?= $titulo ?>">Disciplinas Cursadas</td></tr>
        <tr>
            <td width="10%" style="<?= $coluna ?>">Período</td>
            <td width="44%" style="<?= $coluna ?>">Disciplina</td>
            <td width="13%" style="<?= $coluna . $centro ?>">Carga horária</td>
            <td width="8%" style="<?= $coluna . $centro ?>">Nota</td>
            <td width="12%" style="<?= $coluna . $centro ?>">Frequência</td>
            <td width="13%" style="<?= $coluna ?>">Situação</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($registros as $registro): ?>
            <?php if ($registro->periodo !== $periodo): ?>
                <?php $periodo = $registro->periodo; ?>
                <tr nobr="true">
                    <td colspan="6" width="100%" style="<?= $rotulo ?>"><?= $registro->numero_periodo ?>º período - <?= esc($registro->periodo) ?></td>
                </tr>
            <?php endif ?>
            <tr nobr="true">
                <td width="10%" style="<?= $borda ?>"><?= esc($registro->periodo) ?></td>
                <td width="44%" style="<?= $borda ?>"><?= esc($registro->disciplina) ?></td>
                <td width="13%" style="<?= $borda . $centro ?>"><?= $registro->carga_horaria ?>h</td>
                <td width="8%" style="<?= $borda . $centro ?>"><?= $registro->nota ?></td>
                <td width="12%" style="<?= $borda . $centro ?>"><?= $registro->frequencia ?>%</td>
                <?php if ($registro->situacao() === RegrasAcademicas::REPROVADO): ?>
                    <td width="13%" style="<?= $borda ?>color:#A01B0E;font-weight:bold;"><?= esc($registro->situacao()) ?></td>
                <?php else: ?>
                    <td width="13%" style="<?= $borda ?>"><?= esc($registro->situacao()) ?></td>
                <?php endif ?>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

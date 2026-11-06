<?php
/**
 * Tabela de resultados: incluída na página e devolvida sozinha nas atualizações via fetch.
 *
 * @var list<App\Entities\Aluno> $alunos
 * @var CodeIgniter\Pager\Pager  $pager
 * @var string|null              $erro
 */
$total = $pager->getTotal();
?>
<?php if ($erro !== null): ?>
    <p class="mensagem-erro" role="alert"><?= esc($erro) ?></p>
<?php endif ?>
<table>
    <thead>
        <tr>
            <th>Matrícula</th>
            <th>CPF</th>
            <th>Nome</th>
            <th>Curso</th>
            <th>Campus</th>
            <th class="col-acao"><span class="visualmente-oculto">Ações</span></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($alunos as $aluno): ?>
            <tr>
                <td><?= esc($aluno->matricula) ?></td>
                <td><?= esc($aluno->cpfFormatado()) ?></td>
                <td><?= esc($aluno->nome) ?></td>
                <td><?= esc($aluno->curso) ?></td>
                <td><?= esc($aluno->campus) ?></td>
                <td class="col-acao">
                    <a class="botao-pdf" href="<?= url_to('alunos.historico', $aluno->matricula) ?>"
                        aria-label="Baixar PDF do histórico de <?= esc($aluno->nome, 'attr') ?>">Baixar PDF</a>
                </td>
            </tr>
        <?php endforeach ?>
        <?php if ($alunos === []): ?>
            <tr><td colspan="6" class="vazio">Nenhum aluno encontrado com os critérios informados.</td></tr>
        <?php endif ?>
    </tbody>
</table>
<?php if ($alunos !== []): ?>
    <div class="total-registros" aria-live="polite">
        <?= $total ?> <?= $total === 1 ? 'aluno encontrado' : 'alunos encontrados' ?>
    </div>
    <?= $pager->links('default', 'anterior_proxima') ?>
<?php endif ?>

<?php
/**
 * @var CodeIgniter\Pager\PagerRenderer $pager
 */
?>
<?php if ($pager->getPageCount() > 1): ?>
    <nav class="paginacao" aria-label="Paginação dos resultados">
        <?php if ($pager->hasPreviousPage()): ?>
            <a class="botao" href="<?= esc($pager->getPreviousPage(), 'attr') ?>">« Anterior</a>
        <?php else: ?>
            <span class="botao desabilitado" aria-disabled="true">« Anterior</span>
        <?php endif ?>

        <span>Página <?= $pager->getCurrentPageNumber() ?> de <?= $pager->getPageCount() ?></span>

        <?php if ($pager->hasNextPage()): ?>
            <a class="botao" href="<?= esc($pager->getNextPage(), 'attr') ?>">Próxima »</a>
        <?php else: ?>
            <span class="botao desabilitado" aria-disabled="true">Próxima »</span>
        <?php endif ?>
    </nav>
<?php endif ?>

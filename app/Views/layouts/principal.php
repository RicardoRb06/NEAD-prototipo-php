<?php
/**
 * @var CodeIgniter\View\View $this
 */
?>
<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SIGAA - <?= $this->renderSection('titulo') ?></title>
        <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    </head>
    <body>
        <div class="container">
            <header>
                <div class="info-sistema">
                    <div>
                        <h1>IF Sudeste MG - SIGAA</h1>
                        <p>Sistema Integrado de Gestão de Atividades Acadêmicas</p>
                    </div>
                    <?= $this->renderSection('sessao') ?>
                </div>
                <?= $this->renderSection('painel') ?>
            </header>

            <main class="conteudo">
                <?= $this->renderSection('conteudo') ?>
            </main>

            <footer class="rodape">
                SIGAA | Instituto Federal do Sudeste de MG - (32) 3198-1456 |
                Copyright © 2006-2026 - UFRN - sig17.ifsudestemg.edu.br.sig17 -
                v4.11.3.96
            </footer>
        </div>

        <?= $this->renderSection('scripts') ?>
    </body>
</html>

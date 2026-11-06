<?php
/**
 * @var CodeIgniter\View\View                                   $this
 * @var App\Libraries\FiltroAlunos                              $filtro
 * @var array<int, string>                                      $cursos
 * @var array<int, string>                                      $campi
 * @var array{id: int, nome: string, unidade: string}           $usuario
 * @var list<App\Entities\Aluno>                                $alunos
 * @var CodeIgniter\Pager\Pager                                 $pager
 * @var string|null                                             $erro
 */

use App\Libraries\FiltroAlunos;

?>
<?= $this->extend('layouts/principal') ?>

<?= $this->section('titulo') ?>Busca de Alunos<?= $this->endSection() ?>

<?= $this->section('sessao') ?>
<div class="sessao">
    <span>Tempo de Sessão: 00:25</span>
    <form method="post" action="<?= url_to('logout') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="botao-sair">SAIR</button>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('painel') ?>
<div class="painel-usuario">
    <div class="dados">
        <span><strong>Usuário:</strong> <?= esc($usuario['nome']) ?></span>
        <span><strong>Unidade:</strong> <?= esc($usuario['unidade']) ?></span>
    </div>
    <a href="#" class="modulos">▦ Módulos</a>
</div>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<form id="form-busca" method="get" action="<?= url_to('alunos') ?>" class="formulario">
    <h2 class="titulo">Busca de Alunos</h2>
    <!-- Primeiro botão de envio: é ele que a tecla Enter aciona, não o de PDF. -->
    <button type="submit" class="visualmente-oculto" tabindex="-1">Buscar</button>
    <div class="campos">
        <div class="linha-campo">
            <label for="matricula">Matrícula:</label>
            <input id="matricula" name="matricula" class="campo" type="text" autocomplete="off"
                value="<?= esc($filtro->matricula, 'attr') ?>" autofocus>
        </div>
        <div class="linha-campo">
            <label for="cpf">CPF:</label>
            <input id="cpf" name="cpf" class="campo" type="text" inputmode="numeric" maxlength="14"
                placeholder="000.000.000-00" autocomplete="off" value="<?= esc($filtro->cpf, 'attr') ?>">
        </div>
        <div class="linha-campo">
            <label for="nome">Nome:</label>
            <input id="nome" name="nome" class="campo largo" type="text" autocomplete="off"
                value="<?= esc($filtro->nome, 'attr') ?>">
        </div>
        <div class="linha-campo">
            <label for="curso">Curso:</label>
            <select id="curso" name="curso" class="lista">
                <option value="">-- SELECIONE --</option>
                <?php foreach ($cursos as $id => $nome): ?>
                    <option value="<?= (int) $id ?>" <?= (int) $id === $filtro->cursoId ? 'selected' : '' ?>><?= esc($nome) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="linha-campo">
            <label for="campus">Campus:</label>
            <select id="campus" name="campus" class="lista">
                <option value="">-- SELECIONE --</option>
                <?php foreach ($campi as $id => $nome): ?>
                    <option value="<?= (int) $id ?>" <?= (int) $id === $filtro->campusId ? 'selected' : '' ?>><?= esc($nome) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="linha-campo">
            <label for="tipo-ano">Buscar por ano de:</label>
            <select id="tipo-ano" name="tipo_ano" class="lista">
                <option value="">-- SELECIONE --</option>
                <option value="<?= FiltroAlunos::TIPO_INGRESSO ?>" <?= $filtro->tipoAno === FiltroAlunos::TIPO_INGRESSO ? 'selected' : '' ?>>Ingresso</option>
                <option value="<?= FiltroAlunos::TIPO_FORMACAO ?>" <?= $filtro->tipoAno === FiltroAlunos::TIPO_FORMACAO ? 'selected' : '' ?>>Formação</option>
            </select>
        </div>
        <div class="linha-campo">
            <label for="ano">Ano:</label>
            <input id="ano" name="ano" class="campo" type="number" inputmode="numeric" min="1" max="9999"
                placeholder="ex: 2023" autocomplete="off" value="<?= esc((string) $filtro->ano, 'attr') ?>">
        </div>
    </div>
    <div class="acoes">
        <button type="submit" id="botao-pdf-todos" class="botao" formaction="<?= url_to('alunos.relatorio') ?>">
            Baixar PDF com Todos os Resultados
        </button>
        <a href="<?= url_to('alunos') ?>" class="botao">Limpar</a>
    </div>
</form>

<section class="tabela-resultados" aria-labelledby="titulo-resultados">
    <h2 id="titulo-resultados" class="titulo">Alunos Encontrados</h2>
    <div id="area-resultados">
        <?= $this->include('alunos/_resultados') ?>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/busca.js') ?>"></script>
<?= $this->endSection() ?>

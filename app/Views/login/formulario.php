<?php
/**
 * @var CodeIgniter\View\View $this
 * @var string|null           $erro
 * @var string                $usuarioDigitado
 * @var bool                  $exibirCredenciais
 */
$focarSenha = $usuarioDigitado !== '';
?>
<?= $this->extend('layouts/principal') ?>

<?= $this->section('titulo') ?>Entrar no Sistema<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<form method="post" action="<?= url_to('login.entrar') ?>" class="formulario caixa-login">
    <?= csrf_field() ?>
    <h2 class="titulo">Entrar no Sistema</h2>
    <div class="campos">
        <div class="linha-campo">
            <label for="usuario">Usuário:</label>
            <input id="usuario" name="usuario" class="campo" type="text" autocomplete="username"
                value="<?= esc($usuarioDigitado, 'attr') ?>" required <?= $focarSenha ? '' : 'autofocus' ?>>
        </div>
        <div class="linha-campo">
            <label for="senha">Senha:</label>
            <input id="senha" name="senha" class="campo" type="password" autocomplete="current-password"
                required <?= $focarSenha ? 'autofocus' : '' ?>>
        </div>
        <?php if ($erro !== null): ?>
            <p class="mensagem-erro" role="alert"><?= esc($erro) ?></p>
        <?php endif ?>
    </div>
    <div class="acoes">
        <button type="submit" class="botao">Entrar</button>
    </div>
</form>

<?php if ($exibirCredenciais): ?>
    <section class="credenciais" aria-labelledby="titulo-credenciais">
        <h2 id="titulo-credenciais">Acesso ao protótipo</h2>
        <p>Usuário: <code>admin</code></p>
        <p>Senha: <code>admin</code></p>
    </section>
<?php endif ?>
<?= $this->endSection() ?>

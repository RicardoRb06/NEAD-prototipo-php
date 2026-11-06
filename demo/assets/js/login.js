/*
 * Login simulado da demonstração estática. Não há servidor nem proteção real:
 * os dados são fictícios e públicos. O sistema completo autentica no servidor.
 */
(function () {
    "use strict";

    var CHAVE_SESSAO = "nead-demo-autenticado";

    if (sessionStorage.getItem(CHAVE_SESSAO) === "sim") {
        window.location.replace("busca.html");
        return;
    }

    var form = document.getElementById("form-login");
    var usuario = document.getElementById("usuario");
    var senha = document.getElementById("senha");

    form.noValidate = true;

    function mostrarErro(texto) {
        var erro = form.querySelector(".mensagem-erro");
        if (!erro) {
            erro = document.createElement("p");
            erro.className = "mensagem-erro";
            erro.setAttribute("role", "alert");
            form.querySelector(".campos").appendChild(erro);
        }
        erro.textContent = texto;
    }

    form.addEventListener("submit", function (evento) {
        evento.preventDefault();

        if (!usuario.value.trim() || !senha.value) {
            mostrarErro("Informe o usuário e a senha para entrar.");
            return;
        }

        if (usuario.value.trim() === "admin" && senha.value === "admin") {
            sessionStorage.setItem(CHAVE_SESSAO, "sim");
            window.location.href = "busca.html";
            return;
        }

        mostrarErro("Usuário ou senha incorretos. Use as credenciais de acesso ao protótipo, abaixo do formulário.");
        senha.value = "";
        senha.focus();
    });
})();

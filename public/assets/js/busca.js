/*
 * Melhoria progressiva da busca: sem JavaScript, o formulário e a paginação
 * funcionam por navegação normal; com ele, os resultados atualizam sem recarregar.
 */
(function () {
    "use strict";

    var ATRASO_DIGITACAO_MS = 200;

    var form = document.getElementById("form-busca");
    var campoCpf = document.getElementById("cpf");
    var campoTipoAno = document.getElementById("tipo-ano");
    var campoAno = document.getElementById("ano");
    var botaoPdf = document.getElementById("botao-pdf-todos");
    var area = document.getElementById("area-resultados");

    var temporizador = null;
    var requisicaoAtual = null;

    function formatarCpf(valor) {
        var d = valor.replace(/\D/g, "").slice(0, 11);
        if (d.length > 9) {
            return d.slice(0, 3) + "." + d.slice(3, 6) + "." + d.slice(6, 9) + "-" + d.slice(9);
        }
        if (d.length > 6) {
            return d.slice(0, 3) + "." + d.slice(3, 6) + "." + d.slice(6);
        }
        if (d.length > 3) {
            return d.slice(0, 3) + "." + d.slice(3);
        }
        return d;
    }

    function urlDaBusca() {
        var parametros = new URLSearchParams();
        new FormData(form).forEach(function (valor, campo) {
            if (valor !== "") {
                parametros.append(campo, valor);
            }
        });
        var consulta = parametros.toString();
        return form.action + (consulta ? "?" + consulta : "");
    }

    function carregar(url) {
        // Só a resposta mais recente interessa: uma lenta não sobrescreve outra nova.
        if (requisicaoAtual) {
            requisicaoAtual.abort();
        }
        var requisicao = new AbortController();
        requisicaoAtual = requisicao;

        fetch(url, {
            headers: { "X-Requested-With": "XMLHttpRequest" },
            signal: requisicao.signal,
        })
            .then(function (resposta) {
                // Sessão expirada, erro do servidor: a navegação normal mostra o que houve.
                if (resposta.redirected || !resposta.ok) {
                    window.location.href = url;
                    return null;
                }
                return resposta.text();
            })
            .then(function (html) {
                if (html !== null) {
                    area.innerHTML = html;
                    history.replaceState(null, "", url);
                }
            })
            .catch(function (erro) {
                if (erro.name !== "AbortError") {
                    window.location.href = url;
                }
            });
    }

    campoCpf.addEventListener("input", function () {
        campoCpf.value = formatarCpf(campoCpf.value);
    });

    // Ao digitar um ano sem tipo escolhido, assume ano de ingresso.
    campoAno.addEventListener("input", function () {
        if (campoAno.value && !campoTipoAno.value) {
            campoTipoAno.value = "ingresso";
        }
    });

    // Atualiza a tabela a cada tecla digitada ou opção escolhida.
    form.addEventListener("input", function () {
        clearTimeout(temporizador);
        temporizador = setTimeout(function () {
            carregar(urlDaBusca());
        }, ATRASO_DIGITACAO_MS);
    });

    form.addEventListener("submit", function (evento) {
        // O botão de PDF envia o formulário para o relatório, normalmente.
        if (evento.submitter === botaoPdf) {
            return;
        }
        evento.preventDefault();
        clearTimeout(temporizador);
        carregar(urlDaBusca());
    });

    area.addEventListener("click", function (evento) {
        var link = evento.target.closest(".paginacao a");
        if (link) {
            evento.preventDefault();
            carregar(link.href);
        }
    });
})();

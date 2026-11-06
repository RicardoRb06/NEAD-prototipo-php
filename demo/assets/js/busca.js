/*
 * Busca da demonstração estática: faz no navegador o que o sistema completo faz
 * no servidor. Os PDFs individuais foram gerados pela aplicação na exportação;
 * o relatório filtrado é a junção desses arquivos.
 */
(function () {
    "use strict";

    var CHAVE_SESSAO = "nead-demo-autenticado";
    var ITENS_POR_PAGINA = 20;
    var URL_PDF_LIB = "https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js";

    if (sessionStorage.getItem(CHAVE_SESSAO) !== "sim") {
        window.location.replace("index.html");
        return;
    }

    var form = document.getElementById("form-busca");
    var area = document.getElementById("area-resultados");
    var botaoPdf = document.getElementById("botao-pdf-todos");
    var campoCpf = document.getElementById("cpf");
    var campoTipoAno = document.getElementById("tipo-ano");
    var campoAno = document.getElementById("ano");

    var alunos = [];
    var resultado = [];
    var paginaAtual = 1;
    var erroRelatorio = "";

    function somenteDigitos(texto) {
        return texto.replace(/\D/g, "");
    }

    function paraBusca(texto) {
        return texto.trim().toLowerCase().normalize("NFD").replace(/[̀-ͯ]/g, "");
    }

    function escapar(texto) {
        var elemento = document.createElement("div");
        elemento.textContent = String(texto);
        return elemento.innerHTML.replace(/"/g, "&quot;");
    }

    function formatarCpf(valor) {
        var d = somenteDigitos(valor).slice(0, 11);
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

    function filtroEstaAtivo() {
        return Array.prototype.some.call(form.elements, function (campo) {
            return campo.name && campo.value.trim() !== "";
        });
    }

    function filtrar() {
        var matricula = paraBusca(form.elements.matricula.value).toUpperCase();
        var cpf = somenteDigitos(campoCpf.value);
        var nome = paraBusca(form.elements.nome.value);
        var curso = Number(form.elements.curso.value);
        var campus = Number(form.elements.campus.value);
        var ano = Number(campoAno.value);
        var chaveAno = campoTipoAno.value === "formacao" ? "anoFormacao" : "anoIngresso";

        return alunos.filter(function (a) {
            return (!matricula || a.matricula.indexOf(matricula) !== -1)
                && (!cpf || somenteDigitos(a.cpf).indexOf(cpf) !== -1)
                && (!nome || a.nomeBusca.indexOf(nome) !== -1)
                && (!curso || a.cursoId === curso)
                && (!campus || a.campusId === campus)
                && (!ano || a[chaveAno] === ano);
        });
    }

    function linha(a) {
        return "<tr>"
            + "<td>" + escapar(a.matricula) + "</td>"
            + "<td>" + escapar(a.cpf) + "</td>"
            + "<td>" + escapar(a.nome) + "</td>"
            + "<td>" + escapar(a.curso) + "</td>"
            + "<td>" + escapar(a.campus) + "</td>"
            + '<td class="col-acao"><a class="botao-pdf" download href="pdf/Historico_' + escapar(a.matricula) + '.pdf"'
            + ' aria-label="Baixar PDF do histórico de ' + escapar(a.nome) + '">Baixar PDF</a></td>'
            + "</tr>";
    }

    function paginacao(totalPaginas) {
        if (totalPaginas <= 1) {
            return "";
        }
        return '<nav class="paginacao" aria-label="Paginação dos resultados">'
            + '<button type="button" class="botao" data-pagina="' + (paginaAtual - 1) + '"' + (paginaAtual === 1 ? " disabled" : "") + ">« Anterior</button>"
            + "<span>Página " + paginaAtual + " de " + totalPaginas + "</span>"
            + '<button type="button" class="botao" data-pagina="' + (paginaAtual + 1) + '"' + (paginaAtual === totalPaginas ? " disabled" : "") + ">Próxima »</button>"
            + "</nav>";
    }

    function exibir() {
        var total = resultado.length;
        var totalPaginas = Math.max(1, Math.ceil(total / ITENS_POR_PAGINA));
        paginaAtual = Math.min(Math.max(1, paginaAtual), totalPaginas);
        var inicio = (paginaAtual - 1) * ITENS_POR_PAGINA;

        area.innerHTML = (erroRelatorio ? '<p class="mensagem-erro" role="alert">' + escapar(erroRelatorio) + "</p>" : "")
            + "<table><thead><tr><th>Matrícula</th><th>CPF</th><th>Nome</th><th>Curso</th><th>Campus</th>"
            + '<th class="col-acao"><span class="visualmente-oculto">Ações</span></th></tr></thead><tbody>'
            + (total
                ? resultado.slice(inicio, inicio + ITENS_POR_PAGINA).map(linha).join("")
                : '<tr><td colspan="6" class="vazio">Nenhum aluno encontrado com os critérios informados.</td></tr>')
            + "</tbody></table>"
            + (total
                ? '<div class="total-registros" aria-live="polite">' + total + (total === 1 ? " aluno encontrado" : " alunos encontrados") + "</div>"
                    + paginacao(totalPaginas)
                : "");
    }

    function atualizar() {
        erroRelatorio = "";
        paginaAtual = 1;
        resultado = filtrar();
        exibir();
    }

    function baixar(url, nomeArquivo) {
        var link = document.createElement("a");
        link.href = url;
        link.download = nomeArquivo;
        document.body.appendChild(link);
        link.click();
        link.remove();
    }

    function carregarPdfLib() {
        return new Promise(function (resolve, reject) {
            if (window.PDFLib) {
                resolve();
                return;
            }
            var script = document.createElement("script");
            script.src = URL_PDF_LIB;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    function juntarPdfs(lista) {
        return carregarPdfLib()
            .then(function () {
                return Promise.all(lista.map(function (a) {
                    return fetch("pdf/Historico_" + a.matricula + ".pdf").then(function (resposta) {
                        return resposta.arrayBuffer();
                    });
                }));
            })
            .then(function (arquivos) {
                return window.PDFLib.PDFDocument.create().then(function (destino) {
                    return arquivos.reduce(function (anterior, bytes) {
                        return anterior.then(function () {
                            return window.PDFLib.PDFDocument.load(bytes);
                        }).then(function (origem) {
                            return destino.copyPages(origem, origem.getPageIndices());
                        }).then(function (paginas) {
                            paginas.forEach(function (pagina) {
                                destino.addPage(pagina);
                            });
                        });
                    }, Promise.resolve()).then(function () {
                        return destino.save();
                    });
                });
            });
    }

    function baixarRelatorio() {
        if (resultado.length === 0) {
            erroRelatorio = "Não há alunos para incluir no PDF. Ajuste os critérios de busca.";
            exibir();
            return;
        }

        if (!filtroEstaAtivo()) {
            baixar("pdf/Relatorio_Alunos_Todos.pdf", "Relatorio_Alunos_Todos.pdf");
            return;
        }

        botaoPdf.disabled = true;
        juntarPdfs(resultado)
            .then(function (bytes) {
                var url = URL.createObjectURL(new Blob([bytes], { type: "application/pdf" }));
                baixar(url, "Relatorio_Alunos_Filtrados.pdf");
                setTimeout(function () {
                    URL.revokeObjectURL(url);
                }, 10000);
            })
            .catch(function () {
                erroRelatorio = "Não foi possível montar o PDF. Verifique a conexão e tente novamente.";
                exibir();
            })
            .finally(function () {
                botaoPdf.disabled = false;
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

    form.addEventListener("input", atualizar);

    form.addEventListener("submit", function (evento) {
        evento.preventDefault();
        if (evento.submitter === botaoPdf) {
            baixarRelatorio();
        } else {
            atualizar();
        }
    });

    area.addEventListener("click", function (evento) {
        var botao = evento.target.closest("[data-pagina]");
        if (botao) {
            paginaAtual = Number(botao.dataset.pagina);
            exibir();
        }
    });

    document.getElementById("form-sair").addEventListener("submit", function (evento) {
        evento.preventDefault();
        sessionStorage.removeItem(CHAVE_SESSAO);
        window.location.href = "index.html";
    });

    fetch("dados/alunos.json")
        .then(function (resposta) {
            return resposta.json();
        })
        .then(function (dados) {
            alunos = dados;
            atualizar();
        });
})();

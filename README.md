# NEAD-prototipo-php

Protótipo da **Interface de Consulta Centralizada** do NEAD (IF Sudeste MG): login, busca de
alunos e download do histórico escolar em PDF, sem que a secretaria precise escrever SQL.

É a versão em **PHP, CodeIgniter 4 e MySQL** do protótipo estático
[Fsp30/NEAD-prototipo](https://github.com/Fsp30/NEAD-prototipo). Telas, regras e dados de
demonstração são os mesmos; todos os alunos são fictícios.

## O que muda em relação ao protótipo estático

| Tema | Protótipo estático | Esta versão |
| --- | --- | --- |
| Dados | Array em JavaScript, no navegador | MySQL, com migrations e seeders |
| Autenticação | `sessionStorage` e senha no código-fonte | Sessão no servidor e senha com `password_hash` |
| Filtros e paginação | Só no navegador | No servidor, com URL compartilhável; funcionam sem JavaScript |
| PDF | jsPDF, carregado de uma CDN | TCPDF, gerado no servidor |
| Segurança | Nenhuma camada real | Filtros de acesso, CSRF, consultas parametrizadas e escape de saída |
| Qualidade | Sem testes | Testes unitários e de integração com PHPUnit |

## Requisitos

- PHP 8.2 ou mais recente, com as extensões `intl` e `mbstring`
- MySQL ou MariaDB
- [Composer](https://getcomposer.org/)
- Extensão `sqlite3`, apenas para rodar os testes

No **XAMPP**, ative as extensões no `C:\xampp\php\php.ini`, removendo o `;` do início das linhas
`;extension=intl` e `;extension=sqlite3`, e inicie o MySQL pelo painel de controle.

## Como rodar

```bash
composer install
```

Copie o arquivo `env` para `.env` e ajuste a conexão, se necessário. Os valores padrão servem
para o XAMPP (usuário `root`, sem senha).

```bash
cp env .env
```

```bash
php spark db:create nead_prototipo
```

```bash
php spark migrate --all
```

```bash
php spark db:seed "App\Database\Seeds\DatabaseSeeder"
```

```bash
php spark serve
```

Acesse <http://localhost:8080> e entre com o usuário `admin` e a senha `admin`.

Para recriar o banco do zero, rode `php spark migrate:refresh --all` e repita o seeder.

## Testes

```bash
composer test
```

Os testes usam SQLite em memória, então não tocam no banco MySQL.

## Estrutura

```text
app/
├── Config/Routes.php    Rotas e os filtros de acesso de cada uma
├── Controllers/         Recebem a requisição e devolvem a resposta
├── Database/
│   ├── Migrations/      Esquema do banco
│   └── Seeds/           Dados de demonstração (Demo/ reproduz os alunos do original)
├── Entities/            Aluno e RegistroHistorico
├── Filters/             Exigência de login e redirecionamento de quem já entrou
├── Libraries/           Regras acadêmicas, filtro da busca, autenticação e PDF
├── Models/              Consultas ao banco
└── Views/               Layout, telas, paginação e modelo do PDF
public/assets/           CSS e JavaScript
tests/                   Testes unitários e de integração
```

## Decisões de projeto

- **Situação derivada:** aprovado ou reprovado é calculado a partir de nota e frequência em
  `RegrasAcademicas`, em vez de gravado, para os valores nunca divergirem.
- **Busca sem acento:** a coluna `nome_busca` guarda o nome em minúsculas e sem acentos, o que
  mantém a busca igual em qualquer banco e collation.
- **Melhoria progressiva:** o JavaScript só atualiza os resultados sem recarregar a página. Sem
  ele, o formulário e a paginação continuam funcionando.
- **Dados reproduzíveis:** o seeder porta o gerador com semente do original e produz exatamente
  os mesmos 200 alunos.

## Limitações conhecidas

Herdadas de propósito do protótipo original:

- "Tempo de Sessão" é um texto fixo e o link "Módulos" não leva a lugar nenhum.
- Média geral e IRA usam a mesma fórmula.

Antes de usar com dados reais:

- Troque o usuário `admin`, cuja senha é pública. O quadro com as credenciais na tela de login já
  fica oculto quando `CI_ENVIRONMENT = production`.
- Não rode os seeders em produção: eles criam esse usuário e os alunos fictícios.
- Adicione um limite de tentativas de login e registro de auditoria dos acessos.

## Licença

Distribuído sob a licença Apache 2.0, a mesma do protótipo original. Consulte o arquivo
[LICENSE](LICENSE). Este projeto deriva de
[Fsp30/NEAD-prototipo](https://github.com/Fsp30/NEAD-prototipo), reescrito em PHP.

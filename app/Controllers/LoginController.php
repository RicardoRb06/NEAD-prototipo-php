<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class LoginController extends BaseController
{
    private const ERRO_CAMPOS_VAZIOS = 'Informe o usuário e a senha para entrar.';

    private const ERRO_CREDENCIAIS = 'Usuário ou senha incorretos. Use as credenciais de acesso ao protótipo, abaixo do formulário.';

    public function formulario(): string
    {
        return view('login/formulario', [
            'erro'              => session()->getFlashdata('erro'),
            'usuarioDigitado'   => session()->getFlashdata('usuario_digitado') ?? '',
            'exibirCredenciais' => ENVIRONMENT !== 'production',
        ]);
    }

    public function entrar(): RedirectResponse
    {
        $usuario = trim((string) $this->request->getPost('usuario'));
        $senha   = (string) $this->request->getPost('senha');

        if ($usuario === '' || $senha === '') {
            return $this->voltarComErro(self::ERRO_CAMPOS_VAZIOS, $usuario);
        }

        if (! service('autenticacao')->entrar($usuario, $senha)) {
            return $this->voltarComErro(self::ERRO_CREDENCIAIS, $usuario);
        }

        return redirect()->route('alunos');
    }

    public function sair(): RedirectResponse
    {
        service('autenticacao')->sair();

        return redirect()->route('login');
    }

    /**
     * Não usa withInput(), que guardaria também a senha na sessão.
     */
    private function voltarComErro(string $erro, string $usuario): RedirectResponse
    {
        return redirect()->route('login')
            ->with('erro', $erro)
            ->with('usuario_digitado', $usuario);
    }
}

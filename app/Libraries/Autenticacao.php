<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\UsuarioModel;

/**
 * Login e estado de autenticação guardado na sessão.
 */
class Autenticacao
{
    private const CHAVE_SESSAO = 'usuario';

    public function __construct(private readonly UsuarioModel $usuarios)
    {
    }

    public function entrar(string $usuario, string $senha): bool
    {
        $registro = $this->usuarios->porUsuario($usuario);

        if ($registro === null || ! password_verify($senha, $registro['senha_hash'])) {
            return false;
        }

        // Novo id de sessão no login evita fixação de sessão.
        session()->regenerate(true);
        session()->set(self::CHAVE_SESSAO, [
            'id'      => (int) $registro['id'],
            'nome'    => $registro['nome'],
            'unidade' => $registro['unidade'],
        ]);

        return true;
    }

    public function sair(): void
    {
        session()->destroy();
    }

    public function estaAutenticado(): bool
    {
        return $this->usuarioAtual() !== null;
    }

    /**
     * @return array{id: int, nome: string, unidade: string}|null
     */
    public function usuarioAtual(): ?array
    {
        return session()->get(self::CHAVE_SESSAO);
    }
}

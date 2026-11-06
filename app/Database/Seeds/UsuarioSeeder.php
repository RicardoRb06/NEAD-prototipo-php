<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * Usuário de acesso ao protótipo. A senha é pública: não use em produção.
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $agora = Time::now()->toDateTimeString();

        $this->db->table('usuarios')->insert([
            'usuario'    => 'admin',
            'senha_hash' => password_hash('admin', PASSWORD_DEFAULT),
            'nome'       => 'Ana Paula Souza',
            'unidade'    => 'IF Sudeste MG',
            'created_at' => $agora,
            'updated_at' => $agora,
        ]);
    }
}

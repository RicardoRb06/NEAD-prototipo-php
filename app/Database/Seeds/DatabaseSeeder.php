<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Popula o banco com os dados fictícios de demonstração do protótipo.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UsuarioSeeder::class);
        $this->call(CatalogoSeeder::class);
        $this->call(AlunoSeeder::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table         = 'usuarios';
    protected $useTimestamps = true;
    protected $allowedFields = ['usuario', 'senha_hash', 'nome', 'unidade'];

    /**
     * @return array<string, mixed>|null
     */
    public function porUsuario(string $usuario): ?array
    {
        return $this->where('usuario', $usuario)->first();
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CursoModel extends Model
{
    protected $table         = 'cursos';
    protected $allowedFields = ['nome', 'codigo'];

    /**
     * Opções para listas de seleção, na ordem de cadastro.
     *
     * @return array<int, string> id => nome
     */
    public function opcoes(): array
    {
        return array_column($this->orderBy('id')->findAll(), 'nome', 'id');
    }
}

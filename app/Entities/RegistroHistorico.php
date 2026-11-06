<?php

declare(strict_types=1);

namespace App\Entities;

use App\Libraries\RegrasAcademicas;
use CodeIgniter\Entity\Entity;

/**
 * Uma disciplina cursada por um aluno.
 *
 * @property int    $carga_horaria
 * @property string $disciplina
 * @property int    $frequencia
 * @property int    $nota
 * @property int    $numero_periodo
 * @property string $periodo
 */
class RegistroHistorico extends Entity
{
    protected $dates = [];

    protected $casts = [
        'carga_horaria'  => 'integer',
        'frequencia'     => 'integer',
        'nota'           => 'integer',
        'numero_periodo' => 'integer',
    ];

    public function aprovado(): bool
    {
        return RegrasAcademicas::aprovado($this->nota, $this->frequencia);
    }

    public function situacao(): string
    {
        return RegrasAcademicas::situacao($this->nota, $this->frequencia);
    }
}

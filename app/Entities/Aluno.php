<?php

declare(strict_types=1);

namespace App\Entities;

use App\Libraries\Texto;
use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

/**
 * @property string|null $campus       Preenchido pelo join de AlunoModel
 * @property string|null $curso        Preenchido pelo join de AlunoModel
 * @property string      $cpf
 * @property string      $email
 * @property int         $id
 * @property int|null    $ano_formacao
 * @property int         $ano_ingresso
 * @property string      $matricula
 * @property string      $nascimento
 * @property string      $nome
 * @property int         $semestre_ingresso
 * @property string      $telefone
 * @property string      $vinculo
 */
class Aluno extends Entity
{
    protected $dates = [];

    protected $casts = [
        'id'                => 'integer',
        'curso_id'          => 'integer',
        'campus_id'         => 'integer',
        'ano_ingresso'      => 'integer',
        'semestre_ingresso' => 'integer',
        'ano_formacao'      => '?integer',
    ];

    public function cpfFormatado(): string
    {
        return Texto::formatarCpf($this->cpf);
    }

    public function nascimentoFormatado(): string
    {
        return Time::createFromFormat('Y-m-d', $this->nascimento)->format('d/m/Y');
    }

    public function periodoIngresso(): string
    {
        return $this->ano_ingresso . '.' . $this->semestre_ingresso;
    }
}

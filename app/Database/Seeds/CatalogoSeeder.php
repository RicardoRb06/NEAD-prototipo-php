<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Database\Seeds\Demo\DadosBase;
use CodeIgniter\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DadosBase::CAMPI as $campus) {
            $this->db->table('campi')->insert(['nome' => $campus]);
        }

        foreach (DadosBase::GRADES as $curso => $periodos) {
            $this->db->table('cursos')->insert([
                'nome'   => $curso,
                'codigo' => DadosBase::CODIGOS_CURSO[$curso],
            ]);
            $cursoId = $this->db->insertID();

            $disciplinas = [];

            foreach ($periodos as $indice => $periodo) {
                foreach ($periodo as [$nome, $cargaHoraria]) {
                    $disciplinas[] = [
                        'curso_id'       => $cursoId,
                        'numero_periodo' => $indice + 1,
                        'nome'           => $nome,
                        'carga_horaria'  => $cargaHoraria,
                    ];
                }
            }

            $this->db->table('disciplinas')->insertBatch($disciplinas);
        }
    }
}

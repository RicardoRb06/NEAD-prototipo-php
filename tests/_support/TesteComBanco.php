<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Database\Seeds\DatabaseSeeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Base dos testes que usam o banco: aplica as migrations da aplicação e
 * carrega os dados de demonstração uma vez por classe.
 */
abstract class TesteComBanco extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace   = 'App';
    protected $migrateOnce = true;
    protected $seed        = DatabaseSeeder::class;
    protected $seedOnce    = true;
    protected $refresh     = true;
}

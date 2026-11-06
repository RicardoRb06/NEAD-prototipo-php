<?php

use App\Controllers\AlunosController;
use App\Controllers\LoginController;
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->addRedirect('/', 'alunos');

$routes->group('', ['filter' => 'visitante'], static function (RouteCollection $routes): void {
    $routes->get('login', [LoginController::class, 'formulario'], ['as' => 'login']);
    $routes->post('login', [LoginController::class, 'entrar'], ['as' => 'login.entrar']);
});

$routes->group('', ['filter' => 'autenticado'], static function (RouteCollection $routes): void {
    $routes->post('logout', [LoginController::class, 'sair'], ['as' => 'logout']);

    $routes->get('alunos', [AlunosController::class, 'index'], ['as' => 'alunos']);
    $routes->get('alunos/relatorio', [AlunosController::class, 'relatorio'], ['as' => 'alunos.relatorio']);
    $routes->get('alunos/(:alphanum)/historico', [AlunosController::class, 'historico/$1'], ['as' => 'alunos.historico']);
});

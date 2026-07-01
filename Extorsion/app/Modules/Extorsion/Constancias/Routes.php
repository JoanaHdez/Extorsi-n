<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Rutas publicas del modulo Constancias de Extorsion.
 *
 * @var RouteCollection $routes
 */

$routes->get('/constancia/(:any)', '\App\Modules\Extorsion\Constancias\Controllers\ConstanciasController::constancia/$1');
$routes->post('/constancia/(:any)/cuestionario', '\App\Modules\Extorsion\Constancias\Controllers\ConstanciasController::guardarCuestionarioConstancia/$1');
$routes->get('/constancias/control', '\App\Modules\Extorsion\Constancias\Controllers\ConstanciasController::controlConstancias');
$routes->post('/constancias/control', '\App\Modules\Extorsion\Constancias\Controllers\ConstanciasController::actualizarControlConstancias');
$routes->get('/constancias/descargar/(:any)', '\App\Modules\Extorsion\Constancias\Controllers\ConstanciasController::constancia/$1');
$routes->get('/constancias/(:any)', '\App\Modules\Extorsion\Constancias\Controllers\ConstanciasController::constancia/$1');
$routes->get('/descargar-constancia/(:any)', '\App\Modules\Extorsion\Constancias\Controllers\ConstanciasController::constancia/$1');
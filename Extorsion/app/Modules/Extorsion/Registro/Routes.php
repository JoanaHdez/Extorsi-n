<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Rutas publicas del modulo Registro de Extorsion.
 *
 * @var RouteCollection $routes
 */

$routes->get('/', '\App\Modules\Extorsion\Registro\Controllers\RegistroController::index');
$routes->get('/registro', '\App\Modules\Extorsion\Registro\Controllers\RegistroController::index');
$routes->get('registro/exito', '\App\Modules\Extorsion\Registro\Controllers\RegistroController::exito');
$routes->get('/registro/(:segment)', '\App\Modules\Extorsion\Registro\Controllers\RegistroController::index/$1');

$routes->post('/registro/guardar', '\App\Modules\Extorsion\Registro\Controllers\RegistroController::guardar');
$routes->post('/registro/(:segment)/guardar', '\App\Modules\Extorsion\Registro\Controllers\RegistroController::guardar/$1');
$routes->get('/registro/buscar-nomina/(:num)', '\App\Modules\Extorsion\Registro\Controllers\RegistroController::buscarNomina/$1');
$routes->post('/registro/guardar-personal', '\App\Modules\Extorsion\Registro\Controllers\RegistroController::guardarPersonal');
$routes->post('/registro/(:segment)/guardar-personal', '\App\Modules\Extorsion\Registro\Controllers\RegistroController::guardarPersonal/$1');

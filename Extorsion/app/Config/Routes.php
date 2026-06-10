<?php

namespace Config;
use CodeIgniter\Router\RouteCollection;
use Config\Services;

/**
 * @var RouteCollection $routes
 */

$routes = Services::routes();

$routes->get('/', 'Registro_Controller::index');
$routes->get('/registro', 'Registro_Controller::index');
$routes->get('registro/exito', 'Registro_Controller::exito');
$routes->get('/registro/(:segment)', 'Registro_Controller::index/$1');

$routes->post('/registro/guardar', 'Registro_Controller::guardar');
$routes->post('/registro/(:segment)/guardar', 'Registro_Controller::guardar/$1');
$routes->get('/registro/buscar-nomina/(:num)', 'Registro_Controller::buscarNomina/$1');
$routes->post('/registro/guardar-personal', 'Registro_Controller::guardarPersonal');
$routes->post('/registro/(:segment)/guardar-personal', 'Registro_Controller::guardarPersonal/$1');
$routes->get('/reporte', 'Registro_Controller::reporte');
$routes->get('/reporte/cuestionario', 'Registro_Controller::reporteCuestionario');

$routes->get('/constancia/(:segment)', 'Registro_Controller::constancia/$1');
$routes->post('/constancia/(:segment)/cuestionario', 'Registro_Controller::guardarCuestionarioConstancia/$1');
$routes->get('/constancias/control', 'Registro_Controller::controlConstancias');
$routes->post('/constancias/control', 'Registro_Controller::actualizarControlConstancias');

$routes->get('/reporte/exportar', 'Registro_Controller::exportar');

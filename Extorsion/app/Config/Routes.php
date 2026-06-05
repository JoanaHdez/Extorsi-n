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

$routes->post('/registro/guardar', 'Registro_Controller::guardar');
$routes->get('/registro/categorias/(:num)', 'Registro_Controller::categorias/$1');
$routes->get('/listado', 'Registro_Controller::listado');
$routes->get('/registro/buscar-nomina/(:num)', 'Registro_Controller::buscarNomina/$1');
$routes->post('/registro/guardar-personal', 'Registro_Controller::guardarPersonal');
$routes->get('/reporte', 'Registro_Controller::reporte');

$routes->get('registro/exito', 'Registro_Controller::exito');
$routes->get('/constancia/(:segment)', 'Registro_Controller::constancia/$1');
$routes->get('/constancias/control', 'Registro_Controller::controlConstancias');
$routes->post('/constancias/control', 'Registro_Controller::actualizarControlConstancias');

$routes->get('/reporte/exportar', 'Registro_Controller::exportar');

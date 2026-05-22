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
$routes->get('/registro/municipios/(:num)', 'Registro_Controller::municipios/$1');
$routes->get('/registro/categorias/(:num)', 'Registro_Controller::categorias/$1');
$routes->get('/listado', 'Registro_Controller::listado');
$routes->get('/reporte', 'Registro_Controller::reporte');

$routes->get('/reporte/exportar', 'Registro_Controller::exportar');

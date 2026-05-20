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

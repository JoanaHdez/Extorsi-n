<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Rutas publicas de la API de confirmacion de registro.
 *
 * @var RouteCollection $routes
 */

$routes->get('/api/correos/registro', '\App\Modules\CorreoRegistroAPI\Controllers\CorreoRegistroAPI_Controller::estado');
$routes->options('/api/correos/registro', '\App\Modules\CorreoRegistroAPI\Controllers\CorreoRegistroAPI_Controller::preflight');
$routes->post('/api/correos/registro', '\App\Modules\CorreoRegistroAPI\Controllers\CorreoRegistroAPI_Controller::enviarCorreo');

$routes->options('/api/correos/registro/masivo', '\App\Modules\CorreoRegistroAPI\Controllers\CorreoRegistroAPI_Controller::preflight');
$routes->post('/api/correos/registro/masivo', '\App\Modules\CorreoRegistroAPI\Controllers\CorreoRegistroAPI_Controller::enviarMasivo');
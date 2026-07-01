<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Rutas publicas de la API de invitaciones.
 *
 * @var RouteCollection $routes
 */

$routes->options('/api/correos/invitacion', '\App\Modules\CorreosAPI\Controllers\CorreoInvitacionController::preflight');
$routes->post('/api/correos/invitacion', '\App\Modules\CorreosAPI\Controllers\CorreoInvitacionController::enviarCorreo');

$routes->options('/api/correos/invitacion/masivo', '\App\Modules\CorreosAPI\Controllers\CorreoInvitacionController::preflight');
$routes->post('/api/correos/invitacion/masivo', '\App\Modules\CorreosAPI\Controllers\CorreoInvitacionController::enviarMasivo');

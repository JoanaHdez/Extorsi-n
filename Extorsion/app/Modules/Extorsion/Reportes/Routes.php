<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Rutas publicas del modulo Reportes de Extorsion.
 *
 * @var RouteCollection $routes
 */

$routes->get('/reporte', '\App\Modules\Extorsion\Reportes\Controllers\ReportesController::reporte');
$routes->get('/reporte/cuestionario', '\App\Modules\Extorsion\Reportes\Controllers\ReportesController::reporteCuestionario');
$routes->get('/reporte/cuestionario/exportar-comentarios', '\App\Modules\Extorsion\Reportes\Controllers\ReportesController::exportarComentariosCuestionario');
$routes->get('/reporte/exportar', '\App\Modules\Extorsion\Reportes\Controllers\ReportesController::exportar');

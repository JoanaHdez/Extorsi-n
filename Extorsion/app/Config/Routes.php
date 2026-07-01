<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;
use Config\Services;

/**
 * @var RouteCollection $routes
 */

require APPPATH . 'Modules/CorreoRegistroAPI/Routes.php';

require APPPATH . 'Modules/Extorsion/Registro/Routes.php';
require APPPATH . 'Modules/Extorsion/Reportes/Routes.php';
require APPPATH . 'Modules/Extorsion/Constancias/Routes.php';
require APPPATH . 'Modules/CorreosAPI/Routes.php';

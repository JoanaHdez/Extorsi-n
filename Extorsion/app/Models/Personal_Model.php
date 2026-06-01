<?php

namespace App\Models;

use CodeIgniter\Model;

class Personal_Model extends Model
{
    protected $table = 'personal';
    protected $primaryKey = 'id_personal';
    protected $returnType = 'array';

    protected $allowedFields = [
        'nomina',
        'nombre',
        'apellido_p',
        'apellido_m',
        'area',
        'funcion',
        'id_sexo',
    ];
}

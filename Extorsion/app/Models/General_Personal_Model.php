<?php

namespace App\Models;

use CodeIgniter\Model;

class General_Personal_Model extends Model
{
    protected $table = 'general_personal';
    protected $primaryKey = 'id_general_personal';
    protected $returnType = 'array';

    protected $allowedFields = [
        'nomina',
        'correo',
        'id_municipio',
    ];
}

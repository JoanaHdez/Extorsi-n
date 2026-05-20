<?php

namespace App\Models;

use CodeIgniter\Model;

class Municipio_Model extends Model
{
    protected $table = 'municipio';
    protected $primaryKey = 'id_municipio';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_estado',
        'municipio',
    ];
}

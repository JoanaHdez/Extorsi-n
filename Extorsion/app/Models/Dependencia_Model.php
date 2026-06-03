<?php

namespace App\Models;

use CodeIgniter\Model;

class Dependencia_Model extends Model
{
    protected $table = 'dependencia';
    protected $primaryKey = 'id_dependencia';
    protected $returnType = 'array';

    protected $allowedFields = [
        'dependencia',
    ];
}

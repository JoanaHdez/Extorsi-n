<?php

namespace App\Models;

use CodeIgniter\Model;

class Sexo_Model extends Model
{
    protected $table = 'sexo';
    protected $primaryKey = 'id_sexo';
    protected $returnType = 'array';

    protected $allowedFields = [
        'sexo',
    ];
}

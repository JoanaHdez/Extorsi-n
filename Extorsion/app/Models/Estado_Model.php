<?php

namespace App\Models;

use CodeIgniter\Model;

class Estado_Model extends Model
{
    protected $table = 'estado';
    protected $primaryKey = 'id_estado';
    protected $returnType = 'array';

    protected $allowedFields = [
        'estado'
    ];
}

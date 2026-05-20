<?php

namespace App\Models;

use CodeIgniter\Model;

class Dato_Model extends Model
{
    protected $table = 'dato';
    protected $primaryKey = 'id_dato';
    protected $returnType = 'array';

    protected $allowedFields = [
        'nombre',
        'correo',
    ];
}

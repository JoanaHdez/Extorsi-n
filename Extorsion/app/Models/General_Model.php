<?php

namespace App\Models;

use CodeIgniter\Model;

class General_Model extends Model
{
    protected $table = 'general';
    protected $primaryKey = 'id_general';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_dato',
        'id_sexo',
        'dependencia',
        'id_municipio',
        'id_categoria',
        'categoria_otro',
    ];
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class Categoria_Model extends Model
{
    protected $table = 'categoria';
    protected $primaryKey = 'id_categoria';

    protected $returnType = 'array';

    protected $allowedFields = [
        'id_sector',
        'categoria',
    ];
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class Sector_Model extends Model
{
    protected $table = 'sector';
    protected $primaryKey = 'id_sector';
    protected $returnType = 'array';

    protected $allowedFields = [
        'sector',
    ];
}

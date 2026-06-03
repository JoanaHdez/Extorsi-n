<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DependenciaSeeder extends Seeder
{
    public function run()
    {
        $dependencias = [
            'Sector',
            'Gobierno (servidor público)',
            'Ciudadano',
            'Transporte público',
            'Comercial',
            'Comerciantes',
            'Mercado',
            'Iglesia',
            'Otro',
        ];

        $this->db->table('dependencia')->truncate();

        foreach ($dependencias as $dependencia) {
            $this->db->table('dependencia')->insert([
                'dependencia' => $dependencia,
            ]);
        }
    }
}

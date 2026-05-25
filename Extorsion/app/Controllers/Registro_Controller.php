<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Categoria_Model;
use App\Models\Dato_Model;
use App\Models\Estado_Model;
use App\Models\General_Model;
use App\Models\Municipio_Model;
use App\Models\Sector_Model;
use App\Models\Sexo_Model;

class Registro_Controller extends BaseController
{
    public function index()
    {

        $estado = new Estado_Model();
        $municipio = new Municipio_Model();
        $categoria = new Categoria_Model();
        $sector = new Sector_Model();
        $sexo = new Sexo_Model();

        $data['estados'] = $estado->findAll();
        $data['municipios'] = $municipio->findAll();
        $data['categorias'] = $categoria->findAll();
        $data['sectores'] = $sector->findAll();
        $data['sexos'] = $sexo->findAll();

        $data['style'] = 'assets/Css/registro.css';

        return view('head', $data)
            .   view('Registro', $data);
    } 

    public function guardar()
    {
        $rules = [
            'nombre' => 'required|max_length[100]',
            'apellido_p' => 'required|max_length[100]',
            'apellido_m' => 'required|max_length[100]',
            'correo' => 'required|valid_email',
            'id_sexo' => 'required|integer',
            'dependencia' => 'required|max_length[100]',
            'id_estado' => 'required|integer',
            'id_municipio' => 'required|integer',
            'id_sector' => 'required|integer',
            'id_categoria' => 'required|integer',
        ];

        $messages = [
            'nombre' => [
                'required' => 'El campo nombre es obligatorio.',
            ],
            'apellido_p' => [
                'required' => 'El campo apellido paterno es obligatorio.',
            ],
            'apellido_m' => [
                'required' => 'El campo apellido materno es obligatorio.',
            ],
            'correo' => [
                'required' => 'El campo correo es obligatorio.',
            ],
            'id_sexo' => [
                'required' => 'El campo sexo es obligatorio.',
            ],
            'dependencia' => [
                'required' => 'El campo dependencia es obligatorio.',
            ],
            'id_estado' => [
                'required' => 'El campo estado es obligatorio.',
            ],
            'id_municipio' => [
                'required' => 'El campo municipio es obligatorio.',
            ],
            'id_sector' => [
                'required' => 'El campo sector es obligatorio.',
            ],
            'id_categoria' => [
                'required' => 'El campo categoría es obligatorio.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {

            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }
        $categoria = new Categoria_Model();
        $categoriaSeleccionada = $categoria->find($this->request->getPost('id_categoria'));
        $esOtraCategoria = $categoriaSeleccionada
            && strtolower(trim((string) $categoriaSeleccionada['categoria'])) === 'otros';

        if ($esOtraCategoria && trim((string) $this->request->getPost('categoria_otro')) === '') {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['categoria_otro' => 'Debe especificar la categoría cuando selecciona Otros.']);
        }

        $dato = new Dato_Model();
        $general = new General_Model();

        $idDato = $dato->insert([
            'nombre' => $this->request->getPost('nombre'),
            'apellido_p' => $this->request->getPost('apellido_p'),
            'apellido_m' => $this->request->getPost('apellido_m'),
            'correo' => $this->request->getPost('correo'),
        ]);

        $general->insert([
            'id_dato' => $idDato,
            'id_sexo' => $this->request->getPost('id_sexo'),
            'dependencia' => trim((string) $this->request->getPost('dependencia')),
            'id_municipio' => $this->request->getPost('id_municipio'),
            'id_categoria' => $this->request->getPost('id_categoria'),
            'categoria_otro' => $esOtraCategoria
                ? trim((string) $this->request->getPost('categoria_otro'))
                : null,
        ]);

        return redirect()->to('/registro')->with('success', 'Registro guardado correctamente.');
    }

    public function municipios($id_municipio)
    {
        $municipio = new Municipio_Model();

        $municipios = $municipio
            ->where('id_estado', $id_municipio)
            ->findAll();

        return $this->response->setJSON($municipios);
    }

    public function categorias($id_sector)
    {
        $categoria = new Categoria_Model();

        $categorias = $categoria
            ->where('id_sector', $id_sector)
            ->findAll();

        return $this->response->setJSON($categorias);
    }

    public function listado()
    {
        $db = \Config\Database::connect();
        $buscar = $this->request->getGet('buscar');

        $sql = "
        SELECT 
        d.nombre, 
        d.apellido_p, 
        d.apellido_m,
        d.correo,
        s.sexo,
        g.dependencia,
        e.estado,
        m.municipio,
        sec.sector,
        CASE
            WHEN LOWER(c.categoria) = 'otros' AND g.categoria_otro IS NOT NULL AND g.categoria_otro <> ''
                THEN g.categoria_otro
            ELSE c.categoria
        END AS categoria,
        g.fecha_registro

        FROM general g

        inner join dato d on g.id_dato = d.id_dato
        inner join sexo s on g.id_sexo = s.id_sexo
        inner join municipio m on g.id_municipio = m.id_municipio
        INNER JOIN estado e 
            ON m.id_estado = e.id_estado
        inner join categoria c on g.id_categoria = c.id_categoria
        INNER JOIN sector sec 
            ON c.id_sector = sec.id_sector
        ";

        if (!empty($buscar)) {
            $sql .= " 
            WHERE d.nombre LIKE '%$buscar%'
            OR d.correo LIKE '%$buscar%'
            ";
        }

        $query = $db->query($sql);
        $data['registros'] = $query->getResultArray();
        return view('Listado', $data);
    }

    public function reporte()
    {
        $db = \Config\Database::connect();

        $total = $db->query("
        SELECT COUNT(*) AS total 
        FROM general");

        $sexo = $db->query("
        SELECT s.sexo, COUNT(*) AS total
        FROM general g
        INNER JOIN sexo s 
        ON g.id_sexo = s.id_sexo
        GROUP BY s.sexo
        ");

        $dependencia = $db->query("
        SELECT g.dependencia, COUNT(*) AS total
        FROM general g
        WHERE g.dependencia IS NOT NULL
        AND g.dependencia <> ''
        GROUP BY g.dependencia
        ");

        $dias = $db->query("
        SELECT DATE(g.fecha_registro) AS fecha, COUNT(*) AS total
        FROM general g
        GROUP BY DATE(g.fecha_registro)
        ORDER BY fecha
        ");

        $estado = $db->query("
        SELECT e.estado, COUNT(*) AS total
        FROM general g
        JOIN municipio m
        ON g.id_municipio = m.id_municipio
        JOIN estado e
        ON m.id_estado = e.id_estado
        GROUP BY e.estado
        ");

        $municipio = $db->query("
        SELECT m.municipio, COUNT(*) AS total
        FROM general g
        JOIN municipio m
        ON g.id_municipio = m.id_municipio
        GROUP BY m.municipio
        ");

        $sector = $db->query("
        SELECT sec.sector, COUNT(*) AS total
        FROM general g
        JOIN categoria c
        ON g.id_categoria = c.id_categoria
        JOIN sector sec
        ON c.id_sector = sec.id_sector
        GROUP BY sec.sector
        ");

        $categoria = $db->query("
        SELECT categoria, COUNT(*) AS total
        FROM (
            SELECT
                CASE
                    WHEN LOWER(c.categoria) = 'otros' AND g.categoria_otro IS NOT NULL AND g.categoria_otro <> ''
                        THEN g.categoria_otro
                    ELSE c.categoria
                END AS categoria
            FROM general g
            JOIN categoria c
            ON g.id_categoria = c.id_categoria
        ) categorias
        GROUP BY categoria
        ");

        $registros = $db->query("
SELECT 
    d.nombre,
    d.apellido_p,
    d.apellido_m,
    d.correo,
    m.municipio
FROM general g
INNER JOIN dato d ON g.id_dato = d.id_dato
INNER JOIN municipio m ON g.id_municipio = m.id_municipio
");

        $dashboard = $db->query("
        SELECT
            g.id_general,
            s.sexo,
            e.estado,
            m.municipio,
            sec.sector,
            CASE
                WHEN LOWER(c.categoria) = 'otros' AND g.categoria_otro IS NOT NULL AND g.categoria_otro <> ''
                    THEN g.categoria_otro
                ELSE c.categoria
            END AS categoria,
            DATE(g.fecha_registro) AS fecha,
            g.fecha_registro
        FROM general g
        INNER JOIN sexo s ON g.id_sexo = s.id_sexo
        INNER JOIN municipio m ON g.id_municipio = m.id_municipio
        INNER JOIN estado e ON m.id_estado = e.id_estado
        INNER JOIN categoria c ON g.id_categoria = c.id_categoria
        INNER JOIN sector sec ON c.id_sector = sec.id_sector
        ");

        $data['total'] = $total->getRow()->total;
        $data['sexo'] = $sexo->getResultArray();
        $data['dependencia'] = $dependencia->getResultArray();
        $data['dias'] = $dias->getResultArray();
        $data['estado'] = $estado->getResultArray();
        $data['municipio'] = $municipio->getResultArray();
        $data['sector'] = $sector->getResultArray();
        $data['categoria'] = $categoria->getResultArray(); 
        $data['registros'] = $registros->getResultArray();
        $data['dashboard'] = $dashboard->getResultArray();

        /* $data['total'] = 120;

$data['sexo'] = [
    ['sexo' => 'Masculino', 'total' => 70],
    ['sexo' => 'Femenino', 'total' => 50],
];

$data['dependencia'] = [
    ['dependencia' => 'Seguridad', 'total' => 40],
    ['dependencia' => 'Administración', 'total' => 80],
];

$data['estado'] = [
    ['estado' => 'CDMX', 'total' => 90],
];

$data['municipio'] = [
    ['municipio' => 'Nezahualcóyotl', 'total' => 60],
];

$data['sector'] = [
    ['sector' => 'Público', 'total' => 100],
];

$data['categoria'] = [
    ['categoria' => 'Alta', 'total' => 30],
    ['categoria' => 'Media', 'total' => 90],
]; */

$data['style'] = 'assets/Css/reporte.css';
return view('head', $data)
        . view('Reporte', $data);
    }

    public function exportar()
    {
        $db = \Config\Database::connect();

        $query = $db->query("
        SELECT 
        d.nombre, 
        d.apellido_p, 
        d.apellido_m,
        d.correo,
        s.sexo,
        g.dependencia,
        e.estado,
        m.municipio,
        sec.sector,
        CASE
            WHEN LOWER(c.categoria) = 'otros' AND g.categoria_otro IS NOT NULL AND g.categoria_otro <> ''
                THEN g.categoria_otro
            ELSE c.categoria
        END AS categoria,
        g.fecha_registro

        FROM general g

        INNER JOIN dato d ON g.id_dato = d.id_dato
        INNER JOIN sexo s ON g.id_sexo = s.id_sexo
        INNER JOIN municipio m ON g.id_municipio = m.id_municipio
        INNER JOIN estado e ON m.id_estado = e.id_estado
        INNER JOIN categoria c ON g.id_categoria = c.id_categoria
        INNER JOIN sector sec ON c.id_sector = sec.id_sector
        ");

        $registros = $query->getResultArray();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=registro.csv');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Correo',
            'Sexo',
            'Dependencia',
            'Estado',
            'Municipio',
            'Sector',
            'Categoría',
            'Fecha de Registro'
        ]);

        foreach ($registros as $fila) {
            fputcsv($output, [
                $fila['nombre'],
                $fila['apellido_p'],
                $fila['apellido_m'],
                $fila['correo'],
                $fila['sexo'],
                $fila['dependencia'],
                $fila['estado'],
                $fila['municipio'],
                $fila['sector'],
                $fila['categoria'],
                $fila['fecha_registro']
            ]);
        }

        fclose($output);
        exit;
    }
}

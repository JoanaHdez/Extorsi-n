<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Categoria_Model;
use App\Models\Dato_Model;
use App\Models\Dependencia_Model;
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
        $dependencia = new Dependencia_Model();
        $categoria = new Categoria_Model();
        $sector = new Sector_Model();
        $sexo = new Sexo_Model();

        $data['estados'] = $estado->findAll();
        $data['municipios'] = $municipio->findAll();
        $data['dependencias'] = $dependencia->findAll();
        $data['categorias'] = $categoria->findAll();
        $data['sectores'] = $sector->findAll();
        $data['sexos'] = $sexo->findAll();

        return view('Registro', $data);
    }

    public function guardar()
    {
        $rules = [
            'nombre' => 'required|max_length[100]',
            'apellido_p' => 'required|max_length[100]',
            'apellido_m' => 'required|max_length[100]',
            'correo' => 'required|valid_email',
            'id_sexo' => 'required|integer',
            'id_dependencia' => 'required|integer',
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
            'id_dependencia' => [
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
            'id_dependencia' => $this->request->getPost('id_dependencia'),
            'id_municipio' => $this->request->getPost('id_municipio'),
            'id_categoria' => $this->request->getPost('id_categoria'),
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
        dep.dependencia,
        e.estado,
        m.municipio,
        sec.sector,
        c.categoria,
        g.fecha_registro

        FROM general g

        inner join dato d on g.id_dato = d.id_dato
        inner join sexo s on g.id_sexo = s.id_sexo
        inner join dependencia dep on g.id_dependencia = dep.id_dependencia
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
        SELECT d.dependencia, COUNT(*) AS total
        FROM general g
        JOIN dependencia d
        ON g.id_dependencia = d.id_dependencia
        GROUP BY d.dependencia
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
        SELECT c.categoria, COUNT(*) AS total
        FROM general g
        JOIN categoria c
        ON g.id_categoria = c.id_categoria
        GROUP BY c.categoria
        "); 

        $data['total'] = $total->getRow()->total;
        $data['sexo'] = $sexo->getResultArray();
        $data['dependencia'] = $dependencia->getResultArray();
        $data['estado'] = $estado->getResultArray();
        $data['municipio'] = $municipio->getResultArray();
        $data['sector'] = $sector->getResultArray();
        $data['categoria'] = $categoria->getResultArray();
        return view('Reporte', $data);
    }
}

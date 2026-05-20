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
        $dato = new Dato_Model();
        $general = new General_Model();

        $idDato = $dato->insert([
            'nombre' => $this->request->getPost('nombre'),
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
}

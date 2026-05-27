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

        $data['estados'] = $estado->orderBy('estado', 'ASC')->findAll();
        $data['municipios'] = $municipio->orderBy('municipio', 'ASC')->findAll();
        $data['categorias'] = $categoria->orderBy('categoria', 'ASC')->findAll();
        $data['sectores'] = $sector->orderBy('sector', 'ASC')->findAll();
        $data['sexos'] = $sexo->orderBy('sexo', 'ASC')->findAll();

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
            'id_sector' => 'permit_empty|integer',
            'id_categoria' => 'permit_empty|integer',
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
                'required' => 'El campo categorí­a es obligatorio.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {

            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }
        $dependenciaTexto = mb_strtolower(
            $this->sinAcentos(
                trim($this->request->getPost('dependencia'))
            )
        );


        $esComisaria =
            strpos($dependenciaTexto, 'comisaria') !== false
            || strpos($dependenciaTexto, 'cgsc') !== false;

        $categoria = new Categoria_Model();

        $categoriaId = $this->request->getPost('id_categoria');

        $categoriaSeleccionada = null;

        if (!empty($categoriaId)) {
            $categoriaSeleccionada = $categoria->find($categoriaId);
        }

        $esOtraCategoria = false;

        if ($categoriaSeleccionada) {
            $esOtraCategoria =
                strtolower(trim((string) $categoriaSeleccionada['categoria'])) === 'otros';
        }

        if (
            !$esComisaria &&
            $esOtraCategoria &&
            trim((string) $this->request->getPost('categoria_otro')) === ''
        ) {

            return redirect()->back()
                ->withInput()
                ->with('errors', [
                    'categoria_otro' => 'Debe especificar la categoría cuando selecciona Otros.'
                ]);
        }

        $db = \Config\Database::connect();

        $totalRegistros = $db->table('general')->countAll();

        if ($totalRegistros >= 600) {

            return redirect()->back()
                ->withInput()
                ->with('errors', [
                    'limite' => 'El sistema ha alcanzado el límite máximo de 600 registros.'
                ]);
        }

        $dato = new Dato_Model();
        $general = new General_Model();
        $idDato = $dato->insert([
            'nombre' => mb_strtoupper($this->sinAcentos(trim($this->request->getPost('nombre')))),
            'apellido_p' => mb_strtoupper($this->sinAcentos(trim($this->request->getPost('apellido_p')))),
            'apellido_m' => mb_strtoupper($this->sinAcentos(trim($this->request->getPost('apellido_m')))),
            'correo' => mb_strtoupper(trim($this->request->getPost('correo'))),
        ]);

        $general->insert([
            'id_dato' => $idDato,
            'id_sexo' => $this->request->getPost('id_sexo'),
            'dependencia' => mb_strtoupper($this->sinAcentos(trim($this->request->getPost('dependencia')))),
            'id_municipio' => $this->request->getPost('id_municipio'),
            'id_categoria' => !$esComisaria && !empty($categoriaId)
                ? $categoriaId
                : null,
            'categoria_otro' => $esOtraCategoria
                ? mb_strtoupper($this->sinAcentos(trim($this->request->getPost('categoria_otro'))))
                : null,
        ]);

        return redirect()->to('/registro')->with('success', 'Registro guardado correctamente.');
    }

    public function municipios($id_municipio)
    {
        $municipio = new Municipio_Model();

        $municipios = $municipio
            ->where('id_estado', $id_municipio)
            ->orderBy('municipio', 'ASC')
            ->findAll();

        return $this->response->setJSON($municipios);
    }

    public function categorias($id_sector)
    {
        $categoria = new Categoria_Model();

        $categorias = $categoria
            ->where('id_sector', $id_sector)
            ->orderBy('categoria', 'ASC')
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
        LEFT JOIN categoria c 
        ON g.id_categoria = c.id_categoria

        LEFT JOIN sector sec 
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

        $dias = $db->query("
        SELECT DATE(g.fecha_registro) AS fecha, COUNT(*) AS total
        FROM general g
        GROUP BY DATE(g.fecha_registro)
        ORDER BY fecha
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
        LEFT JOIN categoria c ON g.id_categoria = c.id_categoria
        LEFT JOIN sector sec ON c.id_sector = sec.id_sector
                ");

        $data['total'] = $total->getRow()->total;
        $data['dias'] = $dias->getResultArray();
        $data['sector'] = $sector->getResultArray();
        $data['registros'] = $registros->getResultArray();
        $data['dashboard'] = $dashboard->getResultArray();

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
        IFNULL(sec.sector, 'NO APLICA') AS sector,
                CASE
            WHEN c.categoria IS NULL
                THEN 'NO APLICA'

        WHEN LOWER(c.categoria) = 'otros'
            AND g.categoria_otro IS NOT NULL
            AND g.categoria_otro <> ''
            THEN g.categoria_otro

        ELSE c.categoria
        END AS categoria,
                g.fecha_registro

        FROM general g

        INNER JOIN dato d ON g.id_dato = d.id_dato
        INNER JOIN sexo s ON g.id_sexo = s.id_sexo
        INNER JOIN municipio m ON g.id_municipio = m.id_municipio
        INNER JOIN estado e ON m.id_estado = e.id_estado
        LEFT JOIN categoria c ON g.id_categoria = c.id_categoria
        LEFT JOIN sector sec ON c.id_sector = sec.id_sector
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
            'Categoria',
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

    private function sinAcentos($cadena)
    {
        $buscar = ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'];
        return str_replace($buscar, $reemplazar, $cadena);
    }
}
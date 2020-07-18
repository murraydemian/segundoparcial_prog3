<?php
class Usuario{
    public $id;
    public $correo;
    public $clave;
    public $nombre;
    public $apellido;
    public $perfil;
    public $foto;

    public function __construct($objJson){
        $this->id = isset($objJson->id) ? $objJson->id : -1;
        $this->correo = isset($objJson->correo) ? $objJson->correo : null;
        $this->clave = isset($objJson->clave) ? $objJson->clave : null;
        $this->nombre = isset($objJson->nombre) ? $objJson->nombre : null;
        $this->apellido = isset($objJson->apellido) ? $objJson->apellido : null;
        $this->perfil = isset($objJson->perfil) ? $objJson->perfil : null;
        $this->foto = isset($objJson->foto) ? $objJson->foto : null;
    }

    public function GetStructure(){
        $structure = [0 => ['name' => 'correo', 'value' => $this->correo, 'type' => PDO::PARAM_STR],
                    1 => ['name' => 'nombre', 'value' => $this->nombre, 'type' => PDO::PARAM_STR],
                    2 => ['name' => 'apellido', 'value' => $this->apellido, 'type' => PDO::PARAM_STR],
                    3 => ['name' => 'perfil', 'value' => $this->perfil, 'type' => PDO::PARAM_STR],
                    4 => ['name' => 'foto', 'value' => $this->foto, 'type' => PDO::PARAM_STR],
                    5 => ['name' => 'clave', 'value' => $this->clave, 'type' => PDO::PARAM_STR]];
        return $structure;
    }
    public function GetId(){
        return ['name' => 'id', 'value' => $this->id, 'type' => PDO::PARAM_INT];
    }

    static function GetAll($request, $response){
        $items = DataAccess::GetAll('usuarios');
        foreach($items as $user){
            if($user->clave){
                unset($user->clave);
            }
        }
        if($items != null){
            $responseJson['exito'] = true;
            $responseJson['mensaje'] = 'Conexion exitosa, usuarios leidos: ' . count($items);
            $responseJson['tabla'] = $items;
            $responseJson['status'] = 200;            
        }else{
            $responseJson['exito'] = false;
            $responseJson['mensaje'] = 'Conexion fallida.';
            $responseJson['tabla'] = null;
            $responseJson['status'] = 400;
        }
        $response->getBody()->write(json_encode($responseJson));
        return $response;
    }
    static function AddOne($request, $response){
        $body = $request->getParsedBody();
        $jsonArgs = json_decode($body['cadenaJson']);
        $obj = new Usuario($jsonArgs);        
        if(DataAccess::AddOne('usuarios', $obj)){
            $foto = isset($request->getUploadedFiles()['foto']) ? $request->getUploadedFiles()['foto'] : null;            
            if($foto != null){
                $obj->id = DataAccess::GetIndex('usuarios', $obj->GetStructure()[1]);//el subindice 1 corresponde al correo
                $fname = ''. $obj->id .'-'. date('Ymd') .'.png';
                $obj->foto = $fname;
                $foto->moveTo(__DIR__ . '/../fotos/' . $fname);
                DataAccess::Update('usuarios', $obj, $obj->getId());
                ///
                $responseJson['exito'] = true;
                $responseJson['mensaje'] = 'Se cargo el usuario con foto.';
                $responseJson['status'] = 200;
            }else{
                $responseJson['exito'] = true;
                $responseJson['mensaje'] = 'Se cargo el usuario sin foto.';
                $responseJson['status'] = 200;
            }
        }else{
            $responseJson['exito'] = false;
            $responseJson['mensaje'] = 'Error en carga de datos.';
            $responseJson['status'] = 403;
        }
        $response->getBody()->write(json_encode($responseJson));
        return $response;
    }
}
<?php
class Auto{
    private $id;
    private $color;
    private $marca;
    private $precio;
    private $modelo;

    public function __construct($objJson){
        $this->id = isset($objJson->id) ? $objJson->id : -1;
        $this->color = $objJson->color;
        $this->marca = $objJson->marca;
        $this->precio = $objJson->precio;
        $this->modelo = $objJson->modelo;
    }

    public function GetStructure(){
        $structure = [0 => ['name' => 'color', 'value' => $this->color, 'type' => PDO::PARAM_STR],
                    1 => ['name' => 'marca', 'value' => $this->marca, 'type' => PDO::PARAM_STR],
                    2 => ['name' => 'precio', 'value' => $this->precio, 'type' => PDO::PARAM_INT],
                    3 => ['name' => 'modelo', 'value' => $this->modelo, 'type' => PDO::PARAM_STR]];
        return $structure;
    }
    public function GetId(){
        return ['name' => 'id', 'value' => $this->id, 'type' => PDO::PARAM_INT];
    }

    public function AddOne($request, $response){
        $body = $request->getParsedBody();
        $jsonArgs = json_decode($body['cadenaJson']);
        $objAuto = new Auto($jsonArgs);
        if(DataAccess::AddOne('autos', $objAuto)){
            $responseJson['exito'] = true;
            $responseJson['mensaje'] = 'Se cargaron los datos.';
            $responseJson['status'] = 200;
        }else{
            $responseJson['exito'] = false;
            $responseJson['mensaje'] = 'Error en carga de datos.';
            $responseJson['status'] = 418;
        }
        $response->getBody()->write(json_encode($responseJson));
        return $response;
    }

    static function GetAll($request, $response){
        $items = DataAccess::GetAll('autos');
        if($items != null){
            $responseJson['exito'] = true;
            $responseJson['mensaje'] = 'Conexion exitosa.';
            $responseJson['tabla'] = $items;
            $responseJson['status'] = 200;            
        }else{
            $responseJson['exito'] = false;
            $responseJson['mensaje'] = 'Conexion fallida.';
            $responseJson['tabla'] = null;
            $responseJson['status'] = 400;
        }
        $response = isset($response) ? $response : new Response();
        $response->getBody()->write(json_encode($responseJson));
        return $response;
    }

    public static function Delete($request, $response){            
        $args = @json_decode(($request->getHeader('cadenaJson'))[0]);     
        if(Authenticator::AuthorizeProfiles($request, ['propietario']) && $args != null){
            $where = ['name' => 'id', 'value' => $args->id, 'type' => PDO::PARAM_INT];
            $ok = DataAccess::Delete('autos', $where);        
            if($ok){
                $responseJson['exito'] = true;
                $responseJson['mensaje'] = 'Item eliminado.';
                $responseJson['status'] = 200;
            }else{
                $responseJson['exito'] = false;
                $responseJson['mensaje'] = 'Item no existente.';
                $responseJson['status'] = 418;
            }
        }else{
            $responseJson['exito'] = false;
            $responseJson['mensaje'] = 'Parametros insuficioentes.';
            $responseJson['status'] = 418;
        }
        $response = isset($response) ? $response : new Response();
        $response->GetBody()->write(json_encode($responseJson));
        return $response;                
    }

    public function Modify($request, $response){
        $args = @json_decode(($request->getHeader('cadenaJson'))[0]);
        $objAuto = new Auto($args);
        if(Authenticator::AuthorizeProfiles($request, ['propietario', 'encargado'])){
            if(DataAccess::Update('autos', $objAuto, $objAuto->GetId())){
                $responseJson['exito'] = true;
                $responseJson['mensaje'] = 'Se modificaron los datos.';
                $responseJson['status'] = 200;
            }else{
                $responseJson['exito'] = false;
                $responseJson['mensaje'] = 'Error en modificacion de datos.';
                $responseJson['status'] = 418;
            }
        }else{
            $responseJson['exito'] = false;
            $responseJson['mensaje'] = 'Perfil invalido.';
            $responseJson['status'] = 418;
        }
        $response = isset($response) ? $response : new Response();
        $response->getBody()->write(json_encode($responseJson));
        return $response;
    }

}
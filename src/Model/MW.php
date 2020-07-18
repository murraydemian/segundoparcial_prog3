<?php
use Slim\Psr7\Response;
use Firebase\JWT\JWT;
class MW{
    public function VerifyEmailPasswordSet($request, $handler){
        //var_dump($handler);
        $body = $request->getParsedBody();
        $cadenaJson = json_decode($body['cadenaJson']);
        if(isset($cadenaJson->correo) && isset($cadenaJson->clave)){
            $response = $handler->handle($request);
        }else{
            $responseJson['mensaje'] = 'Correo y/o clave no seteados.';
            $responseJson['status'] = 403;
            $response = new Response();
            $response->getBody()->write(json_encode($responseJson));
        }
        return $response;
    }
    public static function VerifyEmailPasswordEmpty($request, $handler){
        $response = new Response();
        $body = $request->getParsedBody();
        $params = json_decode($body['cadenaJson']);
        if($params->correo != "" && $params->clave != ""){
            $response = $handler->handle($request);
        }else if($params->correo == "" && $params->clave != ""){
            $responseJson['mensaje'] = 'Correo no definido.';
            $responseJson['status'] = 409;
            $response->getBody()->write(json_encode($responseJson));
        }else if($params->correo != "" && $params->clave == ""){
            $responseJson['mensaje'] = 'Clave no definida.';
            $responseJson['status'] = 409;
            $response->getBody()->write(json_encode($responseJson));
        }else{
            $responseJson['mensaje'] = 'Correo y clave no definidos.';
            $responseJson['status'] = 409;
            $response->getBody()->write(json_encode($responseJson));
        }
        return $response;
    }
    public function VerifyExistsLogin($request, $handler){
        $response = new Response();
        $body = $request->GetParsedBody();
        $params = json_decode($body['cadenaJson']);
        $user = DataAccess::GetWhereParam('usuarios', ['name'=>'correo', 'value'=>$params->correo, 'type'=>PDO::PARAM_STR]);
        if($user == null || $user == false){
            $responseJson['mensaje'] = 'Usuario inexistente.';
            $responseJson['status'] = 403;
            $response->getBody()->write(json_encode($responseJson));
        }else{
            if($user->clave == $params->clave){
                $response = $handler->handle($request);
            }else{
                $responseJson['mensaje'] = 'Clave incorrecta.';
                $responseJson['status'] = 403;
                $response->getBody()->write(json_encode($responseJson));
            }
        }
        return $response;
    }
    public static function VerifyExistRegister($request, $handler){
        $response = new Response();
        $body = $request->GetParsedBody();
        $params = json_decode($body['cadenaJson']);
        $user = DataAccess::GetWhereParam('usuarios', ['name'=>'correo', 'value'=>$params->correo, 'type'=>PDO::PARAM_STR]);
        if($user == null || $user == false){
            $response = $handler->handle($request);
        }else{
            $responseJson['mensaje'] = 'Correo ya usado.';
            $responseJson['status'] = 403;
            $response->getBody()->write(json_encode($responseJson));
        }
        return $response;
    }
    public static function VerifyPriceRange($request, $handler){
        $response = new Response();
        $body = $request->GetParsedBody();
        $params = json_decode($body['cadenaJson']);
        if($params->precio >= 50000 && $params->precio <= 600000){
            $response = $handler->handle($request);
        }else{
            $responseJson['mensaje'] = 'Precio fuera de rango.';
            $responseJson['status'] = 409;
            $response->getBody()->write(json_encode($responseJson));
        }
        return $response;
    }
    public function VerifyToken($request, $handler){
        $response = new Response();
        $head = $request->getHeaders();
        $token = isset($head['CitronelaToken'][0]) ? $head['CitronelaToken'][0] : null;
        $sec = @json_decode(file_get_contents(__DIR__. '/privatekey.json'));
        if($sec != null && $token != null){
            try{
                JWT::decode($token, $sec->key, [$sec->alg]);
                $response = $handler->handle($request);
            }catch(Exception $e){
                $responseJson['mensaje'] = 'Token invalido.';
                $responseJson['status'] = 403;
                $response->getBody()->write(json_encode($responseJson));
            }
        }else{
            $responseJson['mensaje'] = 'Error interno.';
            $responseJson['status'] = 403;
            $response->getBody()->write(json_encode($responseJson));
        }        
        return $response;
    }
    public function VerifyEncargado($request, $handler){
        try{
            $response = new Response();
            $head = $request->getHeaders();
            $token = isset($head['CitronelaToken'][0]) ? $head['CitronelaToken'][0] : null;
            $sec = json_decode(file_get_contents(__DIR__. '/privatekey.json'));
            if($token != null){
                $decoded = JWT::decode($token, $sec->key, [$sec->alg]);
                if($decoded->inf->perfil == 'encargado'){
                    $responseJson['mensaje'] = 'Perfil de encargado.';
                    $responseJson['status'] = 200;
                    $responseJson['encargado'] = true;
                }else{
                    $responseJson['mensaje'] = 'Perfil invalido. Perfil de '. $decoded->inf->perfil .'.';
                    $responseJson['status'] = 409;
                    $responseJson['encargado'] = false;
                }
            }else{
                $responseJson['mensaje'] = 'Token no seteado.';
                $responseJson['status'] = 409;
                $responseJson['encargado'] = false;
            }
        }catch(Exception $e){
            $responseJson['mensaje'] = 'Token invalido.';
            $responseJson['status'] = 409;
            $responseJson['encargado'] = false;
        }finally{
            $response = $handler->handle($request);
            $response->getBody()->write(json_encode($responseJson));
            return $response;
        }
    }
    public static function VerifyPropietario($request, $handler){
        try{
            $response = new Response();
            $head = $request->getHeaders();
            $token = isset($head['CitronelaToken'][0]) ? $head['CitronelaToken'][0] : null;
            $sec = json_decode(file_get_contents(__DIR__. '/privatekey.json'));
            if($token != null){
                $decoded = JWT::decode($token, $sec->key, [$sec->alg]);
                if($decoded->inf->perfil == 'propietario'){
                    $responseJson['mensaje'] = 'Perfil de propietario.';
                    $responseJson['status'] = 200;
                    $responseJson['propietario'] = true;
                }else{
                    $responseJson['mensaje'] = 'Perfil invalido. Perfil de '. $decoded->inf->perfil .'.';
                    $responseJson['status'] = 409;
                    $responseJson['propietario'] = false;
                }
            }else{
                $responseJson['mensaje'] = 'Token no seteado.';
                $responseJson['status'] = 409;
                $responseJson['propietario'] = false;
            }
        }catch(Exception $e){
            $responseJson['mensaje'] = 'Token invalido.';
            $responseJson['status'] = 409;
            $responseJson['propietario'] = false;
        }finally{
            $response = $handler->handle($request);
            $response->getBody()->write(json_encode($responseJson));
            return $response;
        }
    }
    public static function FilterCarListByProfile_encargado($request, $handler){
        $response = $handler->handle($request);
        if(Authenticator::AuthorizeProfiles($request, ['encargado'])){
            $jsonResponse = json_decode($response->GetBody()->__ToString());
            foreach($jsonResponse->tabla as $item){
                unset($item->id);
            }
            $response = new Response();
            $response->getBody()->write(json_encode($jsonResponse));                
        }
        return $response;
    }
    public static function FilterCarListByProfile_empleado($request, $handler){
        $response = $handler->handle($request);
        if(Authenticator::AuthorizeProfiles($request, ['empleado'])){
            $jsonResponse = json_decode($response->GetBody()->__ToString());
            $colores = array();
            foreach($jsonResponse->tabla as $item){
                if(!in_array($item->color, $colores)){
                    array_push($colores, $item->color);
                }
            }
            //$jsonResponse->colores = count($colores);
            $jsonResponse->tabla = ['cantidad'=>count($colores),'lista'=>$colores];
            $response = new Response();
            $response->getBody()->write(json_encode($jsonResponse));
        }
        return $response;
    }
    public static function FilterCarListByProfile_propietario($request, $handler){
        $response = $handler->handle($request);
        if(Authenticator::AuthorizeProfiles($request, ['propietario'])){
            $jsonResponse = json_decode($response->GetBody()->__ToString());
            $head = $request->getHeaders();
            $cadenaJson = isset($head['cadenaJson'][0]) ? json_decode($head['cadenaJson'][0]) : null;
            $selectedID = @$cadenaJson->id;
            if($selectedID != null){
                $tabla = array();
                foreach($jsonResponse->tabla as $item){
                    if($item->id == $selectedID){array_push($tabla, $item);}
                }
                $jsonResponse->tabla = $tabla;
            }
            $response = new Response();
            $response->getBody()->write(json_encode($jsonResponse));
        }
        return $response;
    }
    public static function FilterUserListByProfile_encargado($request, $handler){
        $response = $handler->handle($request);
        if(Authenticator::AuthorizeProfiles($request, ['encargado'])){
            $jsonResponse = json_decode($response->GetBody()->__ToString());
            if(isset($jsonResponse->tabla)){
                foreach($jsonResponse->tabla as $item){
                    unset($item->id);
                }
            }
            $response = new Response();
            $response->getBody()->write(json_encode($jsonResponse));
        }
        return $response;
    }
    public static function FilterUserListByProfile_empleado($request, $handler){
        $response = $handler->handle($request);
        if(Authenticator::AuthorizeProfiles($request, ['empleado'])){
            $jsonResponse = json_decode($response->GetBody()->__ToString());
            if(isset($jsonResponse->tabla)){
                foreach($jsonResponse->tabla as $item){
                    unset($item->id, $item->correo, $item->perfil);
                }
            }
            $response = new Response();
            $response->getBody()->write(json_encode($jsonResponse));
        }
        return $response;
    }
    public static function FilterUserListByProfile_propietario($request, $handler){
        $response = $handler->handle($request);
        if(Authenticator::AuthorizeProfiles($request, ['propietario'])){
            $jsonResponse = json_decode($response->GetBody()->__ToString());            
            if(isset($jsonResponse->tabla)){
                $head = $request->getHeaders();
                $cadenaJson = isset($head['cadenaJson'][0]) ? json_decode($head['cadenaJson'][0]) : null;
                if(isset($cadenaJson->apellido)){
                    $apellidos[$cadenaJson->apellido] = 0;
                    foreach($jsonResponse->tabla as $item){
                        if($item->apellido == $cadenaJson->apellido){
                            $apellidos[$cadenaJson->apellido]++;
                        }
                    }
                }else{
                    $apellidos = array();
                    foreach($jsonResponse->tabla as $item){
                        if(in_array($item->apellido, array_keys($apellidos))){
                            $apellidos[$item->apellido]++;
                        }else{
                            $apellidos[$item->apellido] = 1;
                        }
                    }
                }
                $jsonResponse->apellidos = $apellidos;
            }
            $response = new Response();
            $response->getBody()->write(json_encode($jsonResponse));
        }
        return $response;
    }
}
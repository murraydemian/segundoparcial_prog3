<?php
require_once __DIR__ .'/../depend/vendor/autoload.php';
use Firebase\JWT\JWT;
class Authenticator{
    static $TOKENLIFESPAN = 360;
    public static function CreateJWT($userinfo = null){
        $time = time();
        $sec = json_decode(file_get_contents(__DIR__. '/privatekey.json'));
        $payload = array(
        	'iat'=>$time,
            'exp' => $time + (60 * Authenticator::$TOKENLIFESPAN),
            'aud' => Authenticator::CreateAud(),
            'inf' => $userinfo,
            'app' => "api segundoparcial"
        );
        return JWT::encode($payload, $sec->key, $sec->alg);
    }
    public static function VerifyToken($request, $response){
        //Gets request header & extracts token string
        $head = $request->getHeaders();
        $token = isset($head['CitronelaToken'][0]) ? $head['CitronelaToken'][0] : null;
        //Gets private key
        $sec = @json_decode(file_get_contents(__DIR__. '/privatekey.json'));
        if($sec != null && $token != null){
            try{
                JWT::decode($token, $sec->key, [$sec->alg]);
                $responseJson['mensaje'] = 'Token valido.';
                $responseJson['status'] = 200;
            }catch(Exception $e){
                $responseJson['mensaje'] = 'Token invalido.';
                $responseJson['status'] = 403;
            }
        }else{
            $responseJson['mensaje'] = 'Error interno.';
            $responseJson['status'] = 403;
        }
        $response->getBody()->write(json_encode($responseJson));
        return $response;
    }
    public static function Login($request, $response){
        $userinfo = json_decode(($request->getParsedBody())['cadenaJson']);
        $param['name'] = 'correo';
        $param['value'] = $userinfo->correo;
        $param['type'] = PDO::PARAM_STR;
        $user = DataAccess::GetWhereParam('usuarios', $param);
        if(isset($user) && $user != false && Authenticator::VerifyPassword($userinfo, $user)){            
            unset($user->clave);
            $responseJson['status'] = 200;
            $responseJson['exito'] = true;
            $responseJson['jwt'] = Authenticator::CreateJWT($user);
        }else{
            $responseJson['status'] = 403;
            $responseJson['exito'] = false;
            $responseJson['jwt'] = null;
        }
        $response->getBody()->write(json_encode($responseJson));
        return $response;
    }
    public static function VerifyPassword($userloginfo, $userdbinfo){
        return $userloginfo->clave == $userdbinfo->clave;
        //por el momento es redudante esto pero, planeo implementar salado y hasheo previo a
        //guardar las claves en la DB. 
    }
    public static function CreateAud(){
        $aud = '';        
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        }else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $aud = $_SERVER['REMOTE_ADDR'];
        }        
        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();        
        return sha1($aud);
    }
    public static function DecodeToken($token){
        $sec = @json_decode(file_get_contents(__DIR__. '/privatekey.json'));
        $decoded = @JWT::decode($token, $sec->key, [$sec->alg]);
        return $decoded;
    }
    public static function AuthorizeProfiles($request, $profiles){
        try{
            $ok = false;
            $head = $request->getHeaders();
            $token = isset($head['CitronelaToken'][0]) ? $head['CitronelaToken'][0] : null;
            $sec = json_decode(file_get_contents(__DIR__. '/privatekey.json'));
            if($token != null){
                $decoded = JWT::decode($token, $sec->key, [$sec->alg]);
                if(in_array($decoded->inf->perfil, $profiles)){
                    $ok = true;
                }
            }
        }catch(Exception $e){
            $ok = false;
        }finally{
            return $ok;
        }
    }
}
<?php

/**
 *  APi Rest - Citroneta
 *  Descrpcion: api para concecionaria de autos
 *  
 *  Manejo de peticiones:
 *      A nivel de aplicacion:
 *          GET - Retorna un listado completo de los usuarios en formato Json.
 *              La respuesta tendra el formato: {exito:true/false, mensaje:string, tabla:stringJson, status:200/400}
 *          POST - Se agrega un auto a la base de datos.
 *              La peticion tendra el formato {color:string, marca:string, precio:number, modelo:string}
 *              La respuesta tendra un formato {exito:true/false, mensaje:string, status:200/418}
 *      Ruteos:
 *          /autos - GET - Retorna el listado de autos en formato Json.
 *              La respuesta tendra el formato: {exito:true/false, mensaje:string, tabla:stringJson, status:200/400}
 * 
 *          /login - GET - Verifica el JWT recibido en el header de la peticion.
 *              La respuesta tendra el formato {mensaje:string, status:200/403}
 *          /login - POST - Verifica el usuario y contraseña recibidos en la peticion y devuelve un token.
 *              La peticion tendra el formato {correo:string, clave:string}
 *              La respuesta tendra el formato {exito:true/false, jwt:JWT/null, status:200/403}
 * 
 *          /usuarios - POST - Alta de usuarios, agrega un nuevo registro a la base de datos.
 *              La peticion tendra el formato {correo:string, clave:string, nombre:string, apellido:string, perfil:string(1)}
 *                  File:foto
 *                  (1) propietario / encargado / empleado
 *              La respuesta tendra el formato {exito:true/false, mensaje:string, status:200/418}
 * 
 *  Implementacion
 *      Todos los verbos invocaran metodos de la clase Auto o Usuario.
 */
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
///
require __DIR__ . '/../depend/vendor/autoload.php';
require __DIR__ . '/../Model/Auto.php';
require __DIR__ . '/../Model/Usuario.php';
require __DIR__ . '/../Model/dataAccess.php';
require __DIR__ . '/../Model/Auth.php';
require __DIR__ . '/../Model/MW.php';
///
$app = AppFactory::create();
///
///para usar desde localhost/segundoparcial descomentar la siguiente linea
$app->setBasePath('/segundoparcial');
///
$app->get('/', \Usuario::class . '::GetAll')
    ->add(\MW::class . '::FilterUserListByProfile_encargado')
    ->add(\MW::class . '::FilterUserListByProfile_empleado')
    ->add(\MW::class . '::FilterUserListByProfile_propietario')
    ->add(\MW::class . ':VerifyToken');    
$app->post('/', \Auto::class . '::AddOne')
    ->add(\MW::class . '::VerifyPriceRange');
$app->get('/autos', \Auto::class . '::GetAll')
    ->add(\MW::class . '::FilterCarListByProfile_propietario')
    ->add(\MW::class . '::FilterCarListByProfile_encargado')
    ->add(\MW::class . '::FilterCarListByProfile_empleado')
    ->add(\MW::class . ':VerifyToken');
$app->get('/login', \Authenticator::class . '::VerifyToken')
    ->add(\MW::class . '::VerifyPropietario');
$app->post('/login', \Authenticator::class . '::Login')
    ->add(\MW::class . ':VerifyExistsLogin')
    ->add(\MW::class . '::VerifyEmailPasswordEmpty')
    ->add(\MW::class . ':VerifyEmailPasswordSet');
$app->post('/usuarios', \Usuario::class . '::AddOne')
    ->add(\MW::class . '::VerifyExistRegister')
    ->add(\MW::class . '::VerifyEmailPasswordEmpty')
    ->add(\MW::class . ':VerifyEmailPasswordSet');
$app->delete('/', \Auto::class . '::Delete')
    ->add(\MW::class . '::VerifyPropietario')
    ->add(\MW::class . ':VerifyEncargado')
    ->add(\MW::class . ':VerifyToken');
$app->put('/', \Auto::class . '::Modify')
    ->add(\MW::class . '::VerifyPropietario')
    ->add(\MW::class . ':VerifyEncargado')
    ->add(\MW::class . ':VerifyToken');
///
$app->run();
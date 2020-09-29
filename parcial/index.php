<?php
require __DIR__ . '/vendor/autoload.php';
use \Firebase\JWT\JWT;
require_once __DIR__.'/PHP/tratarPath.php';
require_once __DIR__.'/PHP/archivo.php';
require_once __DIR__.'./PHP/jwtoken.php';
require_once __DIR__.'./PHP/marcaAgua.php';
require_once __DIR__.'./PHP/retorno.php';
require_once __DIR__.'./PHP/error.php';
require_once __DIR__.'/PHP/usuario.php';
require_once __DIR__.'/PHP/auto.php';

Archivo::crearArchivos();
$path = getPath();
$request = getRequest();
$header = jwtClass::getHeader('token');
$retorno = Retorno::armarRetorno(false,"No ah entrado en ninguna ruta","",null,null);


if($request == 'POST' && $request != null)
 {
     $comParam = false;
     $resultado = "";
     if(esPathParam($path,'email'))
     {
         $resultado = validarPath($path,"email","?",'/usuario/');
         $newPath = retornarPath($path,'email','/?',$resultado);
         $path = $newPath;
         $comParam = true;
        }
    
        $body = $_POST;
    switch($path)
    {
        case '/registro':
            $retorno = tratarUsuario($body,$resultado,$comParam);
        break;
        case '/login':
            $retorno = tratarLogIn($body);
        break;
        case '/ingreso':
          $retorno = tratarIngreso($header,$body,'POST',"");
        break;
        default:
            $retorno = Retorno::armarRetorno(false,"path incorrecta \"'.$path. '\" no existe","",null,null);
        break;
    }
 }else if($request == 'GET' && $request != null)
 {
    $body = $_GET;
    $patente = "";
    $newPath = retiroPath($path);
    if($newPath !=$path)
    {
        $patente = retornarPatente($path);
        $path = $newPath;
    }
    switch($path)
    { 
        case 'retiro':
            $retorno = tratarRetiro($header,$patente,'GET');
        break;
        case '/ingreso':
            $retorno = tratarIngreso($header,$body,'GET',$patente);
        default:
    break;
    }
}else
{   
    $retorno = Retorno::armarRetorno(false,"Error request no aceptada","",null,null);
}
$retorno->mostrar();
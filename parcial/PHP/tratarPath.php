<?php
function tratarUsuario($param,$resultado,$comParam)
{
    try
    {
        if($comParam)
        {
            $usuario = Usuario::buscar($resultado,'email');
            if($usuario != false)
            {
                return cambiarFoto($usuario);
            }else{
                return Retorno::armarRetorno(false,"No existe el mail","",null,null);
            }
        }else{
            if(!is_a(validarBody(array('email','password','tipo'),$param),'Retorno') &&
                isset($_FILES['imagen']))
                {
                    $files = $_FILES['imagen'];
                    $usuario = Usuario::validar($param['email'],$param['password']);
                    if($usuario == false)//no existe usuario 
                    {
                        if($param['tipo'] != 'admin' && $param['tipo'] != 'user')
                        {
                            return Retorno::armarRetorno(false,"Error en tipo de user","",null,null);
                        }else{
                            $usuario = Usuario::registrar($param['email'],$param['password'],$param['tipo']);
                            if($usuario != null)
                            {
                                $usuarios = Archivo::Leer(dirname(__DIR__,1).'/archivos/users.json','json');
                                array_push($usuarios,$usuario);
                                Archivo::Guardar($usuarios,dirname(__DIR__,1).'/archivos/users.json','json');
                                //guardar imagen
                                //$nombreImagen = $usuario->id.'_'.$usuario->email;
                                $nombreImagen = Usuario::aramarNombre(array($usuario->id,$usuario->email));
                                //Fijarse la cantidad de fotos, ya que puede tirar un warning
                                Archivo::tratarImagen($files,dirname(__DIR__,1).'/imagenes/',$nombreImagen,1);
                                return Retorno::armarRetorno(true,"","",null,json_encode($usuario));
                            }else
                            {
                                return Retorno::armarRetorno(false,"error al registrar usuario","",null,null);
                            }
                        }
                    }else
                    {
                        return Retorno::armarRetorno(false,"Nombre de usuario ya existente","",null,null);
                    }
                }else{
                    return Retorno::armarRetorno(false,"Body erroneo","",null,null);
                }
        }
    }catch(Exception $e)
    {
        return Retorno::armarRetorno(false,"",$e->getMessage(),null,null);
    }
}
function tratarLogIn($param)
{
    try
    {
        if(!is_a(validarBody(array('email','password'),$param),'Retorno'))
        {
            $usuario = Usuario::validar($param['email'],$param['password']);
            if(is_a($usuario, 'Usuario'))
            {   
                $retorno = jwtClass::encodeJWT(jwtClass::armoPayLoad3(json_encode($usuario)));
                return Retorno::armarRetorno(true,"","",$retorno,null);
            }
            else if($usuario == false)
            {
                return Retorno::armarRetorno(false,"Usuario inexistente","",null,null);
            }
            else if($usuario == true)
            {
                return Retorno::armarRetorno(false,"Contraseña incorrecta","",null,null);
            }
        }else
        {
            return Retorno::armarRetorno(false,"Datos incompletos","",null,null);
        }
    }catch(Exception $e)
    {
        return Retorno::armarRetorno(false,"",$e->getMessage(),null,null);
    }
}
function tratarIngreso($header,$body,$request,$patente)
{
    try{
        $retorno = autenticarUsuario($header,'user'); 
        if(is_a($retorno,"Retorno"))
        {
            return $retorno;
        }else
        {
            if($request == 'POST')
            {
                if(!is_a(validarBody(array('patente'),$body),'Retorno'))
                {
                    $tiempo = Auto::GetDate();
                    $email = $retorno->email;
                    $auto = Auto::registrar($email,$body['patente'],$tiempo);
                    if($auto != null)
                    {
                        $autos = Archivo::Leer(Auto::archivo()['Archivo'],Auto::archivo()['Extencion']);
                        foreach($autos as $au)
                        {
                            if($au->patente == $auto->patente && $au->fecha_egreso != "")
                            {
                                return Retorno::armarRetorno(false,"Ya hay un auto estacionado con esta patente","",null,json_encode($au));
                            }
                        }
                        array_push($autos,$auto);
                        Archivo::Guardar($autos,Auto::archivo()['Archivo'],Auto::archivo()['Extencion']);
                        return Retorno::armarRetorno(true,"","",null,json_encode($auto));
                    }else
                    {
                        return Retorno::armarRetorno(false,"error al registrar usuario","",null,null);
                    }
                }else{
                    return Retorno::armarRetorno(false,'Error en Body, request mal echa','',null,null);
                }
            }else if($request == 'GET')
            {
                if($patente == "")
                {
                    $autos = AUTO::filter("","fecha_egreso");
                    return Retorno::armarRetorno(true,"",null,null,json_encode(Auto::traerTodos())) ;

                }
            }
        }
    }catch(Exception $e)
    {
        return Retorno::armarRetorno(false,"",$e->getMessage(),null,null);
    }
}
function tratarRetiro($header,$patente)
{
    try{
        $retorno = autenticarUsuario($header,'user'); 
        if(is_a($retorno,"Retorno"))
        {
            return $retorno;
        }else
        {
            if($patente == "")
            {
                return Retorno::armarRetorno(false,"Patente vacia","",null,null);
            }else
            {
                $auto = Auto::buscar($patente,'patente');
                if($auto != false)
                {
                    $auto->fecha_egreso = Auto::GetDate();
                    $importe = Auto::calcularImporte($auto);
                    $auto->importe = $importe;
                    $autos = Archivo::Leer(Auto::archivo()['Archivo'],Auto::archivo()['Extencion']);
                    foreach($autos as $au)
                    {
                        if($au->patente == $auto->patente)
                        {
                            $au->importe = $importe;
                            $au->fecha_egreso = $auto->fecha_egreso;
                        }
                    }
                    Archivo::Guardar($autos,Auto::archivo()['Archivo'],Auto::archivo()['Extencion']);
                return Retorno::armarRetorno(true,"","",null,json_encode($auto));
                    
                }else{
                   return Retorno::armarRetorno(false,"Patente no existe","",null,null);
                }
            }
       }
    }catch(Exception $e)
    {
        return Retorno::armarRetorno(false,"",$e->getMessage(),null,null);
    }
}



function autenticarUsuario($header,$tipo){
    if($header != null)
    {
        $usuario = jwtClass::autenticarToken($header);
        if(($usuario != false) && isset($usuario->email) && isset($usuario->password) && isset($usuario->tipo))
        {
            $usuarioValidado = Usuario::validar($usuario->email,$usuario->password);// ver esto
            if( is_a($usuarioValidado,"Usuario") && $usuario->tipo == $tipo)
            {
                return $usuarioValidado;
            }else{
                return Retorno::armarRetorno(false,"Usuario invalido","",null,null);
            }
        }else{
            return Retorno::armarRetorno(false,"Error en datos","",null,null);
        }
    }else{
        return Retorno::armarRetorno(false,"No hay Header","",null,null);
    }
}
function validarBody($arrayElementos,$body)
{
    foreach($arrayElementos as $elemento)
    {
        if(!isset($body[$elemento]))
        {
            return Retorno:: armarRetorno(false, 'Faltas campos en el body',"",null,null);
        }
    }
    return true;
}
function validarPath($path,$parametro,$separador,$subpath)
{
    $resultado = str_replace($parametro,"",$path);
    $resultado = str_replace($subpath,"",$resultado);
    $resultado = str_replace($separador,"",$resultado);
    $resultado = str_replace("=","",$resultado);
    return $resultado;
}
function isRetiroPath($path)
{
    if(explode('/',$path)[1] == 'retiro')
    {
        return true;
    }else{

        return false;
    }
}
function retiroPath($path)
{
    if(isRetiroPath($path) == true)
    {
        return explode('/',$path)[1];
    }else{
        return $path;
    }
}
function retornarPatente($path)
{
    if(isRetiroPath($path) == true)
    {
        return explode('/',$path)[2];
    }else{
        return $path;
    }
}

function esPathParam($path,$parametro)
{
    $resultado = str_replace($parametro,"",$path);

    if($resultado == $path)
    {
        return false;
    }else{
        return true;
    }
}
function retornarPath($path,$parametro,$separador,$valor)
{
    $resultado = str_replace($parametro,"",$path);
    $resultado = str_replace($valor,"",$resultado);
    $resultado = str_replace($separador,"",$resultado);
    $resultado = str_replace("=","",$resultado);
    return $resultado;
}
function cambiarFoto($usuario)
{
    if(isset($_FILES['foto']))
    {
        $imagen = $_FILES['foto'];
        $explode = explode('.',$imagen['name'][0]);
        if(array_reverse($explode)[0] == "jpg" && isset($imagen['tmp_name']))
        {
            $nombre =Usuario::aramarNombre(array($usuario->id,$usuario->email));
            $origen = dirname(__DIR__,1).'/imagenes/'.$nombre.(1).'_'.".jpg";
            $destino = dirname(__DIR__,1).'\backup\\'.$nombre.(1).'_'.time().".jpg";
            copy($origen,$destino);//copia de imagenes backup
            $origen = $imagen['tmp_name'][0];
            $destino = dirname(__DIR__,1).'/imagenes/'.$nombre.(1).'_'.".jpg";
            $retorno = move_uploaded_file($origen,$destino);//reemplzo la vieja con la nueva
            if($retorno == true)
            {
                return Retorno::armarRetorno(true,"","",null,$usuario);
            }
        }else{
            return Retorno::armarRetorno(false,"Error de array reverse","",null,null);
        }
    }else{
        return Retorno::armarRetorno(false,"No hay foto cargada","",null,null);
    }
}
function getPath()
{
    $path_elements = explode("/", $_SERVER['REQUEST_URI']);
    $tempPI = "";
    if (isset($path_elements[2])){
        for ($i = 2 ;$i < count($path_elements); $i++ )
        $tempPI .= "/".$path_elements[$i];
    }
    return $tempPI;
}
function getRequest()
{
    if(isset($_SERVER['REQUEST_METHOD']))//post o get
    {
        return $_SERVER['REQUEST_METHOD'];
    }else
    {
        return null;
    }
}

<?php
use \Firebase\JWT\JWT;
class jwtClass{
    
    static function armoPayLoad3($datos)
    {
        return  array(
            "datos"=> $datos
        );
    }
    
    static function encodeJWT($payload,$key="primerparcial")
    {
        try{
            return JWT::encode($payload, $key);
        }catch(Exception $e)
        {
            throw $e;
        }
    }
    static function decodeJWT($jwt ,$key="primerparcial")
    {
        try{
            return JWT::decode($jwt, $key, array('HS256'));
        }catch(Exception $e)
        {
            throw $e;
        }
    }
    public static function autenticarToken($token)
    {
        try{
            $jwt = jwtClass::decodeJWT($token);
            if(isset($jwt->datos))
            {
                return json_decode($jwt->datos);//ojo aca, que la autenticacion depende de como mande yo los tokens
            }
            return false;
        }catch(Exception $e)
        {
            throw $e;
        }
    }
    public static function getHeader($key)
    {
        $header = getallheaders();
        if($header != false)
        {
            if(isset($header[$key]) &&!empty($header[$key]))
            {
                return $header[$key];
            }
        }
        return null;
    }
}
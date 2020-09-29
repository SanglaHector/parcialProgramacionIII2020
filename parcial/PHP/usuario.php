<?php
class Usuario
{
    public $email;
    public $tipo;
    public $id;
    public $password;
    public $activo;

    function __construct($email,$password,$tipo)
    {
        $this->email = $email;
        $this->id = Archivo::generarId(Usuario::archivo()['Archivo'],'stdClass');
        $this->password = $password;
        $this->activo = true;
        $this->tipo = $tipo;
    }
    static function contructor($email,$password,$id,$tipo)
    {
        $usuario = new Usuario($email,$password,$tipo);
        $usuario->id = $id;
        return $usuario;
    }
    public static function archivo()
    {
        return array(
            'Archivo' => dirname(__DIR__,1).'/archivos/users.json',
            'Extencion'=> 'json');
    }
    public static function registrar($email,$password,$tipo){
        
        $nuevoUsuario = new Usuario($email,$password,$tipo);
        return $nuevoUsuario;
    }
    /***
     * Valida que exista un usuario con el mail y con el apellido
     */
    public static function validar($email,$password)
    {
        try{
            $obj = Archivo::Leer(Usuario::archivo()['Archivo'],Usuario::archivo()['Extencion']);
            $usuarios = Array();
            foreach($obj as $o)
            {
                if(isset($o->email)  && isset($o->password) &&  isset($o->id))
                {
                    $usuario = Usuario::contructor($o->email,$o->password,$o->id,$o->tipo);
                    array_push($usuarios,$usuario);
                }
            }
            foreach($usuarios as $usuario )
            {
                if(is_a($usuario,'Usuario'))
                {
                    if($usuario->email == $email && $password == $usuario->password)
                    { 
                        return $usuario;
                    }
                    if($usuario->email == $email)
                    {
                        return true;
                    }
                }
            }
            return false;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }
    /**
     * Autentica token, y valida que el usuario exista en la el archivo
     */
    public function autenticarUsuario($header){
        if($header != null)
        {
            $usuario = jwtClass::autenticarToken($header);
            if(($usuario != false) && isset($usuario->email) && isset($usuario->password))
            {
                $usuarioValidado = Usuario::validar($usuario->email,$usuario->password);// ver esto
                if( is_a($usuarioValidado,"Usuario"))
                {
                    return true;
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
    /***
     * Paso el nombre del atributo y el valor, busca en el archivo y retorna un objeto stdClass del archivo
     */
    static function buscar($atributo,$nombreAtributo)
    {
        $usuarios = Archivo::Leer(Usuario::archivo()['Archivo'],Usuario::archivo()['Extencion']);
        foreach($usuarios as $usuario){
            if($usuario->$nombreAtributo == $atributo){
                return $usuario;
            }
        }
        return false;
    }
    
    public function buscarFoto()
    {
        
    }
    /***
     * Entra un array de objetos usuarios(stdClass) y sale un array de objetos usuarios(stdClass) ordenadas por atributo
     */
    static function sort($nombreAtributo,$tipo,$usuarios)
    {
        $array = array();
        $ordenado = array();
        foreach($usuarios as $usuario)
        {
            $us = (array)$usuario;
            if(isset($us[$nombreAtributo]))
            {
                array_push($array,$us[$nombreAtributo]);
            }else{
                return false;
            }
        }
        if($tipo == 'num')
        {
            sort($array,1);
        }else 
        {
            sort($array,2);
        }
        foreach($array as $arr)
        {
            $usuario = Usuario::buscar($arr,$nombreAtributo);
            if($usuario != false)
            {
                array_push($ordenado,$usuario);
            }
        }
        return $ordenado;
    }
     /***
      * Agarra todos los elementos que hay en el archivo(array de stdClass) y retorna un array de objetos usuarios(stdClass)
      */
    static function filter($atributo,$nombreAtributo)
    {
        $usuarios = Archivo::Leer(Usuario::archivo()['Archivo'],Usuario::archivo()['Extencion']);
        $array = array();
        foreach($usuarios as $usuario)
        {
            $us = (array)$usuario;
            if(isset($us[$nombreAtributo]))
            {
                if($us[$nombreAtributo] == $atributo)
                {
                    $claseUsuario = Usuario::buscar($atributo,$nombreAtributo);
                    array_push($array,$claseUsuario);
                }
            }else{
                return false;
            }
        }
        return $array;
    }
    /***
     * Le paso un array de objetos(usuario o stdClass) y me retorna un string
     */
    public static function mostrar($usuarios)
    {
        $string = "".PHP_EOL;
        foreach($usuarios as $usuario)
        {
            $string = $string."Id: ".$usuario->id.'   -   ';
            $string = $string."Tipo: ".$usuario->tipo.'   -   ';
            $string = $string."Email: ".$usuario->email.PHP_EOL;
        }
        return $string;
    }
    /***
     * Trae todos los elementos de el archivo y ejecuta el metodo mostrar para pasarlos a string
     */
    static function traerTodos()
    {
        $usuarios = Archivo::Leer(Usuario::archivo()['Archivo'],Usuario::archivo()['Extencion']);
        $string = Usuario::mostrar($usuarios);
        return $string;
    }
    /**
     * Pregunta si un objeto(stdClass o usuario) tiene el atributo y el valor del atributo que le pasamos
     */
    static function isA($usuario,$atributo,$nombreAtributo)
    {
        if(isset($usuario[$nombreAtributo]))
        {
            if($usuario[$nombreAtributo] == $atributo )
            {
                return true;
            }
        }
        return false;
    }
    static function aramarNombre($array)
    {
        $string = "";
        if(is_array($array))
        {
            foreach($array as $arr)
            {
                $string = $string.$arr.'_';
            }
        }
        
        return $string;
    }
}
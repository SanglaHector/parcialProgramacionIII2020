<?php
class Auto
{
    public $id;
    public $patente;
    public $fecha_ingreso;
    public $fecha_egreso;
    public $email;
    public $activo;
    public $importe;

    function __construct($email,$patente,$fecha_ingreso)
    {
        $this->email = $email;
        $this->id = Archivo::generarId(Auto::archivo()['Archivo'],'stdClass');
        $this->fecha_ingreso = $fecha_ingreso;
        $this->activo = true;
        $this->patente = $patente;
        $this->fecha_egreso = "";
        $this->importe = 0;
    }
    static function contructor($email,$id,$patente,$fecha_ingreso)
    {
        $usuario = new Auto($email,$patente,$fecha_ingreso);
        $usuario->id = $id;
        return $usuario;
    }
    public static function archivo()
    {
        return array(
            'Archivo' => dirname(__DIR__,1).'/archivos/autos.json',
            'Extencion'=> 'json');
    }
    public static function registrar($email,$patente,$fecha_ingreso){
        
        $nuevoUsuario = new Auto($email,$patente,$fecha_ingreso);
        return $nuevoUsuario;
    }
    /***
     * Valida que exista un usuario con el mail y con el apellido
     */
    public static function validar($email,$clave)
    {
        try{
            $obj = Archivo::Leer(Auto::archivo()['Archivo'],Auto::archivo()['Extencion']);
            $usuarios = Array();
            foreach($obj as $o)
            {
                if(isset($o->email)  && isset($o->clave) &&  isset($o->id))
                {
                    $usuario = Usuario::contructor($o->email,$o->clave,$o->id,$o->tipo);
                    array_push($usuarios,$usuario);
                }
            }
            foreach($usuarios as $usuario )
            {
                if(is_a($usuario,'Usuario'))
                {
                    if($usuario->email == $email && $clave == $usuario->clave)
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
            if(($usuario != false) && isset($usuario->email) && isset($usuario->clave))
            {
                $usuarioValidado = Auto::validar($usuario->email,$usuario->clave);// ver esto
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
        $usuarios = Archivo::Leer(Auto::archivo()['Archivo'],Auto::archivo()['Extencion']);
        foreach($usuarios as $usuario){
            if($usuario->$nombreAtributo == $atributo){
                return $usuario;
            }
        }
        return false;
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
            $usuario = Auto::buscar($arr,$nombreAtributo);
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
        $usuarios = Archivo::Leer(Auto::archivo()['Archivo'],Auto::archivo()['Extencion']);
        $array = array();
        foreach($usuarios as $usuario)
        {
            $us = (array)$usuario;
            if(isset($us[$nombreAtributo]))
            {
                if($us[$nombreAtributo] != $atributo)
                {
                    $claseUsuario = Auto::buscar($atributo,$nombreAtributo);
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
            $string = $string."Patente: ".$usuario->patente.'   -   ';
            $string = $string."Email de dueño: ".$usuario->email.'   -   ';
            $string = $string."Importe ".$usuario->importe.'   -   ';
            $string = $string."Fecha de egreso: ".$usuario->egreso.'   -   ';
            $string = $string."Fecha de ingreso: ".$usuario->fecha_ingreso.PHP_EOL;
        }
        return $string;
    }
    /***
     * Trae todos los elementos de el archivo y ejecuta el metodo mostrar para pasarlos a string
     */
    static function traerTodos()
    {
        $usuarios = Archivo::Leer(Auto::archivo()['Archivo'],Auto::archivo()['Extencion']);
        $string = Auto::mostrar($usuarios);
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
    public static function GetDate()
    {
        $date   = getdate();
        $d      = $date['mday'];
        $m      = $date['mon'];
        $y      = $date['year'];
        $min    = $date['minutes'];
        $hours  = $date['hours'];

        return $d.'-'.$m.'-'.$y.' '.$hours.':'.$min;
    }
    public static function calcularImporte($auto)
    {
       // $diff = $auto->fecha_ingerso->date_diff($auto->fecha_egreso);
        $fecha_i = new DateTime($auto->fecha_ingreso);
        $fecha_f = new DateTime($auto->fecha_egreso);
        $diff = $fecha_i->diff($fecha_f);
        if($diff->h < 4)
        {
            return (100*$diff->h);
        }else if($diff->h > 4 && $diff->h < 12)
        {
            return (60*$diff->h);
        }else{
            return (30*$diff->h);
        }
    }
}
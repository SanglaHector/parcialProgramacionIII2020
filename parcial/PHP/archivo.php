<?php
class Archivo
{
    public static function Guardar($objeto,$path,$mode)
    {
        try{
            $archivo = fopen($path,'w+');
            switch($mode)
            {
                case 'json':
                    $bytes = fwrite($archivo,json_encode($objeto).PHP_EOL);
                break;
                case 'ser':
                    $data = serialize($objeto).PHP_EOL;
                    $bytes = fwrite($archivo,$data);
                break;
                default :
                    $data = $objeto.PHP_EOL;
                    $bytes = fwrite($archivo,$data);
                break;
            }
            fclose($archivo);
        }catch(Exception $e)
        {
            throw $e;
        }
    }
    public static function Leer($path,$mode)
    {
        try{
            if(file_exists($path))
            {
                $archivo = fopen($path,'r+');
                //aca hay que leer solo una vez
                switch($mode)
                {
                    case 'ser':
                        $objeto = (unserialize(fgets($archivo))); 
                    break;
                    case 'json':
                        $objeto = (json_decode(fgets($archivo)));
                        
                    break;
                    default:
                        $objeto = fgets($archivo); 
                     $objeto =  Archivo::leerTxt($objeto,'?',';');
                    break;
                }
                if(($objeto == null) && !is_array($objeto))
                {   
                    return "";
                }
                return $objeto;
            }else{
                return null; 
            }
        }catch(Exception $e)
        {
            throw $e;
        }
    }
    public static function leerTxt($datos,$delimiter,$subDelimiter)
    {
        $arrObjetos = str_getcsv($datos,$delimiter);//divido todos los objetos diferentes
        $retorno = array();
        $final = new stdClass();
        $claves = array();
        $valores = array();
        foreach($arrObjetos as $claveValor)
        {
            $claveValor = str_getcsv($claveValor,$subDelimiter);
            foreach($claveValor as $claveOValor)
            {
                $aux = str_getcsv($claveOValor,'=');
                for ($i=0; $i < count($aux); $i++) { 
                    if($i == 0)
                    {
                        array_push($claves,$aux[$i]);
                    }else
                    {
                        array_push($valores,$aux[$i]);
                    }
                }
            }
            for ($i=0; $i < count($claves); $i++) { 
                if(!is_array($claves[$i]) && !is_array($valores[$i]))
                {
                    error_reporting(E_ALL ^ E_NOTICE);
                    $final->$claves[$i] = $valores[$i];
                }
            } //un opcion bastante buena pero no tanto por el uso de error_reporting
            array_push($retorno,$final);
            $final = new stdClass();
            $claves = array();
            $valores = array();
        }
        return $retorno;
    }
    public static function convertToObject($array) {
        $object = new stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = Archivo::convertToObject($value);
            }
            $object->$key = $value;
        }
        return $object;
    }
    public static function generarId($path,$clase)
    {
        $id = 1;
        $objetos = Archivo::Leer($path,'json');
        if($objetos != null)
        {
            foreach($objetos as $objeto )
            {
                if(is_a($objeto,$clase))
                {
                    if($objeto->id >= $id)
                    { 
                        $id = $objeto->id + 1;
                    }
                }
            }
        }
        return $id;
    }
    public static function tratarImagen($imagen,$path,$nombre,$cantFotos)
    {
        $explode = explode('.',$imagen['name']);
            if(array_reverse($explode)[0] == "jpg" && isset($imagen['tmp_name']))
            {
                $origen = $imagen['tmp_name'];
                $destino = $path.$nombre.(1).'_'.".jpg";
                if(file_exists($origen))
                {
                    move_uploaded_file($origen,$destino);
                }else{
                    return null;
                }
            }else{
                return null;
            }
    }
    static function crearArchivos()
    {
        $archivo1 = Usuario::archivo();
        $archivo2 = Auto::archivo();
        try{
            if(!file_exists($archivo1['Archivo']))
            {
                Archivo::Guardar(array(),$archivo1['Archivo'],$archivo1['Extencion']);
            }
            if(!file_exists($archivo2['Archivo']))
            {
                Archivo::Guardar(array(),$archivo2['Archivo'],$archivo1['Extencion']);
            }
        }catch(Exception $e)
        {
            throw $e;
        }
    }      
}

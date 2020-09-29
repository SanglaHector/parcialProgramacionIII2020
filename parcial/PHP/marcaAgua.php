<?php
function marcaDeAgua($pathImServer)
{
    $explode = explode('.',$pathImServer);
    $explode = array_reverse($explode);
    if($explode[0] == 'jpg' || $explode[0] == 'jpeg' )
    { 
    //$im = imagecreatefromjpeg('C:\xampp\htdocs\parcial2\imagenes\1_1588305824_php.jpg');
    $im = imagecreatefromjpeg($pathImServer);
    //$marcaAgua = imagecreatefromjpeg('C:\xampp\htdocs\parcial2\archivos\marcaAgua.jpg');
    $marcaAgua = imagecreatefromjpeg(dirname(__DIR__,1).'/elefantephp.jpg');
    
    $margenAncho = 10;
    $marchenInferior = 10;
    $ax = imagesx($marcaAgua);
    $ay = imagesy($marcaAgua);
    $nombre = array_reverse(explode('/',$pathImServer))[0];
    $path = dirname(__DIR__,1).'/archivos/marcasDeAgua/'.$nombre;

    imagecopymerge($im,$marcaAgua,imagesx($im) - $ax - $margenAncho , imagesy($im) - $ay - $marchenInferior,0,0,$ax,$ay,30);
 //  imagepng($im,'C:\xampp\htdocs\parcial\archivos\editada.jpg');// aca es donde la guardo, fijarse bien
    imagepng($im,$path);// aca es donde la guardo, fijarse bien
    imagedestroy($im);
    return true;
    }
    return false;
}

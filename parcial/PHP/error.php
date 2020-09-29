<?php
class  MyError
{
    public $descripcion;
    public $exceptionMessege;

    function __construct( $descripcion,$exceptionMessege)
    {
        $this->descripcion = $descripcion;
        $this->exceptionMessege= $exceptionMessege;
    }
}
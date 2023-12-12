<?php

class Mesa
{   
    public const ACCEPTED_PARAMS = ['codigo', 'estado_mesa', 'estado'];
    public const MANDATORY_PARAMS = [];
    public const ESTADOS_MESAS = ['cerrada', 'con cliente esperando pedido', 'con cliente comiendo', 'con cliente pagando'];
    private const CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    public $id;
    public $codigo;
    public $estado_mesa;
    public $estado;
    public $fecha_alta;
    public $fecha_baja;

//ABM
    public function create()
    {
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery("INSERT INTO mesas (codigo, fecha_alta) VALUES (:codigo, :fecha_alta)");
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_alta', date_format(new DateTime(), 'Y-m-d H:i:s'));
        $consulta->execute();
        return $objAccesoDatos->getLastID();
    }

    public function update()
    {
        $objAccesoDato = AccesoDB::getInstance();
        $consulta = $objAccesoDato->prepareQuery("UPDATE mesas SET estado_mesa = :estado_mesa, estado = :estado, fecha_baja = :fecha_baja WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':estado_mesa', $this->estado_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_baja', $this->fecha_baja);
        $consulta->execute();
    }

    public static function update_estado_esperando($codigo){
        self::update_estado($codigo, 'con cliente esperando pedido');
    }

    public static function update_estado_comiendo($codigo){
        self::update_estado($codigo, 'con cliente comiendo');
    }

    public static function update_estado_pagando($codigo){
        self::update_estado($codigo, 'con cliente pagando');
    }

    public static function cerrar_mesa($codigo){
        self::update_estado($codigo, 'cerrada');
    }


    private static function update_estado($codigo, $estado_mesa)
    {
        $objAccesoDato = AccesoDB::getInstance();
        $consulta = $objAccesoDato->prepareQuery("UPDATE mesas SET estado_mesa = :estado_mesa WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindValue(':estado_mesa', $estado_mesa, PDO::PARAM_STR);
        $consulta->execute();
    }


    public static function delete($id)
    {
        $objAccesoDato = AccesoDB::getInstance();
        $consulta = $objAccesoDato->prepareQuery("UPDATE mesas SET estado = 'inactivo', fecha_baja = :fecha_baja WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $id, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_baja', date_format(new DateTime(), 'Y-m-d H:i:s'));
        $consulta->execute();
    }

//FETCH
    public static function fetchAllMesas($onlyActives = true)
    {
        $sql = "SELECT * FROM mesas" . (($onlyActives) ? " WHERE estado = 'activo';" : ";") ;
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function fetchMesa($id)
    {
        $sql = "SELECT * FROM mesas WHERE codigo = :codigo";
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->bindValue(':codigo', $id, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchObject('Mesa');
    }

//QUERIES
    public static function idExists($id) : bool {
        $objAccesoDato = AccesoDB::getInstance();
        $consulta = $objAccesoDato->prepareQuery("SELECT COUNT(codigo) FROM mesas WHERE codigo = :codigo;");
        $consulta->bindValue(':codigo', $id, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetch()[0] == 0 ? false : true ;
    }

//OTHERS
    public static function KeyGen(){
        $chars = str_split(self::CHARSET);
        $size = strlen(self::CHARSET) - 1;
        $key = "";
        for ($i=0; $i<5; $i++) {
            $key = $key . $chars[rand(0, $size)];
        }
        return $key;
    }
}

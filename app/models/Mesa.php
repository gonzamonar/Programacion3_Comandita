<?php

class Mesa
{   
    public const ACCEPTED_PARAMS = ['codigo', 'estado_mesa', 'estado'];
    public const MANDATORY_PARAMS = [];
    public const CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    public $id;
    public $codigo;
    public $estado_mesa;
    public $estado;
    public $fecha_baja;

//ABM
    public function create()
    {
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery("INSERT INTO mesas (codigo) VALUES (:codigo)");
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
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
        //$sql = "SELECT * FROM mesas;";
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function fetchMesa($id, $onlyActives = true)
    {
        $sql = "SELECT * FROM mesas WHERE codigo = :codigo" . ($onlyActives) ? " AND estado = 'activo';" : ";" ;
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->bindValue(':codigo', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

//QUERIES
    public static function idExists($id) : bool {
        $objAccesoDato = AccesoDB::getInstance();
        $consulta = $objAccesoDato->prepareQuery("SELECT COUNT(id) FROM mesas WHERE codigo = :codigo;");
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

<?php

class Encuesta
{
    public const MANDATORY_PARAMS = ['id_pedido', 'codigo_mesa', 'puntuacion_mesa', 'puntuacion_restaurante', 'puntuacion_mozo', 'puntuacion_cocinero', 'valoracion_experiencia'];

    public $id;
    public $id_pedido;
    public $codigo_mesa;
    public $puntuacion_mesa;
    public $puntuacion_restaurante;
    public $puntuacion_mozo;
    public $puntuacion_cocinero;
    public $valoracion_experiencia;
    public $promedio;
    public $fecha_encuesta;

//ABM
    public function create()
    {
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery("INSERT INTO encuestas (id_pedido, codigo_mesa, puntuacion_mesa, puntuacion_restaurante, puntuacion_mozo, puntuacion_cocinero, valoracion_experiencia, promedio, fecha_encuesta)
                                                 VALUES (:id_pedido, :codigo_mesa, :puntuacion_mesa, :puntuacion_restaurante, :puntuacion_mozo, :puntuacion_cocinero, :valoracion_experiencia, :promedio, :fecha_encuesta)");
        $consulta->bindValue(':id_pedido', $this->id_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':puntuacion_mesa', $this->puntuacion_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':puntuacion_restaurante', $this->puntuacion_restaurante, PDO::PARAM_INT);
        $consulta->bindValue(':puntuacion_mozo', $this->puntuacion_mozo, PDO::PARAM_INT);
        $consulta->bindValue(':puntuacion_cocinero', $this->puntuacion_cocinero, PDO::PARAM_INT);
        $consulta->bindValue(':valoracion_experiencia', $this->valoracion_experiencia, PDO::PARAM_STR);
        $consulta->bindValue(':promedio', $this->promedio, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_encuesta', date_format(new DateTime(), 'Y-m-d H:i:s'));
        $consulta->execute();
        return $objAccesoDatos->getLastID();
    }

    public function update()
    {
        $objAccesoDato = AccesoDB::getInstance();
        $consulta = $objAccesoDato->prepareQuery("UPDATE encuestas SET id_pedido = :id_pedido, puntuacion_mesa = :puntuacion_mesa, puntuacion_restaurante = :puntuacion_restaurante WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':id_pedido', $this->id_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':puntuacion_mesa', $this->puntuacion_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':puntuacion_restaurante', $this->puntuacion_restaurante, PDO::PARAM_INT);
        $consulta->bindValue(':puntuacion_mozo', $this->puntuacion_mozo, PDO::PARAM_INT);
        $consulta->bindValue(':puntuacion_cocinero', $this->puntuacion_cocinero, PDO::PARAM_INT);
        $consulta->bindValue(':valoracion_experiencia', $this->valoracion_experiencia, PDO::PARAM_STR);
        $consulta->bindValue(':promedio', $this->promedio, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function delete($id)
    {
        $objAccesoDato = AccesoDB::getInstance();
        $consulta = $objAccesoDato->prepareQuery("DELETE FROM encuestas WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

//FETCH
    public static function fetchAllInstances()
    {
        $sql = "SELECT * FROM encuestas;" ;

        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Encuesta');
    }

    public static function fetchInstanceById($id)
    {
        return self::fetchInstance($id, "id");
    }

    private static function fetchInstance($value, $col, $paramType = PDO::PARAM_INT)
    {
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery("SELECT * FROM encuestas WHERE $col = :value");
        $consulta->bindValue(':value', $value, $paramType);
        $consulta->execute();

        return $consulta->fetchObject('Encuesta');
    }

    public function CalcularPromedio() : float {
        return ($this->puntuacion_mesa + $this->puntuacion_cocinero + $this->puntuacion_mozo + $this->puntuacion_restaurante) / 4;
    }

//QUERIES
    public static function idExists($id) : bool {
        $objAccesoDato = AccesoDB::getInstance();
        $consulta = $objAccesoDato->prepareQuery("SELECT COUNT(id) FROM encuestas WHERE id = :id;");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch()[0] == 0 ? false : true ;
    }
}

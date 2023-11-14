<?php

class Pedido
{   
    public const ACCEPTED_PARAMS = ['nombre_producto', 'sector_producto', 'precio', 'estado'];
    public const MANDATORY_PARAMS = ['nombre_producto', 'sector_producto', 'precio'];

    public $id;
    public $nombre_producto;
    public $sector_producto;
    public $precio;
    public $estado;
    public $fecha_baja;

//ABM
    public function create()
    {
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery("INSERT INTO productos (nombre_producto, sector_producto, precio) VALUES (:nombre_producto, :sector_producto, :precio)");
        $consulta->bindValue(':nombre_producto', $this->nombre_producto, PDO::PARAM_STR);
        $consulta->bindValue(':sector_producto', $this->sector_producto, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
        $consulta->execute();
        return $objAccesoDatos->getLastID();
    }

    public function update()
    {
        $objAccesoDato = AccesoDB::getInstance();
        $consulta = $objAccesoDato->prepareQuery("UPDATE productos SET nombre_producto = :nombre_producto, sector_producto = :sector_producto, precio = :precio WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':nombre_producto', $this->nombre_producto, PDO::PARAM_STR);
        $consulta->bindValue(':sector_producto', $this->sector_producto, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function delete($id)
    {
        $objAccesoDato = AccesoDB::getInstance();
        $consulta = $objAccesoDato->prepareQuery("UPDATE productos SET estado = 'inactivo', fecha_baja = :fecha_baja WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_baja', date_format(new DateTime(), 'Y-m-d H:i:s'));
        $consulta->execute();
    }

//FETCH
    public static function fetchAllProductos($onlyActives = true)
    {
        if ($onlyActives) {
            $sql = "SELECT * FROM productos WHERE estado = 'activo';" ;
        } else {
            $sql = "SELECT * FROM productos;" ;
        }

        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function fetchAllProductsBySector($sector, $onlyActives = true)
    {
        if ($onlyActives) {
            $sql = "SELECT * FROM productos WHERE sector_producto = :sector AND estado = 'activo';" ;
        } else {
            $sql = "SELECT * FROM productos WHERE sector_producto = :sector;" ;
        }

        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->bindValue(':sector', $sector, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function fetchProductoById($id)
    {
        return self::fetchProducto($id, "id");
    }

    public static function fetchProductoByNombre($nombre_producto)
    {
        return self::fetchProducto($nombre_producto, "nombre_producto", PDO::PARAM_STR);
    }

    private static function fetchProducto($value, $col, $paramType = PDO::PARAM_INT)
    {
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery("SELECT * FROM productos WHERE $col = :value && estado = 'activo'");
        $consulta->bindValue(':value', $value, $paramType);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

//QUERIES
    public static function productExists($nombre_producto) : bool {
        $objAccesoDato = AccesoDB::getInstance();
        $consulta = $objAccesoDato->prepareQuery("SELECT COUNT(nombre_producto) FROM productos WHERE nombre_producto = :nombre_producto;");
        $consulta->bindValue(':nombre_producto', $nombre_producto, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetch()[0] == 0 ? false : true ;
    }

    public static function idExists($id) : bool {
        $objAccesoDato = AccesoDB::getInstance();
        $consulta = $objAccesoDato->prepareQuery("SELECT COUNT(id) FROM productos WHERE id = :id;");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch()[0] == 0 ? false : true ;
    }
}

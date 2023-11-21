<?php

    class Pedido
    {   
        public const ACCEPTED_PARAMS = ['codigo_mesa', 'importe_total', 'tiempo_inicio', 'estado'];
        public const MANDATORY_PARAMS = ['codigo_mesa', 'importe_total', 'tiempo_inicio'];
        public const ESTADO_PEDIDO = ['pendiente', 'en preparacion', 'listo para servir'];

        public $id;
        public $codigo_mesa;
        public $estado;
        public $importe_total;
        public $tiempo_estimado;
        public $tiempo_inicio;
        public $tiempo_entrega;
        public $tiempo_neto;

    //ABM
        public function create()
        {
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery("INSERT INTO pedidos (codigo_mesa, importe_total) VALUES (:codigo_mesa, :importe_total)");
            $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_STR);
            $consulta->bindValue(':importe_total', $this->importe_total, PDO::PARAM_INT);
            $consulta->execute();
            return $objAccesoDatos->getLastID();
        }

        public function update()
        {
            $objAccesoDato = AccesoDB::getInstance();
            $consulta = $objAccesoDato->prepareQuery("UPDATE pedidos SET codigo_mesa = :codigo_mesa, importe_total = :importe_total, tiempo_inicio = :tiempo_inicio WHERE id = :id");
            $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
            $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_STR);
            $consulta->bindValue(':importe_total', $this->importe_total, PDO::PARAM_STR);
            $consulta->bindValue(':tiempo_inicio', $this->tiempo_inicio, PDO::PARAM_INT);
            $consulta->execute();
        }

        public static function delete($id)
        {
            $objAccesoDato = AccesoDB::getInstance();
            $consulta = $objAccesoDato->prepareQuery("UPDATE pedidos SET estado = 'inactivo', fecha_baja = :fecha_baja WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->bindValue(':fecha_baja', date_format(new DateTime(), 'Y-m-d H:i:s'));
            $consulta->execute();
        }

    //FETCH
        public static function fetchAllProductos($onlyActives = true)
        {
            if ($onlyActives) {
                $sql = "SELECT * FROM pedidos WHERE estado = 'activo';" ;
            } else {
                $sql = "SELECT * FROM pedidos;" ;
            }

            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery($sql);
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
        }

        public static function fetchAllProductsBySector($sector, $onlyActives = true)
        {
            if ($onlyActives) {
                $sql = "SELECT * FROM pedidos WHERE importe_total = :sector AND estado = 'activo';" ;
            } else {
                $sql = "SELECT * FROM pedidos WHERE importe_total = :sector;" ;
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

        public static function fetchProductoByNombre($codigo_mesa)
        {
            return self::fetchProducto($codigo_mesa, "codigo_mesa", PDO::PARAM_STR);
        }

        private static function fetchProducto($value, $col, $paramType = PDO::PARAM_INT)
        {
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery("SELECT * FROM pedidos WHERE $col = :value && estado = 'activo'");
            $consulta->bindValue(':value', $value, $paramType);
            $consulta->execute();

            return $consulta->fetchObject('Pedido');
        }

    //QUERIES
        public static function productExists($codigo_mesa) : bool {
            $objAccesoDato = AccesoDB::getInstance();
            $consulta = $objAccesoDato->prepareQuery("SELECT COUNT(codigo_mesa) FROM pedidos WHERE codigo_mesa = :codigo_mesa;");
            $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_STR);
            $consulta->execute();
            return $consulta->fetch()[0] == 0 ? false : true ;
        }

        public static function idExists($id) : bool {
            $objAccesoDato = AccesoDB::getInstance();
            $consulta = $objAccesoDato->prepareQuery("SELECT COUNT(id) FROM pedidos WHERE id = :id;");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            return $consulta->fetch()[0] == 0 ? false : true ;
        }
}

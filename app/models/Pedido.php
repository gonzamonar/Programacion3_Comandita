<?php

    class Pedido
    {   
        public const ACCEPTED_PARAMS = ['codigo_mesa', 'importe_total', 'tiempo_inicio', 'estado'];
        public const MANDATORY_PARAMS = ['codigo_mesa', 'importe_total', 'tiempo_inicio'];
        public const ESTADO_PEDIDO = ['pendiente', 'en_preparacion', 'listo_para_servir'];
        private const CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        public const FILE_PARAM = 'foto';

        public $id;
        public $codigo_mesa;
        public $importe_total;
        public $estado;
        public $tiempo_inicio;
        public $tiempo_entrega;
        public $tiempo_estimado;
        public $url_foto;

    //ABM
        public function create()
        {
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery("INSERT INTO pedidos (id, codigo_mesa, importe_total) VALUES (:id, :codigo_mesa, :importe_total)");
            $consulta->bindValue(':id', $this->id, PDO::PARAM_STR);
            $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_STR);
            $consulta->bindValue(':importe_total', $this->importe_total, PDO::PARAM_INT);
            $consulta->execute();
            return $objAccesoDatos->getLastID();
        }

        public static function cargar_productos($id_pedido, $id_producto, $cantidad)
        {
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery("INSERT INTO productos_por_pedido (id_producto, cantidad, id_pedido) VALUES (:id_producto, :cantidad, :id_pedido)");
            $consulta->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
            $consulta->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
            $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
            $consulta->execute();
            return $objAccesoDatos->getLastID();
        }

        public function update()
        {
            $objAccesoDato = AccesoDB::getInstance();
            $consulta = $objAccesoDato->prepareQuery("UPDATE pedidos SET codigo_mesa = :codigo_mesa, estado = :estado, importe_total = :importe_total, tiempo_inicio = :tiempo_inicio,
                                                      tiempo_entrega = :tiempo_entrega, tiempo_estimado = :tiempo_estimado WHERE id = :id");
            $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
            $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_STR);
            $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
            $consulta->bindValue(':importe_total', $this->importe_total, PDO::PARAM_STR);
            $consulta->bindValue(':tiempo_inicio', $this->tiempo_inicio, PDO::PARAM_STR);
            $consulta->bindValue(':tiempo_entrega', $this->tiempo_entrega, PDO::PARAM_STR);
            $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_STR);
            $consulta->execute();
        }

        public function update_imgUrl()
        {
            $objAccesoDato = AccesoDB::getInstance();
            $consulta = $objAccesoDato->prepareQuery("UPDATE pedidos SET url_foto = :url_foto WHERE id = :id");
            $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
            $consulta->bindValue(':url_foto', $this->getImgUrl(), PDO::PARAM_STR);
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
        public static function fetchAllInstances($estado)
        {
            if ($estado == 'ALL') {
                $sql = "SELECT * FROM pedidos;" ;
            } else {
                $sql = "SELECT * FROM pedidos WHERE estado = '$estado';" ;
            }

            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery($sql);
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
        }

        public static function fetchAllPending($sector)
        {
            $sql = 
            "SELECT pp.id as 'nro_orden', nombre_producto as 'producto', cantidad, id_pedido as 'pedido', pp.estado, sector_producto as 'sector'
            FROM productos_por_pedido AS pp
            INNER JOIN productos AS p ON p.id = id_producto
            WHERE sector_producto = :sector AND pp.estado = 'pendiente';";
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery($sql);
            $consulta->bindValue(':sector', $sector, PDO::PARAM_STR);
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        }

        public static function fetchAllPreparacion($sector)
        {
            $sql = 
            "SELECT pp.id as 'nro_orden', nombre_producto as 'producto', cantidad, id_pedido as 'pedido', pp.estado, sector_producto as 'sector'
            FROM productos_por_pedido AS pp
            INNER JOIN productos AS p ON p.id = id_producto
            WHERE sector_producto = :sector AND pp.estado = 'en_preparacion';";
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery($sql);
            $consulta->bindValue(':sector', $sector, PDO::PARAM_STR);
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        }
        
        public static function update_order_preparar($orden, $estado, $tiempo_preparacion)
        {
            $sql = 
            "UPDATE productos_por_pedido SET estado = :estado, tiempo_preparacion = :tiempo_preparacion WHERE id = :id;";
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery($sql);
            $consulta->bindValue(':id', $orden, PDO::PARAM_STR);
            $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
            $consulta->bindValue(':tiempo_preparacion', $tiempo_preparacion, PDO::PARAM_INT);
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        }
        
        public static function update_order_servir($orden, $estado)
        {
            $sql = "UPDATE productos_por_pedido SET estado = :estado WHERE id = :id;";
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery($sql);
            $consulta->bindValue(':id', $orden, PDO::PARAM_STR);
            $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_ASSOC);
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

        public static function fetchInstanceById($id)
        {
            return self::fetchInstance($id, "id");
        }

        public static function fetchInstanceByNombre($codigo_mesa)
        {
            return self::fetchInstance($codigo_mesa, "codigo_mesa", PDO::PARAM_STR);
        }

        private static function fetchInstance($value, $col, $paramType = PDO::PARAM_INT)
        {
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery("SELECT * FROM pedidos WHERE $col = :value");
            $consulta->bindValue(':value', $value, $paramType);
            $consulta->execute();

            return $consulta->fetchObject('Pedido');
        }

    //QUERIES
        public static function alreadyExists($codigo_mesa) : bool {
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

        public static function IsLastOrderToServe($id_pedido) : bool {
            $objAccesoDato = AccesoDB::getInstance();
            $consulta = $objAccesoDato->prepareQuery("SELECT COUNT(estado) FROM productos_por_pedido WHERE id_pedido = :id AND estado != 'listo_para_servir';");
            $consulta->bindValue(':id', $id_pedido, PDO::PARAM_INT);
            $consulta->execute();
            return $consulta->fetch()[0] == 1 ? true : false ;
        }

        private static function fetchIdPedidoFromOrder($orden)
        {
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery("SELECT id_pedido FROM productos_por_pedido WHERE id = :orden");
            $consulta->bindValue(':orden', $orden, PDO::PARAM_INT);
            $consulta->execute();
            return $consulta->fetch()[0];
        }


        public static function fetch_from_order($orden)
        {
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery("SELECT * FROM pedidos WHERE id = :id");
            $consulta->bindValue(':id', self::fetchIdPedidoFromOrder($orden), PDO::PARAM_INT);
            $consulta->execute();

            return $consulta->fetchObject('Pedido');
        }

        

    //UTILS
        public static function KeyGen(){
            $chars = str_split(self::CHARSET);
            $size = strlen(self::CHARSET) - 1;
            $key = "";
            for ($i=0; $i<5; $i++) {
                $key = $key . $chars[rand(0, $size)];
            }
            return $key;
        }


        public static function getImgRootFolder() : string {
            return "./media/ImagenesDeMesas/";
        }

        public static function getDeletedImgFolder() : ?string {
            return "./media/deleted/BackupImagenesDeMesas/";
        }

        public function getImgName() : string {
            return "pedido_" . $this->id;
        }

        private function getImgUrl() : string {
            return self::getImgRootFolder() . $this->getImgName() . '.jpg';
        }

        private function getDeletedImgUrl() : string {
            return self::getDeletedImgFolder() . $this->getImgName() . '.jpg';
        }
        
        public function deleteImg($hard = false) : bool {
            $succeed = false;
            $deleteFolder = self::getDeletedImgFolder();
            if($deleteFolder != null){
                if (!file_exists($deleteFolder)) {
                    mkdir($deleteFolder, 0777, true);
                }
                $succeed = rename($this->getImgUrl(), $this->getDeletedImgUrl());
            } else {
                $succeed = unlink($this->getImgUrl());
            }
            if (!$hard && $succeed){
                $this->url_foto = $this->getDeletedImgUrl();
            }            
            return $succeed;
        }
}

<?php

    class Log {
        public $id;
        public $fecha;
        public $usuario;
        public $host;
        public $port;
        public $path;
        public $method;
        public $query;
        public $body;
        public $status;
        public $resultado;
        public $detalle;
        
        public function insert()
        {
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery("INSERT INTO logs (fecha, usuario, host, port, path, method, query, body, status, resultado, detalle)
                                                              VALUES (:fecha, :usuario, :host, :port, :path, :method, :query, :body, :status, :resultado, :detalle)");
            $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
            $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
            $consulta->bindValue(':host', $this->host, PDO::PARAM_STR);
            $consulta->bindValue(':port', $this->port, PDO::PARAM_STR);
            $consulta->bindValue(':path', $this->path, PDO::PARAM_STR);
            $consulta->bindValue(':method', $this->method, PDO::PARAM_STR);
            $consulta->bindValue(':query', $this->query, PDO::PARAM_STR);
            $consulta->bindValue(':body', $this->body, PDO::PARAM_STR);
            $consulta->bindValue(':status', $this->status, PDO::PARAM_STR);
            $consulta->bindValue(':resultado', $this->resultado, PDO::PARAM_STR);
            $consulta->bindValue(':detalle', $this->detalle, PDO::PARAM_STR);
            $consulta->execute();
            return $objAccesoDatos->getLastID();
        }

        public static function getLogs()
        {
            $sql = "SELECT * FROM logs;" ;
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery($sql);
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_CLASS, 'Log');
        }


    }


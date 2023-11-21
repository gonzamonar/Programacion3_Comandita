<?php

    class Usuario
    {   
        public const PERMISOS = ['socio', 'bartender', 'cervecero', 'cocinero', 'mozo'];
        
        public $id;
        public $usuario;
        public $clave;
        public $permiso;
        public $estado;
        public $fecha_alta;
        public $fecha_baja;

        public function create()
        {
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery("INSERT INTO usuarios (usuario, clave, permiso, fecha_alta) VALUES (:usuario, :clave, :permiso, :fecha_alta)");
            $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);
            $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
            $consulta->bindValue(':clave', $claveHash);
            $consulta->bindValue(':permiso', $this->permiso, PDO::PARAM_STR);
            $consulta->bindValue(':fecha_alta', date_format(new DateTime(), 'Y-m-d H:i:s'));
            $consulta->execute();
            return $objAccesoDatos->getLastID();
        }
        
        public static function fetchAllUsers($onlyActives = true)
        {
            if ($onlyActives) {
                $sql = "SELECT * FROM usuarios WHERE estado = 'activo';" ;
            } else {
                $sql = "SELECT * FROM usuarios;" ;
            }

            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery($sql);
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
        }

        public static function fetchUserByUsername($usuario)
        {
            return self::fetchUser($usuario, "usuario", PDO::PARAM_STR);
        }

        public static function fetchUserById($id)
        {
            return self::fetchUser($id, "id");
        }

        private static function fetchUser($value, $col, $paramType = PDO::PARAM_INT)
        {
            $objAccesoDatos = AccesoDB::getInstance();
            $consulta = $objAccesoDatos->prepareQuery("SELECT * FROM usuarios WHERE $col = :value && estado = 'activo'");
            $consulta->bindValue(':value', $value, $paramType);
            $consulta->execute();

            return $consulta->fetchObject('Usuario');
        }

        public function update()
        {
            $objAccesoDato = AccesoDB::getInstance();
            $consulta = $objAccesoDato->prepareQuery("UPDATE usuarios SET usuario = :usuario, clave = :clave, permiso = :permiso WHERE id = :id");
            $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
            $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
            $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
            $consulta->bindValue(':permiso', $this->permiso, PDO::PARAM_STR);
            $consulta->execute();
        }

        public static function delete($id)
        {
            $objAccesoDato = AccesoDB::getInstance();
            $consulta = $objAccesoDato->prepareQuery("UPDATE usuarios SET estado = 'inactivo', fecha_baja = :fecha_baja WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->bindValue(':fecha_baja', date_format(new DateTime(), 'Y-m-d H:i:s'));
            $consulta->execute();
        }

        public static function usernameExists($username) : bool {
            $objAccesoDato = AccesoDB::getInstance();
            $consulta = $objAccesoDato->prepareQuery("SELECT COUNT(usuario) FROM usuarios WHERE usuario = :username;");
            $consulta->bindValue(':username', $username, PDO::PARAM_STR);
            $consulta->execute();
            return $consulta->fetch()[0] == 0 ? false : true ;
        }

        public static function userIsActive($username) : bool {
            $objAccesoDato = AccesoDB::getInstance();
            $consulta = $objAccesoDato->prepareQuery("SELECT COUNT(usuario) FROM usuarios WHERE usuario = :username AND estado = 'activo';");
            $consulta->bindValue(':username', $username, PDO::PARAM_STR);
            $consulta->execute();
            return $consulta->fetch()[0] == 0 ? false : true ;
        }

        public static function checkPassword($username, $password) : bool {
            return password_verify($password, self::getPassword($username));
        }

        private static function getPassword($username) : string {
            $objAccesoDato = AccesoDB::getInstance();
            $consulta = $objAccesoDato->prepareQuery("SELECT clave FROM usuarios WHERE usuario = :username;");
            $consulta->bindValue(':username', $username, PDO::PARAM_STR);
            $consulta->execute();
            return $consulta->fetch()[0];
        }

        public static function idExists($id) : bool {
            $objAccesoDato = AccesoDB::getInstance();
            $consulta = $objAccesoDato->prepareQuery("SELECT COUNT(id) FROM usuarios WHERE id = :id;");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            return $consulta->fetch()[0] == 0 ? false : true ;
        }
    }

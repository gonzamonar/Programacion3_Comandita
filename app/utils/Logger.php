<?php

    class Logger {
        public const MANDATORY_PARAMS = ['usuario', 'clave'];

        public $username = "";
        public $credential = "";
        public $success = False;
        public $message = "";

        public function Login($username, $password) : bool {
            $this->success = False;
            $this->username = $username;

            if (Usuario::usernameExists($this->username)){
                if (Usuario::userIsActive($this->username)){
                    if (Usuario::checkPassword($this->username, $password)){
                        $this->success = True;
                        $this->message = "Login exitoso.";
                        $this->credential = "admin";
                    } else {
                        $this->message = "Contraseña incorrecta.";
                    }
                } else {
                    $this->message = "El usuario indicado no es un usuario activo.";
                }
            } else {
                $this->message = "El usuario indicado no existe.";
            }

            return $this->success;
        }
    }
?>
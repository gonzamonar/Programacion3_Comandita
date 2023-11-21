<?php

    class Validator
    {
        private const ESTADOS = ['activo', 'inactivo'];

        public $errors;

        public function __construct() {
            $this->errors = [];
        }

        public function ValidateMandatoryParams($paramKeys, $params) {
            foreach ($paramKeys as $key) {
                if (!isset($params[$key])) {
                    $this->errors["UnsetParameter_$key"] = "Debe incluir un parámetro '$key' obligatoriamente." ;
                } else if (empty($params[$key])) {
                    $this->errors["EmptyParameter_$key"] =  "El parámetro '$key' no puede ser un campo vacío." ;
                }
            }
        }

        public function ValidateAcceptedParams($acceptedKeys, $params) {
            if ($params != NULL){
                foreach ($params as $key => $v) {
                    if ( !in_array($key, $acceptedKeys) ) {
                        $this->errors["InvalidParameter_$key"] = "Los parámetros aceptados son: " . self::arrayToString($acceptedKeys) . "." ;
                    }
                }
            } else {
                $this->errors["EmptyRequest"] = "No se enviaron parámetros en la consulta. Los parámetros aceptados son: " . self::arrayToString($acceptedKeys) . "." ;
            }
        }

        public function ValidateOptionalParam($key, $params, $callable) {
            if (isset($params[$key])){
                if (empty($params[$key])) {
                    $this->errors["UnsetParameter_$key"] = "El parámetro opcional '$key' se encuentra vacío." ;
                } else {
                    $this->$callable($params[$key]);
                }
            }
        }

        public function ValidateIdExists($id, $class) {
            if (!$class::idExists($id)){
                $this->errors["IdDoesntExists"] =  "El ID ingresado no existe." ;
            }
        }

        public function ValidateUsernameIsFree($var) {
            $this->UsernameQuery($var, "UsernameAlreadyExists", "El nombre de usuario ingresado ya se encuentra en uso.", true);
        }

        public function ValidateUsernameExists($var) {
            $this->UsernameQuery($var, "UsernameDoesntExists", "El nombre de usuario ingresado no existe.", false);
        }

        private function UsernameQuery($username, $errorName, $errorString, $flag) {
            if (Usuario::usernameExists($username) == $flag){
                $this->errors[$errorName] =  $errorString ;
            }
        }

        public function ValidateProductExists($producto) {
            if (Producto::productExists($producto)){
                $this->errors["ProductAlreadyExists"] =  "Ya existe un producto con el nombre '$producto'." ;
            }
        }

        public function ValidateString($field, $str, $minLen, $maxLen, $bannedChars = []) {
            $len = strlen($str);
            if ($len < $minLen) {
                $this->errors["StringIsTooShort_$field"] = "El campo '$field' no alcanza el mínimo de caracteres requeridos de $minLen";
            }
            if ($len > $maxLen) {
                $this->errors["StringIsTooLong_$field"] = "El campo '$field' supera el máximo de caracteres permitidos de $maxLen";
            }
            foreach ($bannedChars as $c){
                if (str_contains($str, $c)){
                    $this->errors["StringUnvalidChar_$field\_$c"] = "El caracter $c no está permitido en el campo $field.";
                }
            }
        }

        public function ValidatePermisos($permiso){
            $this->ValidateOptions($permiso, Usuario::PERMISOS, 'UnvalidOption_Permiso', 'permiso');
        }

        public function ValidateEstadosMesas($estado){
            $this->ValidateOptions($estado, Mesa::ESTADOS_MESAS, 'UnvalidOption_EstadoMesa', 'estado');
        }

        public function ValidateSectores($sector){
            $this->ValidateOptions($sector, Producto::SECTORES, 'UnvalidOption_Sector', 'sector');
        }
        
        public function ValidateEstados($estado){
            $this->ValidateOptions($estado, self::ESTADOS, 'UnvalidOption_Estado', 'estado');
        }

        private function ValidateOptions($value, $allowedValues, $errorName, $field){
            if (!in_array($value, $allowedValues)) {
                $this->errors[$errorName] = "Los valores aceptados para el campo '$field' son: " . self::arrayToString($allowedValues) . ".";
            }
        }


        public function IsErrorFree(){
            return empty($this->errors);
        }

        private static function arrayToString($array){
            $str = "";
            $counter = 0;
            $size = count($array);
            foreach ($array as $substr) {
                if ($counter == $size -1){
                    $str = $str . " y ";
                } else if ($counter != 0){
                    $str = $str . ", ";
                }
                $str = $str . $substr;
                $counter++;
            }
            return $str;
        }
    }

/*
    class Validation {
        public static function ValidateNumber($var, $fieldName, $min, $type, $muted){
            $error = 0;
            $error |= ($var === null) ? 1 : 0 ;
            $error |= (gettype($var) != $type) ? 2 : 0 ;
            $error |= ($var < $min) ? 4 : 0 ;
            
            if(!$muted){
                echo ($error & 1) ? "El valor del campo $fieldName es inválido. <br>" : "";
                echo ($error & 2) ? "Campo $fieldName de tipo inválido. (se espera $type y es " . gettype($var) . " <br>" : "";
                echo ($error & 4) ? "Valor del campo $fieldName está fuera del rango aceptado. <br>" : "";
            }
            
            return $error == 0;
        }

        public static function ValidateUnrangedNumber($var, $fieldName, $type, $muted){
            $error = 0;
            $error |= ($var === null) ? 1 : 0 ;
            $error |= (gettype($var) != $type) ? 2 : 0 ;
            
            if(!$muted){
                echo ($error & 1) ? "El valor del campo $fieldName es inválido. <br>" : "";
                echo ($error & 2) ? "Campo $fieldName de tipo inválido. (se espera $type y es " . gettype($var) . " <br>" : "";
            }
            
            return $error == 0;
        }

        public static function ValidateString($var, $fieldName, $muted){
            $error = 0;
            $error |= ($var === null) ? 1 : 0 ;
            $error |= (gettype($var) != "string") ? 2 : 0 ;

            if(!$muted){
                echo ($error & 1) ? "El campo $fieldName es inválido. <br>" : "";
                echo ($error & 2) ? "Campo $fieldName de tipo inválido. <br>" : "";
            }
    
            return $error == 0;
        }

        public static function ValidateEmail($email, $fieldName, $muted){
            $valid = Validation::ValidateString($email, $fieldName, $muted);

            if (!str_contains($email, "@")) {
                if(!$muted){
                    echo "Email inválido. <br>";
                }
                $valid = false;
            }
            return $valid;
        }

        public static function ValidateDate($date, $fieldName, $muted) {
            $valid = false;
            if (strlen($date) == 10){
                $exploded_date = explode("-", $date);
                if (sizeof($exploded_date) == 3){
                    if (checkdate((integer) $exploded_date[1], (integer) $exploded_date[2], (integer) $exploded_date[0])) {
                        $valid = true;
                    }
                }
            }
            
            if(!$muted & !$valid){
                echo "La fecha $date del campo $fieldName no es una fecha válida (formato requerido: YYYY-MM-DD). <br>";
            }
            
            return $valid;
        }

        public static function ValidateDateDiff($min_date, $fieldNameMin, $max_date, $fieldNameMax, $muted){
            $valid = false;

            if (Validation::ValidateDate($min_date, $fieldNameMin, $muted) && Validation::ValidateDate($max_date, $fieldNameMax, $muted)){
                if ($min_date <= $max_date){
                    $valid = true;
                } else if (!$muted) {
                    echo "La fecha $min_date ($fieldNameMin) no es menor o igual a la fecha $max_date ($fieldNameMax). <br>";
                }
            }
            
            return $valid;
        }
    }
    */
?>
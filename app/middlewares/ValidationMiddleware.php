<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

require_once './utils/Validator.php';

    class ValidationMiddleware {
    
        public $className;
        public $idField;
        
        public function __construct($className, $idField = 'id')
        {
            $this->className = $className;
            $this->idField = $idField;
        }

        public function __invoke(Request $request, RequestHandler $handler) : Response
        {
            $validator = new Validator();
            $validator->ValidateIdExists(self::getArgs($request)[$this->idField], $this->className);
            return self::StandardValidation($request, $handler, $validator);
        }

//USUARIOS
        public function ValidateLogin(Request $request, RequestHandler $handler) : Response
        {
            $validator = new Validator();
            $params = $request->getParsedBody();
            $validator->ValidateMandatoryParams(Logger::MANDATORY_PARAMS, $params);
            return self::StandardValidation($request, $handler, $validator);
        }

        public function ValidateUsuario_Post(Request $request, RequestHandler $handler) : Response
        {
            $validator = new Validator();
            $params = $request->getParsedBody();
            $validator->ValidateMandatoryParams(['usuario', 'clave', 'permiso'], $params);

            if ($validator->IsErrorFree()){
                $validator->ValidateUsernameIsFree($params['usuario']);
                $validator->ValidateString('clave', $params['clave'], 5, 25);
                $validator->ValidatePermisos($params['permiso']);
            }

            return self::StandardValidation($request, $handler, $validator);
        }
    
        public function ValidateUsuario_Username(Request $request, RequestHandler $handler) : Response
        {
            $validator = new Validator();
            $validator->ValidateUsernameExists(self::getArgs($request)['usuario']);
            return self::StandardValidation($request, $handler, $validator);
        }

        public function ValidateUsuario_Put(Request $request, RequestHandler $handler) : Response
        {
            $validator = new Validator();
            $params = $request->getParsedBody();
            $validator->ValidateIdExists(self::getArgs($request)['id'], 'Usuario');
            $validator->ValidateMandatoryParams(['usuario', 'clave', 'permiso'], $params);

            if ($validator->IsErrorFree()){
                $validator->ValidateUsernameIsFree($params['usuario']);
                $validator->ValidateString('clave', $params['clave'], 5, 25);
                $validator->ValidatePermisos($params['permiso']);
            }

            return self::StandardValidation($request, $handler, $validator);
        }

//PRODUCTOS
        public function ValidateProducto_Post(Request $request, RequestHandler $handler) : Response
        {
            $validator = new Validator();
            $params = $request->getParsedBody();
            $validator->ValidateAcceptedParams(Producto::ACCEPTED_PARAMS, $params);
            $validator->ValidateMandatoryParams(Producto::MANDATORY_PARAMS, $params);
            $validator->ValidateOptionalParam('estado', $params, 'ValidateEstados');

            if ($validator->IsErrorFree()){
                $validator->ValidateProductExists($params['nombre_producto']);
                $validator->ValidateSectores($params['sector_producto']);
            }

            return self::StandardValidation($request, $handler, $validator);
        }

        public function ValidateProducto_Put(Request $request, RequestHandler $handler) : Response
        {
            $validator = new Validator();
            $params = $request->getParsedBody();

            $validator->ValidateIdExists(self::getArgs($request)['id'], 'Producto');
            $validator->ValidateAcceptedParams(Producto::ACCEPTED_PARAMS, $params);
            $validator->ValidateOptionalParam('nombre_producto', $params, 'ValidateProductExists');
            $validator->ValidateOptionalParam('sector_producto', $params, 'ValidateSectores');
            $validator->ValidateOptionalParam('estado', $params, 'ValidateEstados');

            return self::StandardValidation($request, $handler, $validator);
        }


        private function getArgs($request){
            return \Slim\Routing\RouteContext::fromRequest($request)->getRoute()->getArguments();
        }

        private function StandardValidation(Request $request, RequestHandler $handler, Validator $validator) : Response
        {
            if ($validator->IsErrorFree()) {
                $response = $handler->handle($request);
            } else {
                $response = new Response();
                $payload = json_encode(array('errors' => $validator->errors), JSON_PRETTY_PRINT);
                $response->getBody()->write($payload);
            }

            return $response->withHeader('Content-Type', 'application/json');
        }
    }

?>
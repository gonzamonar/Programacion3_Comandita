<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

    class AuthMiddleware {
        private $permissionList;

        public function __construct(array $permissionList) {
            $this->permissionList = $permissionList;
        }

        public function __invoke(Request $request, RequestHandler $handler) : Response
        {
            $parametros = $request->getQueryParams();
            
            if (isset( $parametros['sector']) && !empty( $parametros['sector'])) {
                $permission = $parametros['sector'];
    
                if (in_array($permission, $this->permissionList)) {
                    $response = $handler->handle($request);
                } else {
                    $response = new Response();
                    $payload = json_encode(array('error' => 'Acceso denegado. Error de autenticación.'), JSON_PRETTY_PRINT);
                    $response->getBody()->write($payload);
                }
            } else {
                $response = new Response();
                $payload = json_encode(array('error' => "Debe especificar un parámetro 'sector' para autenticar ingreso."), JSON_PRETTY_PRINT);
                $response->getBody()->write($payload);
            }


            return $response->withHeader('Content-Type', 'application/json');
        }
    }

?>
<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

    class AuthMiddleware {
        private const DEFAULT = ['admin'];
        private $permissionList;

        public function __construct(array $permissionList = null) {
            $this->permissionList = ($permissionList) ? array_merge($permissionList, self::DEFAULT) : self::DEFAULT ;
        }

        public function __invoke(Request $request, RequestHandler $handler) : Response
        {
            $token = AutentificadorJWT::getRequestToken();
            
            if ($token) {
                $data = AutentificadorJWT::getTokenData($token);
                $permission = $data->permiso;
    
                if (in_array($permission, $this->permissionList)) {
                    $response = $handler->handle($request);
                } else {
                    $response = new Response();
                    $payload = json_encode(array('ACCESO_DENEGADO' => 'Permisos insuficientes.'), JSON_PRETTY_PRINT);
                    $response->getBody()->write($payload);
                }
            } else {
                $response = new Response();
                $payload = json_encode(array('ACCESO_DENEGADO' => "Token inválido."), JSON_PRETTY_PRINT);
                $response->getBody()->write($payload);
            }

            return $response->withHeader('Content-Type', 'application/json');
        }
    }

?>
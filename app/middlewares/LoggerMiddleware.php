<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Dompdf\Dompdf;

require_once './utils/Logger.php';
require_once './controllers/ControllerTrait.php';

    class LoggerMiddleware {
        use ControllerTrait;

        public function __invoke(Request $request, RequestHandler $handler): Response
        {
            $params = $request->getQueryParams();
            $body = $request->getParsedBody();
            
            $log = new Log();
            
            $log->fecha = date_format(new DateTime(), 'Y-m-d H:i:s');
            $log->usuario = self::GetUser();
            $log->host = $request->getUri()->getHost();
            $log->port = $request->getUri()->getPort();
            $log->path = $request->getUri()->getPath();
            $log->method = $request->getMethod();
            $log->query = !empty($params)? self::arrayToStr($params) : "";
            $log->body = !empty($body)? self::arrayToStr($body) : "";
            $response = $handler->handle($request);
            $existingContent = json_decode($response->getBody());
            
            $response = new Response();

            $log->status = $response->getStatusCode();
            $log->resultado = isset($existingContent->mensaje->resultado) ? $existingContent->mensaje->resultado : 'unknown' ;
            $log->detalle = isset($existingContent->mensaje->detalle) ? $existingContent->mensaje->detalle : 'unknown' ;
            $log->id = $log->insert();
            //$existingContent->log = $log;
            
            $payload = json_encode($existingContent);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }

        private static function arrayToStr($assoc_array){
            $str = "";
            $counter = 0;
            foreach ($assoc_array as $k => $v) {
                if ($counter != 0){
                    $str = $str . ", ";
                }
                $str = $str . "$k: $v";
                $counter++;
            }
            return $str;
        }

        public function FetchAll($request, $response, $args)
        {
            $payload = self::EncodeFetchPayload(self::CrearMensaje('SUCCESS', "Listado de logs servido con éxito."), "logs", Log::getLogs());
            return self::StandardResponse($response, $payload);
        }

        private function GetUser(){
            $token = AutentificadorJWT::getRequestToken();
            $user = 'cliente';
            if ($token) {
                $data = AutentificadorJWT::getTokenData($token);
                $user = $data->usuario;
            }
            return $user;
        }
    }
?>
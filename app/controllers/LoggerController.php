<?php
require_once './utils/Logger.php';
require_once './utils/AutenticadorJWT.php';
require_once './controllers/ControllerTrait.php';

class LoggerController
{
    use ControllerTrait;

    public function Login($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $logger = new Logger();
        $logger->Login($params['usuario'], $params['clave']);

        if ($logger->success) {
            $datos = array(
                'usuario' => $logger->username,
                'permiso' => $logger->credential
            );
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = self::EncodePayload('jwt_token', $token);
        } else {
            $payload = self::EncodePayload('ACCESO_DENEGADO', $logger->message);
        }

        return self::StandardResponse($response, $payload);
    }
/*
    public function DevolverPayload($request, $response, $args)
    {
        $token = getallheaders()['Authorization'];
        $token = explode("Bearer ", $token)[1];
        $token_data = AutentificadorJWT::ObtenerData($token);
        $payload = self::EncodePayload('Token', $token_data);
        return self::StandardResponse($response, $payload);
    }

    public function devolverDatos($request, $response, $args)
    {
        $datos = array('usuario' => 'rogelio@agua.com','perfil' => 'Administrador', 'alias' => "PinkBoy");
        $token= AutentificadorJWT::CrearToken($datos); 
        $payload=AutentificadorJWT::ObtenerData($token);
        $newResponse = $response->withJson($payload, 200); 
        return $newResponse;
    }

    public function ValidarSesion($request, $response, $args)
    {
        $datos = array('usuario' => 'rogelio@agua.com','perfil' => 'Administrador', 'alias' => "PinkBoy");
        $token= AutentificadorJWT::CrearToken($datos); 
        $esValido=false;
        try 
        {
          AutentificadorJWT::verificarToken($token);
          $esValido=true;      
        }
        catch (Exception $e) {      
          //guardar en un log
          echo $e;
        }
        if( $esValido)
        {
            // hago la operacion del  metodo
            echo "ok";
        }   
        return $response;
    }
*/
}

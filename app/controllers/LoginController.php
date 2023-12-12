<?php
require_once './utils/Login.php';
require_once './utils/AutenticadorJWT.php';
require_once './controllers/ControllerTrait.php';

class LoginController
{
    use ControllerTrait;

    public function Login($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $login = new Login();
        $login->Login($params['usuario'], $params['clave']);

        if ($login->success) {
            $datos = array(
                'usuario' => $login->username,
                'permiso' => $login->credential
            );
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = self::EncodeFetchPayload(self::CrearMensaje('SUCCESS', $login->message), "jwt_token", $token);
        } else {
            $payload = self::EncodePayload("mensaje", self::CrearMensaje('ACCESO_DENEGADO', $login->message));
        }

        return self::StandardResponse($response, $payload);
    }
}

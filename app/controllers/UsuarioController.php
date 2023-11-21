<?php
require_once './models/Usuario.php';
require_once './interfaces/IABM.php';
require_once './controllers/ControllerTrait.php';

class UsuarioController extends Usuario implements IABM
{
    use ControllerTrait;

    public function CreateOne($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $permiso = $parametros['permiso'];

        $user = new Usuario();
        $user->usuario = $usuario;
        $user->clave = $clave;
        $user->permiso = $permiso;

        $user->create();
        $payload = self::EncodePayload("mensaje", "Usuario creado con exito");
        return self::StandardResponse($response, $payload);
    }

    public function FetchOne($request, $response, $args)
    {
        $payload = self::EncodePayload("usuario", Usuario::fetchUserById($args['id']));
        return self::StandardResponse($response, $payload);
    }

    public function FetchOneByUsername($request, $response, $args)
    {
        $payload = self::EncodePayload("usuario", Usuario::fetchUserByUsername($args['usuario']));
        return self::StandardResponse($response, $payload);
    }

    public function FetchAll($request, $response, $args)
    {
        $data = AutentificadorJWT::getTokenData(AutentificadorJWT::getRequestToken());
        $onlyActives = true;
        if ($data->permiso == "admin") {
            $onlyActives = false;
        }
        
        $payload = self::EncodePayload("usuarios", Usuario::fetchAllUsers($onlyActives));
        return self::StandardResponse($response, $payload);
    }
    
    public function UpdateOne($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        
        $user = Usuario::fetchUserById($args['id']);
        $user->usuario = $parametros['usuario'];
        $user->clave = $parametros['clave'];
        $user->permiso = $parametros['permiso'];
        $user->update();

        $payload = self::EncodePayload("mensaje", "Usuario modificado con exito");
        return self::StandardResponse($response, $payload);
    }
    
    public function DeleteOne($request, $response, $args)
    {
        Usuario::delete($args['id']);

        $payload = self::EncodePayload("mensaje", "Usuario borrado con exito");
        return self::StandardResponse($response, $payload);
    }

    
}

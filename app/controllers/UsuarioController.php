<?php
require_once './models/Usuario.php';
require_once './interfaces/IABM.php';

class UsuarioController extends Usuario implements IABM
{
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
        return self::StandardResponse($response, self::EncodePayload("mensaje", "Usuario creado con exito"));
    }


    public function FetchOne($request, $response, $args)
    {
        return self::StandardResponse($response, self::EncodePayload("usuario", Usuario::fetchUserById($args['id'])));
    }


    public function FetchOneByUsername($request, $response, $args)
    {
        return self::StandardResponse($response, self::EncodePayload("usuario", Usuario::fetchUserByUsername($args['usuario'])));
    }


    public function FetchAll($request, $response, $args)
    {
        return self::StandardResponse($response, self::EncodePayload("usuarios", Usuario::fetchAllUsers()));
    }


    public function FetchAllUnfiltered($request, $response, $args)
    {
        return self::StandardResponse($response, self::EncodePayload("usuarios", Usuario::fetchAllUsers(false)));
    }
    

    public function UpdateOne($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        
        $user = Usuario::fetchUserById($args['id']);
        $user->usuario = $parametros['usuario'];
        $user->clave = $parametros['clave'];
        $user->permiso = $parametros['permiso'];

        $user->update();
        return self::StandardResponse($response, self::EncodePayload("mensaje", "Usuario modificado con exito"));
    }

    
    public function DeleteOne($request, $response, $args)
    {
        Usuario::delete($args['id']);
        return self::StandardResponse($response, self::EncodePayload("mensaje", "Usuario borrado con exito"));
    }



    private function StandardResponse($response, $payload){
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    private function EncodePayload($key, $value){
      return json_encode(array($key => $value), JSON_PRETTY_PRINT);
    }


}

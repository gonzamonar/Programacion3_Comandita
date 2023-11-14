<?php
require_once './models/Mesa.php';
require_once './interfaces/IABM.php';

class MesaController extends Mesa implements IABM
{

//ABM
    public function CreateOne($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $mesa = new Mesa();
        if (isset($params['codigo'])){
            $mesa->codigo = $params['codigo'];
        } else {
            do {
                $codigo = Mesa::KeyGen();
            } while (Mesa::idExists($codigo));
            $mesa->codigo = $codigo;
        }
        $mesa->create();
        return self::StandardResponse($response, self::EncodePayload("mensaje", "Mesa creada con exito"));
    }

    public function UpdateOne($request, $response, $args)
    {
        $params = $request->getParsedBody();
        
        $mesa = Mesa::fetchMesa($args['codigo'], false);

        if (isset($params['nombre_mesa'])){
            $mesa->nombre_mesa = $params['nombre_mesa'];
        }
        if (isset($params['sector_mesa'])){ 
            $mesa->sector_mesa = $params['sector_mesa']; 
        }
        if (isset($params['precio'])){ 
            $mesa->precio = $params['precio']; 
        }
        if (isset($params['estado'])){
            $mesa->estado = $params['estado'];
            if ($params['estado'] == 'activo'){
                $mesa->fecha_baja = NULL;
            } else {
                $mesa->fecha_baja = date_format(new DateTime(), 'Y-m-d H:i:s');
            }
        }
        
        $mesa->update();

        return self::StandardResponse($response, self::EncodePayload("mensaje", "Mesa modificado con exito"));
    }

    public function DeleteOne($request, $response, $args)
    {
        Mesa::delete($args['codigo']);
        return self::StandardResponse($response, self::EncodePayload("mensaje", "Mesa borrado con exito"));
    }


//FETCH
    public function FetchOne($request, $response, $args)
    {
        return self::StandardResponse($response, self::EncodePayload("mesa", Mesa::fetchMesa($args['id'])));
    }

    public function FetchAll($request, $response, $args)
    {
        return self::StandardResponse($response, self::EncodePayload("mesas", Mesa::fetchAllMesas()));
    }

    public function FetchAllUnfiltered($request, $response, $args)
    {
        return self::StandardResponse($response, self::EncodePayload("mesas", Mesa::fetchAllMesas(false)));
    }



//PRIVATES
    private function StandardResponse($response, $payload){
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    private function EncodePayload($key, $value){
      return json_encode(array($key => $value), JSON_PRETTY_PRINT);
    }


}

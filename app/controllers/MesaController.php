<?php
require_once './models/Mesa.php';
require_once './interfaces/IABM.php';
require_once './controllers/ControllerTrait.php';

class MesaController extends Mesa implements IABM
{
    use ControllerTrait;

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
        $id_mesa = $mesa->create();
        $payload = self::EncodePayload("mensaje", "Mesa $id_mesa ($mesa->codigo) creada con exito");
        return self::StandardResponse($response, $payload);
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

        $payload = self::EncodePayload("mensaje", "Mesa modificada con exito");
        return self::StandardResponse($response, $payload);
    }

    public function DeleteOne($request, $response, $args)
    {
        Mesa::delete($args['codigo']);
        $payload = self::EncodePayload("mensaje", "Mesa borrada con exito");
        return self::StandardResponse($response, $payload);
    }

//FETCH
    public function FetchOne($request, $response, $args)
    {
        $payload = self::EncodePayload("mesa", Mesa::fetchMesa($args['id']));
        return self::StandardResponse($response, $payload);
    }

    public function FetchAll($request, $response, $args)
    {
        $payload = self::EncodePayload("mesas", Mesa::fetchAllMesas());
        return self::StandardResponse($response, $payload);
    }
}

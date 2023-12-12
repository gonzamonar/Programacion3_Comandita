<?php
require_once './models/Pedido.php';
require_once './interfaces/IABM.php';
require_once './interfaces/IABMwithImages.php';
require_once './controllers/ControllerTrait.php';

class PedidoController extends Pedido implements IABM, IABMwithImages
{
    use ControllerTrait;
    use ImgControllerTrait;

//ABM
    public function CreateOne($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $productos = $params['productos'];

        if (isset($params['codigo_mesa']) && !empty($params['codigo_mesa'])){
            if (Mesa::idExists($params['codigo_mesa'])){
                $mesa = Mesa::fetchMesa($params['codigo_mesa']);
                if ($mesa->estado_mesa == "cerrada"){
                    $pedido = new Pedido();
                    $pedido->codigo_mesa = $params['codigo_mesa'];
                    $pedido->importe_total = Producto::CalcularMonto($productos);
                    Mesa::update_estado_esperando($pedido->codigo_mesa);
                    do {
                        $id = Pedido::KeyGen();
                    } while (Pedido::idExists($id));
                    $pedido->id = $id;
                    $pedido->create();
                    foreach ($productos as $producto){
                        Pedido::cargar_productos($pedido->id, Producto::GetIdFromName($producto['producto']), $producto['cantidad']);
                    }
                    $payload = self::EncodePayload("mensaje", "Pedido $id creado con exito");
                } else {
                    $payload = self::EncodePayload("ERROR", "La mesa ya se encuentra en uso");
                }

            } else {
                $payload = self::EncodePayload("ERROR", "La mesa indicada no existe.");
            }

        } else {
            $payload = self::EncodePayload("ERROR", "Debe agregar un código de mesa.");
        }
        return self::StandardResponse($response, $payload);
    }

    public function AgregarImagen($request, $response, $args){
        $params = $request->getParsedBody();

        $pedido = Pedido::fetchInstanceById($params['id_pedido']);
        if ($pedido){
            self::SaveUploadedImage($request, self::getImgRootFolder(), $pedido->getImgName());
            $pedido->update_imgUrl();
            $payload = self::EncodePayload("mensaje", "Imagen agregada con exito");
        } else {
            $payload = self::EncodePayload("mensaje", "Pedido inexistente");
        }
        return self::StandardResponse($response, $payload);
    }

    public function UpdateOne($request, $response, $args)
    {
        $params = $request->getParsedBody();
        
        $pedido = Pedido::fetchInstanceById($args['id'], false);

        if (isset($params['nombre_producto'])){
            $pedido->nombre_producto = $params['nombre_producto'];
        }
        if (isset($params['sector_producto'])){ 
            $pedido->sector_producto = $params['sector_producto']; 
        }
        if (isset($params['precio'])){ 
            $pedido->precio = $params['precio']; 
        }
        if (isset($params['estado'])){
            $pedido->estado = $params['estado'];
            if ($params['estado'] == 'activo'){
                $pedido->fecha_baja = NULL;
            } else {
                $pedido->fecha_baja = date_format(new DateTime(), 'Y-m-d H:i:s');
            }
        }
        
        $pedido->update();

        $payload = self::EncodePayload("mensaje", "Pedido modificado con exito");
        return self::StandardResponse($response, $payload);
    }

    public function DeleteOne($request, $response, $args)
    {
        Pedido::delete($args['id']);
        $payload = self::EncodePayload("mensaje", "Pedido borrado con exito");
        return self::StandardResponse($response, $payload);
    }


//FETCH
    public function FetchOne($request, $response, $args)
    {
        $payload = self::EncodePayload("pedido", Pedido::fetchInstanceById($args['id']));
        return self::StandardResponse($response, $payload);
    }

    public function FetchAll($request, $response, $args)
    {
        $query = $request->getQueryParams();
        $estado = "ALL";
        if (isset($query['estado']) && !empty($query['estado'] && in_array($query['estado'], Pedido::ESTADO_PEDIDO))){
            $estado = $query['estado'];
        }

        $payload = self::EncodePayload("pedidos", Pedido::fetchAllInstances($estado));
        return self::StandardResponse($response, $payload);
    }


// ACCIONES SOBRE PEDIDOS
    private function GetSector(){
        $token = AutentificadorJWT::getRequestToken();
        $sector = null;
        if ($token) {
            $data = AutentificadorJWT::getTokenData($token);
            $permiso = $data->permiso;
        }

        switch ($permiso) {
            case "bartender":
                $sector = "barra_tragos";
                break;
            case "cervecero":
                $sector = "barra_choperas";
                break;
            case "cocinero":
                $sector = "cocina";
                break;
            case "mozo":
                $sector = "candy_bar";
                break;
        }

        return $sector;
    }

    public function FetchPendientes($request, $response, $args)
    {
        $sector = self::GetSector();
        if ($sector){
            $payload = self::EncodePayload("pedidos", Pedido::fetchAllPending($sector));
        } else {
            $payload = self::EncodePayload("ERROR", "No se pudo validar el sector.");
        }
        
        return self::StandardResponse($response, $payload);
    }

    public function ServirPedido($request, $response, $args)
    {
        $params = $request->getParsedBody();

        if ( isset($params['codigo_mesa']) && !empty($params['codigo_mesa'])) {
            $id = $params['codigo_mesa'];
            if (Mesa::idExists($id)){
                Mesa::update_estado_comiendo($id);
                $payload = self::EncodePayload("SUCCESS", "Estado de mesa $id cambiado a 'con cliente comiendo'");
            } else {
                $payload = self::EncodePayload("ERROR", "La mesa $id no existe");
            }
        } else {
            $payload = self::EncodePayload("ERROR", "Debe incluir un parámetro 'codigo_mesa'");
        }

        return self::StandardResponse($response, $payload);
    }

    public function PagarPedido($request, $response, $args)
    {
        $params = $request->getParsedBody();

        if ( isset($params['codigo_mesa']) && !empty($params['codigo_mesa'])) {
            $id = $params['codigo_mesa'];
            if (Mesa::idExists($id)){
                Mesa::update_estado_pagando($id);
                $payload = self::EncodePayload("SUCCESS", "Estado de mesa $id cambiado a 'con cliente pagando'");
            } else {
                $payload = self::EncodePayload("ERROR", "La mesa $id no existe");
            }
        } else {
            $payload = self::EncodePayload("ERROR", "Debe incluir un parámetro 'codigo_mesa'");
        }

        return self::StandardResponse($response, $payload);
    }
    
    public function CerrarMesa($request, $response, $args)
    {
        $params = $request->getParsedBody();

        if ( isset($params['codigo_mesa']) && !empty($params['codigo_mesa'])) {
            $id = $params['codigo_mesa'];
            if (Mesa::idExists($id)){
                Mesa::cerrar_mesa($id);
                $payload = self::EncodePayload("SUCCESS", "Mesa $id cerrada");
            } else {
                $payload = self::EncodePayload("ERROR", "La mesa $id no existe");
            }
        } else {
            $payload = self::EncodePayload("ERROR", "Debe incluir un parámetro 'codigo_mesa'");
        }

        return self::StandardResponse($response, $payload);
    }

    public function FetchPreparacion($request, $response, $args)
    {        
        $sector = self::GetSector();
        if ($sector){
            $payload = self::EncodePayload("pedidos", Pedido::fetchAllPreparacion($sector));
        } else {
            $payload = self::EncodePayload("ERROR", "No se pudo validar el sector.");
        }
        return self::StandardResponse($response, $payload);
    }

    public function UpdateOrdenPreparacion($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $orden = $params['orden'];
        $tiempo = $params['tiempo_preparacion'];
        $time = $params['preparacion_pedido'];

        Pedido::update_order_preparar($orden, 'en_preparacion', $tiempo);

        $pedido = Pedido::fetch_from_order($orden);
        if ($pedido->estado == 'pendiente'){
            $pedido->estado = 'en_preparacion';
            $pedido->tiempo_inicio = date_format(new DateTime(), 'Y-m-d H:i:s');
            $newdate = date('Y-m-d H:i:s', (strtotime ("$pedido->tiempo_inicio+$time Minute")));
            $pedido->tiempo_estimado = $newdate;
            $pedido->update();
        }

        $payload = self::EncodePayload("mensaje", "Orden $orden actualizada con exito");
        return self::StandardResponse($response, $payload);
    }

    public function UpdateOrdenServir($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $orden = $params['orden'];
        $pedido = Pedido::fetch_from_order($orden);

        if (Pedido::IsLastOrderToServe($pedido->id)) {
            $pedido->estado = 'listo_para_servir';
            $pedido->tiempo_entrega = date_format(new DateTime(), 'Y-m-d H:i:s');
            $pedido->update();
        }

        Pedido::update_order_servir($orden, 'listo_para_servir');

        $payload = self::EncodePayload("mensaje", "Orden $orden actualizada con exito");
        return self::StandardResponse($response, $payload);
    }

    public function ConsultarPedido($request, $response, $args)
    {
        $query = $request->getQueryParams();
        if (isset($query['codigo_mesa']) && !empty($query['codigo_mesa'])){
            if (isset($query['codigo_pedido']) && !empty($query['codigo_pedido'])){
                $id_mesa = $query['codigo_mesa'];
                $id_pedido = $query['codigo_pedido'];
                if (Pedido::idExists($id_pedido)){
                    $pedido = Pedido::fetchInstanceById($id_pedido);
                    if ($pedido->codigo_mesa == $id_mesa){
                        if ($pedido->tiempo_estimado != null){
                            $payload = self::EncodePayload("Tiempo Estimado", $pedido->tiempo_estimado);
                        } else {
                            $payload = self::EncodePayload("Tiempo Estimado", "Su pedido aún no está en preparación.");
                        }
                    } else {
                        $payload = self::EncodePayload("ERROR", "La mesa no corresponde al pedido.");
                    }
                } else {
                    $payload = self::EncodePayload("ERROR", "El pedido no existe.");
                }
            } else {
                $payload = self::EncodePayload("ERROR", "Debe completar el campo 'codigo_pedido'.");
            }
        } else {
            $payload = self::EncodePayload("ERROR", "Debe completar el campo 'codigo_mesa'.");
        }


        return self::StandardResponse($response, $payload);
    }

}

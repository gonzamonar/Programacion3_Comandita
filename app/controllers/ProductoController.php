<?php
require_once './models/Producto.php';
require_once './interfaces/IABM.php';
require_once './controllers/ControllerTrait.php';

class ProductoController extends Producto implements IABM
{
    use ControllerTrait;
//ABM
    public function CreateOne($request, $response, $args)
    {
        $params = $request->getParsedBody();

        $product = new Producto();
        $product->nombre_producto = $params['nombre_producto'];
        $product->sector_producto = $params['sector_producto'];
        $product->precio = $params['precio'];
        $product->create();

        $payload = self::EncodePayload("mensaje", "Producto creado con exito");
        return self::StandardResponse($response, $payload);
    }

    public function UpdateOne($request, $response, $args)
    {
        $params = $request->getParsedBody();
        
        $product = Producto::fetchProductoById($args['id'], false);

        if (isset($params['nombre_producto'])){
            $product->nombre_producto = $params['nombre_producto'];
        }
        if (isset($params['sector_producto'])){ 
            $product->sector_producto = $params['sector_producto']; 
        }
        if (isset($params['precio'])){ 
            $product->precio = $params['precio']; 
        }
        if (isset($params['estado'])){
            $product->estado = $params['estado'];
            if ($params['estado'] == 'activo'){
                $product->fecha_baja = NULL;
            } else {
                $product->fecha_baja = date_format(new DateTime(), 'Y-m-d H:i:s');
            }
        }
        
        $product->update();

        $payload = self::EncodePayload("mensaje", "Producto modificado con exito");
        return self::StandardResponse($response, $payload);
    }

    public function DeleteOne($request, $response, $args)
    {
        Producto::delete($args['id']);
        $payload = self::EncodePayload("mensaje", "Producto borrado con exito");
        return self::StandardResponse($response, $payload);
    }


//FETCH
    public function FetchOne($request, $response, $args)
    {
        $payload = self::EncodePayload("producto", Producto::fetchProductoById($args['id']));
        return self::StandardResponse($response, $payload);
    }

    public function FetchOneByNombre($request, $response, $args)
    {
        $payload = self::EncodePayload("producto", Producto::fetchProductoByNombre($args['nombre']));
        return self::StandardResponse($response, $payload);
    }

    public function FetchAll($request, $response, $args)
    {
        $payload = self::EncodePayload("productos", Producto::fetchAllProductos());
        return self::StandardResponse($response, $payload);
    }

    public function FetchAllBySector($request, $response, $args)
    {
        $payload = self::EncodePayload("productos", Producto::fetchAllProductsBySector($args['sector']));
        return self::StandardResponse($response, $payload);
    }
}

<?php
require_once './models/Producto.php';
require_once './interfaces/IABM.php';

class ProductoController extends Producto implements IABM
{

//ABM
    public function CreateOne($request, $response, $args)
    {
        $params = $request->getParsedBody();

        $product = new Producto();
        $product->nombre_producto = $params['nombre_producto'];
        $product->sector_producto = $params['sector_producto'];
        $product->precio = $params['precio'];
        $product->create();

        return self::StandardResponse($response, self::EncodePayload("mensaje", "Producto creado con exito"));
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

        return self::StandardResponse($response, self::EncodePayload("mensaje", "Producto modificado con exito"));
    }

    public function DeleteOne($request, $response, $args)
    {
        Producto::delete($args['id']);
        return self::StandardResponse($response, self::EncodePayload("mensaje", "Producto borrado con exito"));
    }


//FETCH
    public function FetchOne($request, $response, $args)
    {
        return self::StandardResponse($response, self::EncodePayload("producto", Producto::fetchProductoById($args['id'])));
    }

    public function FetchOneByNombre($request, $response, $args)
    {
        return self::StandardResponse($response, self::EncodePayload("producto", Producto::fetchProductoByNombre($args['nombre'])));
    }

    public function FetchAll($request, $response, $args)
    {
        return self::StandardResponse($response, self::EncodePayload("productos", Producto::fetchAllProductos()));
    }

    public function FetchAllBySector($request, $response, $args)
    {

        return self::StandardResponse($response, self::EncodePayload("productos", Producto::fetchAllProductsBySector($args['sector'])));
    }

    public function FetchAllUnfiltered($request, $response, $args)
    {
        return self::StandardResponse($response, self::EncodePayload("productos", Producto::fetchAllProductos(false)));
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

<?php
require_once './models/Encuesta.php';
require_once './interfaces/IABM.php';
require_once './controllers/ControllerTrait.php';

class EncuestaController extends Encuesta implements IABM
{
    use ControllerTrait;
//ABM
    public function CreateOne($request, $response, $args)
    {
        $params = $request->getParsedBody();

        $encuesta = new Encuesta();
        $encuesta->id_pedido = $params['id_pedido'];
        $encuesta->codigo_mesa = $params['codigo_mesa'];
        $encuesta->puntuacion_mesa = $params['puntuacion_mesa'];
        $encuesta->puntuacion_restaurante = $params['puntuacion_restaurante'];
        $encuesta->puntuacion_mozo = $params['puntuacion_mozo'];
        $encuesta->puntuacion_cocinero = $params['puntuacion_cocinero'];
        $encuesta->valoracion_experiencia = $params['valoracion_experiencia'];
        $encuesta->promedio = $encuesta->CalcularPromedio();
        $id = $encuesta->create();

        $payload = self::EncodePayload("mensaje", "Encuesta Nº $id creada con exito");
        return self::StandardResponse($response, $payload);
    }

    public function UpdateOne($request, $response, $args)
    {
        $params = $request->getParsedBody();
        //$encuesta->update();

        $payload = self::EncodePayload("mensaje", "Sin implementar");
        return self::StandardResponse($response, $payload);
    }

    public function DeleteOne($request, $response, $args)
    {
        Encuesta::delete($args['id']);
        $payload = self::EncodePayload("mensaje", "Encuesta borrado con exito");
        return self::StandardResponse($response, $payload);
    }


//FETCH
    public function FetchOne($request, $response, $args)
    {
        $payload = self::EncodePayload("Encuesta", Encuesta::fetchInstanceById($args['id']));
        return self::StandardResponse($response, $payload);
    }

    public function FetchAll($request, $response, $args)
    {
        $payload = self::EncodePayload("Encuestas", Encuesta::fetchAllInstances());
        return self::StandardResponse($response, $payload);
    }
}

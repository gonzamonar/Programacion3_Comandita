<?php
require_once './models/Mesa.php';
require_once './interfaces/IABM.php';
require_once './controllers/ControllerTrait.php';

class ConsultasController
{
    use ControllerTrait;

//ABM
    public function MejoresEncuestas($request, $response, $args)
    {
        $payload = self::EncodePayload("Mejores_Encuestas", self::QueryMejoresEncuestas());
        return self::StandardResponse($response, $payload);
    }

    private function QueryMejoresEncuestas(){
        $sql = "SELECT * FROM encuestas ORDER BY promedio DESC LIMIT 5;" ;
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Encuesta');
    }


    public function MesaMasUsada($request, $response, $args)
    {   
        $mesa = self::QueryMesaMasUsada();
        $id = $mesa['codigo_mesa'];
        $veces = $mesa['cantidad_usos'];

        $payload = self::EncodePayload("Mesa_Más_Usada", "La mesa más usada fue la Nº$id, un total de $veces veces.");
        return self::StandardResponse($response, $payload);
    }

    private function QueryMesaMasUsada(){
        $sql = "SELECT codigo_mesa, COUNT(codigo_mesa) AS 'cantidad_usos' FROM pedidos GROUP BY codigo_mesa ORDER BY COUNT(codigo_mesa) DESC LIMIT 1;" ;
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC)[0];
    }


    public function PedidosDemorados($request, $response, $args)
    {
        $payload = self::EncodePayload("Pedidos_Entregados_Con_Demora", self::QueryPedidosDemorados());
        return self::StandardResponse($response, $payload);
    }

    private function QueryPedidosDemorados(){
        $sql = "SELECT * FROM pedidos WHERE tiempo_estimado < tiempo_entrega;" ;
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public function PedidosEntregados($request, $response, $args)
    {
        $payload = self::EncodePayload("Pedidos_Entregados_Antes_De_Tiempo", self::QueryPedidosEntregados());
        return self::StandardResponse($response, $payload);
    }

    private function QueryPedidosEntregados(){
        $sql = "SELECT * FROM pedidos WHERE tiempo_estimado >= tiempo_entrega;" ;
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public function DescargarLogo($request, $response, $args){
        $filename = './assets/logo.pdf';
  
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/text');
        header('Content-Disposition: attachment; filename="'. basename($filename) .'.pdf"');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: max-age=0');
        readfile($filename);
        return $response;
    }

    public function ProductosMasVendidos($request, $response, $args)
    {
        $payload = self::EncodePayload("Productos_Más_Vendidos", self::QueryProductosMasVendidos());
        return self::StandardResponse($response, $payload);
    }

    private function QueryProductosMasVendidos(){
        $sql = 
        "SELECT nombre_producto as 'producto', SUM(cantidad) as 'cantidad_vendida'
        FROM productos_por_pedido AS pp
        INNER JOIN productos AS p ON p.id = id_producto
        GROUP BY producto
        ORDER BY cantidad_vendida DESC;" ;
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function MesasPorFactura($request, $response, $args)
    {
        $payload = self::EncodePayload("Mesas_por_facturacion", self::QueryMesasPorFactura());
        return self::StandardResponse($response, $payload);
    }

    private function QueryMesasPorFactura(){
        $sql = 
        "SELECT codigo_mesa, importe_total, DATE(tiempo_inicio) as 'fecha' FROM pedidos GROUP BY codigo_mesa ORDER BY importe_total DESC;" ;
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function FacturacionMesaPorFecha($request, $response, $args)
    {
        $query = $request->getQueryParams();
        if (isset($query['codigo_mesa']) && !empty($query['codigo_mesa'])) {
            if (isset($query['fecha_inicio']) && !empty($query['fecha_inicio'])) {
                if (isset($query['codigo_mesa']) && !empty($query['fecha_fin'])) {
                    $codigo_mesa = $query['codigo_mesa'];
                    $fecha_inicio = $query['fecha_inicio'];
                    $fecha_fin = $query['fecha_fin'];

                    $mesa = self::QueryFacturacionMesaPorFecha($codigo_mesa, $fecha_inicio, $fecha_fin);
                    $monto = $mesa['facturacion'] ? $mesa['facturacion'] : 0 ;
                    $payload = self::EncodePayload("Informe_Facturación", "La mesa $codigo_mesa ha facturado entre el $fecha_inicio y el $fecha_fin un total de $$monto.");
                } else {
                    $payload = self::EncodePayload("ERROR", "Debe agregar un parámetro 'fecha_fin'.");
                }
            } else {
                $payload = self::EncodePayload("ERROR", "Debe agregar un parámetro 'fecha_inicio'.");
            }
        } else {
            $payload = self::EncodePayload("ERROR", "Debe agregar un parámetro 'codigo_mesa'.");
        }

        return self::StandardResponse($response, $payload);
    }

    private function QueryFacturacionMesaPorFecha($codigo_mesa, $fecha_inicio, $fecha_fin){
        $sql = 
        "SELECT codigo_mesa, SUM(importe_total) as 'facturacion' FROM pedidos WHERE  codigo_mesa = :codigo_mesa AND DATE(tiempo_inicio) >= :fecha_inicio AND DATE(tiempo_inicio) <= :fecha_fin;" ;
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC)[0];
    }


    public function IngresosAlSistema($request, $response, $args)
    {
        $query = $request->getQueryParams();
        if (isset($query['usuario']) && !empty($query['usuario'])) {
                    $usuario = $query['usuario'];

                    $payload = json_encode(array("IngresosUsuario" => 
                                            array(
                                                "usuario" => $usuario,
                                                "ingresos" => self::QueryIngresosAlSistema($usuario),
                                            )),
                                JSON_PRETTY_PRINT);
        } else {
            $payload = self::EncodePayload("ERROR", "Debe agregar un parámetro 'usuario'.");
        }
        return self::StandardResponse($response, $payload);
    }

    private function QueryIngresosAlSistema($usuario){
        $sql = 
        "SELECT fecha FROM logs WHERE path LIKE '%login%' AND resultado = 'SUCCESS' AND body LIKE :usuario;";
        $objAccesoDatos = AccesoDB::getInstance();
        $consulta = $objAccesoDatos->prepareQuery($sql);
        $consulta->bindValue(':usuario', "%usuario: $usuario,%", PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}

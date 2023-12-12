<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDB.php';
require_once './middlewares/AuthMiddleware.php';
require_once './middlewares/LoggerMiddleware.php';
require_once './middlewares/ValidationMiddleware.php';
require_once './controllers/EncuestaController.php';
require_once './controllers/LoginController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/ConsultasController.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env.myconfig');
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

$app->add(new LoggerMiddleware());

// Routes
$app->group('/logs', function (RouteCollectorProxy $group) {
      $group->get('[/]', \LoggerMiddleware::class . ':FetchAll');
    })->add(new AuthMiddleware(['socio']))
     ->add(\AuthMiddleware::class . ':verificarToken');

$app->group('/login', function (RouteCollectorProxy $group) {
      $group->post('[/]', \LoginController::class . ':Login')
            ->add(\ValidationMiddleware::class . ':ValidateLogin');
  });

$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':FetchAll');
    $group->get('/{id}', \UsuarioController::class . ':FetchOne')
          ->add(new ValidationMiddleware('Usuario'));
    $group->get('/username/{usuario}', \UsuarioController::class . ':FetchOneByUsername')
          ->add(\ValidationMiddleware::class . ':ValidateUsuario_Username');;
    $group->post('[/]', \UsuarioController::class . ':CreateOne')
          ->add(\ValidationMiddleware::class . ':ValidateUsuario_Post');
    $group->put('/{id}', \UsuarioController::class . ':UpdateOne')
          ->add(\ValidationMiddleware::class . ':ValidateUsuario_Put');
    $group->delete('/{id}', \UsuarioController::class . ':DeleteOne')
          ->add(new ValidationMiddleware('Usuario'));
  })->add(new AuthMiddleware(['socio']))
    ->add(\AuthMiddleware::class . ':verificarToken');

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \ProductoController::class . ':FetchAll');
    $group->get('/sector/{sector}', \ProductoController::class . ':FetchAllBySector');
    $group->get('/nombre/{nombre}', \ProductoController::class . ':FetchOneByNombre');
    $group->get('/{id}', \ProductoController::class . ':FetchOne')
          ->add(new ValidationMiddleware('Producto'));
    $group->post('[/]', \ProductoController::class . ':CreateOne')
          ->add(\ValidationMiddleware::class . ':ValidateProducto_Post');
    $group->put('/{id}', \ProductoController::class . ':UpdateOne')
          ->add(\ValidationMiddleware::class . ':ValidateProducto_Put');
    $group->delete('/{id}', \ProductoController::class . ':DeleteOne')
          ->add(new ValidationMiddleware('Producto'));
  })->add(\AuthMiddleware::class . ':verificarToken');

$app->group('/mesas', function (RouteCollectorProxy $group) {
      $group->get('[/]', \MesaController::class . ':FetchAll');
      $group->get('/{codigo}', \MesaController::class . ':FetchOne');
      $group->post('[/]', \MesaController::class . ':CreateOne');
      $group->put('/{codigo}', \MesaController::class . ':UpdateOne');
      $group->delete('/{codigo}', \MesaController::class . ':DeleteOne');
  })->add(new AuthMiddleware(['socio', 'mozo']))
    ->add(\AuthMiddleware::class . ':verificarToken');

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \PedidoController::class . ':FetchAll');
    $group->get('/{id}', \PedidoController::class . ':FetchOne');
    $group->post('[/]', \PedidoController::class . ':CreateOne');
    $group->post('/agregar_foto', \PedidoController::class . ':AgregarImagen')
            ->add(\ValidationMiddleware::class . ':ValidateAltaFoto');
    $group->put('/{id}', \PedidoController::class . ':UpdateOne');
    $group->delete('/{id}', \PedidoController::class . ':DeleteOne');
  })->add(new AuthMiddleware(['socio', 'mozo']))
    ->add(\AuthMiddleware::class . ':verificarToken');


$app->group('/empleados', function (RouteCollectorProxy $group) {
      $group->get('/pedidos_pendientes', \PedidoController::class . ':FetchPendientes');
      $group->post('/actualizar_orden', \PedidoController::class . ':UpdateOrdenPreparacion');
      $group->get('/pedidos_preparacion', \PedidoController::class . ':FetchPreparacion');
      $group->post('/finalizar_orden', \PedidoController::class . ':UpdateOrdenServir');
})->add(new AuthMiddleware(['bartender', 'cervecero', 'cocinero']))
  ->add(\AuthMiddleware::class . ':verificarToken');

$app->group('/consultas', function (RouteCollectorProxy $group) {
      $group->get('/mejores_encuestas', \ConsultasController::class . ':MejoresEncuestas');
      $group->get('/mesa_mas_usada', \ConsultasController::class . ':MesaMasUsada');
      $group->get('/pedidos_demorados', \ConsultasController::class . ':PedidosDemorados');
      $group->get('/pedidos_entregados', \ConsultasController::class . ':PedidosEntregados');
      $group->get('/descargar_logo', \ConsultasController::class . ':DescargarLogo');
      $group->get('/productos_mas_vendidos', \ConsultasController::class . ':ProductosMasVendidos');
      $group->get('/mesas_por_facturacion', \ConsultasController::class . ':MesasPorFactura');
      $group->get('/facturacion_por_fecha', \ConsultasController::class . ':FacturacionMesaPorFecha');
      $group->get('/ingresos_al_sistema', \ConsultasController::class . ':IngresosAlSistema');
})->add(\AuthMiddleware::class . ':verificarToken');


$app->post('/mesa_comiendo', \PedidoController::class . ':ServirPedido')
      ->add(new AuthMiddleware(['socio', 'mozo']))
      ->add(\AuthMiddleware::class . ':verificarToken');
$app->post('/mesa_pagando', \PedidoController::class . ':PagarPedido')
      ->add(new AuthMiddleware(['socio', 'mozo']))
      ->add(\AuthMiddleware::class . ':verificarToken');
$app->post('/cerrar_mesa', \PedidoController::class . ':CerrarMesa')
      ->add(new AuthMiddleware(['socio']))
      ->add(\AuthMiddleware::class . ':verificarToken');


$app->get('/estado_pedido', \PedidoController::class . ':ConsultarPedido');

$app->group('/encuestas', function (RouteCollectorProxy $group) {
    $group->get('[/]', \EncuestaController::class . ':FetchAll');
    $group->post('[/]', \EncuestaController::class . ':CreateOne')
            ->add(\ValidationMiddleware::class . ':ValidateAltaEncuesta');
});



$app->run();

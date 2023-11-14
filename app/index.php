<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDB.php';
require_once './middlewares/AuthMiddleware.php';
require_once './middlewares/ValidationMiddleware.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';

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

// Routes
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':FetchAll');
    $group->get('/db', \UsuarioController::class . ':FetchAllUnfiltered');
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
  })->add(new AuthMiddleware(['admin']));


  $app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \ProductoController::class . ':FetchAll');
    $group->get('/db', \ProductoController::class . ':FetchAllUnfiltered');
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

  });
  
      $app->group('/mesas', function (RouteCollectorProxy $group) {
      $group->get('[/]', \MesaController::class . ':FetchAll');
      $group->get('/db', \MesaController::class . ':FetchAllUnfiltered');
      $group->get('/{codigo}', \MesaController::class . ':FetchOne');
      $group->post('[/]', \MesaController::class . ':CreateOne');
      $group->put('/{codigo}', \MesaController::class . ':UpdateOne');
      $group->delete('/{codigo}', \MesaController::class . ':DeleteOne');
  });


/*
$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':FetchAll');
    $group->get('/{id}', \UsuarioController::class . ':FetchOne');
    $group->post('[/]', \UsuarioController::class . ':CreateOne');
    $group->put('/{id}', \UsuarioController::class . ':UpdateOne');
    $group->delete('/{id}', \UsuarioController::class . ':DeleteOne');
  });

$app->group('/encuestas', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':FetchAll');
    $group->get('/{id}', \UsuarioController::class . ':FetchOne');
    $group->post('[/]', \UsuarioController::class . ':CreateOne');
    $group->put('/{id}', \UsuarioController::class . ':UpdateOne');
    $group->delete('/{id}', \UsuarioController::class . ':DeleteOne');
  });
*/

$app->run();

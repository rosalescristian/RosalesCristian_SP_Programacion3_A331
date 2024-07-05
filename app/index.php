<?php

// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);
date_default_timezone_set("America/Argentina/Buenos_Aires"); # seteo zona horario default

/* use Psr\Http\Message\ResponseInterface as Response; */ // No podemos usar esta interfaz xq ya estamos usando la de Psr7 en la linea 11
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
/* use Slim\Handlers\Strategies\RequestHandler; */ // Esta la comente xq en la clase usamos REQUESTHANDLER de la linea 9
use Slim\Psr7\Response; // No podemos usar esta interfaz xq ya estamos usando la de Psr en la linea 7
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';
require_once './db/AccesoDatos.php';
// require_once './middlewares/Logger.php';
require_once './middlewares/RolMiddleware.php';
require_once './middlewares/DatosMiddleware.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

//MIDDLEWARES
// Add error middleware
$app->addErrorMiddleware(true, true, true);
// Add parse body
$app->addBodyParsingMiddleware(); // Se parsea el body por si entrar por PUT


// Routes
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno')
              ->add(new RolMiddleware(['admin']))
              ->add(\DatosMiddleware::class . ':datosMiddlewareUsuario');
  });

$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductoController::class . ':TraerTodos');
  $group->get('/{producto}', \ProductoController::class . ':TraerUno');
  $group->post('[/]', \ProductoController::class . ':CargarUno')
              ->add(new RolMiddleware(['socio','mozo','bartender','cervecero','cocinero']))
              ->add(\DatosMiddleware::class . ':datosMiddlewareProducto');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class . ':TraerTodos');
  $group->get('/{mesa}', \MesaController::class . ':TraerUno');
  $group->post('[/]', \MesaController::class . ':CargarUno')
            ->add(new RolMiddleware(['socio']))
            ->add(\DatosMiddleware::class . ':datosMiddlewareMesa');
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidoController::class . ':TraerTodos');
  $group->get('/{pedido}', \PedidoController::class . ':TraerUno');
  $group->post('[/]', \PedidoController::class . ':CargarUno')
            ->add(new RolMiddleware(['mozo']))
            ->add(\DatosMiddleware::class . ':datosMiddlewarePedido');
  $group->put('/{id_pedido}/{id_pedido_estado}', PedidoController::class . ':modificarUno') // DEBUG THIS!!!
            ->add(new RolMiddleware(['mozo']))
            ->add(\DatosMiddleware::class . ':datosMiddlewareModificarStatusPedido');
});

$app->group('/archivos', function(RouteCollectorProxy $group){
  $group->post('/cargar-csv', \ProductoController::class . ':importarCsvProductos');
  $group->get('/exportar-csv', \ProductoController::class . ':exportarCsvProductos');
});

$app->run();
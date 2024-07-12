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
require_once './controllers/ProductoController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/UsuarioController.php';

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
$app->post('/login',\UsuarioController::class . ':login');

$app->group('/tienda', function (RouteCollectorProxy $group) {
  $group->post('/alta', \ProductoController::class . ':CargarUno');
});

$app->group('/ventas', function (RouteCollectorProxy $group) {
  $group->post('/alta', \PedidoController::class . ':CargarUno');
});

$app->group('/registro', function (RouteCollectorProxy $group) {
  $group->post('[/]', \UsuarioController::class . ':CargarUno');
});

$app->group('/ventas/consultar', function (RouteCollectorProxy $group) {
  $group->get('/productos/vendidos', \ProductoController::class . ':productosVendidos'); // OK
  $group->get('/ventas/porUsuario', \PedidoController::class . ':ventasPorUsuario'); // OK
  $group->get('/ventas/porProducto', \PedidoController::class . ':ventasPorTipoProducto'); // OK
  $group->get('/productos/entreValores', \ProductoController::class . ':ventasEntreValores'); // OK
  $group->get('/ventas/ingresos', \PedidoController::class . ':TraerUno'); // IF fecha null entonces todos, else otro metodo de 1 dia
  $group->get('/productos/masVendidos', \ProductoController::class . ':MasVendidos'); // OK
});

$app->run();
<?php
require_once './models/Pedido.php';
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        $email = $parametros['mail'];
        $nombre = $parametros['nombre'];
        $tipo = $parametros['tipo'];
        $marca = $parametros['marca'];
        $stock = $parametros['stock'];
        $imagen = $uploadedFiles['imagen_venta'] ?? null;

        if ($imagen === null || $imagen->getError() !== UPLOAD_ERR_OK) {
          $payload = json_encode(array("mensaje" => "No se subió ninguna imagen o hubo un error en la carga."));
          $response->getBody()->write($payload);
          return $response->withHeader('Content-Type', 'application/json');
        }

        $directorioImagenes = __DIR__ . '/../ImagenesDeVenta/2024/';
        
        if (!file_exists($directorioImagenes)) {
          // Intentar crear el directorio
          if (!mkdir($directorioImagenes, 0777, true)) {
              $payload = json_encode(array("mensaje" => "No se pudo crear el directorio para guardar las imágenes"));
              $response->getBody()->write($payload);
              return $response->withHeader('Content-Type', 'application/json');
          }
        }

        $nombreUsuario = explode('@', $email)[0];
        $fecha = date('Ymd');
        $nombreImagen = $nombre . '_' . $tipo . '_' . '_' . $marca . '_' . $nombreUsuario . $fecha . '.' . pathinfo($imagen->getClientFilename(), PATHINFO_EXTENSION);

        $producto = Producto::obtenerProductoPorNombreTipoMarca($nombre, $tipo, $marca);

        if(!$producto){
          $payload = json_encode(array("error" => "No se encuentra ese producto en la tienda."));
          $response->getBody()->write($payload);
          return $response->withHeader('Content-Type', 'application/json');
        }

        if($producto->stock < $stock){
          $payload = json_encode(array("error" => "No hay suficiente stock en la tienda."));
          $response->getBody()->write($payload);
          return $response->withHeader('Content-Type', 'application/json');
        }

        $precioProducto = Producto::obtenerPrecio($nombre, $tipo, $marca);
        $montoTotal = $precioProducto * $stock;

        // Creamos el pedido
        $ped = new Pedido();
        $ped->email = $email;
        $ped->nombre = $nombre;
        $ped->tipo = $tipo;
        $ped->marca = $marca;
        $ped->stock = $stock;
        $ped->precio = $montoTotal;
        $ped->imagen = $nombreImagen;

        $ped->crearPedido();

        $payload = json_encode(array("mensaje" => "Pedido creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos producto por ID de pedido
        $ped = $args['pedido'];
        $pedido = Pedido::obtenerPedido($ped);
        $payload = json_encode($pedido);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::obtenerTodos();
        $payload = json_encode(array("listaPedido" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
      $datos = $request->getParsedBody();

        $numeroPedido = $datos['numero_pedido'] ?? null;
        $email = $datos['email'] ?? null;
        $nombre = $datos['nombre'] ?? null;
        $tipo = $datos['tipo'] ?? null;
        $marca = $datos['marca'] ?? null;
        $cantidad = $datos['cantidad'] ?? null;

        if (!$numeroPedido || !$email || !$nombre || !$tipo || !$marca || !$cantidad) {
          $response->getBody()->write(json_encode(['mensaje' => 'Todos los campos son obligatorios']));
          return $response->withHeader('Content-Type', 'application/json');
        }

        $venta = Pedido::obtenerPorNumeroPedido($numeroPedido);

        if ($venta) {
            $venta->email = $email;
            $venta->nombre = $nombre;
            $venta->tipo = $tipo;
            $venta->marca = $marca;
            $venta->stock = $cantidad;

            $resultado = Pedido::modificarPedido($venta);

            if ($resultado) {
              $response->getBody()->write(json_encode(['mensaje' => 'Venta modificada exitosamente']));
              return $response->withHeader('Content-Type', 'application/json');
          } else {
              $response->getBody()->write(json_encode(['mensaje' => 'Error al modificar la venta']));
              return $response->withHeader('Content-Type', 'application/json');
          }
      } else {
          $response->getBody()->write(json_encode(['mensaje' => 'No existe ese número de pedido']));
          return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $pedidoId = $parametros['pedido_id'];
        Pedido::borrarPedido($pedidoId);

        $payload = json_encode(array("mensaje" => "Pedido borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    private function guardarImagen($imagen, $email, $nombre, $tipo, $marca)
    {

        $nombreUsuario = substr($email, 0, strpos($email, '@'));
        $fechaVenta = date('Y-m-d');
        $nombreArchivo = "{$nombreUsuario}_{$nombre}_{$tipo}_{$marca}_{$fechaVenta}.jpg";

        $directorioImagenes = __DIR__ . '/../ImagenesDeVenta/2024/';

        try {
            $imagen->moveTo($directorioImagenes . $nombreArchivo);
        } catch (Exception $e) {
            throw new Exception("Error al guardar la imagen: " . $e->getMessage());
        }

        return '/ImagenesDeVenta/2024/' . $nombreArchivo;
    }

    public function ventasPorUsuario($request, $response, $args)
    {
      $params = $request->getQueryParams();
      if(!isset($params['email'])){
        $payload = json_encode(array("error" => "No se ingreso un mail valido")); 
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
      }
      $email = $params['email'];

        $pedidosUsuario = Pedido::pedidosPorUsuario($email);
        $payload = json_encode(array("listaPedido" => $pedidosUsuario));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function ventasPorTipoProducto($request, $response, $args)
    {
      $params = $request->getQueryParams();
      if(!isset($params['tipo'])){
        $payload = json_encode(array("error" => "No se ingreso un tipo de producto valido")); 
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
      }
      $tipo = $params['tipo'];

        $pedidosUsuario = Pedido::pedidosPorTipoProducto($tipo);
        $payload = json_encode(array("listaPedido" => $pedidosUsuario));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function ventasEntreValores($request, $response, $args)
    {
      $params = $request->getQueryParams();
      if (!isset($params['valor_minimo'], $params['valor_maximo'])) {
          $payload = json_encode(array("error" => "No se ingresaron un valor máximo y mínimo válido")); 
          $response->getBody()->write($payload);
          return $response->withHeader('Content-Type', 'application/json');
      } else {
          $max = $params['valor_maximo'];
          $min = $params['valor_minimo'];
      }
  
      $cantidadVendidos = Pedido::obtenerVentasEntreValores($min, $max);
  
      if ($cantidadVendidos === null || empty($cantidadVendidos)) {
          $payload = json_encode(array("mensaje" => "No se encontraron ventas entre $min y $max."));
      } else {
          $payload = json_encode(array("listaPedido" => $cantidadVendidos));
      }
  
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function ingresos($request, $response, $args)
    {
      $params = $request->getQueryParams();
    
      if (!isset($params['fecha_venta'])) {
          $ingresosTotales = Pedido::obtenerGananciasTotales();
          $payload = json_encode(array("totalGanancias" => $ingresosTotales));
      } else {
          $fecha = $params['fecha_venta'];
          $ingresosPorDia = Pedido::obtenerGananciasPorFecha($fecha);
          
          if ($ingresosPorDia === null || empty($ingresosPorDia)) {
              $payload = json_encode(array("mensaje" => "No se encontraron ventas para la fecha $fecha."));
          } else {
              $payload = json_encode(array("ganancias" => $ingresosPorDia));
          }
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    /* public function ModificarUno($request, $response, $args){

    } */
}

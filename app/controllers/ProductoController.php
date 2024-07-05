<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        $nombre = $parametros['nombre'];
        $precio = $parametros['precio'];
        $tipo = $parametros['tipo'];
        $marca = $parametros['marca'];
        $stock = $parametros['stock'];
        $imagen = $uploadedFiles['imagen'] ?? null;
        

        if ($imagen === null || $imagen->getError() !== UPLOAD_ERR_OK) {
          $payload = json_encode(array("mensaje" => "No se subió ninguna imagen o hubo un error en la carga."));
          $response->getBody()->write($payload);
          return $response->withHeader('Content-Type', 'application/json');
      }

        $directorioImagenes = __DIR__ . '/../ImagenesDeProductos/2024/';

        // Verificar si el directorio existe, si no, crearlo
        if (!file_exists($directorioImagenes)) {
            // Intentar crear el directorio
            if (!mkdir($directorioImagenes, 0777, true)) {
                $payload = json_encode(array("mensaje" => "No se pudo crear el directorio para guardar las imágenes"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }

        $nombreImagen = $tipo . '_' . $nombre . '_' . uniqid() . '.' . pathinfo($imagen->getClientFilename(), PATHINFO_EXTENSION);

        try {
          $imagen->moveTo($directorioImagenes . $nombreImagen);
      } catch (Exception $e) {
          $payload = json_encode(array("mensaje" => "Error al guardar la imagen del producto"));
          $response->getBody()->write($payload);
          return $response->withHeader('Content-Type', 'application/json');
      }

        // Creamos el producto
        $prod = new Producto();
        $prod->nombre = $nombre;
        $prod->precio = $precio;
        $prod->tipo = $tipo;
        $prod->marca = $marca;
        $prod->stock = $stock;
        $prod->imagen = '/ImagenesDeProductos/2024/' . $nombreImagen;

        $prod->crearProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
      // Buscamos producto por nombre de producto
      $param = $request->getParsedBody();
      $producto = null;
      $marca = '';
      $tipo = '';
  
      if (isset($param['marca'], $param['tipo'], $param['nombre'])) {
          $nombre = $param['nombre'];
          $tipo = $param['tipo'];
          $marca = $param['marca'];
          $producto = Producto::obtenerProductoPorNombreTipoMarca($nombre, $tipo, $marca);
      }
      
      if ($producto) {
          $payload = json_encode(array("mensaje" => "Existe"));
      } else {
          $productosPorMarca = Producto::obtenerProductosPorMarca($marca);
          $productosPorTipo = Producto::obtenerProductosPorTipo($tipo);
  
          if (empty($productosPorMarca)) {
              $payload = json_encode(array("mensaje" => "No existen productos de la marca " . $marca));
          } elseif (empty($productosPorTipo)) {
              $payload = json_encode(array("mensaje" => "No existen productos del tipo " . $tipo));
          } else {
              $payload = json_encode(array("mensaje" => "Producto no encontrado", "marca" => $marca, "tipo" => $tipo));
          }
      }
  
      $response->getBody()->write($payload);
      return $response
          ->withHeader('Content-Type', 'application/json');
  }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::obtenerTodos();
        $payload = json_encode(array("listaProducto" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        Producto::modificarProducto($nombre);

        $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $productoId = $parametros['producto_id'];
        Producto::borrarProducto($productoId);

        $payload = json_encode(array("mensaje" => "Producto borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }


    public function importarCsvProductos($request, $response, $args)
    {
      $uploadedFiles = $request->getUploadedFiles();
      
      if (empty($uploadedFiles['archivo_csv'])) {
        $payload = json_encode(["mensaje" => "No se subió ningún archivo. Reintente."]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
      }

      $archivoCsv = $uploadedFiles['archivo_csv'];
      if ($archivoCsv->getError() !== UPLOAD_ERR_OK) {
          $payload = json_encode(array("mensaje" => "Error al subir el archivo. Reintente."));
          $response->getBody()->write($payload);
          return $response->withHeader('Content-Type', 'application/json');
      }

      /* $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
      if ($fileInfo->file($archivoCsv->file) !== 'text/plain') {
          $payload = json_encode(["mensaje" => "El archivo subido no es un archivo CSV válido."]);
          return $response->withStatus(400)
                          ->withHeader('Content-Type', 'application/json')
                          ->write($payload);
    } */
      // Obtener la ruta del archivo subido
      $rutaArchivo = $archivoCsv->getStream()->getMetadata('uri');

      // Procesar el archivo CSV
      $objAccesoDatos = AccesoDatos::obtenerInstancia();
      $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombre, descripcion, precio, stock, departamento_id, tipo_producto_id, fecha_alta) VALUES (:nombre, :descripcion, :precio, :stock, :departamento_id, :tipo_producto_id, :fecha_alta)");
      
      if (($handle = fopen($rutaArchivo, 'r')) !== false) {
          $header = fgetcsv($handle, 1000, ',');
          
          while (($data = fgetcsv($handle, 1000, ',')) !== false) {
              $row = array_combine($header, $data);

              $producto = new Producto();
              $producto->nombre = $row['nombre'];
              $producto->descripcion = $row['descripcion'];
              $producto->precio = $row['precio'];
              $producto->stock = $row['stock'];
              $producto->departamento_id = $row['departamento_id'];
              $producto->tipo_producto_id = $row['tipo_producto_id'];

              $producto->crearProducto();
          }
          fclose($handle);

          $payload = json_encode(array("mensaje" => "Productos agregados con éxito desde CSV"));
          $response->getBody()->write($payload);
          
          return $response->withHeader('Content-Type', 'application/json');
      
        } else {
            $payload = json_encode(array("mensaje" => "Error al procesar el archivo CSV"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
  }

  public function exportarCsvProductos($request, $response, $args)
  {
    // Nombre del archivo CSV
    $fileName = 'productos_exportados_' . date('Ymd') . '.csv';
    // Ruta del directorio donde se exportan los CSV
    $rutaDirectorio = './Files/ExportedFiles/';
    // Ruta completa del archivo CSV
    $rutaArchivo = $rutaDirectorio . $fileName;

    if (!is_dir($rutaDirectorio) && !mkdir($rutaDirectorio, 0777, true)) {
      $payload = json_encode(array("mensaje" => "Error al crear el directorio para guardar el archivo CSV"));
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    // Abro el archivo CSV para escritura
    $output = fopen($rutaArchivo, 'w');

    // Verifico si se pudo abrir el archivo
    if ($output === false) {
        $payload = json_encode(array("mensaje" => "Error al crear el archivo CSV"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Escribo los headers del archivo CSV
    fputcsv($output, array(
        'ID', 'Nombre', 'Descripción', 'Precio', 'Stock', 'Departamento ID', 'Tipo Producto ID',
        'Fecha Alta', 'Fecha Modificación', 'Fecha Baja'
    ));
    // Traigo todos los productos
    $productos = Producto::obtenerTodos();

    // Escribo los datos de los productos en el archivo CSV
    foreach ($productos as $producto) {
        fputcsv($output, array(
            $producto->id,
            $producto->nombre,
            $producto->descripcion,
            $producto->precio,
            $producto->stock,
            $producto->departamento_id,
            $producto->tipo_producto_id,
            $producto->fecha_alta,
            $producto->fecha_modificacion,
            $producto->fecha_baja
        ));
    }

    // Cierro el archivo!!!
    fclose($output);

    $payload = json_encode(array("mensaje" => "Archivo CSV exportado correctamente a $rutaArchivo"));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');

  }

  public function productosVendidos($request, $response, $args)
    {
        $params = $request->getQueryParams();
        if(!isset($params['fecha_venta'])){
          $fecha = date('Y-m-d', strtotime('-1 day'));
        }
        else{
          $fecha = $params['fecha_venta'];
        }

        $cantidadVendidos = Producto::obtenerCantidadVendidosPorFecha($fecha);
        $payload = json_encode(array("mensaje" => "Se vendieron " . $cantidadVendidos . " el dia ". $fecha)); 
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function MasVendidos($request, $response, $args)
    {
      $lista = Producto::productosMasVendidos();
      $payload = json_encode(array("listaProducto" => $lista));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    
}

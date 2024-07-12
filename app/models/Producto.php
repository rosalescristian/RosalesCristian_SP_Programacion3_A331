<?php

class Producto
{
    public $id;
    public $nombre;
    public $precio;
    public $tipo;
    public $marca;
    public $stock;
    public $imagen;
    public $fecha_alta;
    public $fecha_modificacion;
    public $fecha_baja;

    public function crearProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $productoExistente = Producto::obtenerProductoPorNombreTipoMarca($this->nombre, $this->tipo, $this->marca);
        
        if($productoExistente){
            Producto::actualizarProducto($productoExistente->id, $this->precio, $this->stock);
        }
        else{

            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombre, precio, tipo, marca, stock, imagen) VALUES (:nombre, :precio, :tipo, :marca, :stock, :imagen)");
        
            /* $fecha = new DateTime(date("d-m-Y")); */

            $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
            $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
            $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
            $consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
            $consulta->bindValue(':stock', $this->stock, PDO::PARAM_STR);
            $consulta->bindValue(':imagen', $this->imagen, PDO::PARAM_STR);
            /* $consulta->bindValue(':fecha_alta', date_format($fecha, 'Y-m-d H:i:s')); */
            
            $consulta->execute();

            return $objAccesoDatos->obtenerUltimoId();
        
        }
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id
                                                        , nombre
                                                        , descripcion
                                                        , precio
                                                        , stock
                                                        , departamento_id
                                                        , tipo_producto_id
                                                        , fecha_alta
                                                        , fecha_modificacion
                                                        , fecha_baja
                                                         FROM productos");
        
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerProducto($producto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id
                                                        , nombre
                                                        , marca
                                                        , tipo
                                                        , precio
                                                        , stock
                                                        , imagen
                                                        FROM productos
                                                        WHERE nombre = :nombre
                                                        AND marca = :marca
                                                        AND tipo = :tipo");
        $consulta->bindValue(':nombre', $producto['nombre'], PDO::PARAM_STR);
        $consulta->bindValue(':marca', $producto['marca'], PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $producto['tipo'], PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

    public static function modificarProducto($producto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET nombre = :nombre, descripcion = :descripcion WHERE id = :id");
        $consulta->bindValue(':producto', $producto->producto, PDO::PARAM_STR);
        $consulta->bindValue(':descripcion', $producto->descripcion, PDO::PARAM_STR);
        $consulta->bindValue(':id', $producto->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function actualizarProducto($id, $precio, $stock)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE productos 
                                                        SET precio = :precio, 
                                                            stock = stock + :stock
                                                        WHERE id = :id");
        $consulta->bindValue(':precio', $precio, PDO::PARAM_STR);
        $consulta->bindValue(':stock', $stock, PDO::PARAM_INT);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarProducto($producto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET fecha_baja = :fechaBaja WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id', $producto, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }

    public static function obtenerProductoPorNombreTipoMarca($nombre, $tipo, $marca)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id
                                                        , nombre
                                                        , marca
                                                        , tipo
                                                        , precio
                                                        , stock
                                                        , imagen
                                                        FROM productos
                                                        WHERE nombre = :nombre
                                                        AND marca = :marca
                                                        AND tipo = :tipo");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':marca', $marca, PDO::PARAM_STR);
        $consulta->execute();
        
        return $consulta->fetchObject('Producto');
    }

    public static function obtenerProductosPorMarca($marca)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id
                                                        , nombre
                                                        , marca
                                                        , tipo
                                                        , precio
                                                        , stock
                                                        , imagen
                                                        FROM productos 
                                                        WHERE marca = :marca");
        $consulta->bindValue(':marca', $marca, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerProductosPorTipo($tipo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id
                                                        , nombre
                                                        , marca
                                                        , tipo
                                                        , precio
                                                        , stock
                                                        , imagen
                                                        FROM productos 
                                                        WHERE tipo = :tipo");
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerPrecio($nombre, $tipo, $marca)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT precio
                                                       FROM productos 
                                                       WHERE nombre = :nombre 
                                                       AND tipo = :tipo 
                                                       AND marca = :marca");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':marca', $marca, PDO::PARAM_STR);
        $consulta->execute();

        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($resultado) {
            return $resultado['precio'];
        } else {
            return null;
        }
    }

    public function obtenerCantidadVendidosPorFecha($fecha)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT COUNT(*) AS CANTIDAD
                                                       FROM VENTAS
                                                       WHERE FECHA_VENTA = :FECHA ");
        $consulta->bindValue(':FECHA', $fecha, PDO::PARAM_STR);
        $consulta->execute();

        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        return $resultado['CANTIDAD'];

    }

    public static function productosMasVendidos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT SUM(stock) AS stock
                                                    , nombre_producto AS nombre 
                                                    FROM VENTAS 
                                                    GROUP BY nombre_producto 
                                                    ORDER BY stock DESC
                                                    LIMIT 1");
        
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS);
    }

    public function obtenerProductoMasVendido()
    {
        var_dump("entro al query");
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT nombre_producto AS nombre
                                                        , SUM(stock) AS stock
                                                        FROM VENTAS
                                                        GROUP BY 1
                                                        ORDER BY 2 DESC
                                                        LIMIT 1");
        
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        return $resultado['nombre'];
    }

}
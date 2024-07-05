<?php

class Pedido
{
    public $email;
    public $nombre;
    public $tipo;
    public $marca;
    public $stock;
    public $precio;
    public $imagen;

    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO 
                                                        ventas ( email
                                                                , nombre_producto
                                                                , tipo_producto
                                                                , marca_producto
                                                                , stock
                                                                , precio
                                                                , fecha_venta
                                                                , imagen) 
                                                        VALUES (:email, :nombre_producto, :tipo_producto
                                                                , :marca_producto, :stock, :precio, :fecha_venta, :imagen)");
        $fecha = new DateTime(date("Y-m-d H:i:s"));

        /* $codigo_pedido = $this->generarCodigoUnico();

        $consulta->bindValue(':id', $codigo_pedido, PDO::PARAM_STR); */
        /* $consulta->bindValue(':mes', date_format($fecha, 'Ym'));
        $consulta->bindValue(':fecha', date_format($fecha, 'Y-m-d')); */
        $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
        $consulta->bindValue(':nombre_producto', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo_producto', $this->tipo, PDO::PARAM_STR);
        $consulta->bindValue(':marca_producto', $this->marca, PDO::PARAM_STR);
        $consulta->bindValue(':stock', $this->stock, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_venta', date_format($fecha, 'Y-m-d H:i:s'), PDO::PARAM_STR);
        $consulta->bindValue(':imagen', $this->imagen, PDO::PARAM_STR);

        $consulta->execute();

        $objAccesoDatos->obtenerUltimoId();

        $consultaActualizarStock = $objAccesoDatos->prepararConsulta("UPDATE productos
                                                                        SET stock = stock - :cantidad
                                                                        WHERE nombre = :nombre_producto 
                                                                        AND tipo = :tipo_producto 
                                                                        AND marca = :marca_producto");
    // Asignar los valores de los parámetros para actualizar el stock
        $consultaActualizarStock->bindValue(':nombre_producto', $this->nombre, PDO::PARAM_STR);
        $consultaActualizarStock->bindValue(':tipo_producto', $this->tipo, PDO::PARAM_STR);
        $consultaActualizarStock->bindValue(':marca_producto', $this->marca, PDO::PARAM_STR);
        $consultaActualizarStock->bindValue(':cantidad', $this->stock, PDO::PARAM_INT);

        // Ejecutar la consulta para actualizar el stock
        $consultaActualizarStock->execute();

        return $objAccesoDatos; // Devolver el ID de la venta insertada
    }

    /* public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id
                                                            , mes
                                                            , fecha
                                                            , fecha_hora
                                                            , turno_id
                                                            , estado_id
                                                            , id_empleado
                                                            , id_mesa
                                                            , url_foto
                                                            , id_pedido_estado
                                                            , fecha_hora_fin
                                                            , mesa_encuesta
                                                            , restaurante_encuesta
                                                            , mozo_encuesta
                                                            , cocinero_encuesta
                                                            , observaciones
                                                            , propina
                                                         FROM pedidos");
        
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedido($pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id
                                                            , mes
                                                            , fecha
                                                            , fecha_hora
                                                            , turno_id
                                                            , estado_id
                                                            , id_empleado
                                                            , id_mesa
                                                            , url_foto
                                                            , id_pedido_estado
                                                            , fecha_hora_fin
                                                            , mesa_encuesta
                                                            , restaurante_encuesta
                                                            , mozo_encuesta
                                                            , cocinero_encuesta
                                                            , observaciones
                                                            , propina
                                                         FROM pedidos
                                                        WHERE id = :id");
        $consulta->bindValue(':id', $pedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    } */

    public static function modificarPedido($pedido)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos 
                                                        SET   /* estado_id = :estado_id
                                                            , url_foto = :url_foto
                                                            ,  */id_pedido_estado = :id_pedido_estado
                                                            /* , fecha_hora_fin = :fecha_hora_fin
                                                            , mesa_encuesta = :mesa_encuesta
                                                            , restaurante_encuesta = :restaurante_encuesta
                                                            , mozo_encuesta = :mozo_encuesta
                                                            , cocinero_encuesta = :cocinero_encuesta
                                                            , observaciones = :observaciones
                                                            , propina = :propina  */
                                                    WHERE id = :id");
        /* $consulta->bindValue(':pedido', $pedido->pedido, PDO::PARAM_STR); */
        $consulta->bindValue(':id', $pedido->id, PDO::PARAM_INT);
        /* $consulta->bindValue(':estado_id', $pedido->estado_id, PDO::PARAM_STR);
        $consulta->bindValue(':url_foto', $pedido->url_foto, PDO::PARAM_STR); */
        $consulta->bindValue(':id_pedido_estado', $pedido->id_pedido_estado, PDO::PARAM_STR);
        /* $consulta->bindValue(':fecha_hora_fin', $pedido->fecha_hora_fin, PDO::PARAM_STR);
        $consulta->bindValue(':mesa_encuesta', $pedido->mesa_encuesta, PDO::PARAM_STR);
        $consulta->bindValue(':restaurante_encuesta', $pedido->restaurante_encuesta, PDO::PARAM_STR);
        $consulta->bindValue(':mozo_encuesta', $pedido->mozo_encuesta, PDO::PARAM_STR);
        $consulta->bindValue(':cocinero_encuesta', $pedido->cocinero_encuesta, PDO::PARAM_STR);
        $consulta->bindValue(':obervaciones', $pedido->obervaciones, PDO::PARAM_STR);
        $consulta->bindValue(':propina', $pedido->propina, PDO::PARAM_STR); */
        $consulta->execute();
    }

    public static function borrarPedido($pedido)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET fecha_hora_fin = :fecha_hora_fin WHERE id = :id");
        $fecha = new DateTime(date("Y-m-d H:i:s"));
        $consulta->bindValue(':id', $pedido, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_hora_fin', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }

    private function generarCodigo($longitud) {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codigo = '';
        $max = strlen($caracteres) - 1;

        for ($i = 0; $i < $longitud; $i++) {
            $codigo .= $caracteres[random_int(0, $max)];
        }

        return $codigo;
    }

    private function generarCodigoUnico($longitud = 5) {
        do {
            $codigo_pedido = $this->generarCodigo($longitud);
        } while ($this->codigoExisteEnBD($codigo_pedido));

        return $codigo_pedido;
    }

    private function codigoExisteEnBD($codigo_pedido) {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("SELECT COUNT(*) FROM mesas WHERE codigo_mesa = :codigo_mesa");
        $consulta->execute(['codigo_mesa' => $codigo_pedido]);
        return $consulta->fetchColumn() > 0;
    }

    public static function pedidosPorUsuario($email)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT *
                                                         FROM ventas
                                                        WHERE email = :email");
        $consulta->bindValue(':email', $email, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function pedidosPorTipoProducto($tipo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT *
                                                         FROM ventas
                                                        WHERE tipo = :tipo");
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public function obtenerVentasEntreValores($min, $max)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT *
                                                       FROM VENTAS
                                                       WHERE precio between :min and :max");
        $consulta->bindValue(':min', $min, PDO::PARAM_STR);
        $consulta->bindValue(':max', $max, PDO::PARAM_STR);
        $consulta->execute();

        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        return $resultado;

    }
    
}
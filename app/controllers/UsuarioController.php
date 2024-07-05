<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
use Firebase\JWT\JWT;

class UsuarioController extends Usuario implements IApiUsable
{

    public function login($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $usuario = $data['usuario'] ?? '';
        $password = $data['password'] ?? '';

        Usuario::loginUsuario($usuario, $password);
        // Aquí deberías validar el usuario y contraseña en tu sistema
        // Simplemente como ejemplo, vamos a verificar un usuario fijo
        if ($usuario === 'usuario_ejemplo' && $password === 'password_ejemplo') {
            // Generar token JWT
            $tokenPayload = [
                'usuario' => $usuario,
                'perfil' => 'perfil_del_usuario',
            ];

            $config = require __DIR__ . '/../config.php';
            $token = JWT::encode($tokenPayload, $config['secret']);

            return $response->withJson(['token' => $token]);
        } else {
            return $response->withStatus(401)->withJson(['error' => 'Usuario o contraseña incorrectos']);
        }
    }
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        
        $mail = $parametros['mail'];
        $usuario = $parametros['usuario'];
        $contrasena = $parametros['contrasena'];
        $perfilId = $parametros['perfil'];
        $imagen = $uploadedFiles['foto'] ?? null;

        switch($perfilId){
          case 0:
            $perfil = 'admin';
            break;
          case 1:
            $perfil = 'empleado';
            break;
          default:
            $perfil = 'cliente';
            break;
        }

        if ($imagen === null || $imagen->getError() !== UPLOAD_ERR_OK) {
          $payload = json_encode(array("mensaje" => "No se subió ninguna imagen o hubo un error en la carga."));
          $response->getBody()->write($payload);
          return $response->withHeader('Content-Type', 'application/json');
        }

        $directorioImagenes = __DIR__ . '/../ImagenesDeUsuarios/2024/';
        
        if (!file_exists($directorioImagenes)) {
          // Intentar crear el directorio
          if (!mkdir($directorioImagenes, 0777, true)) {
              $payload = json_encode(array("mensaje" => "No se pudo crear el directorio para guardar las imágenes"));
              $response->getBody()->write($payload);
              return $response->withHeader('Content-Type', 'application/json');
          }
        }

        $fecha = date('Ymd');
        $nombreImagen = $usuario . '_' . $perfil . '_' . $fecha. '.' . pathinfo($imagen->getClientFilename(), PATHINFO_EXTENSION);
        
        try {
          $imagen->moveTo($directorioImagenes . $nombreImagen);
        } catch (Exception $e) {
            $payload = json_encode(array("mensaje" => "Error al guardar la imagen del producto"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }

        // Creamos el usuario
        $usr = new Usuario();
        $usr->mail = $mail;
        $usr->usuario = $usuario;
        $usr->contrasena = $contrasena;
        $usr->perfil = $perfil;
        $usr->foto = $nombreImagen;
        $usr->crearUsuario();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por nombre
        $usr = $args['usuario'];
        $usuario = Usuario::obtenerUsuario($usr);
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        Usuario::modificarUsuario($nombre);

        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuarioId = $parametros['usuarioId'];
        Usuario::borrarUsuario($usuarioId);

        $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}

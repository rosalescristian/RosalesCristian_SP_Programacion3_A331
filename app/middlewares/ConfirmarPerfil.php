<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class ConfirmarPerfil
{
    private array $perfiles;

    public function __construct(array $perfiles)
    {
        $this->perfiles = $perfiles;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = new \Slim\Psr7\Response();

        // Obtener el token JWT del header
        $token = $request->getHeaderLine('Authorization');
        if (empty($token)) {
            $response->getBody()->write(json_encode(['error' => 'Token no proporcionado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $token = str_replace('Bearer ', '', $token);

        try {
            // Decodificar el token
            $decoded = JWT::decode($token, new key($_ENV['JWT_SECRET'], 'HS256'));
            /* $decodedArray = (array) $decoded; */

            // Verificar el perfil
            if (!in_array($decoded->perfil, $this->perfiles)) {
                $response->getBody()->write(json_encode(['error' => 'Acceso denegado: perfil incorrecto']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
            }

            // Continuar con la petición
            return $handler->handle($request);
        } catch (ExpiredException $e) {
            $response->getBody()->write(json_encode(['error' => 'Token expirado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } catch (SignatureInvalidException $e) {
            $response->getBody()->write(json_encode(['error' => 'Firma inválida']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Token inválido', 'details' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}

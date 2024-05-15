<?php

namespace App\Controllers;

//use Pojo_Administrador;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Container\ContainerInterface;

require_once __DIR__ . "/../Models/Utils.php";

class AdministradorController
{
    public function __construct(ContainerInterface $ci)
    {
        $this->jwtSecret = $ci->get('JwtSecretsMap')["Administrador"];
    }

    public function login(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        if (!empty($data)) {
            return $this->loginWithPass($request, $response, $args);
        } else if (!empty($request->getHeader('Authorization'))) {
            return $this->loginWithToken($request, $response, $args);
        } else {
            $response->getBody()->write(json_encode(["Aprovado" => false, "User" => null, "Token" => null, "Tipo" => "Administrador"]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function loginWithToken(Request $request, Response $response, $args)
    {
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtSecret);

        if (strtotime("+30 minutes") < $parsedToken["exp"]) { ///Se tiver faltando +de 30 minutos para expirar
            $User = unserialize(base64_decode($parsedToken["User"]));
            if ($User) {
                $jwt = ($helper->getParsedToken($request, $this->jwtSecret))->getJwt();
                return $this->responderLogin($User["email"], $User["senha"], $response, $jwt->getToken());
            }
        } else if (strtotime("now") < $parsedToken["exp"] && isset($parsedToken["User"])) { ///Não expirou ainda
            $User = unserialize(base64_decode($parsedToken["User"]));
            if ($User) {
                return $this->responderLogin($User["email"], $User["senha"], $response);
            }
        }
        $response->getBody()->write(json_encode(["Aprovado" => false, "User" => null, "Token" => null, "Tipo" => "Administrador"]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    private function loginWithPass(Request $request, Response $response, $args)
    {
        $Dao_Administrador = \Dao_Administrador::getInstance();
        $data = $request->getParsedBody();
        $error = array();
        $login_aprovado = true;

        if (!isset($data["email"])) {
            array_push($error, "Deve conter o atributo 'email'");
        } else if (!is_string($data["email"])) {
            array_push($error, "O atributo 'email' deve ser do tipo 'string'");
        }

        if (!isset($data["senha"])) {
            array_push($error, "Deve conter o atributo 'senha'");
        } else if (!is_string($data["senha"])) {
            array_push($error, "O atributo 'senha' deve ser do tipo 'string'");
        }

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            $login_aprovado = false;
        } else if (!$Dao_Administrador->hasWithEmail($data["email"])) {
            $login_aprovado = false;
        } else if (strlen($data["senha"]) < 8) {
            $login_aprovado = false;
        }

        if ($login_aprovado) {
            return $this->responderLogin($data["email"], $data["senha"], $response);
        }
        $response->getBody()->write(json_encode(["Aprovado" => false, "User" => null, "Token" => null]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    private function responderLogin($email, $senha, Response $response, $token = false)
    {
        $Dao_Administrador = \Dao_Administrador::getInstance();

        $result = $Dao_Administrador->Login($email, $senha);

        if (is_string($result)) {
            $response->getBody()->write("Erro interno do servidor");
            //$response->getBody()->write($result);
            return $response->withStatus(500);
        } else if ($result == null || $result == false) {
            $response->getBody()->write(json_encode(["Aprovado" => false, "User" => null, "Token" => null, "Tipo" => "Administrador"]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        if (!$token) {
            $JwtFactory = new \PsrJwt\Factory\Jwt();
            $JwtBuilder = $JwtFactory->builder();

            $JwtToken = $JwtBuilder->setSecret($this->jwtSecret)
                ->setPayloadClaim("User", base64_encode(serialize(["id" => $result->getId(), "email" => $email, "senha" => $senha])))
                ->setExpiration(strtotime('+6 hours'))
                ->build();

            $token = $JwtToken->getToken();
        }

        $user = [
            "id" => $result->getId(),
            "nome" => $result->getNome(),
            "email" => $result->getEmail(),
            "cpf" => $result->getCpf(),
            "telefone" => $result->getTelefone(),
            "tipo" => $result->getTipo()
        ];

        $response->getBody()->write(
            json_encode(
                [
                    "Aprovado" => true,
                    "User" => $user,
                    "Token" => $token,
                    "Tipo" => "Administrador"
                ]
            )
        );
        //$response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /*
    public function put(Request $request, Response $response, $args)
    {
    }

    public function delete(Request $request, Response $response, $args)
    {
    }
    */
}

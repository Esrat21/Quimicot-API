<?php

namespace App\Controllers;

use Pojo_Escola;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Container\ContainerInterface;

require_once __DIR__ . "/../Models/Utils.php";

class EscolaController
{
    public function __construct(ContainerInterface $ci)
    {
        $this->jwtProf = $ci->get('JwtSecretsMap')["Professor"];
        //$this->msg = $ci->get('Oi');
        //$this->msg = "oi";
    }

    public function get(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        $Dao_Escola = \Dao_Escola::getInstance();
        if (isset($query["id"])) {
            $id = $query["id"];
            $res = json_encode($Dao_Escola->FindById($id));
            $response->getBody()->write($res);
        } else {
            $res = json_encode($Dao_Escola->GetAll());
            $response->getBody()->write($res);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function validaParametros($data, $Dao_Escola)
    {
        $error = array();

        if (!isset($data["nome"])) {
            array_push($error, "Deve conter o atributo 'nome'");
        } else {
            $data["nome"] = \Utils::clearXss($data["nome"]);
            if (!is_string($data["nome"])) {
                array_push($error, "O atributo 'nome' deve ser do tipo 'string'");
            } else if ($Dao_Escola->existsNome($data["nome"])) {
                array_push($error, "O 'nome' desta escola já esta cadastrado");
            }
        }

        return $error;
    }

    public function post(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();

        $Dao_Escola = \Dao_Escola::getInstance();

        $error = $this->validaParametros($data, $Dao_Escola);

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $result = $Dao_Escola->Inserir(Pojo_Escola::FromData($data));
        if (is_string($result)) {
            $response->getBody()->write("Erro interno do servidor");
            //$response->getBody()->write($result);
            return $response->withStatus(500);
        }
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function prof_get(Request $request, Response $response, $args)
    {
        $Dao_Professor_Escola = \Dao_Professor_Escola::getInstance();

        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtProf);
        $User = unserialize(base64_decode($parsedToken["User"]));

        $error = array();
        if (!$User) {
            array_push($error, "Token inválido");
        }
        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $res = $Dao_Professor_Escola->getEscolasDoProf($User["id"]);
        if (is_string($res)) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write(json_encode($res));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function put(Request $request, Response $response, $args)
    {
    }

    public function delete(Request $request, Response $response, $args)
    {
    }
}

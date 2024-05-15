<?php

namespace App\Controllers;

use Pojo_ElementosTabelaPeriodica;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Container\ContainerInterface;

require_once __DIR__ . "/../Models/Utils.php";

class ElementosTabelaPeriodicaController
{
    public function __construct(ContainerInterface $ci)
    {
        //$this->msg = $ci->get('Oi');
        //$this->msg = "oi";
    }

    public function get(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        $Dao_ElementosTabelaPeriodica = \Dao_ElementosTabelaPeriodica::getInstance();
        if (isset($query["sigla"])) {
            $sigla = $query["sigla"];
        } else if(isset($args["sigla"])) {
            $sigla = $args["sigla"];
        } else {
            $response->getBody()->write("Informe a 'sigla'");
            return $response->withStatus(400);
        }
        $elemento = $Dao_ElementosTabelaPeriodica->FindBySigla($sigla);
        if($elemento == "{}") {
            return $response->withStatus(404);
        } else {
            $res = json_encode($elemento);
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json');
        }
        /* else {
            $res = json_encode($Dao_ElementosTabelaPeriodica->GetAll());
            $response->getBody()->write($res);
        }*/   
    }

    public function getAll(Request $request, Response $response, $args)
    {
        $Dao_ElementosTabelaPeriodica = \Dao_ElementosTabelaPeriodica::getInstance();
        $elementos = $Dao_ElementosTabelaPeriodica->GetAll();
        if($elementos == "{}") {
            return $response->withStatus(404);
        } else {
            $res = json_encode($elementos);
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function getNames(Request $request, Response $response, $args)
    {
        $Dao_ElementosTabelaPeriodica = \Dao_ElementosTabelaPeriodica::getInstance();
        $elementos = $Dao_ElementosTabelaPeriodica->GetAllNames();
        if($elementos == "{}") {
            return $response->withStatus(404);
        } else {
            $res = json_encode($elementos);
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function getFiltered(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        if(!isset($query["filtro"])) {
            $response->getBody()->write("Informe o tipo de filtragem: '?filtro=classificacao' ou '?filtro=grupo'");
            return $response->withStatus(400);
        }
        $filtro = $query["filtro"];
        $Dao_ElementosTabelaPeriodica = \Dao_ElementosTabelaPeriodica::getInstance();
        if ($filtro == "classificacao") {
            $res = json_encode($Dao_ElementosTabelaPeriodica->getClassificacoes());
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json');
        } else if($filtro == "grupo") {
            $res = json_encode($Dao_ElementosTabelaPeriodica->getGrupos());
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write("Tipo de filtragem inválido, use: '?filtro=classificacao' ou '?filtro=grupo'");
            return $response->withStatus(400);
        }
    }

    public function validaParametros($data)
    {
        $error = array();

        if (!isset($data["sigla"])) {
            array_push($error, "Deve conter o atributo 'sigla'");
        } else {
            $data["sigla"] = \Utils::clearXss($data["sigla"]);
            if (!is_string($data["sigla"])) {
                array_push($error, "O atributo 'sigla' deve ser do tipo 'string'");
            }
        }

        // _TODO_ Validar se é um objeto json
        if (isset($data["objeto"])) {

        }

        return $error;
    }

    public function post(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();

        $Dao_ElementosTabelaPeriodica = \Dao_ElementosTabelaPeriodica::getInstance();

        $error = $this->validaParametros($data);

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $result = $Dao_ElementosTabelaPeriodica->Inserir(Pojo_ElementosTabelaPeriodica::FromData($data));
        if (is_string($result)) {
            $response->getBody()->write("Erro interno do servidor");
            //$response->getBody()->write($result);
            return $response->withStatus(500);
        }
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function put(Request $request, Response $response, $args)
    {
    }

    public function delete(Request $request, Response $response, $args)
    {
    }
}

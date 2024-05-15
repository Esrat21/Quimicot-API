<?php

namespace App\Controllers;

use Pojo_Aluno_Turma;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Container\ContainerInterface;

require_once __DIR__ . "/../Models/Utils.php";

class Aluno_TurmaController
{
    public function __construct(ContainerInterface $ci)
    {
        $this->jwtAluno = $ci->get('JwtSecretsMap')["Aluno"];
        $this->jwtProfessor = $ci->get('JwtSecretsMap')["Professor"];
    }

    public function ADM_get(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        $Dao_Aluno_Turma = \Dao_Aluno_Turma::getInstance();
        if (isset($query["id"])) {
            $id = $query["id"];
            $res = json_encode($Dao_Aluno_Turma->FindById($id));
            $response->getBody()->write($res);
        } else if (isset($query["aluno"])) {
            $aluno = $query["aluno"];
            $res = json_encode($Dao_Aluno_Turma->FindByAluno($aluno));
            $response->getBody()->write($res);
        } else if (isset($query["turma"])) {
            $turma = $query["turma"];
            $res = json_encode($Dao_Aluno_Turma->FindByTurma($turma));
            $response->getBody()->write($res);
        } else {
            $res = json_encode($Dao_Aluno_Turma->GetAll());
            $response->getBody()->write($res);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function get(Request $request, Response $response, $args)
    {
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtAluno);
        $User = unserialize(base64_decode($parsedToken["User"]));

        $error = array();

        $Dao_Aluno = \Dao_Aluno::getInstance();

        if (!isset($User) || !isset($User["id"]) || !is_numeric($User["id"])) {
            array_push($error, "Token inválido, faça login novamente");
        } else {
            $existe = $Dao_Aluno->hasWithId($User["id"]);
            if (boolval($existe)) {
                if ($existe != 1) {
                    array_push($error, "Ocorreu um erro inesperado");
                }
            } else {
                array_push($error, "O 'aluno' informado no token não existe");
            }
        }

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $Dao_Aluno_Turma = \Dao_Aluno_Turma::getInstance();

        $res = json_encode($Dao_Aluno_Turma->GetTurmasByAluno($User["id"]));
        $response->getBody()->write($res);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function validaParametros(&$data, $Dao_Aluno_Turma, $Dao_Aluno, $Dao_Turma, $User)
    {
        $error = array();

        if (isset($data["aluno"])) {
            unset($data["aluno"]);
        }
        $data["aluno"] = $User["id"];

        if (!isset($data["turma"])) {
            array_push($error, "Deve conter o atributo 'turma'");
        } else if (!is_numeric($data["turma"])) {
            array_push($error, "O atributo 'turma' deve ser um id do tipo 'int'");
        } else if (!$Dao_Turma->hasWithId($data["turma"])) {
            array_push($error, "A 'turma' informada não existe");
        }

        if (!isset($data["senha"])) {
            array_push($error, "Deve conter o atributo 'senha'");
        } else if (!is_string($data["senha"])) {
            array_push($error, "O atributo 'senha' deve ser do tipo 'string'");
        } else if (strlen($data["senha"]) < 8) {
            array_push($error, "O atributo 'senha' deve conter ao menos 8 caracteres");
        } else if (!$Dao_Turma->VerificarSenha($data["turma"], $data["senha"])) {
            array_push($error, "A 'senha' de acesso não é válida");
        }

        if ($Dao_Aluno_Turma->hasThisConn($User["id"], $data["turma"])) {
            array_push($error, "Este aluno já esta na 'turma'");
        }

        // _TODO_ Validar se é um JSON
        if (isset($data["dados_aluno"])) {

        }
        if (isset($data["dados_turma"])) {

        }

        return $error;
    }

    public function post(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();

        $Dao_Aluno_Turma = \Dao_Aluno_Turma::getInstance();
        $Dao_Aluno = \Dao_Aluno::getInstance();
        $Dao_Turma = \Dao_Turma::getInstance();

        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtAluno);
        $User = unserialize(base64_decode($parsedToken["User"]));

        $error = $this->validaParametros($data, $Dao_Aluno_Turma, $Dao_Aluno, $Dao_Turma, $User);

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $result = $Dao_Aluno_Turma->Inserir(Pojo_Aluno_Turma::FromData($data));
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

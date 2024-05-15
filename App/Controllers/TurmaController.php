<?php

namespace App\Controllers;

use Pojo_Turma;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Container\ContainerInterface;

require_once __DIR__ . "/../Models/Utils.php";

class TurmaController
{
    public function __construct(ContainerInterface $ci)
    {
        $this->jwtProf = $ci->get('JwtSecretsMap')["Professor"];
        //$this->msg = $ci->get('Oi');
        //$this->msg = "oi";
    }

    public function ADM_get(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        $Dao_Turma = \Dao_Turma::getInstance();
        if (isset($query["id"])) {
            $id = $query["id"];
            $res = json_encode($Dao_Turma->FindById($id));
            $response->getBody()->write($res);
        } else if (isset($query["professor"])) {
            $professor = $query["professor"];
            $res = json_encode($Dao_Turma->GetAllByProfessor($professor));
            $response->getBody()->write($res);
        } else if (isset($query["escola"])) {
            $escola = $query["escola"];
            $res = json_encode($Dao_Turma->GetAllByEscola($escola));
            $response->getBody()->write($res);
        } else {
            $res = json_encode($Dao_Turma->GetAll());
            $response->getBody()->write($res);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getAlunosDaTurma(Request $request, Response $response, $args)
    {
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtProf);
        $User = unserialize(base64_decode($parsedToken["User"]));
        
        $error = array();

        $Dao_Professor = \Dao_Professor::getInstance();
        $Dao_Escola = \Dao_Escola::getInstance();
        $Dao_Aluno_Turma = \Dao_Aluno_Turma::getInstance();
        $Dao_Turma = \Dao_Turma::getInstance();

        if (!isset($User) || !isset($User["id"]) || !is_numeric($User["id"])) {
            
            array_push($error, "Token inválido, faça login novamente");
        } else {
            $existe = $Dao_Professor->hasWithId($User["id"]);
            if ($existe) {
                $res = json_decode(json_encode($Dao_Turma->GetAllByProfessor($User["id"])), true);

                $query = $request->getQueryParams();

                if(isset($query["turma"])) {
                    $newRes = false;
                    $inArr = false;
                    foreach ($res as &$turma) {
                        if($query["turma"] == $turma["id"]) {
                            $newRes = $Dao_Aluno_Turma->GetAlunosByTurma($turma["id"]);
                            $inArr = true;
                        }
                    }
                    if($inArr) {
                        $response->getBody()->write(json_encode($newRes));
                    } else {
                        array_push($error, "A 'turma' informada não pertence ao professor");
                    }
                    
                } else {
                    foreach ($res as &$turma) {
                        $turma["escola"] = $Dao_Escola->FindById($turma["escola"]);
                        $turma["Alunos"] = $Dao_Aluno_Turma->GetAlunosByTurma($turma["id"]);
                        unset($turma["professor"]);
                    }
                    $response->getBody()->write(json_encode($res));
                }
            } else {
                array_push($error, "O 'professor' informado no token não existe");
            }
        }

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function get(Request $request, Response $response, $args)
    {
        $Dao_Turma = \Dao_Turma::getInstance();
        $Dao_Professor = \Dao_Professor::getInstance();
        $Dao_Escola = \Dao_Escola::getInstance();

        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtProf);
        $User = unserialize(base64_decode($parsedToken["User"]));
        if ($User) {
            $existe = $Dao_Professor->hasWithId($User["id"]);
            if ($existe) {
                $res = json_decode(json_encode($Dao_Turma->GetAllByProfessor($User["id"])), true);
                foreach ($res as &$turma) {
                    $turma["escola"] = $Dao_Escola->FindById($turma["escola"]);
                    unset($turma["professor"]);
                }
                $response->getBody()->write(json_encode($res));
            } else {
                $response->getBody()->write("Professor não encontrado");
                $newResponse = $response->withStatus(400);
                return $newResponse;
            }
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function validaParametros(&$data, \Dao_Professor $Dao_Professor, \Dao_Escola $Dao_Escola, \Dao_Professor_Escola $Dao_Professor_Escola, $parsedToken)
    {
        $error = array();

        if (!isset($data["nome"])) {
            array_push($error, "Deve conter o atributo 'nome'");
        } else {
            $data["nome"] = \Utils::clearXss($data["nome"]);
            if (!is_string($data["nome"])) {
                array_push($error, "O atributo 'nome' deve ser do tipo 'string'");
            }
        }

        if (isset($parsedToken["User"])) {
            $User = unserialize(base64_decode($parsedToken["User"]));
            if ($User) {
                $prof = $Dao_Professor->Login($User["email"], $User["senha"]);
                if (!$prof) {
                    array_push($error, "Login de professor recusado");
                } else {
                    if (isset($data["professor"])) {
                        unset($data["professor"]);
                    }
                    $data["professor"] = $prof->getId();
                }
            }
        } else {
            array_push($error, "Token de professor recusado");
        }

        if (!isset($data["escola"])) {
            array_push($error, "Deve conter o atributo 'escola'");
        } else if (!is_numeric($data["escola"])) {
            array_push($error, "O atributo 'escola' deve ser um id do tipo 'int'");
        } else if (!($Dao_Escola->hasWithId($data["escola"]))) {
            array_push($error, "A 'escola' informada não existe");
        } else if(!($Dao_Professor_Escola->isProfOfEscola($data["professor"], $data["escola"]))) {
            array_push($error, "O 'professor' não é vinculado a esta 'escola'");
        }

        if (!isset($data["senha"])) {
            array_push($error, "Deve conter o atributo 'senha'");
        } else if (!is_string($data["senha"])) {
            array_push($error, "O atributo 'senha' deve ser do tipo 'string'");
        } else if (strlen($data["senha"]) < 8) {
            array_push($error, "O atributo 'senha' deve conter ao menos 8 caracteres");
        }

        if (!isset($data["ano"])) {
            array_push($error, "Deve conter o atributo 'ano'");
        } else if (!(\Utils::validateDate($data["ano"], 'Y'))) {
            array_push($error, "O atributo 'ano' deve ser um ano");
        }

        return $error;
    }

    public function post(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();

        $Dao_Turma = \Dao_Turma::getInstance();
        $Dao_Professor = \Dao_Professor::getInstance();
        $Dao_Escola = \Dao_Escola::getInstance();
        $Dao_Professor_Escola = \Dao_Professor_Escola::getInstance();

        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtProf);

        $error = $this->validaParametros($data, $Dao_Professor, $Dao_Escola, $Dao_Professor_Escola, $parsedToken);

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $result = $Dao_Turma->Inserir(Pojo_Turma::FromData($data));
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

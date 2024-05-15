<?php

namespace App\Controllers;

use Pojo_Log;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Container\ContainerInterface;

require_once __DIR__ . "/../Models/Utils.php";

class LogController
{
    public function __construct(ContainerInterface $ci)
    {
        $this->jwtAluno = $ci->get('JwtSecretsMap')["Aluno"];
        //$this->msg = $ci->get('Oi');
        //$this->msg = "oi";
    }

    public function ADM_getCsv(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        $Dao_Log = \Dao_Log::getInstance();
        if (isset($query["id"])) {
            $id = $query["id"];
            $res = json_encode($Dao_Log->FindById($id));
            $response->getBody()->write($res);
        } else {
            $res = $Dao_Log->GetAll();
            $csv = ["id,aluno,turma_fase,detalhes,Coins,LeuPlaca,mortes,tipo"];
            $response->getBody()->write("id,aluno,turma_fase,detalhes,Coins,LeuPlaca,mortes,tipo");
            foreach ($res as $index => $log) {
                try {
                    $obj = json_decode($log->getObjeto(), 1);
                    $line = "\n" . $log->getID()
                        . "," . $log->getAluno()
                        . "," . $log->getTurma_fase()
                        . "," . $log->getDetalhes()
                        . "," . $obj["LeuPlaca"]
                        . "," . count($obj["Coins"])
                        . "," . $obj["qtd_mortes"]
                        . "," . $log->getTipo();
                    array_push($csv, $line);
                    $response->getBody()->write($line);
                } catch (\Exception $e) {
                    error_log(print_r("LogController.php -> ADM_getCsv", 1));
                    error_log(print_r("Ocorreu um Erro decodificar o log:<br><br>" . $log->getObjeto() . "<br><br>" . $e->getMessage(), 1));
                }
            }
            //$response->getBody()->write(print_r($csv,1));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function get(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        $Dao_Log = \Dao_Log::getInstance();
        if (isset($query["id"])) {
            $id = $query["id"];
            $res = json_encode($Dao_Log->FindById($id));
            $response->getBody()->write($res);
        } else {
            $res = json_encode($Dao_Log->GetAll());
            $response->getBody()->write($res);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function validaParametros(&$data, $User, $Dao_Aluno, $Dao_Turma_Fase)
    {
        //error_log(print_r($data, TRUE));
        $error = array();

        if (!isset($User) || !isset($User["id"]) || !is_numeric($User["id"])) {
            array_push($error, "Token inválido, faça login novamente");
        } else {
            $existe = $Dao_Aluno->hasWithId($User["id"]);
            if (boolval($existe)) {
                if ($existe != 1) {
                    array_push($error, "Ocorreu um erro inesperado");
                } else {
                    $data["aluno"] = $User["id"];
                }
            } else {
                array_push($error, "O 'aluno' informado no token não existe");
            }
        }

        if (!isset($data["turma_fase"])) {
            array_push($error, "Deve conter o atributo 'turma_fase'");
        } else if (!is_numeric($data["turma_fase"])) {
            array_push($error, "O atributo 'turma_fase' deve ser um id do tipo 'int'");
        } else {
            $turma_fase = $Dao_Turma_Fase->hasWithId($data["turma_fase"]);
            if (!$turma_fase) {
                array_push($error, "A ligação 'turma_fase' indicada não foi encontrada");
            }
        }

        if (!isset($data["detalhes"])) {
            array_push($error, "Deve conter o atributo 'detalhes'");
        } else {
            $data["detalhes"] = \Utils::clearXss($data["detalhes"]);
            if (!is_string($data["detalhes"])) {
                array_push($error, "O atributo 'detalhes' deve ser do tipo 'string'");
            }
        }

        if (!isset($data["tipo"])) {
            array_push($error, "Deve conter o atributo 'tipo'");
        } else {
            $data["tipo"] = \Utils::clearXss($data["tipo"]);
            if (!is_string($data["tipo"])) {
                array_push($error, "O atributo 'tipo' deve ser uma 'string'");
            }
        }

        if (!isset($data["comeco"])) {
            array_push($error, "Deve conter o atributo 'comeco'");
        } else if (!is_string($data["comeco"])) {
            array_push($error, "O atributo 'comeco' deve ser uma 'string' no formato 'Y-m-d H:i:s', como '2020-08-12 10:24:52'");
        } else if (!\Utils::validaDateTime($data["comeco"])) {
            array_push($error, "O atributo 'comeco' não é uma data válida");
        }

        if (isset($data["fim"])) {
            if (!is_string($data["fim"])) {
                array_push($error, "O atributo 'fim' deve ser uma 'string' no formato 'Y-m-d H:i:s', como '2020-08-12 10:24:52'");
            } else if (!\Utils::validaDateTime($data["fim"])) {
                array_push($error, "O atributo 'fim' não é uma data válida");
            } else if (strtotime($data["comeco"]) >= strtotime($data["fim"])) {
                array_push($error, "O atributo 'fim' deve ser maior que o atributo 'comeco'");
            }
        }

        if (isset($data["objeto"]) || !(\Utils::isJson($data["objeto"]))) {
            if (!is_string($data["objeto"])) {
                array_push($error, "O atributo 'objeto' não é um json válido");
            }
        }

        return $error;
    }

    public function post(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();

        $Dao_Log = \Dao_Log::getInstance();
        $Dao_Aluno = \Dao_Aluno::getInstance();
        $Dao_Turma_Fase = \Dao_Turma_Fase::getInstance();

        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtAluno);
        $User = unserialize(base64_decode($parsedToken["User"]));

        $error = $this->validaParametros($data, $User, $Dao_Aluno, $Dao_Turma_Fase);

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $result = $Dao_Log->Inserir(Pojo_Log::FromData($data));
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

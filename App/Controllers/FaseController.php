<?php

namespace App\Controllers;

use Pojo_Fase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Container\ContainerInterface;

require_once __DIR__ . "/../Models/Utils.php";

class FaseController
{
    private $jwtAluno;
    public function __construct(ContainerInterface $ci)
    {
        $this->jwtAluno = $ci->get('JwtSecretsMap')["Aluno"];
        //$this->msg = $ci->get('Oi');
        //$this->msg = "oi";
    }

    public function get(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        $Dao_Fase = \Dao_Fase::getInstance();
        if (isset($query["id"])) {
            $id = $query["id"];
            $res = json_encode($Dao_Fase->FindById($id));
            $response->getBody()->write($res);
        } else {
            $res = json_encode($Dao_Fase->GetAll());
            $response->getBody()->write($res);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function Aluno_get(Request $request, Response $response, $args)
    {
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtAluno);
        $User = unserialize(base64_decode($parsedToken["User"]));

        $error = array();

        if (!isset($User) || !isset($User["id"]) || !is_numeric($User["id"])) {
            array_push($error, "Token inválido, faça login novamente");
        } else {
            $Dao_Aluno = \Dao_Aluno::getInstance();
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
        $Dao_Turma_Fase = \Dao_Turma_Fase::getInstance();
        $Dao_Fase = \Dao_Fase::getInstance();
        $Dao_Quiz = \Dao_Quiz::getInstance();

        $turmas = $Dao_Aluno_Turma->FindTurmasByAluno($User["id"]);
        if (\is_string($turmas)) {
            throw new \Exception($turmas);
        }

        $fases = array();
        foreach ($turmas as $id_turma) {
            $fasesDaTurma = $Dao_Turma_Fase->FindFasesByTurma($id_turma);
            if (is_array($fasesDaTurma)) {
                array_push($fases, ...$fasesDaTurma);
            }
        }
        $fases = array_unique($fases);

        $query = $request->getQueryParams();

        if (isset($query["idTurma"])) {
            ///Pegar pela turma
            if (in_array($query["idTurma"], $turmas)) {
                $elemento = null;
                if (isset($query["elemento"])) {
                    $elemento = $query["elemento"];
                }
                $fasesDaTurma = $Dao_Turma_Fase->GetFasesByTurma($query["idTurma"], $elemento);
                if (is_string($fasesDaTurma)) {
                    $response->getBody()->write(json_encode(["Errors" => ["Erro interno do Servidor", "SQL error"]]));
                    return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
                }
                foreach ($fasesDaTurma as &$fase) {
                    $quizes = $Dao_Quiz->QuizesQuantidadeAndRespondidosByTfAluno($User["id"], $fase["Turma_Fase_id"]);
                    if (!isset($fase["Fase_contem"])) {
                        $fase["Fase_contem"] = [];
                    } else {
                        $fase["Fase_contem"] = get_object_vars($fase["Fase_contem"]);
                    }
                    $fase["Fase_contem"]["quizes"] = $quizes;
                }
                $res = json_encode($fasesDaTurma);
                $response->getBody()->write($res);
            } else {
                $response->getBody()->write(json_encode(["Errors" => ["O 'idTurma' informado não pode ser acessado por este aluno"]]));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
        } else if (isset($query["idFase"])) {
            ///Pega pelo ID da Fase
            if (in_array($query["idFase"], $fases)) {
                $res = json_encode($Dao_Fase->FindById($query["idFase"]));
                $response->getBody()->write($res);
            } else {
                $response->getBody()->write(json_encode(["Errors" => ["O 'idFase' informado não pode ser acessado por este aluno"]]));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
        } else {
            ///Retorna todas fases do aluno
            $res = json_encode($fases);
            $response->getBody()->write($res);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function quiz(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        //$Dao_Fase = \Dao_Fase::getInstance();
        //Verificar se existe a fase
        $Dao_Quiz = \Dao_Quiz::getInstance();
        $Dao_Alternativa = \Dao_Alternativa::getInstance();

        if ($args["id"] <= 0) {
            $response->getBody()->write(json_encode(["Errors" => ["o 'id' de 'turma_fase' informado é inválido"]]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $quizes = $Dao_Quiz->QuizesDoTurma_Fase($args["id"]);

        if (\is_string($quizes)) {
            throw new \Exception($quizes);
        }

        $ct = count($quizes);
        if ($ct <= 0) {
            $response->getBody()->write(json_encode(["Errors" => ["Este 'turma_fase' não contém nenhum 'quiz'"]]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $sorteado = rand(0, $ct - 1);
        $quiz_sorteado = $quizes[$sorteado];

        $alternativas = $Dao_Alternativa->FindAllByQuiz($quiz_sorteado->getId());
        $alternativasAsArray = json_decode(json_encode($alternativas), 1);
        foreach ($alternativasAsArray as &$alt) {
            unset($alt["justificativa"]);
            unset($alt["alt_correta"]);
            unset($alt["quiz"]);
        }
        shuffle($alternativasAsArray);
        $resposta = [
            "quiz_id" => $quiz_sorteado->getId(),
            "pergunta" => $quiz_sorteado->getPergunta(),
            "alternativas" => $alternativasAsArray,
        ];
        $res = json_encode($resposta);
        $response->getBody()->write($res);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function validaParametros(&$data)
    {
        $error = array();

        if (!isset($data["nome"])) {
            array_push($error, "Deve conter o atributo 'nome'");
        } else {
            $data["nome"] = \Utils::clearXss($data["nome"]);
            if (!is_string($data["nome"])) {
                array_push($error, "O atributo 'nome' deve ser do tipo 'string'");
            } else if (\Utils::isBlankOrEmpty($data["nome"])) {
                array_push($error, "O atributo 'nome' está vazio");
            }
        }

        if (!isset($data["url"])) {
            array_push($error, "Deve conter o atributo 'url'");
        } else if (!is_string($data["url"])) {
            array_push($error, "O atributo 'url' deve ser do tipo 'string'");
        } else if (!filter_var($data["url"], FILTER_VALIDATE_URL)) {
            array_push($error, "O atributo 'url' não é uma URL válida");
        }

        if (!isset($data["criador"])) {
            array_push($error, "Deve conter o atributo 'criador'");
        } else {
            $data["criador"] = \Utils::clearXss($data["criador"]);
            if (!is_string($data["criador"])) {
                array_push($error, "O atributo 'criador' deve ser do tipo 'string'");
            } else if (\Utils::isBlankOrEmpty($data["nome"])) {
                array_push($error, "O atributo 'nome' está vazio");
            }
        }

        if (!isset($data["dificuldade"])) {
            array_push($error, "Deve conter o atributo 'dificuldade'");
        } else if (strlen($data["dificuldade"]) != 1) {
            array_push($error, "O atributo 'dificuldade' deve ser um Char. 'F' - Fácil, 'M' - Médio, 'D' - Difícil.");
        } else if ($data["dificuldade"] != 'F' && $data["dificuldade"] != 'M' && $data["dificuldade"] != 'D') {
            array_push($error, "A 'dificuldade' é inválida, use 'F' - Fácil, 'M' - Médio, 'D' - Difícil.");
        }

        if (isset($data["tempo_medio_seg"])) {
            if (!is_numeric($data["tempo_medio_seg"])) {
                array_push($error, "O atributo 'tempo_medio_seg' deve ser do tipo 'int'");
            } else {
                $data["tempo_medio_seg"] += 0;
                if (!is_int($data["tempo_medio_seg"])) {
                    array_push($error, "O atributo 'tempo_medio_seg' deve ser do tipo 'int'");
                } else if ($data["tempo_medio_seg"] <= 0) {
                    array_push($error, "O atributo 'tempo_medio_seg' deve ser > 0");
                }
            }
        }

        if (isset($data["contem"])) {
            if (is_array($data["contem"])) {
                $contem = [];
                if (isset($data["contem"]["elementos"]) && is_array($data["contem"]["elementos"])) {
                    $contem = [
                        "elementos" => $data["contem"]["elementos"]
                    ];
                }
                /*if (isset($data["contem"]["dificuldade"]) && is_string($data["contem"]["dificuldade"])) {
                    $valid = in_array($data["contem"]["dificuldade"], [
                        "F",  //Fácil
                        "M", //Médio
                        "D" //Difícil
                    ]);
                    if ($valid) {
                        array_push($contem, $data["contem"]["dificuldade"]);
                    }
                }*/
                $data["contem"] = json_encode($contem);
            }
        }

        if (isset($data["vars"])) {
            if (is_array($data["vars"])) {
                $data["vars"] = json_encode($data["vars"]);
            }
        }

        return $error;
    }

    public function post(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();

        $Dao_Fase = \Dao_Fase::getInstance();

        $error = $this->validaParametros($data);

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $result = $Dao_Fase->Inserir(Pojo_Fase::FromData($data));
        if (is_string($result)) {
            //$response->getBody()->write("Erro interno do servidor");
            $response->getBody()->write($result);
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

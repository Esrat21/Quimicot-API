<?php

namespace App\Controllers;

use Pojo_Quiz;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Container\ContainerInterface;

require_once __DIR__ . "/../Models/Utils.php";

class QuizController
{
    public function __construct(ContainerInterface $ci)
    {
        $this->jwtSecret["Professor"] = $ci->get('JwtSecretsMap')["Professor"];
    }

    public function ADM_get(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        $Dao_Quiz = \Dao_Quiz::getInstance();
        if (isset($query["id"])) {
            $id = $query["id"];
            $res = json_encode($Dao_Quiz->FindById($id));
            $response->getBody()->write($res);
        } else {
            $res = json_encode($Dao_Quiz->GetAll());
            $response->getBody()->write($res);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function validaParametros(&$data, $Dao_Turma_Fase, $update = false)
    {
        $error = array();

        if (!isset($data["pergunta"])) {
            array_push($error, "Deve conter o atributo 'pergunta'");
        } else {
            $data["pergunta"] = \Utils::clearXss($data["pergunta"]);
            if (!is_string($data["pergunta"])) {
                array_push($error, "O atributo 'pergunta' deve ser do tipo 'string'");
            }
        }

        if ($update) {
            return $error;
        }

        if (!isset($data["turma_fase"])) {
            array_push($error, "Deve conter o atributo 'turma_fase'");
        } else if (!is_numeric($data["turma_fase"])) {
            array_push($error, "O atributo 'turma_fase' deve ser um id do tipo 'int'");
        } else if (!$Dao_Turma_Fase->hasWithId($data["turma_fase"])) {
            array_push($error, "A ligação 'turma_fase' indicada não existe");
        }

        if (!isset($data["alternativas"])) {
            array_push($error, "Deve conter o um 'array' de 'alternativas'");
        } else if (!is_array($data["alternativas"])) {
            array_push($error, "O atributo 'alternativas' deve ser um 'array'");
        } else {
            foreach ($data["alternativas"] as $key => &$value) {
                if(!is_array($value)) {
                    if(is_string($value)) {
                        $descricao = $value;
                        $value = array();
                        $value["descricao"] = $descricao;
                    } else {
                        array_push($error, "O atributo 'alternativas' deve ser um 'array' de 'string' contendo as descrições, ou um 'array' de objetos do tipo { \"descricao\": \"...\", \"justificativa\": \"...\" }");
                        break;
                    }
                }
                $value["descricao"] = \Utils::clearXss($value["descricao"]);
                if (!is_string($value["descricao"])) {
                    array_push($error, "A descricao $key deve ser uma 'string'");
                } else if (\Utils::isBlankOrEmpty($value["descricao"])) {
                    array_push($error, "A descricao $key tem um texto vazio!");
                }
                if (isset($value["justificativa"])) {
                    $value["justificativa"] = \Utils::clearXss($value["justificativa"]);
                    if (!is_string($value["justificativa"])) {
                        array_push($error, "A 'justificativa' da alternativa $key deve ser uma 'string'");
                    } else if (\Utils::isBlankOrEmpty($value["justificativa"])) {
                        array_push($error, "A alternativa $key tem uma 'justificativa' vazia!");
                    }
                }
            }
        }

        ///Talvez mudar isso
        if (!isset($data["alt_correta"])) {
            array_push($error, "Deve conter o atributo 'alt_correta'");
        } else if (!is_numeric($data["alt_correta"])) {
            array_push($error, "O atributo 'alt_correta' deve ser um indice do tipo 'int'");
        } else if (!array_key_exists($data["alt_correta"], $data["alternativas"])) {
            array_push($error, "A 'alt_correta' deve existir em 'alternativas'");
        }

        return $error;
    }

    public function post(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();

        $Dao_Quiz = \Dao_Quiz::getInstance();
        $Dao_Alternativa = \Dao_Alternativa::getInstance();
        $Dao_Turma_Fase = \Dao_Turma_Fase::getInstance();

        $error = $this->validaParametros($data, $Dao_Turma_Fase);

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $resultQuiz = $Dao_Quiz->Inserir(Pojo_Quiz::FromData($data));
        if (is_string($resultQuiz)) {
            $response->getBody()->write("Erro interno do servidor");
            //$response->getBody()->write($resultQuiz);
            return $response->withStatus(500);
        }

        foreach ($data["alternativas"] as $key => &$value) {
            if (isset($value["id"])) {
                unset($value["id"]);
            }

            if ($key == $data["alt_correta"]) {
                $value["alt_correta"] = true;
            } else {
                $value["alt_correta"] = false;
            }

            $alternativa = \Pojo_Alternativa::FromData($value);
            $alternativa->setQuiz($resultQuiz["ID"]);

            $result = $Dao_Alternativa->Inserir($alternativa);
            if (is_string($result)) {
                $response->getBody()->write("Erro interno do servidor");
                //$response->getBody()->write($result);
                return $response->withStatus(500);
            }
        }

        $response->getBody()->write(json_encode($resultQuiz));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function put(Request $request, Response $response, $args)
    {
        $Dao_Quiz = \Dao_Quiz::getInstance();
        $Dao_Professor = \Dao_Professor::getInstance();

        $quiz_id = $args["id"];

        if (!$Dao_Quiz->hasWithId($quiz_id)) {
            $response->getBody()->write(json_encode(["Errors" => ["O 'id' do quiz informado não foi encontrado"]]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        //Resgata os dados do Token
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtSecret["Professor"]);
        $User = unserialize(base64_decode($parsedToken["User"]));

        $error = [];

        //Confere os dados necessários do Token
        if (!isset($User) || !isset($User["id"]) || !is_numeric($User["id"])) {
            array_push($error, "Token inválido, faça login novamente");
        }

        //Verifica se o Professor informado no Token existe
        $existe = $Dao_Professor->hasWithId($User["id"]);
        if (!$existe) {
            array_push($error, "(Token inválido!) Professor não encontrado");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        //Verifica se o professor é o dono do quiz
        $quiz_id = intval($args["id"]);
        $pertence = $Dao_Quiz->QuizPertenceAoProf($quiz_id, intval($User["id"]));
        if (!$pertence) {
            array_push($error, "O 'quiz' de 'id' = " . $quiz_id . " não pertence ao professor");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        //Atualiza a pergunta
        $data = $request->getParsedBody();
        $error = $this->validaParametros($data, null, true);
        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $quiz_old = $Dao_Quiz->FindById($quiz_id);

        $quiz_atualizado = Pojo_Quiz::FromData(
            [
                "id" => $quiz_old->getId(),
                "turma_fase" => $quiz_old->getTurma_Fase(),
                "pergunta" => $data["pergunta"]
            ]
        );

        $resultQuiz = $Dao_Quiz->Editar($quiz_atualizado);
        if (is_string($resultQuiz)) {
            $response->getBody()->write("Erro interno do servidor");
            //$response->getBody()->write($result);
            return $response->withStatus(500);
        }

        $response->getBody()->write(json_encode(["Success" => $resultQuiz]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, $args)
    {
        $Dao_Quiz = \Dao_Quiz::getInstance();
        $Dao_Professor = \Dao_Professor::getInstance();

        //Resgata os dados do Token
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtSecret["Professor"]);
        $User = unserialize(base64_decode($parsedToken["User"]));

        $error = [];

        //Confere os dados necessários do Token
        if (!isset($User) || !isset($User["id"]) || !is_numeric($User["id"])) {
            array_push($error, "Token inválido, faça login novamente");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        //Verifica se o Professor informado no Token existe
        $existe = $Dao_Professor->hasWithId($User["id"]);
        if (!$existe) {
            array_push($error, "(Token inválido!) Professor não encontrado");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        //Verifica se o professor é o dono do quiz
        $quiz_id = intval($args["id"]);
        $pertence = $Dao_Quiz->QuizPertenceAoProf($quiz_id, intval($User["id"]));
        if (!$pertence) {
            array_push($error, "O 'quiz' de 'id' = " . $quiz_id . " não pertence ao professor");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $result = $Dao_Quiz->Deletar($quiz_id);
        if (is_string($result)) {
            $response->getBody()->write("Erro interno do servidor");
            //$response->getBody()->write($result);
            return $response->withStatus(500);
        } else {
            $response->getBody()->write(json_encode(["Success" => true]));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function updateAlternativa(Request $request, Response $response, $args)
    {
        $Dao_Quiz = \Dao_Quiz::getInstance();
        $Dao_Alternativa = \Dao_Alternativa::getInstance();
        $Dao_Professor = \Dao_Professor::getInstance();

        //Resgata os dados do Token
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtSecret["Professor"]);
        $User = unserialize(base64_decode($parsedToken["User"]));

        $error = [];

        //Confere os dados necessários do Token
        if (!isset($User) || !isset($User["id"]) || !is_numeric($User["id"])) {
            array_push($error, "Token inválido, faça login novamente");
        }

        //Verifica se o Professor informado no Token existe
        $existe = $Dao_Professor->hasWithId($User["id"]);
        if (!$existe) {
            array_push($error, "(Token inválido!) Professor não encontrado");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        //Verifica se o professor é o dono do quiz
        $quiz_id = intval($args["id"]);
        $pertence = $Dao_Quiz->QuizPertenceAoProf($quiz_id, intval($User["id"]));
        if (!$pertence) {
            array_push($error, "O 'quiz' de 'id' = " . $quiz_id . " não pertence ao professor");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $alt_id = intval($args["idAlt"]);
        $alt_old = $Dao_Alternativa->FindById($alt_id);
        if ($alt_old->getQuiz() != $quiz_id) {
            $response->getBody()->write("A alternativa informada não pertence ao quiz");
            $newResponse = $response->withStatus(400);
            return $newResponse;
        }

        $data = $request->getParsedBody();

        if(!isset($data["descricao"])) {
            if(isset($data[0])) {
                $descricao = $data[0];
                $data = array();
                $data["descricao"] = $descricao;
            } else {
                array_push($error, "A descricao deve ser uma 'string'");
            }
        }

        $data["descricao"] = \Utils::clearXss($data["descricao"]);
        if (!is_string($data["descricao"])) {
            array_push($error, "A descricao deve ser uma 'string'");
        } else if (\Utils::isBlankOrEmpty($data["descricao"])) {
            array_push($error, "A descricao tem um texto vazio!");
        }
        if (isset($data["justificativa"])) {
            $data["justificativa"] = \Utils::clearXss($data["justificativa"]);
            if (!is_string($data["justificativa"])) {
                array_push($error, "A 'justificativa' da alternativa deve ser uma 'string'");
            } else if (\Utils::isBlankOrEmpty($data["justificativa"])) {
                array_push($error, "A alternativa tem uma 'justificativa' vazia!");
            }
        } else {
            $data["justificativa"] = $alt_old->getJustificativa();
        }

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $data["id"] = $alt_old->getId();
        $data["quiz"] = $alt_old->getQuiz();
        $data["alt_correta"] = $alt_old->isAlt_correta();

        $nova_alternativa = \Pojo_Alternativa::FromData($data);

        $result = $Dao_Alternativa->Editar($nova_alternativa);
        if (is_string($result)) {
            $response->getBody()->write("Erro interno do servidor");
            //$response->getBody()->write($result);
            return $response->withStatus(500);
        }

        $response->getBody()->write(json_encode(["Success" => $result]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}

<?php

namespace App\Controllers;

use Pojo_Turma_Fase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Container\ContainerInterface;

require_once __DIR__ . "/../Models/Utils.php";

class Turma_FaseController
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
        $Dao_Turma_Fase = \Dao_Turma_Fase::getInstance();
        $Dao_Fase = \Dao_Fase::getInstance();

        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtProf);
        $User = unserialize(base64_decode($parsedToken["User"]));

        $error = array();
        if (!$User) {
            array_push($error, "Token inválido");
        }
        if (!isset($args["id"])) {
            array_push($error, "'id' de turma não informado");
        }

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $turma = $args["id"];

        if (!$this->turmaPertenceAoProf($User["id"], $turma)) {
            array_push($error, "O 'id' de turma não pertence a este professor");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $res = $Dao_Turma_Fase->FindByTurma($turma);

        if (isset($query["vinculadas"]) && $query["vinculadas"] == "false") {
            $fases = $Dao_Fase->GetAll();
            $naoVinculadas = array_filter($fases, function ($val) use ($res) {
                foreach ($res as $ligacao) {
                    if ($ligacao->getFase() == $val->getId()) {
                        return false;
                    }
                }
                return true;
            });

            $response->getBody()->write(json_encode(array_values($naoVinculadas)));
        } else {
            $respostaEstruturada = array();

            foreach ($res as $ligacao) {
                $fase = $Dao_Fase->FindById($ligacao->getFase());
                $fase->setVars(json_decode($fase->getVars()));
                array_push($respostaEstruturada, ["ID" => $ligacao->getID(), "Fase" => $fase]);
            }

            $response->getBody()->write(json_encode($respostaEstruturada));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    private function turmaPertenceAoProf($prof, $turma)
    {
        $Dao_Turma = \Dao_Turma::getInstance();
        $turmasDoProf = $Dao_Turma->GetAllByProfessor($prof);
        foreach ($turmasDoProf as $objTurma) {
            if ($objTurma->getId() == $turma) {
                return true;
            }
        }
        return false;
    }

    public function get_prof_fases_e_suas_turmas(Request $request, Response $response, $args)
    {
        $Dao_Turma_Fase = \Dao_Turma_Fase::getInstance();
        $Dao_Fase = \Dao_Fase::getInstance();
        $Dao_Quiz = \Dao_Quiz::getInstance();

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

        $prof = $User["id"];

        $fases = $Dao_Fase->GetAll();
        $tfs = $Dao_Turma_Fase->getTurmas_Fases_Detalhados_Por_Prof($prof);
        
        $res = array();
        foreach($fases as $fase) {
            $res_turmas = array();
            foreach($tfs as $tf) {
                if($tf["Turma_Fase_fase"] == $fase->getID()) {
                    $res_turma = [
                        "TurmaFase_ID" => $tf["Turma_Fase_id"],
                        "qtdQuiz" => $Dao_Quiz->GetQtdQuizesPorTF($tf["Turma_Fase_id"]),
                        "Turma" => [
                            "id" => $tf["Turma_id"],
                            "nome" => $tf["Turma_nome"],
                            "ano" => $tf["Turma_ano"],
                            "escola" => $tf["Turma_escola"]
                        ]
                    ];
                    array_push($res_turmas, $res_turma);
                }
            }
            $res_fase = [
                "Fase" => $fase,
                "Turmas" => $res_turmas
            ];
            array_push($res, $res_fase);
        }

        $response->getBody()->write(json_encode($res));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ADM_get(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        $Dao_Turma_Fase = \Dao_Turma_Fase::getInstance();
        error_log(print_r($args, true));
        if (isset($query["id"])) {
            $id = $query["id"];
            $res = json_encode($Dao_Turma_Fase->FindById($id));
            $response->getBody()->write($res);
        } else if (isset($query["turma"])) {
            $turma = $query["turma"];
            $res = json_encode($Dao_Turma_Fase->FindByTurma($turma));
            $response->getBody()->write($res);
        } else if (isset($query["fase"])) {
            $fase = $query["fase"];
            $res = json_encode($Dao_Turma_Fase->FindByFase($fase));
            $response->getBody()->write($res);
        } else {
            $res = json_encode($Dao_Turma_Fase->GetAll());
            $response->getBody()->write($res);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function validaParametros($data, $Dao_Turma_Fase, $Dao_Turma, $Dao_Fase, $User)
    {
        $error = array();

        if (!isset($data["turma"])) {
            array_push($error, "Deve conter o atributo 'turma'");
        } else if (!is_numeric($data["turma"])) {
            array_push($error, "O atributo 'turma' deve ser um id do tipo 'int'");
        } else if (!$Dao_Turma->hasWithId($data["turma"])) {
            array_push($error, "A 'turma' informada não existe");
        }

        if (!isset($data["fase"])) {
            array_push($error, "Deve conter o atributo 'fase'");
        } else if (!is_numeric(intval($data["fase"]))) {
            array_push($error, "O atributo 'fase' deve ser um id do tipo 'int'");
        } else if (!$Dao_Fase->hasWithId($data["fase"])) {
            array_push($error, "A 'fase' informada não existe");
        }

        if ($Dao_Turma_Fase->hasThisConn($data["turma"], $data["fase"])) {
            array_push($error, "Esta 'turma' já está ligada à esta 'fase'");
        }

        if (!$this->turmaPertenceAoProf($User["id"], $data["turma"])) {
            array_push($error, "A 'turma' não pertence a este professor");;
        }

        return $error;
    }

    public function post(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();

        $Dao_Turma_Fase = \Dao_Turma_Fase::getInstance();
        $Dao_Turma = \Dao_Turma::getInstance();
        $Dao_Fase = \Dao_Fase::getInstance();


        $data["turma"] = (int) $args["id"];

        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtProf);
        $User = unserialize(base64_decode($parsedToken["User"]));

        $error = $this->validaParametros($data, $Dao_Turma_Fase, $Dao_Turma, $Dao_Fase, $User);

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $result = $Dao_Turma_Fase->Inserir(Pojo_Turma_Fase::FromData($data));
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

    public function DELETE_validaParametros($data, $Dao_Turma_Fase, $Dao_Turma, $Dao_Fase, $User)
    {
        $error = array();

        if (!isset($data["turma"])) {
            array_push($error, "Deve conter o atributo 'turma'");
        } else if (!is_numeric($data["turma"])) {
            array_push($error, "O atributo 'turma' deve ser um id do tipo 'int'");
        } else if (!$Dao_Turma->hasWithId($data["turma"])) {
            array_push($error, "A 'turma' informada não existe");
        }

        if (!isset($data["fase"])) {
            array_push($error, "Deve conter o atributo 'fase'");
        } else if (!is_numeric(intval($data["fase"]))) {
            array_push($error, "O atributo 'fase' deve ser um id do tipo 'int'");
        } else if (!$Dao_Fase->hasWithId($data["fase"])) {
            array_push($error, "A 'fase' informada não existe");
        }

        if (!$Dao_Turma_Fase->hasThisConn($data["turma"], $data["fase"])) {
            array_push($error, "Esta 'turma' não está ligada à esta 'fase'");
        }

        if (!$this->turmaPertenceAoProf($User["id"], $data["turma"])) {
            array_push($error, "A 'turma' não pertence a este professor");;
        }

        return $error;
    }

    public function delete(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();

        $Dao_Turma_Fase = \Dao_Turma_Fase::getInstance();
        $Dao_Turma = \Dao_Turma::getInstance();
        $Dao_Fase = \Dao_Fase::getInstance();

        $data["turma"] = (int) $args["id"];

        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtProf);
        $User = unserialize(base64_decode($parsedToken["User"]));

        $error = $this->DELETE_validaParametros($data, $Dao_Turma_Fase, $Dao_Turma, $Dao_Fase, $User);

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $obj_TF = $Dao_Turma_Fase->getThisConn($data["turma"], $data["fase"]);
        $result = $Dao_Turma_Fase->Deletar($obj_TF->getId());
        if (is_string($result)) {
            $response->getBody()->write("Erro interno do servidor");
            //$response->getBody()->write($result);
            return $response->withStatus(500);
        } else {
            $response->getBody()->write(json_encode(["Success" => true]));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getQuizes(Request $request, Response $response, $args)
    {
        $Dao_Turma_Fase = \Dao_Turma_Fase::getInstance();
        $Dao_Quiz = \Dao_Quiz::getInstance();
        $Dao_Alternativa = \Dao_Alternativa::getInstance();

        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtProf);
        $User = unserialize(base64_decode($parsedToken["User"]));

        $error = array();
        if (!$User) {
            array_push($error, "Token inválido");
        }
        if (!isset($args["id"])) {
            array_push($error, "'id' de turma não informado");
        }

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $turma = $args["id"];
        $fase = $args["idFase"];

        if (!$this->turmaPertenceAoProf($User["id"], $turma)) {
            array_push($error, "O 'id' de turma não pertence a este professor");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        if (!$Dao_Turma_Fase->hasThisConn($turma, $fase)) {
            array_push($error, "A turma informada não está vinculada a fase informada");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $turma_fase = $Dao_Turma_Fase->getThisConn($turma, $fase);

        $quizes = $Dao_Quiz->QuizesDoTurma_Fase($turma_fase->getId());

        $res = [];
        foreach ($quizes as $quiz) {
            array_push($res, [
                "quiz_id" => $quiz->getId(),
                "pergunta" => $quiz->getPergunta(),
                "alternativas" => $Dao_Alternativa->FindAllByQuiz($quiz->getId()) //Pega cada alternativa
            ]);
        }

        $response->getBody()->write(json_encode($res));
        return $response->withHeader('Content-Type', 'application/json');
    }
}

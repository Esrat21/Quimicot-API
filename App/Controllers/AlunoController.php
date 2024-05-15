<?php

namespace App\Controllers;

use Pojo_Aluno;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Container\ContainerInterface;

require_once __DIR__ . "/../Models/Utils.php";

class AlunoController
{
    private $jwtSecret;

    public function __construct(ContainerInterface $ci)
    {
        $this->jwtSecret = $ci->get('JwtSecretsMap')["Aluno"];
    }

    public function ADM_get(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        $Dao_Aluno = \Dao_Aluno::getInstance();
        if (isset($query["id"])) {
            $id = $query["id"];
            $res = json_encode($Dao_Aluno->FindById($id));
            $response->getBody()->write($res);
        } else {
            $res = json_encode($Dao_Aluno->GetAll());
            $response->getBody()->write($res);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function get(Request $request, Response $response, $args)
    {
        $Dao_Aluno = \Dao_Aluno::getInstance();
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtSecret);
        $User = unserialize(base64_decode($parsedToken["User"]));
        if ($User) {
            $existe = $Dao_Aluno->hasWithId($User["id"]);
            if ($existe) {
                $resAluno = json_decode(json_encode($Dao_Aluno->FindById($User["id"])), true);
                unset($resAluno["senha"]);

                ///Verifica se o aluno tem permissão de acessar a fase por aquela turma
                $query = $request->getQueryParams();
                if (isset($query["turmaFase"])) {
                    //Verifica se o turmaFase existe
                    $Dao_Turma_Fase = \Dao_Turma_Fase::getInstance();
                    $tf = $Dao_Turma_Fase->FindById($query["turmaFase"]);
                    if ($tf == "{}" || is_string($tf)) {
                        $response->getBody()->write("id 'turmaFase' não encontrado");
                        $newResponse = $response->withStatus(404);
                        return $newResponse;
                    }
                    //Verifica se o aluno esta cadastrado na turma
                    $Dao_Aluno_Turma = \Dao_Aluno_Turma::getInstance();
                    $aluno_turma = $Dao_Aluno_Turma->getThisConn($User["id"], $tf->getTurma());
                    if ($aluno_turma == "{}" || is_string($aluno_turma)) {
                        $response->getBody()->write("O 'aluno' não tem permissão de jogar a 'fase' por esta 'turma' (turmaFase inválido)");
                        $newResponse = $response->withStatus(403);
                        return $newResponse;
                    }
                    //Objeto de resposta
                    $res = [
                        "Aluno" => $resAluno,
                        "idFase" => $tf->getFase(),
                        "idTurma" => $tf->getTurma(),
                        "Dados" => [
                            "Aluno" => $aluno_turma->getDados_aluno(),
                            "Turma" => $aluno_turma->getDados_turma(),
                        ],
                    ];
                } else {
                    $res = $resAluno;
                }

                $response->getBody()->write(json_encode($res));
            } else {
                $response->getBody()->write("Aluno não encontrado, pegue um novo Token");
                $newResponse = $response->withStatus(404);
                return $newResponse;
            }
        } else {
            $response->getBody()->write("Token inválido");
            $newResponse = $response->withStatus(403);
            return $newResponse;
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function validaParametros(&$data, \Dao_Aluno $Dao_Aluno)
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
        if (!isset($data["email"])) {
            array_push($error, "Deve conter o atributo 'email'");
        } else if (!is_string($data["email"])) {
            array_push($error, "O atributo 'email' deve ser do tipo 'string'");
        } else if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            array_push($error, "O 'email' informado é inválido");
        } else if ($Dao_Aluno->hasWithEmail($data["email"])) {
            array_push($error, "O 'email' informado já foi utilizado por outra conta");
        }
        if (!isset($data["senha"])) {
            array_push($error, "Deve conter o atributo 'senha'");
        } else if (!is_string($data["senha"])) {
            array_push($error, "O atributo 'senha' deve ser do tipo 'string'");
        } else if (strlen($data["senha"]) < 8) {
            array_push($error, "O atributo 'senha' deve conter ao menos 8 caracteres");
        }

        return $error;
    }

    public function post(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();

        $Dao_Aluno = \Dao_Aluno::getInstance();

        $error = $this->validaParametros($data, $Dao_Aluno);

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $data["senha"] = password_hash($data["senha"], PASSWORD_DEFAULT);

        $result = $Dao_Aluno->Inserir(Pojo_Aluno::FromData($data));
        if (is_string($result)) {
            $response->getBody()->write("Erro interno do servidor");
            //$response->getBody()->write($result);
            return $response->withStatus(500);
        }
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function login(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        if (!empty($data)) {
            return $this->loginWithPass($request, $response, $args);
        } else {
            return $this->loginWithToken($request, $response, $args);
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
        $response->getBody()->write(json_encode(["Aprovado" => false, "User" => null, "Token" => null, "Tipo" => "Aluno"]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    private function loginWithPass(Request $request, Response $response, $args)
    {
        $Dao_Aluno = \Dao_Aluno::getInstance();
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
        } else if (!$Dao_Aluno->hasWithEmail($data["email"])) {
            $login_aprovado = false;
        } else if (strlen($data["senha"]) < 8) {
            $login_aprovado = false;
        }

        if ($login_aprovado) {
            return $this->responderLogin($data["email"], $data["senha"], $response);
        }
        $response->getBody()->write(json_encode(["Aprovado" => false, "User" => null, "Token" => null, "Tipo" => "Aluno"]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    private function responderLogin($email, $senha, Response $response, $token = false)
    {
        $Dao_Aluno = \Dao_Aluno::getInstance();

        $result = $Dao_Aluno->Login($email, $senha);
        //error_log(print_r($result, true));
        if (is_string($result)) {
            $response->getBody()->write("Erro interno do servidor");
            //$response->getBody()->write($result);
            return $response->withStatus(500);
        } else if ($result == null || $result == false) {
            $response->getBody()->write(json_encode(["Aprovado" => false, "User" => null, "Token" => null, "Tipo" => "Aluno"]));
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
        ];

        $response->getBody()->write(
            json_encode(
                [
                    "Aprovado" => true,
                    "User" => $user,
                    "Token" => $token,
                    "Tipo" => "Aluno",
                ]
            )
        );
        //$response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getQtdJogadasByAluno(Request $request, Response $response, $args)
    {
        $Dao_Aluno = \Dao_Aluno::getInstance();
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtSecret);
        $User = unserialize(base64_decode($parsedToken["User"]));
        if (!$User || !$Dao_Aluno->hasWithId($User["id"])) {
            $response->getBody()->write("Token inválido");
            $newResponse = $response->withStatus(403);
            return $newResponse;
        }
        $errors = [];
        $query = $request->getQueryParams();
        if (!isset($query["turma"])) {
            array_push($errors, "Variável 'turma' não está definida");
        }
        if (!empty($errors)) {
            $response->getBody()->write(json_encode(["Errors" => $errors]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $res = $Dao_Aluno->GetQtdJogadasByAluno($User["id"], $query["turma"]);
        if(is_string($res)) {
            $response->getBody()->write(json_encode(["Errors" => ["Erro interno do Servidor", "SQL error"]]));
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

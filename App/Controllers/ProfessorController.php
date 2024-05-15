<?php

namespace App\Controllers;

use Pojo_Professor;
use Pojo_Professor_Escola;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Container\ContainerInterface;

require_once __DIR__ . "/../Models/Utils.php";

class ProfessorController
{
    public function __construct(ContainerInterface $ci)
    {
        $this->jwtSecret = $ci->get('JwtSecretsMap')["Professor"];
        //$this->msg = $ci->get('Oi');
        //$this->msg = "oi";
    }

    public function alterarPendencia(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $Dao_Professor = \Dao_Professor::getInstance();
        if (isset($args['id'])) {
            if (isset($data["metodo"])) {
                $id = $args['id'];
                $metodo = $data["metodo"];
                $existe = $Dao_Professor->hasWithId($id);
                if ($existe) {
                    $pojo = $Dao_Professor->FindById($id);

                    if ($metodo == "aceitar") {
                        $pojo->setCad_pendente(0);
                    } else if ($metodo == "revogar") {
                        $pojo->setCad_pendente(1);
                    }
                    $response->getBody()->write(json_encode(["Alterado" => $Dao_Professor->Editar($pojo)]));
                    return $response->withHeader('Content-Type', 'application/json');
                } else {
                    $response->getBody()->write("id não encontrado");
                    $newResponse = $response->withStatus(400);
                }
            } else {
                $response->getBody()->write("Esperado o parâmetro 'metodo' de valor [aceitar | revogar]");
                $newResponse = $response->withStatus(400);
            }
        } else {
            $response->getBody()->write("Esperado um id");
            $newResponse = $response->withStatus(400);
        }
        return $newResponse;
    }

    public function ADM_get(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        $Dao_Professor = \Dao_Professor::getInstance();
        if (isset($query["id"])) {
            $id = $query["id"];
            $existe = $Dao_Professor->hasWithId($query["id"]);
            if (boolval($existe)) {
                $res = json_encode($Dao_Professor->FindById($id));
                $response->getBody()->write($res);
            } else {
                $response->getBody()->write("id não encontrado");
                $newResponse = $response->withStatus(400);
                return $newResponse;
            }
        } else {
            $res = json_encode($Dao_Professor->GetAll());
            $response->getBody()->write($res);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function get(Request $request, Response $response, $args)
    {
        $Dao_Professor = \Dao_Professor::getInstance();
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtSecret);
        $User = unserialize(base64_decode($parsedToken["User"]));
        if ($User) {
            $existe = $Dao_Professor->hasWithId($User["id"]);
            if (boolval($existe)) {
                $res = json_decode(json_encode($Dao_Professor->FindById($User["id"])), true);
                unset($res["senha"]);
                $response->getBody()->write(json_encode($res));
            } else {
                $response->getBody()->write("Professor não encontrado");
                $newResponse = $response->withStatus(400);
                return $newResponse;
            }
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function validaParametros(&$data, \Dao_Professor $Dao_Professor, \Dao_Escola $Dao_Escola)
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
        } else if ($Dao_Professor->hasWithEmail($data["email"])) {
            array_push($error, "O 'email' informado já foi utilizado por outra conta");
        }
        if (!isset($data["cpf"])) {
            array_push($error, "Deve conter o atributo 'cpf'");
        } else if (!is_string($data["cpf"])) {
            array_push($error, "O atributo 'cpf' deve ser do tipo 'string'");
        } else if (!(\Utils::validaCPF($data["cpf"]))) {
            array_push($error, "O atributo 'cpf' não é válido");
        } else if ($Dao_Professor->hasWithCpf($data["cpf"])) {
            array_push($error, "O 'cpf' informado já foi utilizado por outra conta");
        }

        if (!isset($data["senha"])) {
            array_push($error, "Deve conter o atributo 'senha'");
        } else if (!is_string($data["senha"])) {
            array_push($error, "O atributo 'senha' deve ser do tipo 'string'");
        } else if (strlen($data["senha"]) < 8) {
            array_push($error, "O atributo 'senha' deve conter ao menos 8 caracteres");
        }

        if (isset($data["telefone"])) {
            if (!is_string($data["telefone"])) {
                array_push($error, "O atributo 'telefone' deve ser do tipo 'string'");
            } else if (strlen($data["telefone"]) > 15 || !preg_match("/\(?\d{2}\)?\s?\d{5}\-?\d{4}/", $data["telefone"])) {
                array_push($error, "O atributo 'telefone' não é válido");
            }
            //error_log(print_r($data["telefone"], TRUE));
            $data["telefone"] = preg_replace("/[^0-9]/", "", $data["telefone"]);
        }

        if (isset($data["escolas"])) {
            if (!is_array($data["escolas"])) {
                array_push($error, "O atributo 'escolas' deve ser um 'array' de 'id'");
            } else {
                foreach ($data["escolas"] as $value) {
                    if (!is_numeric($value) || $value <= 0) {
                        array_push($error, "O 'array' de 'escolas' deve conter apenas inteiros >= 1");
                        break;
                    } else {
                        if (!$Dao_Escola->hasWithId($value)) {
                            array_push($error, "Não foi encontrado uma escola com o 'id': '" . $value . "'");
                            break;
                        }
                    }
                }
            }
        }

        return $error;
    }

    public function post(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();

        $Dao_Professor = \Dao_Professor::getInstance();
        $Dao_Escola = \Dao_Escola::getInstance();
        $Dao_Professor_Escola = \Dao_Professor_Escola::getInstance();

        $error = $this->validaParametros($data, $Dao_Professor, $Dao_Escola);

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $data["senha"] = password_hash($data["senha"], PASSWORD_DEFAULT);

        $result = $Dao_Professor->Inserir(Pojo_Professor::FromData($data));
        if (is_string($result)) {
            $response->getBody()->write("Erro interno do servidor");
            //$response->getBody()->write($result);
            return $response->withStatus(500);
        }

        //Vinculando as escolas
        if (isset($data["escolas"])) {
            foreach ($data["escolas"] as $value) {
                $Pojo_Professor_Escola = new Pojo_Professor_Escola();
                $Pojo_Professor_Escola->setEscola($value);
                ///__TODO__ Conferir isso aqui
                //error_log(print_r($result, true));
                $Pojo_Professor_Escola->setProfessor($result["ID"]);

                $Dao_Professor_Escola->Inserir($Pojo_Professor_Escola);
            }
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function login(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        if (!empty($data)) {
            return $this->loginWithPass($request, $response, $args);
        } else if (!empty($request->getHeader('Authorization'))) {
            return $this->loginWithToken($request, $response, $args);
        } else {
            $response->getBody()->write(json_encode(["Aprovado" => false, "User" => null, "Token" => null, "Tipo" => "Professor"]));
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
        $response->getBody()->write(json_encode(["Aprovado" => false, "User" => null, "Token" => null, "Tipo" => "Professor"]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    private function loginWithPass(Request $request, Response $response, $args)
    {
        $Dao_Professor = \Dao_Professor::getInstance();
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
        } else if (!$Dao_Professor->hasWithEmail($data["email"])) {
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
        $Dao_Professor = \Dao_Professor::getInstance();

        $result = $Dao_Professor->Login($email, $senha);

        //error_log(print_r($result, true));
        if (is_string($result)) {
            $response->getBody()->write("Erro interno do servidor");
            //$response->getBody()->write($result);
            return $response->withStatus(500);
        } else if ($result == null || $result == false) {
            $response->getBody()->write(json_encode(["Aprovado" => false, "User" => null, "Token" => null, "Tipo" => "Professor"]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        } else if ($result->getCad_pendente() == 1) {
            $response->getBody()->write(json_encode(["Aprovado" => false, "User" => null, "Token" => null, "Tipo" => "Cadastro Pendente (Professor)"]));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
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
        ];

        $response->getBody()->write(
            json_encode(
                [
                    "Aprovado" => true,
                    "User" => $user,
                    "Token" => $token,
                    "Tipo" => "Professor",
                ]
            )
        );
        //$response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function put(Request $request, Response $response, $args)
    {
    }

    public function delete(Request $request, Response $response, $args)
    {
    }

    public function getQuizes(Request $request, Response $response, $args)
    {
        $Dao_Quiz = \Dao_Quiz::getInstance();
        $Dao_Alternativa = \Dao_Alternativa::getInstance();

        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtSecret);
        $User = unserialize(base64_decode($parsedToken["User"]));

        $error = array();
        if (!$User) {
            array_push($error, "Token inválido");
        }

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $quizes = $Dao_Quiz->GetQuizesPorProf($User["id"]);
        if(is_string($quizes)) {
            $response->getBody()->write(json_encode(["Errors" => ["Erro interno do Servidor", "SQL error"]]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
        $res = [];
        foreach ($quizes as $quiz) {
            array_push($res, [
                "quiz_id" => $quiz->getId(),
                "pergunta" => $quiz->getPergunta(),
                "contem" => $quiz->getContem(),
                "alternativas" => $Dao_Alternativa->FindAllByQuiz($quiz->getId()) //Pega cada alternativa
            ]);
        }

        $response->getBody()->write(json_encode($res));
        return $response->withHeader('Content-Type', 'application/json');
    }
}

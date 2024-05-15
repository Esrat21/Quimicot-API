<?php

namespace App\Controllers;

use Pojo_Resposta;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Container\ContainerInterface;

require_once __DIR__ . "/../Models/Utils.php";

class RespostaController
{
    private $jwtSecret = [];
    public function __construct(ContainerInterface $ci)
    {
        $this->jwtSecret["Aluno"] = $ci->get('JwtSecretsMap')["Aluno"];
        $this->jwtSecret["Professor"] = $ci->get('JwtSecretsMap')["Professor"];
    }

    public function get(Request $request, Response $response, $args)
    {
        $query = $request->getQueryParams();
        $Dao_Resposta = \Dao_Resposta::getInstance();
        if (isset($query["id"])) {
            $id = $query["id"];
            $res = json_encode($Dao_Resposta->FindById($id));
            $response->getBody()->write($res);
        } else {
            $res = json_encode($Dao_Resposta->GetAll());
            $response->getBody()->write($res);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function validaParametros($data)
    {
        $error = array();

        if (!isset($data["escolha"])) {
            array_push($error, "Deve conter o atributo 'escolha'");
        } else if (!is_numeric($data["escolha"])) {
            array_push($error, "O atributo 'escolha' deve ser um id do tipo 'int'");
        }

        if (!isset($data["data_hora"])) {
            array_push($error, "Deve conter o atributo 'data_hora'");
        } else if (!is_string($data["data_hora"])) {
            array_push($error, "O atributo 'data_hora' deve ser uma 'string' no formato 'Y-m-d H:i:s', como '2020-08-12 10:24:52'");
        } else if (!\Utils::validaDateTime($data["data_hora"])) {
            array_push($error, "O atributo 'data_hora' não é uma data válida");
        }

        if (!isset($data["quiz"])) {
            array_push($error, "Deve conter o atributo 'quiz'");
        } else if (!is_numeric($data["quiz"])) {
            array_push($error, "O atributo 'quiz' deve ser um id do tipo 'int'");
        }
        ///Verificar se o quiz é existe

        //Meio desnecessário, mas pode ficar aqui
        if (!isset($data["aluno"])) {
            array_push($error, "Token: Deve conter o atributo 'id'");
        } else if (!is_numeric($data["aluno"])) {
            array_push($error, "Token: O atributo 'id' deve ser do tipo 'int'");
        }

        return $error;
    }

    public function post(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();

        $Dao_Resposta = \Dao_Resposta::getInstance();

        $Dao_Aluno = \Dao_Aluno::getInstance();
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtSecret["Aluno"]);
        $User = unserialize(base64_decode($parsedToken["User"]));
        if (!$User || !$Dao_Aluno->hasWithId($User["id"])) {
            $response->getBody()->write("Token inválido");
            $newResponse = $response->withStatus(403);
            return $newResponse;
        }
        $data["aluno"] = $User["id"];

        $error = $this->validaParametros($data);

        if (count($error) > 0) {
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $pojo_Resposta = Pojo_Resposta::FromData($data);
        $Dao_Alternativa = \Dao_Alternativa::getInstance();
        $alternativas = $Dao_Alternativa->FindAllByQuiz($pojo_Resposta->getQuiz());
        $respostaCerta = $this->corrigir($alternativas, $pojo_Resposta);

        $result = $Dao_Resposta->Inserir($pojo_Resposta);

        if (is_string($result)) {
            $response->getBody()->write("Erro interno do servidor");
            //$response->getBody()->write($result);
            return $response->withStatus(500);
        }
        //$response->getBody()->write(json_encode($result));
        $response->getBody()->write(
            json_encode(
                [
                    "Correto" => $pojo_Resposta->isCerto(),
                    "idDaResposta" => $result["ID"],
                    "alternativasJustificadas" => $alternativas,
                ]
            )
        );
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function corrigir($alternativas, Pojo_Resposta $Pojo_Resposta)
    {
        $certo = 0;
        $correta = -1;
        foreach ($alternativas as $alt) {
            if ($alt->isAlt_correta()) {
                $correta = $alt->getId();
                if ($correta == $Pojo_Resposta->getEscolha()) {
                    $certo = 1; ///Esta certo
                }
            }
        }

        $Pojo_Resposta->setCerto($certo);
        //Retorna a alt certa
        return $correta;
    }

    /* __TODO__ */
    public function estatisticasNivelTurma(Request $request, Response $response, $args)
    {
        /*
          Feito para uso do professor;
          Providencia as métricas sobre:
            Quantidade de tentativas
            Quantidade de acertos
            Quantidade de erros
            "Aproveitamento" médio da turma
          __TODO__ Além de providenciar os dados acima para cada Turma_Fase.
          __TODO__ E também para cada Aluno.
        */

        $error = [];

        //Precisa passar o id da Turma
        if (!isset($args["id"])) {
            $response->getBody()->write("Necessário passar o 'id' do 'Turma' na URL (../turma/:id)");
            $newResponse = $response->withStatus(400);
            return $newResponse;
        }

        //Pega as instancias das DAO's
        $Dao_Turma = \Dao_Turma::getInstance();
        $Dao_Professor = \Dao_Professor::getInstance();
        $Dao_Turma_Fase = \Dao_Turma_Fase::getInstance();
        //$Dao_Quiz = \Dao_Quiz::getInstance();
        $Dao_Fase = \Dao_Fase::getInstance();

        //Resgata os dados do Token
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtSecret["Professor"]);
        $User = unserialize(base64_decode($parsedToken["User"]));

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

        //Verifica se o professor é o dono da Turma informada
        $id_turma = intval($args["id"]);
        $pertence = $Dao_Turma->TurmaPertenceAoProf($id_turma, intval($User["id"]));
        if (!$pertence) {
            array_push($error, "A 'Turma' de 'id' = " . $id_turma . " não pertence ao professor");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        //Pega todos os Turma_Fase da Turma
        $arr_turma_fase = $Dao_Turma_Fase->FindByTurma($id_turma);

        //Declara as Metricas
        $acertosTotais = 0;
        $errosTotais = 0;
        $tentativasTotais = 0;
        $aproveitamentoTotal = 0;

        //Gera a estatistica de cada quiz
        $estatisticas_arr_turma_fase = [];

        $len_arr_turma_fase = count($arr_turma_fase);
        for ($i = 0; $i < $len_arr_turma_fase; $i++) {
            $turma_fase = $arr_turma_fase[$i];
            $estatistica = $this->calculaEstatisticasTurmaFase($turma_fase->getId());
            //$estatistica["idFase"] = $turma_fase->getFase();
            $estatistica["Fase"] = $Dao_Fase->FindById($turma_fase->getFase());

            $acertosTotais += $estatistica["Metricas"]["Acertos"];
            $errosTotais += $estatistica["Metricas"]["Erros"];
            $tentativasTotais += $estatistica["Metricas"]["Tentativas"];
            $aproveitamentoTotal += $estatistica["Aproveitamento"];

            array_push($estatisticas_arr_turma_fase, $estatistica);
        }

        //Declara as variáveis de média
        $fases_media_acertos = 0;
        $fases_media_erros = 0;
        $fases_media_tentativas = 0;
        $fases_media_aproveitamento = 0;

        //Calcula as médias
        if ($len_arr_turma_fase > 0) {
            $fases_media_acertos = round($acertosTotais / $len_arr_turma_fase);
            $fases_media_erros = round($errosTotais / $len_arr_turma_fase);
            $fases_media_tentativas = round($tentativasTotais / $len_arr_turma_fase);
            $fases_media_aproveitamento = round($aproveitamentoTotal / $len_arr_turma_fase, 2);
        }

        //Cria o objeto de resposta
        $resultados = [
            "idTurma" => $id_turma,
            "Turma_Fases" => $estatisticas_arr_turma_fase,
            "Metricas" => [
                "Acertos" => $acertosTotais,
                "Erros" => $errosTotais,
                "Tentativas" => $tentativasTotais
            ],
            "Medias" => [
                "Acertos" => $fases_media_acertos,
                "Erros" => $fases_media_erros,
                "Tentativas" => $fases_media_tentativas
            ],
            "AproveitamentoNasFases" => $fases_media_aproveitamento // Em %
        ];

        //Converte para JSON
        $response->getBody()->write(
            json_encode($resultados)
        );

        //Responde a requisição com cabeçalho para JSON
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function estatisticasNivelAlunoChamadaAluno(Request $request, Response $response, $args)
    {
        /*
          Feito para uso do Aluno;
          Providencia as métricas sobre um individuo sobre:
            Quantidade de tentativas
            Quantidade de acertos
            Quantidade de erros
            "Aproveitamento" médio do aluno
          Contém o parametro ?turma=<id> que busca somente em uma turma,
          senão calcula e retorna para todas turmas do Aluno.
        */

        $error = [];

        //Pega as instancias das DAO's
        $Dao_Aluno_Turma = \Dao_Aluno_Turma::getInstance();
        $Dao_Aluno = \Dao_Aluno::getInstance();
        $Dao_Resposta = \Dao_Resposta::getInstance();

        //Resgata os dados do Token
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtSecret["Aluno"]);
        $User = unserialize(base64_decode($parsedToken["User"]));

        //Confere os dados necessários do Token
        if (!isset($User) || !isset($User["id"]) || !is_numeric($User["id"])) {
            array_push($error, "Token inválido, faça login novamente");
        }

        $id_aluno = $User["id"];
        //Verifica se o Aluno informado no Token existe
        $existe = $Dao_Aluno->hasWithId($id_aluno);
        if (!$existe) {
            array_push($error, "(Token inválido!) Aluno não encontrado");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        //Pega as turmas do aluno
        $ids_turmasDoAluno = $Dao_Aluno_Turma->FindTurmasByAluno($id_aluno);

        //Resgata os paramentros da query
        $query = $request->getQueryParams();

        //Se tiver especificado uma turma na query
        if (isset($query["turma"])) {
            //Verifica se o aluno esta na turma especificada na query
            if (!in_array($query["turma"], $ids_turmasDoAluno, false)) {
                array_push($error, "O 'aluno' de 'id' = " . $id_aluno . " não faz parte da 'turma' de 'id' = " . $query["turma"]);
                $response->getBody()->write(json_encode(["Errors" => $error]));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
        }

        //Pega os registros do aluno nas turmas em que o aluno esta matriculado
        $registros = $Dao_Resposta->GetRegistrosSobreRespostasDoAluno($id_aluno, null);

        $lv_Turma = $this->calculaEstatisticasAluno($ids_turmasDoAluno, $registros, $query["turma"]);

        //Converte para JSON
        $response->getBody()->write(
            json_encode($lv_Turma)
        );

        //Responde a requisição com cabeçalho para JSON
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function estatisticasNivelAluno(Request $request, Response $response, $args)
    {
        /*
          Feito para uso do professor;
          Providencia as métricas sobre um individuo sobre:
            Quantidade de tentativas
            Quantidade de acertos
            Quantidade de erros
            "Aproveitamento" médio do aluno
          Contém o parametro ?turma=<id> que busca somente em uma turma,
          senão calcula e retorna para todas turmas do professor.
        */

        $error = [];

        //Precisa passar o id do aluno
        if (!isset($args["id"])) {
            $response->getBody()->write("Necessário passar o 'id' do do aluno na URL (../aluno/:id)");
            $newResponse = $response->withStatus(400);
            return $newResponse;
        }

        $id_aluno = $args["id"];

        //Pega as instancias das DAO's
        $Dao_Turma = \Dao_Turma::getInstance();
        $Dao_Aluno_Turma = \Dao_Aluno_Turma::getInstance();
        $Dao_Professor = \Dao_Professor::getInstance();
        $Dao_Resposta = \Dao_Resposta::getInstance();

        //Resgata os dados do Token
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtSecret["Professor"]);
        $User = unserialize(base64_decode($parsedToken["User"]));

        //Confere os dados necessários do Token
        if (!isset($User) || !isset($User["id"]) || !is_numeric($User["id"])) {
            array_push($error, "Token inválido, faça login novamente");
        }

        $id_professor = $User["id"];

        //Verifica se o Professor informado no Token existe
        $existe = $Dao_Professor->hasWithId($id_professor);
        if (!$existe) {
            array_push($error, "(Token inválido!) Professor não encontrado");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        //Pega as turmas do professor e as turmas do aluno
        $turmasDoProfessor = $Dao_Turma->GetAllByProfessor($id_professor);
        $ids_turmasDoAluno = $Dao_Aluno_Turma->FindTurmasByAluno($id_aluno);

        //Resgata os paramentros da query
        $query = $request->getQueryParams();

        //Se tiver especificado uma turma na query
        if (isset($query["turma"])) {
            //Verifica se a Turma especificada na query pertence ao professor

            $pertence = false;
            foreach ($turmasDoProfessor as $turmaP) {
                if ($turmaP->getId() == $query["turma"]) {
                    $pertence = true;
                    break;
                }
            }
            if (!$pertence) {
                array_push($error, "A 'turma' de 'id' = " . $query["turma"] . " não pertence ao professor");
                $response->getBody()->write(json_encode(["Errors" => $error]));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
            //Verifica se o aluno esta na turma especificada na query
            if (!in_array($query["turma"], $ids_turmasDoAluno, false)) {
                array_push($error, "O 'aluno' de 'id' = " . $id_aluno . " não faz parte da 'turma' de 'id' = " . $query["turma"]);
                $response->getBody()->write(json_encode(["Errors" => $error]));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
        }

        //Verifica se o aluno informado esta em alguma turma do professor
        $turmasEmComum = [];
        foreach ($turmasDoProfessor as $turma) {
            if (in_array($turma->getId(), $ids_turmasDoAluno, false)) {
                array_push($turmasEmComum, $turma);
            }
        }
        if (empty($turmasEmComum)) {
            array_push($error, "O 'aluno' de 'id' = " . $id_aluno . " não é aluno do professor");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        //Pega os registros do aluno nas turmas do professor em que o aluno esta matriculado
        $registros = $Dao_Resposta->GetRegistrosSobreRespostasDoAluno($id_aluno, $id_professor);

        $lv_Turma = $this->calculaEstatisticasAluno($turmasEmComum, $registros, $query["turma"]);

        //Converte para JSON
        $response->getBody()->write(
            json_encode($lv_Turma)
        );

        //Responde a requisição com cabeçalho para JSON
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function calculaEstatisticasAluno(array $turmas, array $registros, int $filtro_turma = null)
    {
        $Dao_Fase = \Dao_Fase::getInstance();
        $Dao_Turma_Fase = \Dao_Turma_Fase::getInstance();
        $Dao_Quiz = \Dao_Quiz::getInstance();

        //Itera as turmas analizando os resultados
        $lv_Turma = [];
        foreach ($turmas as $turma) {

            $turma_id = $turma;
            if (is_object($turma)) {
                $turma_id = $turma->getId();
            }

            //Se for especificado uma turma, calcula somente se for a especificada
            if (isset($filtro_turma) && $filtro_turma != $turma_id) {
                continue;
            }

            //Pega quais fases tem vinculadas na turma
            $turmas_fases = $Dao_Turma_Fase->FindByTurma($turma_id);

            //Itera as fases analizando os resultados
            $lv_Fase = [];
            foreach ($turmas_fases as $turma_fase) {
                //Extrai a fase do turma_fase
                $fase = $Dao_Fase->FindById($turma_fase->getFase());

                //Pega os Quizes ligados aquela fase nessa turma
                $quizes = $Dao_Quiz->QuizesDoTurma_Fase($turma_fase->getId());

                //Itera as quizes analizando os resultados
                $lv_Quiz = [];
                foreach ($quizes as $quiz) {
                    $tentativasTotaisQuiz = 0;
                    $acertosTotaisQuiz = 0;
                    $errosTotaisQuiz = 0;
                    $penalidade = 0;

                    //Conta tentativas, penalidade, erros e acertos do aluno no quiz
                    $flagLimiterAproveitamento = false;
                    $len_registros = count($registros);
                    for ($i = 0; $i < $len_registros; $i++) {
                        $resposta = $registros[$i];
                        if ($resposta->getQuiz() == $quiz->getId()) {
                            if ($resposta->isCerto()) {
                                $acertosTotaisQuiz += 1;
                                $flagLimiterAproveitamento = true;
                            } else {
                                if (!$flagLimiterAproveitamento) {
                                    //Para depois calcular o aproveitamento do aluno no quiz
                                    $penalidade += 1;
                                }
                                $errosTotaisQuiz += 1;
                            }
                            $tentativasTotaisQuiz += 1;
                            unset($registros[$i]);
                        }
                    }
                    $registros = array_values($registros);

                    //Calcula o aproveitamento do aluno no quiz
                    $aproveitamentoQuiz = round((1 / (1 + $penalidade)) * 100, 2);

                    //Salva as informações obtidas
                    array_push(
                        $lv_Quiz,
                        [
                            "Quiz" => $quiz,
                            "Aproveitamento" => $aproveitamentoQuiz,
                            "Metricas" => [
                                "Tentativas" => $tentativasTotaisQuiz,
                                "Acertos" => $acertosTotaisQuiz,
                                "Erros" => $errosTotaisQuiz
                            ]
                        ]
                    );
                }

                //Calcula o aproveitamento do aluno na Fase
                $aproveitamentoFase = 0;
                $tentativasTotaisFase = 0;
                $acertosTotaisFase = 0;
                $errosTotaisFase = 0;
                foreach ($lv_Quiz as $analizeQuiz) {
                    $aproveitamentoFase += $analizeQuiz["Aproveitamento"];
                    $tentativasTotaisFase += $analizeQuiz["Metricas"]["Tentativas"];
                    $acertosTotaisFase += $analizeQuiz["Metricas"]["Acertos"];
                    $errosTotaisFase += $analizeQuiz["Metricas"]["Erros"];
                }
                $contagem_lvQuiz = count($lv_Quiz);
                if($contagem_lvQuiz != 0) {
                    $aproveitamentoFase /= $contagem_lvQuiz;
                }
                
                //Salva as informações obtidas
                array_push(
                    $lv_Fase,
                    [
                        "Turma_Fase" => $turma_fase,
                        "Fase" => $fase,
                        "Aproveitamento" => $aproveitamentoFase,
                        "Quizes" => $lv_Quiz,
                        "Metricas" => [
                            "Tentativas" => $tentativasTotaisFase,
                            "Acertos" => $acertosTotaisFase,
                            "Erros" => $errosTotaisFase
                        ]
                    ]
                );
            }

            //Calcula o aproveitamento do aluno na Turma
            $aproveitamentoTurma = 0;
            $tentativasTotaisTurma = 0;
            $acertosTotaisTurma = 0;
            $errosTotaisTurma = 0;
            foreach ($lv_Fase as $analizeFase) {
                $aproveitamentoTurma += $analizeFase["Aproveitamento"];
                $tentativasTotaisTurma += $analizeFase["Metricas"]["Tentativas"];
                $acertosTotaisTurma += $analizeFase["Metricas"]["Acertos"];
                $errosTotaisTurma += $analizeFase["Metricas"]["Erros"];
            }
            $aproveitamentoTurma /= count($lv_Fase);

            //Declara as variáveis das médias
            $media_acertos = 0;
            $media_erros = 0;
            $media_tentativas = 0;

            //Calcula as médias
            $qtd_quizes_jogados = count($lv_Fase);
            if ($qtd_quizes_jogados > 0) {
                $media_acertos = round($acertosTotaisTurma / $qtd_quizes_jogados);
                $media_erros = round($errosTotaisTurma / $qtd_quizes_jogados);
                $media_tentativas = round($tentativasTotaisTurma / $qtd_quizes_jogados);
            }

            //Salva as informações obtidas
            array_push(
                $lv_Turma,
                [
                    "Turma" => $turma,
                    "Aproveitamento" => $aproveitamentoTurma,
                    "Fases" => $lv_Fase,
                    "Metricas" => [
                        "Tentativas" => $tentativasTotaisTurma,
                        "Acertos" => $acertosTotaisTurma,
                        "Erros" => $errosTotaisTurma
                    ],
                    "Medias" => [
                        "Tentativas" => $media_tentativas,
                        "Acertos" => $media_acertos,
                        "Erros" => $media_erros
                    ]
                ]
            );
        }

        //Se tiver especificado a turma na query, retorna apenas um objeto, não um array
        if (isset($filtro_turma)) {
            $lv_Turma = $lv_Turma[0];
        }

        return $lv_Turma;
    }

    private function calculaEstatisticasTurmaFase(int $id_turma_fase)
    {
        //Instancia a Dao_Quiz
        $Dao_Quiz = \Dao_Quiz::getInstance();

        //Busca todos os quizes ligados a esse Turma_Fase
        $quizes = $Dao_Quiz->QuizesDoTurma_Fase($id_turma_fase);

        //Declara as Metricas
        $acertosTotais = 0;
        $errosTotais = 0;
        $tentativasTotais = 0;
        $aproveitamentoTotal = 0;

        //Gera a estatistica de cada quiz
        $estatisticas_quizes = [];

        $len_quizes = count($quizes);
        $qtd_quizes_jogados = 0;
        for ($i = 0; $i < $len_quizes; $i++) {
            $quiz = $quizes[$i];
            $estatistica = $this->calculaEstatisticasQuiz($quiz->getId());
            $estatistica["PerguntaQuiz"] = $quiz->getPergunta();

            $acertosTotais += $estatistica["Metricas"]["Acertos"];
            $errosTotais += $estatistica["Metricas"]["Erros"];
            $tentativasTotais += $estatistica["Metricas"]["Tentativas"];
            if ($estatistica["Metricas"]["Tentativas"] > 0) {
                $qtd_quizes_jogados += 1;
            }
            $aproveitamentoTotal += $estatistica["Aproveitamento"];

            array_push($estatisticas_quizes, $estatistica);
        }

        //Declara as variáveis das médias
        $media_acertos = 0;
        $media_erros = 0;
        $media_tentativas = 0;
        $media_aproveitamento = 0;

        //Calcula as médias
        if ($qtd_quizes_jogados > 0) {
            $media_acertos = round($acertosTotais / $qtd_quizes_jogados);
            error_log(print_r($acertosTotais / $qtd_quizes_jogados, TRUE));
            $media_erros = round($errosTotais / $qtd_quizes_jogados);
            $media_tentativas = round($tentativasTotais / $qtd_quizes_jogados);
            $media_aproveitamento = round($aproveitamentoTotal / $qtd_quizes_jogados, 2);
        }

        //Cria o objeto de resposta
        $resultados = [
            "idTurma_Fase" => $id_turma_fase,
            "Quizes" => $estatisticas_quizes,
            "Metricas" => [
                "Acertos" => $acertosTotais,
                "Erros" => $errosTotais,
                "Tentativas" => $tentativasTotais,
                "qtd_quizes" => $len_quizes,
                "qtd_quizes_jogados" => $qtd_quizes_jogados
            ],
            "Medias" => [
                "Acertos" => $media_acertos,
                "Erros" => $media_erros,
                "Tentativas" => $media_tentativas
            ],
            "Aproveitamento" => $media_aproveitamento // Em %
        ];

        return $resultados;
    }

    public function estatisticasNivelTurmaFase(Request $request, Response $response, $args)
    {
        /*
          Feito para uso do professor;
          Providencia as métricas sobre:
            Quantidade de tentativas
            Quantidade de acertos
            Quantidade de erros
            "Aproveitamento" médio na fase
          Além de providenciar os dados acima para cada quiz do Turma_Fase.
        */

        $error = [];

        //Precisa passar o id do Turma_Fase
        if (!isset($args["id"])) {
            $response->getBody()->write("Necessário passar o 'id' do 'Turma_Fase' na URL (../turmaFase/:id)");
            $newResponse = $response->withStatus(400);
            return $newResponse;
        }

        //Pega as instancias das DAO's
        $Dao_Professor = \Dao_Professor::getInstance();
        $Dao_Turma_Fase = \Dao_Turma_Fase::getInstance();

        //Resgata os dados do Token
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtSecret["Professor"]);
        $User = unserialize(base64_decode($parsedToken["User"]));

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

        //Verifica se o professor é o dono do Turma_Fase informado
        $id_turma_fase = intval($args["id"]);
        $pertence = $Dao_Turma_Fase->TurmaFasePertenceAoProf($id_turma_fase, intval($User["id"]));
        if (!$pertence) {
            array_push($error, "O 'Turma_Fase' de 'id' = " . $id_turma_fase . " não pertence ao professor");
            $response->getBody()->write(json_encode(["Errors" => $error]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        //Calcula as estatisticas
        $resultados = $this->calculaEstatisticasTurmaFase($id_turma_fase);

        //Converte para JSON
        $response->getBody()->write(
            json_encode($resultados)
        );

        //Responde a requisição com cabeçalho para JSON
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function calculaEstatisticasQuiz(int $quiz_id)
    {
        //Pega a instância da Dao_Resposta para pegar os registros
        $Dao_Resposta = \Dao_Resposta::getInstance();

        //Busca os registros de respostas do Quiz solicitado
        $registros = $Dao_Resposta->GetRegistrosPorQuiz($quiz_id);
        $tentativasTotais = count($registros);
        $acertosTotais = 0;
        $errosTotais = 0;

        //Separa os registros por aluno
        $registrosPorAluno = [];
        while (count($registros) > 0) {
            $conferindo = $registros[0]->getAluno();
            if (!isset($registrosPorAluno[$conferindo]) || !is_array($registrosPorAluno[$conferindo])) {
                $registrosPorAluno[$conferindo] = [];
            }
            for ($i = 0; $i < count($registros); $i++) {
                if ($registros[$i]->getAluno() == $conferindo) {
                    array_push($registrosPorAluno[$conferindo], $registros[$i]);
                    unset($registros[$i]);
                }
            }
            $registros = array_values($registros);
        }

        //Calcula as metricas
        $metricasPorAluno = [];
        foreach ($registrosPorAluno as $key => $regs_aluno) {
            $metricasPorAluno[$key] = ["Acertos" => 0, "Erros" => 0, "Tentativas" => count($regs_aluno)];
            for ($i = 0; $i < $metricasPorAluno[$key]["Tentativas"]; $i++) {
                if ($regs_aluno[$i]->isCerto()) {
                    $metricasPorAluno[$key]["Acertos"] += 1;
                } else {
                    $metricasPorAluno[$key]["Erros"] += 1;
                }
            }
            $acertosTotais += $metricasPorAluno[$key]["Acertos"];
            $errosTotais += $metricasPorAluno[$key]["Erros"];
        }

        //Calcula as médias
        $n_alunos = count($registrosPorAluno);
        if ($n_alunos > 0) {
            $mediaAlunos_tentativas = round($tentativasTotais / $n_alunos);
            $mediaAlunos_acertos = round($acertosTotais / $n_alunos);
            $mediaAlunos_erros = round($errosTotais / $n_alunos);

            //Calcula o aproveitamento
            $penalidade = 0;
            foreach ($registrosPorAluno as $key => $regs_aluno) {
                for ($i = 0; $i < $metricasPorAluno[$key]["Tentativas"]; $i++) {
                    if ($regs_aluno[$i]->isCerto()) {
                        break;
                    } else {
                        $penalidade += 1;
                    }
                }
            }
            $aproveitamento = round(($n_alunos / ($n_alunos + $penalidade)) * 100, 2);
        } else {
            $mediaAlunos_tentativas = 0;
            $mediaAlunos_acertos = 0;
            $mediaAlunos_erros = 0;
            $aproveitamento = 0;
        }

        //Constroi o objeto de resposta
        $resultados = [
            "idQuiz" => $quiz_id,
            "Metricas" => [
                "Acertos" => $acertosTotais,
                "Erros" => $errosTotais,
                "Tentativas" => $tentativasTotais,
                "qtd_alunos" => $n_alunos
            ],
            "Medias" => [
                "Acertos" => $mediaAlunos_acertos,
                "Erros" => $mediaAlunos_erros,
                "Tentativas" => $mediaAlunos_tentativas
            ],
            "Aproveitamento" => $aproveitamento // Em %
        ];

        return $resultados;
    }

    public function estatisticasNivelQuiz(Request $request, Response $response, $args)
    {
        /*
          Feito para uso do professor;
          Providencia as métricas sobre:
            Quantidade de tentativas
            Quantidade de acertos
            Quantidade de erros
            Quantidade média dos itens acima por aluno (considerando apenas os que tentaram)
            "Aproveitamento" médio obtido na questão
        */

        $error = [];

        //Precisa passar o id do quiz
        if (!isset($args["id"])) {
            $response->getBody()->write("Necessário passar o 'id' do quiz na URL (../quiz/:id)");
            $newResponse = $response->withStatus(400);
            return $newResponse;
        }

        //Pega as instancias das DAO's
        $Dao_Professor = \Dao_Professor::getInstance();
        $Dao_Quiz = \Dao_Quiz::getInstance();


        //Resgata os dados do Token
        $helper = new \PsrJwt\Helper\Request();
        $parsedToken = $helper->getTokenPayload($request, $this->jwtSecret["Professor"]);
        $User = unserialize(base64_decode($parsedToken["User"]));

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

        //Calcula as estatisticas do quiz
        $resultados = $this->calculaEstatisticasQuiz($quiz_id);

        //Converte para JSON
        $response->getBody()->write(
            json_encode($resultados)
        );

        //Responde a requisição com cabeçalho para JSON
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function put(Request $request, Response $response, $args)
    {
    }

    public function delete(Request $request, Response $response, $args)
    {
    }
}

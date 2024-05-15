<?php

declare(strict_types=1);

final class Prof_Analises_Test_2 extends PHPUnit\Framework\TestCase
{
    /**
     * @depends Professor_Test::test_login_prof
     * @depends Aluno_Test_2::test_responder_quiz_turma
     */
    public function test_analise_quiz($token): void
    {
        $client = new GuzzleHttp\Client();

        //Resposta Certa
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/professor/analises/quiz/1', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);

        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $must_be_keys = ["idQuiz", "Metricas", "Medias", "Aproveitamento"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body);
        }
        $this->assertIsArray($json_body["Metricas"]);
        $this->assertIsArray($json_body["Medias"]);
        $must_be_keys = ["Acertos", "Erros", "Tentativas"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body["Metricas"]);
            $this->assertArrayHasKey($key, $json_body["Medias"]);
        }
        $soma = $json_body["Metricas"]["Acertos"] + $json_body["Metricas"]["Erros"];
        $this->assertEquals($soma, $json_body["Metricas"]["Tentativas"]);
    }

    /**
     * @depends Professor_Test::test_login_prof
     * @depends Aluno_Test_2::test_responder_quiz_turma
     */
    public function test_analise_turma_fase($token): void
    {
        $client = new GuzzleHttp\Client();

        //Resposta Certa
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/professor/analises/turmaFase/2', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);

        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $must_be_keys = ["idTurma_Fase", "Quizes", "Metricas", "Medias", "Aproveitamento"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body);
        }
        $this->assertIsArray($json_body["Metricas"]);
        $this->assertIsArray($json_body["Medias"]);
        $must_be_keys = ["Acertos", "Erros", "Tentativas"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body["Metricas"]);
            $this->assertArrayHasKey($key, $json_body["Medias"]);
        }
        $soma = $json_body["Metricas"]["Acertos"] + $json_body["Metricas"]["Erros"];
        $this->assertEquals($soma, $json_body["Metricas"]["Tentativas"]);
        $this->assertIsArray($json_body["Quizes"]);
        foreach ($json_body["Quizes"] as $quiz) {
            $must_be_keys = ["idQuiz", "Metricas", "Medias", "Aproveitamento", "PerguntaQuiz"];
            foreach ($must_be_keys as $key) {
                $this->assertArrayHasKey($key, $quiz);
            }
            $this->assertIsArray($quiz["Metricas"]);
            $this->assertIsArray($quiz["Medias"]);
            $must_be_keys = ["Acertos", "Erros", "Tentativas"];
            foreach ($must_be_keys as $key) {
                $this->assertArrayHasKey($key, $quiz["Metricas"]);
                $this->assertArrayHasKey($key, $quiz["Medias"]);
            }
            $soma = $quiz["Metricas"]["Acertos"] + $quiz["Metricas"]["Erros"];
            $this->assertEquals($soma, $quiz["Metricas"]["Tentativas"]);
        }
    }

    /**
     * @depends Professor_Test::test_login_prof
     * @depends Aluno_Test_2::test_responder_quiz_turma
     */
    public function test_analise_aluno($token): void
    {
        $client = new GuzzleHttp\Client();

        //Resposta Certa
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/professor/analises/aluno/1', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);

        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        foreach ($json_body as $umaTurma) {
            $this->assertIsArray($umaTurma);
            $must_be_keys = ["Turma", "Aproveitamento", "Fases", "Metricas"];
            foreach ($must_be_keys as $key) {
                $this->assertArrayHasKey($key, $umaTurma);
            }
            $this->assertIsArray($umaTurma["Turma"]);
            $must_be_keys = ["id", "nome", "ano", "escola", "professor"];
            foreach ($must_be_keys as $key) {
                $this->assertArrayHasKey($key, $umaTurma["Turma"]);
            }
            $this->assertIsArray($umaTurma["Fases"]);
            foreach ($umaTurma["Fases"] as $fase) {
                $must_be_keys = ["Turma_Fase", "Fase", "Aproveitamento", "Quizes", "Metricas"];
                foreach ($must_be_keys as $key) {
                    $this->assertArrayHasKey($key, $fase);
                }
                $this->assertIsArray($fase["Turma_Fase"]);
                $must_be_keys = ["id", "turma", "fase"];
                foreach ($must_be_keys as $key) {
                    $this->assertArrayHasKey($key, $fase["Turma_Fase"]);
                }
                $this->assertIsArray($fase["Fase"]);
                $must_be_keys = ["id", "nome", "url", "criador", "dificuldade", "tempo_medio_seg", "contem", "vars"];
                foreach ($must_be_keys as $key) {
                    $this->assertArrayHasKey($key, $fase["Fase"]);
                }
                $this->assertIsArray($fase["Metricas"]);
                $must_be_keys = ["Acertos", "Erros", "Tentativas"];
                foreach ($must_be_keys as $key) {
                    $this->assertArrayHasKey($key, $fase["Metricas"]);
                }
                $soma = $fase["Metricas"]["Acertos"] + $fase["Metricas"]["Erros"];
                $this->assertEquals($soma, $fase["Metricas"]["Tentativas"]);
                $this->assertIsArray($fase["Quizes"]);
                foreach ($fase["Quizes"] as $quiz) {
                    $must_be_keys = ["Quiz", "Metricas", "Aproveitamento"];
                    foreach ($must_be_keys as $key) {
                        $this->assertArrayHasKey($key, $quiz);
                    }
                    $this->assertIsArray($quiz["Quiz"]);
                    $must_be_keys = ["id", "turma_fase", "pergunta"];
                    foreach ($must_be_keys as $key) {
                        $this->assertArrayHasKey($key, $quiz["Quiz"]);
                    }
                    $this->assertIsArray($quiz["Metricas"]);
                    $must_be_keys = ["Acertos", "Erros", "Tentativas"];
                    foreach ($must_be_keys as $key) {
                        $this->assertArrayHasKey($key, $quiz["Metricas"]);
                    }
                    $soma = $quiz["Metricas"]["Acertos"] + $quiz["Metricas"]["Erros"];
                    $this->assertEquals($soma, $quiz["Metricas"]["Tentativas"]);
                }
            }
            $this->assertIsArray($umaTurma["Metricas"]);
            $must_be_keys = ["Acertos", "Erros", "Tentativas"];
            foreach ($must_be_keys as $key) {
                $this->assertArrayHasKey($key, $umaTurma["Metricas"]);
            }
        }
    }

    /**
     * @depends Professor_Test::test_login_prof
     * @depends Aluno_Test_2::test_responder_quiz_turma
     */
    public function test_analise_turma($token): void
    {
        $client = new GuzzleHttp\Client();

        //Resposta Certa
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/professor/analises/turma/1', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);

        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);

        $must_be_keys = ["idTurma", "Turma_Fases", "Metricas", "AproveitamentoNasFases"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body);
        }

        $this->assertIsArray($json_body["Metricas"]);
        $this->assertIsArray($json_body["Medias"]);
        $must_be_keys = ["Acertos", "Erros", "Tentativas"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body["Metricas"]);
            $this->assertArrayHasKey($key, $json_body["Medias"]);
        }
        $soma = $json_body["Metricas"]["Acertos"] + $json_body["Metricas"]["Erros"];
        $this->assertEquals($soma, $json_body["Metricas"]["Tentativas"]);

        $this->assertIsArray($json_body["Turma_Fases"]);

        foreach ($json_body["Turma_Fases"] as $tf) {
            $this->assertIsArray($tf);
            $must_be_keys = ["idTurma_Fase", "Quizes", "Metricas", "Medias", "Aproveitamento", "Fase"];
            foreach ($must_be_keys as $key) {
                $this->assertArrayHasKey($key, $tf);
            }

            $this->assertIsArray($tf["Metricas"]);
            $this->assertIsArray($tf["Medias"]);
            $must_be_keys = ["Acertos", "Erros", "Tentativas"];
            foreach ($must_be_keys as $key) {
                $this->assertArrayHasKey($key, $tf["Metricas"]);
                $this->assertArrayHasKey($key, $tf["Medias"]);
            }
            $soma = $tf["Metricas"]["Acertos"] + $tf["Metricas"]["Erros"];
            $this->assertEquals($soma, $tf["Metricas"]["Tentativas"]);

            $this->assertIsArray($tf["Quizes"]);
            foreach($tf["Quizes"] as $quiz) {
                $this->assertIsArray($quiz);
                $must_be_keys = ["idQuiz", "Metricas", "Medias", "Aproveitamento", "PerguntaQuiz"];
                foreach ($must_be_keys as $key) {
                    $this->assertArrayHasKey($key, $quiz);
                }

                $this->assertIsArray($quiz["Medias"]);
                $this->assertIsArray($quiz["Metricas"]);
                $this->assertArrayHasKey("qtd_alunos", $quiz["Metricas"]);
                $must_be_keys = ["Acertos", "Erros", "Tentativas"];
                foreach ($must_be_keys as $key) {
                    $this->assertArrayHasKey($key, $quiz["Medias"]);
                    $this->assertArrayHasKey($key, $quiz["Metricas"]);
                }

            }

            $this->assertIsArray($tf["Fase"]);
            $must_be_keys = ["id", "nome", "url", "criador", "dificuldade", "tempo_medio_seg", "contem", "vars"];
            foreach ($must_be_keys as $key) {
                $this->assertArrayHasKey($key, $tf["Fase"]);
            }
        }
    }
}

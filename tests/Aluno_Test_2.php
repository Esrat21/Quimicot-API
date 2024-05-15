<?php

declare(strict_types=1);

final class Aluno_Test_2 extends PHPUnit\Framework\TestCase
{
    /**
     * @depends Aluno_Test::test_login
     * @depends Aluno_Test::test_ingresso_turma
     * @depends Prof_Quiz_Turma_Test::test_atualizar_alternativa_quiz_turma
     */
    public function test_listar_fases_turma($token): void
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/aluno/turmas/fases', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);

        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        foreach($json_body as $id) {
            $this->assertTrue(is_numeric($id));
        }

        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/aluno/turmas/fases?idTurma=1', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);

        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        foreach($json_body as $id) {
            $this->assertIsArray($id);
        }
        $must_be_keys = ["Turma_Fase_id", "Fase_id", "Fase_nome", "Fase_url", "Fase_vars"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body[0]);
        }

        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/aluno/turmas/fases?idFase=1', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);

        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $must_be_keys = ["id", "nome", "url", "criador", "dificuldade", "tempo_medio_seg", "contem", "vars"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body);
        }
    }

    /**
     * @depends Aluno_Test::test_login
     * @depends Aluno_Test::test_ingresso_turma
     * @depends Prof_Quiz_Turma_Test::test_atualizar_alternativa_quiz_turma
     */
    public function test_get_quizes_turma($token): void
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/aluno/turmas/fases/2/quiz', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);

        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $must_be_keys = ["quiz_id", "pergunta", "alternativas"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body);
        }
        $this->assertIsArray($json_body["alternativas"]);
        $this->assertIsArray($json_body["alternativas"][0]);
        $alternativa = $json_body["alternativas"][0];
        $must_be_keys = ["id", "descricao"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $alternativa);
        }
    }

    /**
     * @depends Aluno_Test::test_login
     * @depends Aluno_Test_2::test_get_quizes_turma
     */
    public function test_responder_quiz_turma($token): void
    {
        $client = new GuzzleHttp\Client();

        //Resposta Certa
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/aluno/turmas/fases/2/quiz', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["escolha" => 4, "data_hora" => date("Y-m-d H:i:s"), "quiz" => 1]
        ]);

        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $must_be_keys = ["Correto", "idDaResposta", "alternativasJustificadas"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body);
        }
        $this->assertEquals($json_body["Correto"], 1);
        $this->assertIsArray($json_body["alternativasJustificadas"]);
        $alternativasJustificadas = $json_body["alternativasJustificadas"];
        foreach ($alternativasJustificadas as $alt) {
            $this->assertIsArray($alt);
            $must_be_keys = ["id", "quiz", "alt_correta", "descricao", "justificativa"];
            foreach ($must_be_keys as $key) {
                $this->assertArrayHasKey($key, $alt);
            }
            $this->assertEquals($alt["quiz"], 1);
            if($alt["alt_correta"]) {
                $this->assertEquals($alt["id"], 4);
            }
        }

        //Resposta Errada
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/aluno/turmas/fases/2/quiz', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["escolha" => 1, "data_hora" => date("Y-m-d H:i:s"), "quiz" => 1]
        ]);

        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $must_be_keys = ["Correto", "idDaResposta", "alternativasJustificadas"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body);
        }
        $this->assertEquals($json_body["Correto"], 0);
        $this->assertIsArray($json_body["alternativasJustificadas"]);
        $alternativasJustificadas = $json_body["alternativasJustificadas"];
        foreach ($alternativasJustificadas as $alt) {
            $this->assertIsArray($alt);
            $must_be_keys = ["id", "quiz", "alt_correta", "descricao", "justificativa"];
            foreach ($must_be_keys as $key) {
                $this->assertArrayHasKey($key, $alt);
            }
            $this->assertEquals($alt["quiz"], 1);
            if($alt["alt_correta"]) {
                $this->assertEquals($alt["id"], 4);
            }
        }
    }

    /**
     * @depends Aluno_Test::test_login
     * @depends Aluno_Test_2::test_listar_fases_turma
     */
    public function test_enviar_log($token): void
    {
        $client = new GuzzleHttp\Client();

        //Resposta Certa
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/aluno/log', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => [
                "turma_fase" => 2,
                "detalhes" => "morreu nas coordenadas x: 111, y: 123",
                "tipo" => "morte",
                "comeco" => date("Y-m-d H:i:s", strtotime('-2 hours', time())),
                "fim" => date("Y-m-d H:i:s"),
                "objeto" => json_encode([
                    "obstaculos" => [[10, 10], [11, 11], [12, 12]]
                ])
            ]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertArrayHasKey("ID", $json_body);
        $this->assertEquals(1, $json_body["ID"]);
    }
}

<?php

declare(strict_types=1);

final class Prof_Quiz_Turma_Test extends PHPUnit\Framework\TestCase
{
    /**
     * @depends Professor_Test::test_login_prof
     * @depends Prof_Turma_Test::test_vincular_e_desvincular_fase_turma
     */
    public function test_vincular_quiz_turma($token): void
    {
        $client = new GuzzleHttp\Client();
        //Tipo de alternativa sem justificativa
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/professor/turmas/quiz', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["pergunta" => "Uma pergunta de teste: 2 + 2 = ?", "turma_fase" => 2, "alternativas" => ["5", "6", "7", "4"], "alt_correta" => 3]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertArrayHasKey("ID", $json_body);
        $this->assertEquals($json_body["ID"], 1);

        //Tipo de alternativa com justificativa
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/professor/turmas/quiz', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["pergunta" => "Uma pergunta de teste: 2 - 2 = ?", "turma_fase" => 2, "alternativas" => [
                ["descricao" => "5", "justificativa" => "Justificando um erro"],
                ["descricao" => "4", "justificativa" => "Justificando um erro"],
                ["descricao" => "-2", "justificativa" => "Justificando um erro"],
                ["descricao" => "0", "justificativa" => "Justificando um erro"],
            ], "alt_correta" => 3]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertArrayHasKey("ID", $json_body);
        $this->assertEquals($json_body["ID"], 2);

        //Tipo de alternativa mista
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/professor/turmas/quiz', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["pergunta" => "Uma pergunta de teste: 3 - 2 = ?", "turma_fase" => 2, "alternativas" => [
                ["descricao" => "5", "justificativa" => "Justificando um erro"],
                ["descricao" => "4", "justificativa" => "Justificando um erro"],
                "-2",
                ["descricao" => "1", "justificativa" => "Justificando um erro"],
            ], "alt_correta" => 3]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertArrayHasKey("ID", $json_body);
        $this->assertEquals($json_body["ID"], 3);
    }

    /**
     * @depends Professor_Test::test_login_prof
     * @depends Prof_Turma_Test::test_vincular_e_desvincular_fase_turma
     */
    public function test_deletar_quiz_turma($token): void
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/professor/turmas/quiz/3/deletar', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertArrayHasKey("Success", $json_body);
        $this->assertEquals($json_body["Success"], 1);
    }

    /**
     * @depends Professor_Test::test_login_prof
     * @depends Prof_Turma_Test::test_vincular_e_desvincular_fase_turma
     */
    public function test_atualizar_quiz_turma($token): void
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/professor/turmas/quiz/2/atualizar', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["pergunta" => "Uma pergunta de teste: 5 - 5 = ?"]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertIsBool($json_body["Success"]);
        $this->assertEquals($json_body["Success"], true);
    }

    /**
     * @depends Professor_Test::test_login_prof
     * @depends Prof_Turma_Test::test_vincular_e_desvincular_fase_turma
     */
    public function test_atualizar_alternativa_quiz_turma($token): void
    {
        $client = new GuzzleHttp\Client();
        //Sem justificativa
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/professor/turmas/quiz/1/atualizarAlternativa/1', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["5"],
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertIsBool($json_body["Success"]);
        $this->assertEquals($json_body["Success"], true);

        //Com justificativa
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/professor/turmas/quiz/1/atualizarAlternativa/1', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["descricao" => "5", "justificativa" => "Justificando um erro"],
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertIsBool($json_body["Success"]);
        $this->assertEquals($json_body["Success"], true);
    }
}

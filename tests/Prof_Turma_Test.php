<?php

declare(strict_types=1);

final class Prof_Turma_Test extends PHPUnit\Framework\TestCase
{

    /**
     * @depends Professor_Test::test_login_prof
     * @depends Professor_Test::test_criar_turma
     */
    public function test_listar_turmas($token): void
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/professor/turmas', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
    }

    /**
     * @depends Professor_Test::test_login_prof
     * @depends Professor_Test::test_criar_turma
     */
    public function test_vincular_e_desvincular_fase_turma($token): void
    {
        $client = new GuzzleHttp\Client();
        //Vincula
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/professor/turmas/1/vincularFase', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["fase" => 1]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertArrayHasKey("ID", $json_body);
        $this->assertEquals($json_body["ID"], 1);

        //Desvincula
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/professor/turmas/1/desvincularFase', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["fase" => 1]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey("Success", $json_body);
        $this->assertEquals($json_body["Success"], 1);

        //Vincula novamente
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/professor/turmas/1/vincularFase', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["fase" => 1]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertArrayHasKey("ID", $json_body);
        $this->assertEquals($json_body["ID"], 2);
    }

    /**
     * @depends Professor_Test::test_login_prof
     * @depends Aluno_Test::test_ingresso_turma
     */
    public function test_listar_alunos_turma($token): void
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/professor/turmas', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $must_be_keys = ["id", "nome", "ano", "escola"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body[0]);
        }
        $this->assertIsArray($json_body[0]["escola"]);
        $must_be_keys = ["id", "nome"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body[0]["escola"]);
        }
    }
}

<?php

declare(strict_types=1);

final class Aluno_Test extends PHPUnit\Framework\TestCase
{
    public static $alunoTest = [
        "nome" => "Aluno Ciclano",
        "email" => "aluno@email.com",
        "senha" => "100senha"
    ];

    public function test_cadastro(): String
    {
        
        $client = new GuzzleHttp\Client(['http_errors' => false]);
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/aluno', [
            "json" => self::$alunoTest
        ]);
        
        if($response->getStatusCode() != 200) {
            self::markTestSkipped("Para testar o cadastro de aluno apague o aluno com email '" . self::$alunoTest['email'] . "'.");
            return 0;
        } else {
            $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
            $this->assertEquals(200, $response->getStatusCode());
            $json_body = json_decode($response->getBody()->getContents(), true);
            $this->assertIsArray($json_body);
            $this->assertIsNumeric($json_body["ID"]);
            return $json_body["ID"];
        }
    }

    /**
     * @depends Aluno_Test::test_cadastro
     */
    public function test_login(): String
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/aluno/login', [
            "json" => ["email" => self::$alunoTest["email"], "senha" => self::$alunoTest["senha"]]
        ]);
        $response2 = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/login', [
            "json" => ["email" => self::$alunoTest["email"], "senha" => self::$alunoTest["senha"]]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response2->getHeader('content-type')[0]);
        $this->assertEquals(200, $response2->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $json_body2 = json_decode($response2->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertIsArray($json_body2);
        $this->assertEquals($json_body["User"], $json_body2["User"]);
        $must_be_keys = ["Aprovado", "User", "Token", "Tipo"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body);
        }
        $user = $json_body["User"];
        $this->assertIsArray($user);
        $user_must_be_keys = ["id", "nome", "email"];
        foreach ($user_must_be_keys as $key) {
            $this->assertArrayHasKey($key, $user);
        }
        $this->assertIsString($json_body["Token"]);
        $this->assertIsString($json_body["Tipo"]);
        $this->assertEquals($json_body["Tipo"], "Aluno");
        $this->assertIsBool($json_body["Aprovado"]);
        $this->assertEquals($json_body["Aprovado"], true);

        return $json_body["Token"];
    }

    /**
     * @depends Aluno_Test::test_login
     * @depends Professor_Test::test_criar_turma
     */
    public function test_ingresso_turma($token): String
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/aluno/turmas/ingressar', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["turma" => 1, "senha" => "100senha"]
        ]);

        if($response->getStatusCode() != 200) {
            self::markTestSkipped("Para testar o ingresso de turma crie a turma 1 com senha 100senha");
            return 0;
        } else {
            $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
            $this->assertEquals(200, $response->getStatusCode());
            $json_body = json_decode($response->getBody()->getContents(), true);
            $this->assertIsArray($json_body);
            $this->assertIsNumeric($json_body["ID"]);
            return $json_body["ID"];
        }
    }

    /**
     * @depends Aluno_Test::test_login
     * @depends Aluno_Test::test_ingresso_turma
     */
    public function test_listar_turmas($token): void
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/aluno/turmas', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $must_be_keys = ["Aluno_Turma_id", "Turma_id", "Turma_nome", "Turma_ano", "Escola_id", "Escola_nome", "Professor_id", "Professor_nome", "Professor_email"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body[0]);
        }
    }
}

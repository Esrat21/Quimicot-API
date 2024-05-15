<?php

declare(strict_types=1);

final class Professor_Test extends PHPUnit\Framework\TestCase
{
    public static $professorTest = [
        "nome" => "Professor Fulano",
        "email" => "professor@email.com",
        "senha" => "100senha",
        "cpf" => "235.053.650-50", //CPF Aleatório
        "telefone" => "34988776655", //Telefone Aleatório
        "escolas" => [1]
    ];

    public static $turmaTest = [
        "nome" => "Turma 1",
        "escola" => 1,
        "senha" => "100senha",
        "ano" => "2021"
    ];

    /**
     * @depends Admin_Test_2::test_permissoes_professor
     * @depends Professor_Cadastro_Test::test_cadastro
     */
    public function test_login_prof(): String
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/professor/login', [
            "json" => ["email" => Professor_Test::$professorTest["email"], "senha" => Professor_Test::$professorTest["senha"]]
        ]);
        $response2 = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/login', [
            "json" => ["email" => Professor_Test::$professorTest["email"], "senha" => Professor_Test::$professorTest["senha"]]
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
        $user_must_be_keys = ["id", "nome", "email", "cpf", "telefone"];
        foreach ($user_must_be_keys as $key) {
            $this->assertArrayHasKey($key, $user);
        }
        $this->assertIsString($json_body["Token"]);
        $this->assertIsString($json_body["Tipo"]);
        $this->assertEquals($json_body["Tipo"], "Professor");
        $this->assertIsBool($json_body["Aprovado"]);
        $this->assertEquals($json_body["Aprovado"], true);

        return $json_body["Token"];
    }
    
    /**
     * @depends Professor_Test::test_login_prof
     */
    public function test_criar_turma($token): String
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/professor/turmas', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => self::$turmaTest
        ]);

        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertIsNumeric($json_body["ID"]);
        return $json_body["ID"];
    }

}

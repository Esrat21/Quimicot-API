<?php

declare(strict_types=1);

final class Admin_Test extends PHPUnit\Framework\TestCase
{
    public function test_login(): String
    {
        $admin_email = "admin@email.br";
        $admin_senha = "100senha";
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/admin/login', [
            "json" => ["email" => $admin_email, "senha" => $admin_senha]
        ]);
        if ($response->getStatusCode() != 200) {
            self::markTestSkipped("Para testar o cadastro de escolas cadastre o Admin com email '{$admin_email}' e senha '{$admin_senha}'");
        } else {
            $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
            $this->assertEquals(200, $response->getStatusCode());
            $json_body = json_decode($response->getBody()->getContents(), true);
            $this->assertIsArray($json_body);
            $must_be_keys = ["Aprovado", "User", "Token", "Tipo"];
            foreach ($must_be_keys as $key) {
                $this->assertArrayHasKey($key, $json_body);
            }
            $user = $json_body["User"];
            $this->assertIsArray($user);
            $user_must_be_keys = ["id", "nome", "email", "cpf", "telefone", "tipo"];
            foreach ($user_must_be_keys as $key) {
                $this->assertArrayHasKey($key, $user);
            }
            $this->assertIsString($json_body["Token"]);
            $this->assertIsString($json_body["Tipo"]);
            $this->assertEquals($json_body["Tipo"], "Administrador");
            $this->assertIsBool($json_body["Aprovado"]);
            $this->assertEquals($json_body["Aprovado"], true);
    
            return $json_body["Token"];
        }
    }

    /**
     * @depends test_login
     */
    public function test_login_token(String $token): void
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/admin/login', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $must_be_keys = ["Aprovado", "User", "Token", "Tipo"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body);
        }
        $user = $json_body["User"];
        $this->assertIsArray($user);
        $user_must_be_keys = ["id", "nome", "email", "cpf", "telefone", "tipo"];
        foreach ($user_must_be_keys as $key) {
            $this->assertArrayHasKey($key, $user);
        }
        $this->assertIsString($json_body["Token"]);
        $this->assertIsString($json_body["Tipo"]);
        $this->assertEquals($json_body["Tipo"], "Administrador");
        $this->assertIsBool($json_body["Aprovado"]);
        $this->assertEquals($json_body["Aprovado"], true);
    }

    public function test_invalid_login(): void
    {
        $client = new GuzzleHttp\Client(['http_errors' => false]);
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/admin/login', [
            "json" => ["email" => "admin@email.br", "senha" => "SenhaErrada"]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(401, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $must_be_keys = ["Aprovado", "User", "Token"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $json_body);
        }
        $this->assertIsBool($json_body["Aprovado"]);
        $this->assertEquals($json_body["Aprovado"], false);
        $this->assertEquals($json_body["User"], null);
        $this->assertEquals($json_body["Token"], null);
        $this->assertEquals($json_body["Tipo"], "Administrador");
    }

    /**
     * @depends test_login
     */
    public function test_cadastrar_fase(String $token): void
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/admin/fase', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => [
                "nome" => "Balões",
                "url" => "https://batataic2.000webhostapp.com/",
                "criador" => "João Gabriel de Matos Dairel",
                "dificuldade" => "M",
                "vars" => []
            ]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertArrayHasKey("ID", $json_body);
    }

    /**
     * @depends test_login
     */
    public function test_cadastrar_escola(String $token): void
    {
        $nome_escola = "Escola Estadual Messias Pedreiro";
        $client = new GuzzleHttp\Client(['http_errors' => false]);
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/admin/escola', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => [
                "nome" => $nome_escola
            ]
        ]);
        if ($response->getStatusCode() != 200) {
            self::markTestSkipped("Para testar o cadastro de escolas apague a escola com nome '{$nome_escola}'");
        } else {
            $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
            $this->assertEquals(200, $response->getStatusCode());
            $json_body = json_decode($response->getBody()->getContents(), true);
            $this->assertIsArray($json_body);
            $this->assertArrayHasKey("ID", $json_body);
        }
    }

    /**
     * @depends test_login
     */
    public function test_get_professores(String $token): void
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/admin/professor', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
    }
  
}

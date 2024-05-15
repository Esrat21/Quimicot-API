<?php

declare(strict_types=1);

final class Admin_Test_2 extends PHPUnit\Framework\TestCase
{
    /**
     * @depends Admin_Test::test_login
     * @depends Professor_Cadastro_Test::test_cadastro
     */
    public function test_permissoes_professor(String $token, String $id): String
    {
        $client = new GuzzleHttp\Client();        
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . "/admin/acessoProfessor/{$id}", [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["metodo" => "aceitar"]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);

        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . "/admin/acessoProfessor/{$id}", [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["metodo" => "revogar"]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);

        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . "/admin/acessoProfessor/{$id}", [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
            "json" => ["metodo" => "aceitar"]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);

        return $id;
    }
}

<?php

declare(strict_types=1);

final class Professor_Cadastro_Test extends PHPUnit\Framework\TestCase
{
    /**
     * @depends Admin_Test::test_cadastrar_escola
     */
    public function test_cadastro(): String
    {
        
        $client = new GuzzleHttp\Client(['http_errors' => false]);
        $response = $client->request('POST', 'http://' . $_ENV['URL_API'] . '/professor', [
            "json" => Professor_Test::$professorTest
        ]);
        
        if($response->getStatusCode() != 200) {
            self::markTestSkipped("Para testar o cadastro de professor apague o professor com email '" . Professor_Test::$professorTest['email'] . "' e/ou cpf '" . Professor_Test::$professorTest['cpf'] . "'.");
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
}

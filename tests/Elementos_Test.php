<?php declare(strict_types=1);

final class Elementos_Test extends PHPUnit\Framework\TestCase
{
    public function test_Get_GruposDeElementos(): void
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/elementos/filtered?filtro=grupo');
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertContainsOnly('string', $json_body);
    }
    
    public function test_Get_ClassificacaoDeElementos(): void
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/elementos/filtered?filtro=classificacao');
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertContainsOnly('string', $json_body);
    }
    
    public function test_Get_Elemento(): void
    {
        $elemento = "H";

        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/elementos/get/' . $elemento);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('sigla', $json_body);
        $this->assertArrayHasKey('objeto', $json_body);
        $this->assertIsArray($json_body["objeto"]);

        $must_be_keys_of_objeto = ["Tipo", "Grupo", "Simbolo", "Período", "Subgrupo", "Densidade", "Classificação", "Ponto de Fusão", "Número atômico", "Eletronegatividade", "Ponto de Ebulição", "Composição do átomo", "Massa atômica relativa", "Raio atômico calculado", "Distribuição eletrônica"];
        foreach($must_be_keys_of_objeto as $key) {
            $this->assertArrayHasKey($key, $json_body["objeto"]);
        }
    }
}
<?php declare(strict_types=1);

final class Escolas_Test extends PHPUnit\Framework\TestCase
{
    public function test_Get_Escolas(): void
    {
        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/escola');
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertContainsOnly('array', $json_body);
        if(count($json_body) > 0) {
            $escola_exemplo = $json_body[0];
            $this->assertIsArray($escola_exemplo);
            $must_be_keys = ["id","nome"];
            foreach($must_be_keys as $key) {
                $this->assertArrayHasKey($key, $escola_exemplo);
            }
        }
    }
}
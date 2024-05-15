<?php

declare(strict_types=1);

final class Prof_Turma_Test_2 extends PHPUnit\Framework\TestCase
{
    /**
     * @depends Professor_Test::test_login_prof
     * @depends Prof_Turma_Test::test_vincular_e_desvincular_fase_turma
     */
    public function test_listar_fases_turma($token): void
    {
        $client = new GuzzleHttp\Client();
        //Vincula
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/professor/turmas/1/fases', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertIsArray($json_body[0]);
        $turma_fase = $json_body[0];
        $must_be_keys = ["ID", "Fase"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $turma_fase);
        }
        $this->assertIsArray($turma_fase["Fase"]);
        $must_be_keys = ["id", "nome", "url", "criador", "criador", "dificuldade", "tempo_medio_seg", "contem", "vars"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $turma_fase["Fase"]);
        }
    }
    
    /**
     * @depends Professor_Test::test_login_prof
     * @depends Prof_Quiz_Turma_Test::test_vincular_quiz_turma
     */
    public function test_listar_quizes_turma_fase($token): void
    {
        $client = new GuzzleHttp\Client();
        //Vincula
        $response = $client->request('GET', 'http://' . $_ENV['URL_API'] . '/professor/turmas/1/fases/1/quizes', [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);
        $this->assertEquals('application/json', $response->getHeader('content-type')[0]);
        $this->assertEquals(200, $response->getStatusCode());
        $json_body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($json_body);
        $this->assertIsArray($json_body[0]);
        $quiz = $json_body[0];
        $must_be_keys = ["quiz_id", "pergunta", "alternativas"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $quiz);
        }
        $this->assertIsArray($quiz["alternativas"]);
        $this->assertIsArray($quiz["alternativas"][0]);
        $alternativa = $quiz["alternativas"][0];
        $must_be_keys = ["id", "quiz", "alt_correta", "descricao", "justificativa"];
        foreach ($must_be_keys as $key) {
            $this->assertArrayHasKey($key, $alternativa);
        }
        $this->assertEquals($quiz["quiz_id"], $alternativa["quiz"]);
    }
}

<?php

use GuzzleHttp\Psr7;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $App) use ($container) {
    $App->get('/', 'IndexController:index');

    //$app->post('/aluno', 'AlunoController:post');

    $App->group('/login', function (RouteCollectorProxy $group) use ($container) {
        $group->post('', function (Request $request, Response $response, $args) use ($container) {

            $ProfessorController = $container->get("ProfessorController");
            $retorno = $ProfessorController->login($request, $response, $args);
            $statusLogin = $retorno->getStatusCode();
            if (($statusLogin >= 200 && $statusLogin < 300) || $statusLogin == 403) {
                return $retorno;
            }
            $response = $response->withBody(Psr7\Utils::streamFor(''));
            $AlunoController = $container->get("AlunoController");
            $retorno = $AlunoController->login($request, $response, $args);
            $statusLogin = $retorno->getStatusCode();

            if ($statusLogin >= 200 && $statusLogin < 300) {
                return $retorno;
            }

            $response = $response->withBody(Psr7\Utils::streamFor(''));

            $response->getBody()->write(json_encode(["Aprovado" => false, "User" => null, "Token" => null, "Tipo" => "Desconhecido"]));
            return $response->withStatus(401);;
        });
    });

    $App->group('/professor', function (RouteCollectorProxy $group) {
        $group->post('', 'ProfessorController:post'); ///Cadastro
        $group->post('/login', 'ProfessorController:login'); ///Login
    });
    $App->group('/professor', function (RouteCollectorProxy $group) {
        $group->get('', 'ProfessorController:get'); ///Acesso aos Dados
        $group->get('/escolas', 'EscolaController:prof_get'); ///Pegar todas escolas do prof
        $group->get('/allquizes', 'ProfessorController:getQuizes'); //Pegar todos os quizes do prof
        $group->get('/fasescomturmas', 'Turma_FaseController:get_prof_fases_e_suas_turmas');
        $group->group('/turmas', function (RouteCollectorProxy $turmasGroup) {
            $turmasGroup->get('', 'TurmaController:get'); ///Acesso aos Dados
            $turmasGroup->post('', 'TurmaController:post'); ///Cadastrar turma
            $turmasGroup->get('/alunos', 'TurmaController:getAlunosDaTurma'); ///Pegar todos alunos do professor, ou filtra pelo ?turma=<id>
            $turmasGroup->group('/quiz', function (RouteCollectorProxy $quizGroup) {
                $quizGroup->post('', 'QuizController:post'); ///Adicionar quiz (por completo)
                $quizGroup->post('/{id:[0-9]+}/deletar', 'QuizController:delete'); ///Deletar um quiz (por completo)
                $quizGroup->post('/{id:[0-9]+}/atualizar', 'QuizController:put'); ///Atualiza a pergunta de um quiz
                $quizGroup->post('/{id:[0-9]+}/atualizarAlternativa/{idAlt:[0-9]+}', 'QuizController:updateAlternativa'); ///Atualiza uma alternativa do quiz
            });
            $turmasGroup->group('/{id:[0-9]+}', function (RouteCollectorProxy $turmaGroup) {
                $turmaGroup->post('/vincularFase', 'Turma_FaseController:post'); ///Vincular turma a uma fase
                $turmaGroup->post('/desvincularFase', 'Turma_FaseController:delete'); ///Desvincular da turma uma fase
                $turmaGroup->group('/fases', function (RouteCollectorProxy $fasesGroup) {
                    $fasesGroup->get('', 'Turma_FaseController:get'); ///Ver fases vinculadas a uma turma, ou não vinculadas com ?vinculadas=false
                    $fasesGroup->get('/{idFase:[0-9]+}/quizes', 'Turma_FaseController:getQuizes'); ///Pegar quizes que estão vinculados na ligação Turma_Fase
                });
            });
        });
        $group->group('/analises', function (RouteCollectorProxy $analisesGroup) {
            $analisesGroup->get('/quiz/{id:[0-9]+}', 'RespostaController:estatisticasNivelQuiz'); ///Retorna informações sobre o desempenho em um quiz
            $analisesGroup->get('/aluno/{id:[0-9]+}', 'RespostaController:estatisticasNivelAluno'); ///Retorna informações sobre o desempenho em um aluno, o parametro ?turma=<id> busca somente em uma turma
            $analisesGroup->get('/turmaFase/{id:[0-9]+}', 'RespostaController:estatisticasNivelTurmaFase'); ///Retorna informações sobre o desempenho em uma fase da turma
            $analisesGroup->get('/turma/{id:[0-9]+}', 'RespostaController:estatisticasNivelTurma'); ///Retorna informações sobre o desempenho em uma turma
        });
    })->add(\PsrJwt\Factory\JwtMiddleware::json($container->get('JwtSecretsMap')["Professor"], 'jwt', ['Errors' => ['Authorisation Failed']]));

    $App->group('/aluno', function (RouteCollectorProxy $group) {
        $group->post('', 'AlunoController:post'); ///Cadastro
        $group->post('/login', 'AlunoController:login'); ///Login
    });
    $App->group('/aluno', function (RouteCollectorProxy $group) {
        $group->get('', 'AlunoController:get'); ///Acesso aos Dados
        $group->group('/turmas', function (RouteCollectorProxy $turmasGroup) {
            $turmasGroup->get('', 'Aluno_TurmaController:get'); ///Ver turmas que aluno esta inscrito
            $turmasGroup->post('/ingressar', 'Aluno_TurmaController:post'); ///Vincular um aluno a uma turma
            $turmasGroup->group("/fases", function (RouteCollectorProxy $fasesGroup) {
                $fasesGroup->get('', 'FaseController:Aluno_get'); ///Pegar Fases da turma, tbm pega detalhes das fazes e seus quizes
                $fasesGroup->get('/{id:[0-9]+}/quiz', 'FaseController:quiz'); ///Retorna um quiz da turma_fase
                $fasesGroup->post('/{id:[0-9]+}/quiz', 'RespostaController:post'); ///Salva resposta do quiz da turma_fase (deve passar o id do quiz via body)
            });
        });
        $group->group("/resultados", function (RouteCollectorProxy $resultadosGroup) {
            $resultadosGroup->get('/qtdJogadas', 'AlunoController:getQtdJogadasByAluno'); ///Ver quantas vezes aluno jogou a fase ?turma=<id>
            $resultadosGroup->get('/analises', 'RespostaController:estatisticasNivelAlunoChamadaAluno'); ///Retorna informações sobre o desempenho do aluno, o parametro ?turma=<id> busca somente em uma turma
        });
        $group->group('/log', function (RouteCollectorProxy $logGroup) {
            //$app->get('', 'LogController:get');
            $logGroup->post('', 'LogController:post'); ///Registrar algum acontecimento
        });
    })->add(\PsrJwt\Factory\JwtMiddleware::json($container->get('JwtSecretsMap')["Aluno"], 'jwt', ['Errors' => ['Authorisation Failed']]));

    $App->group('/admin', function (RouteCollectorProxy $group) {
        $group->post('/login', 'AdministradorController:login'); ///Login
        /*
        O cadastro de Administradores deve ser feito direto no banco de dados
        https://www.php.net/manual/pt_BR/function.password-hash.php
        */
        $group->get('/logsCsv', 'LogController:ADM_getCsv');
    });
    $App->group('/admin', function (RouteCollectorProxy $group) {
        $group->get('/professor', 'ProfessorController:ADM_get'); ///Retorna professores cadastrado
        $group->post('/acessoProfessor/{id:[0-9]+}', 'ProfessorController:alterarPendencia'); ///Libera o acesso para o professor
        $group->post('/escola', 'EscolaController:post'); ///Insere uma escola no sistema
        $group->post('/fase', 'FaseController:post'); ///Insere uma fase no sistema
    })->add(\PsrJwt\Factory\JwtMiddleware::json($container->get('JwtSecretsMap')["Administrador"], 'jwt', ['Errors' => ['Authorisation Failed']]));

    $App->get('/escola', 'EscolaController:get'); ///Mostra as escolas cadastradas
    $App->get('/fases', 'FaseController:get'); ///Mostra as fases cadastradas

    $App->group('/elementos', function (RouteCollectorProxy $group) {
        $group->get('/get/{sigla}', 'ElementosTabelaPeriodicaController:get'); ///Busca um elemento periodico
        $group->get('/filtered', 'ElementosTabelaPeriodicaController:getFiltered'); ///Busca algo relacionado aos elementos
        $group->get('/names', 'ElementosTabelaPeriodicaController:getNames'); ///Pega todas siglas e nomes
        $group->get('', 'ElementosTabelaPeriodicaController:getAll'); ///Pega todos os elementos
    });
    
});

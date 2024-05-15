<?php

require_once __DIR__ . "/../../Controllers/RespostaController.php";
require_once __DIR__ . "/../Pojos/Pojo_Resposta.php";

class Dao_Resposta
{
    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new Dao_Resposta();
        return self::$instance;
    }

    public function Inserir(Pojo_Resposta $resposta)
    {
        try {
            $sql = "INSERT INTO `Resposta`
            (`escolha`,`data_hora`,`certo`,`quiz`,`aluno`)
            VALUES
            (:escolha,:data_hora,:certo,:quiz,:aluno);";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":escolha", $resposta->getEscolha());
            $p_sql->bindValue(":data_hora", $resposta->getData_hora());
            $p_sql->bindValue(":quiz", $resposta->getQuiz());
            $p_sql->bindValue(":aluno", $resposta->getAluno());
            $p_sql->bindValue(":certo", $resposta->isCerto());

            //return $p_sql->execute();

            #Retornar o id inserido
            $p_sql->execute();
            $p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Resposta.php -> Inserir", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Editar(Pojo_Resposta $resposta)
    {
        try {
            $sql = "UPDATE `Resposta`
            SET
            `escolha` = :escolha,
            `data_hora` = :data_hora,
            `quiz` = :quiz,
            `aluno` = :aluno 
            WHERE `id` = :id;";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":id", $resposta->getId());
            $p_sql->bindValue(":escolha", $resposta->getEscolha());
            $p_sql->bindValue(":data_hora", $resposta->getData_hora());
            $p_sql->bindValue(":aluno", $resposta->getAluno());

            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Resposta.php -> Editar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Deletar($id)
    {
        try {
            $sql = "DELETE FROM `Resposta` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Resposta.php -> Deletar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAll()
    {
        try {
            $sql = "SELECT `id`,`escolha`,`data_hora`,`certo`,`quiz`,`aluno` FROM `Resposta`;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $respostas = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($respostas, $this->rowToPojo($row));
            }
            return $respostas;
        } catch (Exception $e) {
            error_log(print_r("Dao_Resposta.php -> GetAll", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindById($id)
    {
        try {
            $sql = "SELECT `id`,`escolha`,`data_hora`,`certo`,`quiz`,`aluno` FROM `Resposta` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Resposta.php -> FindById", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetRegistrosPorQuiz(int $quiz_id)
    {
        try {
            $sql = "SELECT `id`,`escolha`,`data_hora`,`certo`,`quiz`,`aluno` 
            FROM `Resposta` 
            WHERE `quiz` = :quiz";

            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":quiz", $quiz_id);
            $p_sql->execute();

            $respostas = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($respostas, $this->rowToPojo($row));
            }
            return $respostas;
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno.php -> GetRegistrosPorQuiz", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetRegistrosSobreRespostasDoAluno($aluno, $professor = null)
    {
        try {

            $limitProfessor = "";

            //Se passar o id do professor ele busca apenas nas turmas do professor especificado
            if($professor != null) {
                $limitProfessor = "AND `turma` in 
                (
                    SELECT `id` 
                    FROM `Turma` 
                    WHERE `professor` = :professor
                ) ";
            }

            $sql = "SELECT `id`,`escolha`,`data_hora`,`certo`,`quiz`,`aluno` 
            FROM `Resposta` 
            WHERE `quiz` in (
                SELECT `id` FROM `Quiz` 
                WHERE `turma_fase` in (
                    SELECT `id` FROM `Turma_Fase` 
                    WHERE `turma` in (
                        SELECT `turma` 
                        FROM `Aluno_Turma` 
                        WHERE `aluno` = :aluno
                    ) " . $limitProfessor . "
                )
            ) 
            AND `aluno` = :aluno 
            ORDER BY `quiz`, `id`;";

            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":aluno", $aluno);

            if($professor != null) {
                $p_sql->bindValue(":professor", $professor);
            }

            $p_sql->execute();

            $respostas = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($respostas, $this->rowToPojo($row));
            }
            return $respostas;
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno.php -> GetRegistrosSobreRespostas", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    private function rowToPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_Resposta::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }
}

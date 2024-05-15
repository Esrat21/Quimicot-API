<?php

require_once __DIR__ . "/../../Controllers/QuizController.php";
require_once __DIR__ . "/../Pojos/Pojo_Quiz.php";

class Dao_Quiz
{
    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new Dao_Quiz();
        return self::$instance;
    }

    public function Inserir(Pojo_Quiz $quiz)
    {
        try {
            $sql = "INSERT INTO `Quiz`
            (`turma_fase`,`pergunta`,`contem`)
            VALUES
            (:turma_fase,:pergunta,:contem);";

            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":turma_fase", $quiz->getTurma_Fase());
            $p_sql->bindValue(":pergunta", $quiz->getPergunta());
            $p_sql->bindValue(":contem", json_encode($quiz->getContem()));

            //return $p_sql->execute();

            #Retornar o id inserido
            $p_sql->execute();
            $p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Quiz.php -> Inserir", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Editar(Pojo_Quiz $quiz)
    {
        try {
            $sql = "UPDATE `Quiz`
            SET
            `turma_fase` = :turma_fase,
            `pergunta` = :pergunta,
            `contem` = :contem
            WHERE `id` = :id;";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":id", $quiz->getId());
            $p_sql->bindValue(":turma_fase", $quiz->getTurma_Fase());
            $p_sql->bindValue(":pergunta", $quiz->getPergunta());
            $p_sql->bindValue(":contem", json_encode($quiz->getContem()));

            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Quiz.php -> Editar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Deletar($id)
    {
        try {
            $sql = "DELETE FROM `Quiz` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Quiz.php -> Deletar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAll()
    {
        try {
            $sql = "SELECT `id`,`turma_fase`,`pergunta`,`contem` FROM `Quiz`;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $alunos = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($alunos, $this->rowToPojo($row));
            }
            return $alunos;
        } catch (Exception $e) {
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindById($id)
    {
        try {
            $sql = "SELECT `id`,`turma_fase`,`pergunta`,`contem` FROM `Quiz` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Quiz.php -> FindById", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithId($id)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Quiz` WHERE `id` = :id) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Quiz.php -> hasWithId", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function QuizesDoTurma_Fase($id_turma_fase)
    {
        try {
            $sql = "SELECT `id`,`turma_fase`,`pergunta`,`contem` FROM `Quiz` WHERE `turma_fase` = :turma_fase;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":turma_fase", $id_turma_fase);
            $p_sql->execute();
            $arrPojos = [];
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($arrPojos, $this->rowToPojo($row));
            }
            return $arrPojos;
        } catch (Exception $e) {
            error_log(print_r("Dao_Quiz.php -> QuizesDoTurma_Fase", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function QuizesQuantidadeAndRespondidosByTfAluno($id_aluno, $id_turma_fase)
    {
        try {
            $sql = "SELECT COUNT(`Quiz`.`id`) as `qtd_quizes`, (SELECT COUNT(DISTINCT `Resposta`.`quiz`) FROM `Resposta`, `Quiz` WHERE `Resposta`.`aluno` = :aluno AND `Resposta`.`quiz` = `Quiz`.`id` AND `Quiz`.`turma_fase` = :turma_fase) as `qtd_respondidos` FROM `Quiz` WHERE `Quiz`.`turma_fase` = :turma_fase";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":aluno", $id_aluno);
            $p_sql->bindValue(":turma_fase", $id_turma_fase);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return [
                "qtd_quizes" => intval($row["qtd_quizes"]),
                "qtd_respondidos" => intval($row["qtd_respondidos"])
            ];
        } catch (Exception $e) {
            error_log(print_r("Dao_Quiz.php -> QuizesQuantidadeAndRespondidosByTfAluno", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function QuizPertenceAoProf(int $id_quiz, int $id_prof)
    {
        try {
            $sql = "SELECT COUNT(`id`) > 0 as `Pertence` FROM `Professor` WHERE `id` IN (
                SELECT `professor` FROM `Turma` WHERE `id` IN (
                    SELECT `turma` FROM `Turma_Fase` WHERE `id` IN (
                        SELECT `turma_fase` FROM `Quiz` WHERE `id` = :quiz
                    )
                )
            ) AND `id` = :professor;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":quiz", $id_quiz);
            $p_sql->bindValue(":professor", $id_prof);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["Pertence"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Quiz.php -> QuizPertenceAoProf", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetQtdQuizesPorTF(int $id_turma_fase)
    {
        try {
            $sql = "SELECT COUNT(`id`) as `qtd` FROM `Quiz` WHERE `turma_fase`=:turma_fase;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":turma_fase", $id_turma_fase);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["qtd"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Quiz.php -> GetQtdQuizesPorTF", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetQuizesPorProf(int $id_prof)
    {
        try {
            $sql = "SELECT `id`,`turma_fase`,`pergunta`,`contem` FROM `Quiz` WHERE `turma_fase` IN (
                SELECT `id` from `Turma_Fase` WHERE `turma` IN (
                    SELECT `id` FROM `Turma` WHERE `professor` = :professor
                )
            )";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":professor", $id_prof);
            $p_sql->execute();
            $arrPojos = [];
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($arrPojos, $this->rowToPojo($row));
            }
            return $arrPojos;
        } catch (Exception $e) {
            error_log(print_r("Dao_Quiz.php -> GetQtdQuizesPorTF", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    private function rowToPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_Quiz::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }
}

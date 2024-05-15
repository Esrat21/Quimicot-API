<?php

//require_once __DIR__ . "/../../Controllers/AlternativaController.php";
require_once __DIR__ . "/../Pojos/Pojo_Alternativa.php";

class Dao_Alternativa
{
    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Dao_Alternativa();
        }

        return self::$instance;
    }

    public function Inserir(Pojo_Alternativa $alternativa)
    {
        try {
            $sql = "INSERT INTO `Alternativa` (`quiz`, `alt_correta`, `descricao`, `justificativa`) VALUES (:quiz, :alt_correta, :descricao, :justificativa);";

            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":quiz", $alternativa->getQuiz());
            $p_sql->bindValue(":alt_correta", $alternativa->isAlt_correta());
            $p_sql->bindValue(":descricao", $alternativa->getDescricao());
            $p_sql->bindValue(":justificativa", $alternativa->getJustificativa());

            //return $p_sql->execute();

            #Retornar o id inserido
            $p_sql->execute();
            $p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Alternativa.php -> Inserir", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Editar(Pojo_Alternativa $alternativa)
    {
        try {
            $sql = "UPDATE `Alternativa`
            SET
            `quiz` = :quiz,
            `alt_correta` = :alt_correta,
            `descricao` = :descricao,
            `justificativa` = :justificativa
            WHERE `id` = :id;";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":id", $alternativa->getId());
            $p_sql->bindValue(":quiz", $alternativa->getQuiz());
            $p_sql->bindValue(":alt_correta", $alternativa->isAlt_correta());
            $p_sql->bindValue(":descricao", $alternativa->getDescricao());
            $p_sql->bindValue(":justificativa", $alternativa->getJustificativa());

            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Alternativa.php -> Editar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Deletar($id)
    {
        try {
            $sql = "DELETE FROM `Alternativa` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Alternativa.php -> Deletar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAll()
    {
        try {
            $sql = "SELECT `id`, `quiz`, `alt_correta`, `descricao`, `justificativa` FROM `Alternativa`;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $alunos = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($alunos, $this->rowToPojo($row));
            }
            return $alunos;
        } catch (Exception $e) {
            error_log(print_r("Dao_Alternativa.php -> GetAll", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindById($id)
    {
        try {
            $sql = "SELECT `id`, `quiz`, `alt_correta`, `descricao`, `justificativa` FROM `Alternativa` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Alternativa.php -> FindById", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindAllByQuiz($quizId)
    {
        try {
            $sql = "SELECT `id`, `quiz`, `alt_correta`, `descricao`, `justificativa` FROM `Alternativa` WHERE `quiz` = :quizId;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":quizId", $quizId);
            $p_sql->execute();
            $alternativas = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($alternativas, $this->rowToPojo($row));
            }
            return $alternativas;
        } catch (Exception $e) {
            error_log(print_r("Dao_Alternativa.php -> FindAllByQuiz", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    private function rowToPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_Alternativa::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }
}

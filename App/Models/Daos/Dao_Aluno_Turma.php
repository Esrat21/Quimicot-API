<?php

require_once __DIR__ . "/../../Controllers/Aluno_TurmaController.php";
require_once __DIR__ . "/../Pojos/Pojo_Aluno_Turma.php";

class Dao_Aluno_Turma
{
    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Dao_Aluno_Turma();
        }

        return self::$instance;
    }

    public function Inserir(Pojo_Aluno_Turma $aluno_turma)
    {
        try {
            $sql = "INSERT INTO `Aluno_Turma`
            (`aluno`,`turma`,`dados_aluno`,`dados_turma`)
            VALUES
            (:aluno,:turma,:dados_aluno,:dados_turma);";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":aluno", $aluno_turma->getAluno());
            $p_sql->bindValue(":turma", $aluno_turma->getTurma());
            $p_sql->bindValue(":dados_aluno", $aluno_turma->getDados_aluno());
            $p_sql->bindValue(":dados_turma", $aluno_turma->getDados_turma());

            //return $p_sql->execute();

            #Retornar o id inserido
            $p_sql->execute();
            $p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno_Turma.php -> Inserir", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Editar(Pojo_Aluno_Turma $aluno_turma)
    {
        try {
            $sql = "UPDATE `Aluno_Turma`
            SET
            `aluno` = :aluno,
            `turma` = :turma,
            `dados_aluno` = :dados_aluno,
            `dados_turma` = :dados_turma
            WHERE `id` = :id;";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":id", $aluno_turma->getId());
            $p_sql->bindValue(":aluno", $aluno_turma->getAluno());
            $p_sql->bindValue(":turma", $aluno_turma->getTurma());
            $p_sql->bindValue(":dados_aluno", $aluno_turma->getDados_aluno());
            $p_sql->bindValue(":dados_turma", $aluno_turma->getDados_turma());

            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno_Turma.php -> Editar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Deletar($id)
    {
        try {
            $sql = "DELETE FROM `Aluno_Turma` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno_Turma.php -> Deletar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAll()
    {
        try {
            $sql = "SELECT `id`,
            `aluno`,
            `turma`,
            `dados_aluno`,
            `dados_turma`
            FROM `Aluno_Turma`;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $alunos = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($alunos, $this->rowToPojo($row));
            }
            return $alunos;
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno_Turma.php -> GetAll", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindById($id)
    {
        try {
            $sql = "SELECT `id`,
            `aluno`,
            `turma`,
            `dados_aluno`,
            `dados_turma`
            FROM `Aluno_Turma`
            WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno_Turma.php -> FindById", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindByAluno($id_aluno)
    {
        try {
            $sql = "SELECT `id`,
            `aluno`,
            `turma`,
            `dados_aluno`,
            `dados_turma`
            FROM `Aluno_Turma`
            WHERE `aluno` = :id_aluno;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id_aluno", $id_aluno);
            $p_sql->execute();
            $aluno_turma = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($aluno_turma, $this->rowToPojo($row));
            }
            return $aluno_turma;
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno_Turma.php -> FindByAluno", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindTurmasByAluno($id_aluno)
    {
        try {
            $sql = "SELECT `turma`
            FROM `Aluno_Turma`
            WHERE `aluno` = :id_aluno;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id_aluno", $id_aluno);
            $p_sql->execute();
            $turmas = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($turmas, $row["turma"]);
            }
            return $turmas;
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno_Turma.php -> FindTurmasByAluno", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetTurmasByAluno($id_aluno)
    {
        try {
            //__TODO__
            $sql = "SELECT
            `Aluno_Turma`.`id` as `Aluno_Turma_id`,
            `Turma`.`id` as `Turma_id`,
            `Turma`.`nome` as `Turma_nome`,
            `Turma`.`ano` as `Turma_ano`,
            `Escola`.`id` as `Escola_id`,
            `Escola`.`nome` as `Escola_nome`,
            `Professor`.`id` as `Professor_id`,
            `Professor`.`nome` as `Professor_nome`,
            `Professor`.`email` as `Professor_email`
            FROM
            `Turma`
            INNER JOIN `Aluno_Turma`
            ON `Turma`.`id` = `Aluno_Turma`.`turma`
            INNER JOIN `Escola`
            ON `Escola`.`id` = `Turma`.`escola`
            INNER JOIN `Professor`
            ON `Professor`.`id` = `Turma`.`professor`
            WHERE `Aluno_Turma`.`aluno` = :id_aluno
            ";

            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id_aluno", $id_aluno);
            $p_sql->execute();
            $turmas = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($turmas, $row);
            }
            return $turmas;
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno_Turma.php -> GetTurmasByAluno", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAlunosByTurma($id_turma)
    {
        try {
            $sql = "SELECT `id`,`nome`,`email` FROM `Aluno` WHERE `id` IN (SELECT `aluno` FROM `Aluno_Turma` WHERE `turma` = :id_turma)";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id_turma", $id_turma);
            $p_sql->execute();
            $alunos = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($alunos, $row);
            }
            return $alunos;
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno_Turma.php -> GetAlunosByTurma", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindByTurma($id_turma)
    {
        try {
            $sql = "SELECT `id`,
            `aluno`,
            `turma`,
            `dados_aluno`,
            `dados_turma`
            FROM `Aluno_Turma`
            WHERE `turma` = :id_turma;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id_turma", $id_turma);
            $p_sql->execute();
            $aluno_turma = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($aluno_turma, $this->rowToPojo($row));
            }
            return $aluno_turma;
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno_Turma.php -> FindByTurma", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithId($id)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `aluno_turma` WHERE `id` = :id) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno_Turma.php -> hasWithId", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasThisConn($id_aluno, $id_turma)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Aluno_Turma` WHERE `aluno` = :aluno AND `turma` = :turma) > 0 as res;";

            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":aluno", $id_aluno);
            $p_sql->bindValue(":turma", $id_turma);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno_Turma.php -> hasThisConn", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function getThisConn($id_aluno, $id_turma)
    {
        try {
            $sql = "SELECT `id`,`aluno`,`turma`,`dados_aluno`,`dados_turma`
            FROM `Aluno_Turma`
            WHERE `aluno` = :aluno AND `turma` = :turma";

            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":aluno", $id_aluno);
            $p_sql->bindValue(":turma", $id_turma);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno_Turma.php -> getThisConn", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    private function rowToPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_Aluno_Turma::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }
}

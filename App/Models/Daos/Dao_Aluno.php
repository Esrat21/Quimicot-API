<?php

require_once __DIR__ . "/../../Controllers/AlunoController.php";
require_once __DIR__ . "/../Pojos/Pojo_Aluno.php";

class Dao_Aluno
{
    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Dao_Aluno();
        }

        return self::$instance;
    }

    public function Inserir(Pojo_Aluno $aluno)
    {
        try {
            $sql = "INSERT INTO `Aluno`
            (`nome`,`senha`,`email`)
            VALUES
            (:nome,:senha,:email);";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":nome", $aluno->getNome());
            $p_sql->bindValue(":senha", $aluno->getSenha());
            $p_sql->bindValue(":email", $aluno->getEmail());

            //return $p_sql->execute();

            #Retornar o id inserido
            $p_sql->execute();
            $p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno.php -> Inserir", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Editar(Pojo_Aluno $aluno)
    {
        try {
            $sql = "UPDATE `Aluno`
            SET
            `nome` = :nome,
            `senha` = :senha,
            `email` = :email
            WHERE `id` = :id;";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":id", $aluno->getId());
            $p_sql->bindValue(":nome", $aluno->getNome());
            $p_sql->bindValue(":senha", $aluno->getSenha());
            $p_sql->bindValue(":email", $aluno->getEmail());

            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno.php -> Editar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Deletar($id)
    {
        try {
            $sql = "DELETE FROM `Aluno` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno.php -> Deletar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAll()
    {
        try {
            $sql = "SELECT `id`,
            `nome`,
            `senha`,
            `email`
            FROM `Aluno`";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $alunos = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($alunos, $this->rowToPojo($row));
            }
            return $alunos;
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno.php -> GetAll", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithEmail($email)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Aluno` WHERE `email` = :email) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":email", $email);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno.php -> hasWithEmail", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithId($id)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Aluno` WHERE `id` = :id) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno.php -> hasWithId", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindById($id)
    {
        try {
            $sql = "SELECT `id`,
            `nome`,
            `senha`,
            `email`
            FROM `Aluno`
            WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno.php -> FindById", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAVGTimePerFase($aluno, $turma_fase)
    {
        try {
            $sql = "SELECT AVG(TIMESTAMPDIFF(SECOND, `comeco`, `fim`)) AS Tempo_Gasto
            FROM `Log`
            WHERE `tipo` = :tipo
            AND `turma_fase` = :turma_fase
            AND `aluno` = :aluno;";

            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":tipo", 'fim da fase');
            $p_sql->bindValue(":turma_fase", $turma_fase);
            $p_sql->bindValue(":aluno", $aluno);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno.php -> GetAVGTimePerFase", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Login($email, $senha)
    {
        try {
            $sql = "SELECT `id`,
            `nome`,
            `email`,
            `senha`
            FROM `Aluno`
            WHERE `email` = :email";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":email", addslashes($email));
            $p_sql->execute();
            $aluno = $p_sql->fetch(PDO::FETCH_ASSOC);
            if ($aluno == null || !isset($aluno)) {
                return false;
            }
            $senhaCerta = password_verify($senha, $aluno["senha"]);
            if (!$senhaCerta) {
                return false;
            }
            return $this->rowToPojo($aluno);
        } catch (Exception $e) {
            error_log(print_r("Dao_Aluno.php -> Login", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetQtdJogadasByAluno($aluno, $turma)
    {
        try {
            $sql = "SELECT `Turma_Fase`.`id` as `TurmaFase`, COUNT(`Log`.`id`) as `Vezes` FROM `Log`, `Turma_Fase`, `Fase` WHERE `Log`.`turma_fase` = `Turma_Fase`.`id` AND `Turma_Fase`.`fase` = `Fase`.`id` AND `aluno` = :aluno AND `Turma_Fase`.`turma` = :turma";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":aluno", $aluno);
            $p_sql->bindValue(":turma", $turma);
            $p_sql->execute();
            $fases = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                $row["TurmaFase"] = intval($row["TurmaFase"]);
                $row["Vezes"] = intval($row["Vezes"]);
                array_push($fases, $row);
            }
            return $fases;
        } catch (Exception $e) {
            error_log($e);
            error_log(print_r("Dao_Aluno.php -> GetQtdJogadasByAluno", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    private function rowToPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_Aluno::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }
}

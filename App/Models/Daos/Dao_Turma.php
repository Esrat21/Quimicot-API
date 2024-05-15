<?php

require_once __DIR__ . "/../../Controllers/TurmaController.php";
require_once __DIR__ . "/../Pojos/Pojo_Turma.php";

class Dao_Turma
{
    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new Dao_Turma();
        return self::$instance;
    }

    public function Inserir(Pojo_Turma $turma)
    {
        try {
            $sql = "INSERT INTO `Turma`
            (`nome`,`escola`,`ano`,`professor`,`senha`)
            VALUES
            (:nome,:escola,:ano,:professor,:senha);";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":nome", $turma->getNome());
            $p_sql->bindValue(":ano", $turma->getAno());
            $p_sql->bindValue(":escola", $turma->getEscola());
            $p_sql->bindValue(":professor", $turma->getProfessor());
            $p_sql->bindValue(":professor", $turma->getProfessor());
            $p_sql->bindValue(":senha", password_hash($turma->getSenha(), PASSWORD_DEFAULT));

            //return $p_sql->execute();

            #Retornar o id inserido
            $p_sql->execute();
            $p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma.php -> Inserir", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Editar(Pojo_Turma $turma)
    {
        try {
            $sql = "UPDATE `Turma`
            SET
            `nome` = :nome,
            `escola` = :escola,
            `ano` = :ano,
            `professor` = :professor,
            `senha` = :senha
            WHERE `id` = :id;";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":id", $turma->getId());
            $p_sql->bindValue(":nome", $turma->getNome());
            $p_sql->bindValue(":ano", $turma->getAno());
            $p_sql->bindValue(":escola", $turma->getEscola());
            $p_sql->bindValue(":professor", $turma->getProfessor());
            $p_sql->bindValue(":senha", password_hash($turma->getSenha(), PASSWORD_DEFAULT));

            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma.php -> Editar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Deletar($id)
    {
        try {
            $sql = "DELETE FROM `Turma` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma.php -> Deletar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAll()
    {
        try {
            $sql = "SELECT `id`,`nome`,`ano`,`escola`,`professor` FROM `Turma`;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $alunos = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($alunos, $this->rowToPojo($row));
            }
            return $alunos;
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma.php -> GetAll", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindById($id)
    {
        try {
            $sql = "SELECT `id`,`nome`,`ano`,`escola`,`professor` FROM `Turma` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma.php -> FindById", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithId($id)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Turma` WHERE `id` = :id) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma.php -> hasWithId", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAllByProfessor($professor)
    {
        try {
            $sql = "SELECT `id`,`nome`,`ano`,`escola`,`professor` FROM `Turma` WHERE `professor` = :professor;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":professor", $professor);
            $p_sql->execute();
            $alunos = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($alunos, $this->rowToPojo($row));
            }
            return $alunos;
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma.php -> GetAllByProfessor", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function TurmaPertenceAoProf($id_turma, $id_professor)
    {
        try {
            $sql = "SELECT COUNT(`id`) > 0 as `Pertence` FROM `Turma` WHERE `id` = :turma AND `professor` = :professor;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":turma", $id_turma);
            $p_sql->bindValue(":professor", $id_professor);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["Pertence"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma.php -> TurmaPertenceAoProf", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAllByEscola($escola)
    {
        try {
            $sql = "SELECT `id`,`nome`,`ano`,`escola`,`professor` FROM `Turma` WHERE `escola` = :escola;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":escola", $escola);
            $p_sql->execute();
            $alunos = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($alunos, $this->rowToPojo($row));
            }
            return $alunos;
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma.php -> GetAllByEscola", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function VerificarSenha($id_turma, $senha)
    {
        try {
            $sql = "SELECT `id`,
            `nome`,
            `ano`,
            `escola`,
            `professor`,
            `senha` 
            FROM `Turma`
            WHERE `id` = :id";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id_turma);
            $p_sql->execute();
            $turma = $p_sql->fetch(PDO::FETCH_ASSOC);
            if ($turma == null || !isset($turma)) {
                return false;
            }
            $senhaCerta = password_verify($senha, $turma["senha"]);
            if(!$senhaCerta) {
                return false;
            }
            return $this->rowToPojo($turma);
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma.php -> VerificarSenha", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    private function rowToPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_Turma::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }
}

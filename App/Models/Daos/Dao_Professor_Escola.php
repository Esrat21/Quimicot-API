<?php

//require_once __DIR__ . "/../../Controllers/Professor_EscolaController.php";
require_once __DIR__ . "/../Pojos/Pojo_Professor_Escola.php";

class Dao_Professor_Escola
{
    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new Dao_Professor_Escola();
        return self::$instance;
    }

    public function Inserir(Pojo_Professor_Escola $Professor_Escola)
    {
        try {
            $sql = "INSERT INTO `Professor_Escola`
            (`escola`, `professor`)
            VALUES
            (:escola, :professor);";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":escola", $Professor_Escola->getEscola());
            $p_sql->bindValue(":professor", $Professor_Escola->getProfessor());

            //return $p_sql->execute();

            #Retornar o id inserido
            $p_sql->execute();
            $p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor_Escola.php -> Inserir", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Editar(Pojo_Professor_Escola $Professor_Escola)
    {
        try {
            $sql = "UPDATE `Professor_Escola`
            SET `escola` = :escola, `professor` = :professor
            WHERE `id` = :id;";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":id", $Professor_Escola->getId());
            $p_sql->bindValue(":escola", $Professor_Escola->getEscola());
            $p_sql->bindValue(":professor", $Professor_Escola->getProfessor());

            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor_Escola.php -> Editar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Deletar($id)
    {
        try {
            $sql = "DELETE FROM `Professor_Escola` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor_Escola.php -> Deletar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAll()
    {
        try {
            $sql = "SELECT `id`,`escola`,`professor` FROM `Professor_Escola`;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $prof_escola = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($prof_escola, $this->rowToPojo($row));
            }
            return $prof_escola;
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor_Escola.php -> GetAll", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindById($id)
    {
        try {
            $sql = "SELECT `id`,`escola`,`professor` FROM `Professor_Escola` WHERE `id` = :id LIMIT 1";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor_Escola.php -> FindById", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithId($id)
    {
        try {
            $sql = "SELECT COUNT(*) > 0 as res FROM `Professor_Escola` WHERE `id` = :id LIMIT 1";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor_Escola.php -> hasWithId", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function isProfOfEscola($prof, $esc)
    {
        try {
            $sql = "SELECT COUNT(*) > 0 as res FROM `Professor_Escola` WHERE `escola` = :esc AND `professor` = :prof LIMIT 1";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":esc", $esc);
            $p_sql->bindValue(":prof", $prof);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor_Escola.php -> isProfOfEscola", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }


    public function getEscolasDoProf($prof)
    {
        try {
            $sql = "SELECT `Escola`.`id`, `Escola`.`nome` FROM `Escola`
            INNER JOIN `Professor_Escola` ON `Escola`.`id` = `Professor_Escola`.`escola`
            WHERE `Professor_Escola`.`professor` = :prof";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":prof", $prof);
            $p_sql->execute();
            $prof_escola = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($prof_escola, $this->rowToEscolaPojo($row));
            }
            return $prof_escola;
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor_Escola.php -> isProfOfEscola", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    private function rowToEscolaPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_Escola::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }

    private function rowToPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_Professor_Escola::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }
}

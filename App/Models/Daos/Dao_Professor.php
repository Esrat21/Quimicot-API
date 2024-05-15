<?php

require_once __DIR__ . "/../../Controllers/ProfessorController.php";
require_once __DIR__ . "/../Pojos/Pojo_Professor.php";

class Dao_Professor
{
    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Dao_Professor();
        }

        return self::$instance;
    }

    public function Inserir(Pojo_Professor $professor)
    {
        try {
            $sql = "INSERT INTO `Professor`
            (`nome`,`email`,`cpf`,`telefone`,`senha`,`cad_pendente`)
            VALUES
            (:nome,:email,:cpf,:telefone,:senha,:cad_pendente);";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":nome", $professor->getNome());
            $p_sql->bindValue(":email", $professor->getEmail());
            $p_sql->bindValue(":cpf", $professor->getCpf());
            $p_sql->bindValue(":telefone", $professor->getTelefone());
            $p_sql->bindValue(":senha", $professor->getSenha());
            $p_sql->bindValue(":cad_pendente", $professor->getCad_pendente());

            //return $p_sql->execute();

            #Retornar o id inserido
            $p_sql->execute();
            $p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor.php -> Inserir", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Editar(Pojo_Professor $professor)
    {
        try {
            $sql = "UPDATE `Professor`
            SET
            `nome` = :nome,
            `email` = :email,
            `cpf` = :cpf,
            `telefone` = :telefone,
            `senha` = :senha,
            `cad_pendente` = :cad_pendente
            WHERE `id` = :id;";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":id", $professor->getId());
            $p_sql->bindValue(":nome", $professor->getNome());
            $p_sql->bindValue(":email", $professor->getEmail());
            $p_sql->bindValue(":cpf", $professor->getCpf());
            $p_sql->bindValue(":telefone", $professor->getTelefone());
            $p_sql->bindValue(":senha", $professor->getSenha());
            $p_sql->bindValue(":cad_pendente", $professor->getCad_pendente());

            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor.php -> Editar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Deletar($id)
    {
        try {
            $sql = "DELETE FROM `Professor` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor.php -> Deletar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAll()
    {
        try {
            $sql = "SELECT `id`,
            `nome`,
            `email`,
            `cpf`,
            `telefone`,
            `cad_pendente`
            FROM `Professor`";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $professores = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($professores, $this->rowToPojo($row));
            }
            return $professores;
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor.php -> GetAll", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithEmail($email)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Professor` WHERE `email` = :email) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":email", $email);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor.php -> hasWithEmail", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithCpf($cpf)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Professor` WHERE `cpf` = :cpf) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":cpf", $cpf);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor.php -> hasWithCpf", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithId($id)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Professor` WHERE `id` = :id) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor.php -> hasWithId", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindById($id)
    {
        try {
            $sql = "SELECT `id`,
            `nome`,
            `email`,
            `cpf`,
            `telefone`,
            `senha`,
            `cad_pendente`
            FROM `Professor`
            WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor.php -> FindById", 1));
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
            `cpf`,
            `telefone`,
            `cad_pendente`,
            `senha` 
            FROM `Professor`
            WHERE `email` = :email";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":email", addslashes($email));
            $p_sql->execute();
            $prof = $p_sql->fetch(PDO::FETCH_ASSOC);
            if ($prof == null || !isset($prof)) {
                return false;
            }
            $senhaCerta = password_verify($senha, $prof["senha"]);
            if(!$senhaCerta) {
                return false;
            }
            return $this->rowToPojo($prof);
        } catch (Exception $e) {
            error_log(print_r("Dao_Professor.php -> Login", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    private function rowToPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_Professor::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }
}

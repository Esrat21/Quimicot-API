<?php

require_once __DIR__ . "/../../Controllers/EscolaController.php";
require_once __DIR__ . "/../Pojos/Pojo_Escola.php";

class Dao_Escola
{
    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new Dao_Escola();
        return self::$instance;
    }

    public function Inserir(Pojo_Escola $escola)
    {
        try {
            $sql = "INSERT INTO `Escola` (`nome`) VALUES (:nome);";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":nome", $escola->getNome());

            //return $p_sql->execute();

            #Retornar o id inserido
            $p_sql->execute();
            $p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Escola.php -> Inserir", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Editar(Pojo_Escola $escola)
    {
        try {
            $sql = "UPDATE `Escola`
            SET `nome` = :nome
            WHERE `id` = :id;";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":id", $escola->getId());
            $p_sql->bindValue(":nome", $escola->getNome());

            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Escola.php -> Editar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Deletar($id)
    {
        try {
            $sql = "DELETE FROM `Escola` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Escola.php -> Deletar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAll()
    {
        try {
            $sql = "SELECT `id`,`nome` FROM `Escola`;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $alunos = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($alunos, $this->rowToPojo($row));
            }
            return $alunos;
        } catch (Exception $e) {
            error_log(print_r("Dao_Escola.php -> GetAll", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindById($id)
    {
        try {
            $sql = "SELECT `id`,`nome` FROM `Escola` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Escola.php -> FindById", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithId($id)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Escola` WHERE `id` = :id) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Escola.php -> hasWithId", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function existsNome($nome)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Escola` WHERE `nome` = :nome) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":nome", $nome);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Escola.php -> existsNome", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    private function rowToPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_Escola::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }
}

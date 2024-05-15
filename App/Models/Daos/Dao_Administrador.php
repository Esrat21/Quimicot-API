<?php

require_once __DIR__ . "/../../Controllers/AdministradorController.php";
require_once __DIR__ . "/../Pojos/Pojo_Administrador.php";

class Dao_Administrador
{
    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Dao_Administrador();
        }

        return self::$instance;
    }

    //Não é ideal permitir a inserção de administrador via API,
    //Porém o código ta pronto aqui
    /*
    public function Inserir(Pojo_Administrador $administrador)
    {
        try {
            $sql = "INSERT INTO `Administrador`
            (`nome`,`email`,`cpf`,`telefone`,`senha`,`tipo`)
            VALUES
            (:nome,:email,:cpf,:telefone,:senha,:tipo);";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":nome", $administrador->getNome());
            $p_sql->bindValue(":email", $administrador->getEmail());
            $p_sql->bindValue(":cpf", $administrador->getCpf());
            $p_sql->bindValue(":telefone", $administrador->getTelefone());
            $p_sql->bindValue(":senha", $administrador->getSenha());
            $p_sql->bindValue(":tipo", $administrador->getTipo());

            #Retornar o id inserido
            $p_sql->execute();
            $p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Administrador.php -> Inserir", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }
    */

    public function Editar(Pojo_Administrador $administrador)
    {
        try {
            $sql = "UPDATE `Administrador`
            SET
            `nome` = :nome,
            `email` = :email,
            `cpf` = :cpf,
            `telefone` = :telefone,
            `senha` = :senha,
            `tipo` = :tipo
            WHERE `id` = :id;";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":id", $administrador->getId());
            $p_sql->bindValue(":nome", $administrador->getNome());
            $p_sql->bindValue(":email", $administrador->getEmail());
            $p_sql->bindValue(":cpf", $administrador->getCpf());
            $p_sql->bindValue(":telefone", $administrador->getTelefone());
            $p_sql->bindValue(":senha", $administrador->getSenha());
            $p_sql->bindValue(":tipo", $administrador->getTipo());

            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Administrador.php -> Editar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    //Não é ideal permitir a remoção de administrador via API,
    //Porém o código ta pronto aqui
    /*
    public function Deletar($id)
    {
        try {
            $sql = "DELETE FROM `Administrador` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Administrador.php -> Deletar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }
    */

    //Não vi utilidade em listar todos adiministradores,
    //Porém o código ta pronto aqui
    /*
    public function GetAll()
    {
        try {
            $sql = "SELECT `id`,
            `nome`,
            `email`,
            `cpf`,
            `telefone`,
            `tipo`
            FROM `Administrador`";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $administradores = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($administradores, $this->rowToPojo($row));
            }
            return $administradores;
        } catch (Exception $e) {
            error_log(print_r("Dao_Administrador.php -> GetAll", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }
    */

    public function hasWithEmail($email)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Administrador` WHERE `email` = :email) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":email", $email);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Administrador.php -> hasWithEmail", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithCpf($cpf)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Administrador` WHERE `cpf` = :cpf) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":cpf", $cpf);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Administrador.php -> hasWithCpf", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithId($id)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Administrador` WHERE `id` = :id) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Administrador.php -> hasWithId", 1));
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
            `tipo`
            FROM `Administrador`
            WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Administrador.php -> FindById", 1));
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
            `tipo`,
            `senha` 
            FROM `Administrador`
            WHERE `email` = :email";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":email", addslashes($email));
            $p_sql->execute();
            $adm = $p_sql->fetch(PDO::FETCH_ASSOC);
            if ($adm == null || !isset($adm)) {
                return false;
            }
            $senhaCerta = password_verify($senha, $adm["senha"]);
            if(!$senhaCerta) {
                return false;
            }
            return $this->rowToPojo($adm);
        } catch (Exception $e) {
            error_log(print_r("Dao_Administrador.php -> Login", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    private function rowToPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_Administrador::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }
}

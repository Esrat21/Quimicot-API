<?php

require_once __DIR__ . "/../../Controllers/LogController.php";
require_once __DIR__ . "/../Pojos/Pojo_Log.php";

class Dao_Log
{
    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Dao_Log();
        }

        return self::$instance;
    }

    public function Inserir(Pojo_Log $log)
    {
        try {
            $sql = "INSERT INTO `Log`
            (`aluno`,`turma_fase`,`detalhes`,`objeto`,`tipo`,`comeco`,`fim`)
            VALUES
            (:aluno,:turma_fase,:detalhes,:objeto,:tipo,:comeco,:fim);";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":aluno", $log->getAluno());
            $p_sql->bindValue(":turma_fase", $log->getTurma_fase());
            $p_sql->bindValue(":detalhes", $log->getDetalhes());
            $p_sql->bindValue(":objeto", $log->getObjeto());
            $p_sql->bindValue(":tipo", $log->getTipo());
            $p_sql->bindValue(":comeco", $log->getComeco());
            $p_sql->bindValue(":fim", $log->getFim());

            //return $p_sql->execute();

            #Retornar o id inserido
            $p_sql->execute();
            $p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Log.php -> Inserir", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Editar(Pojo_Log $log)
    {
        try {
            $sql = "UPDATE `Log`
            SET
            `aluno` = :aluno,
            `turma_fase` = :turma_fase,
            `detalhes` = :detalhes,
            `objeto` = :objeto,
            `tipo` = :tipo,
            `comeco` = :comeco,
            `fim` = :fim>
            WHERE `id` = :id;";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":id", $log->getId());
            $p_sql->bindValue(":turma_fase", $log->getTurma_fase());
            $p_sql->bindValue(":detalhes", $log->getDetalhes());
            $p_sql->bindValue(":objeto", $log->getObjeto());
            $p_sql->bindValue(":tipo", $log->getTipo());
            $p_sql->bindValue(":comeco", $log->getComeco());
            $p_sql->bindValue(":fim", $log->getFim());

            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Log.php -> Editar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Deletar($id)
    {
        try {
            $sql = "DELETE FROM `Log` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Log.php -> Deletar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAll()
    {
        try {
            $sql = "SELECT `id`,`aluno`,`turma_fase`,`detalhes`,`objeto`,`tipo`,`comeco`,`fim` FROM `Log`;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $logs = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($logs, $this->rowToPojo($row));
            }
            return $logs;
        } catch (Exception $e) {
            error_log(print_r("Dao_Log.php -> GetAll", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindById($id)
    {
        try {
            $sql = "SELECT `id`,`aluno`,`turma_fase`,`detalhes`,`objeto`,`tipo`,`comeco`,`fim`
            FROM `Log` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Log.php -> FindById", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithId($id)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Log` WHERE `id` = :id) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Log.php -> hasWithId", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    private function rowToPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_Log::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }
}

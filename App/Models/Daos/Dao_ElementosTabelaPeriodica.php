<?php

require_once __DIR__ . "/../../Controllers/ElementosTabelaPeriodicaController.php";
require_once __DIR__ . "/../Pojos/Pojo_ElementosTabelaPeriodica.php";

class Dao_ElementosTabelaPeriodica
{
    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new Dao_ElementosTabelaPeriodica();
        return self::$instance;
    }

    public function Inserir(Pojo_ElementosTabelaPeriodica $elemento)
    {
        try {
            $sql = "INSERT INTO `ElementosTabelaPeriodica`
            (`sigla`,`objeto`)
            VALUES
            (:sigla,:objeto);";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":sigla", $elemento->getSigla());
            $p_sql->bindValue(":objeto", $elemento->getObjeto());

            //return $p_sql->execute();

            #Retornar o id inserido
            $p_sql->execute();
            $p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_ElementosTabelaPeriodica.php -> Inserir", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Editar(Pojo_ElementosTabelaPeriodica $elemento)
    {
        try {
            $sql = "UPDATE `ElementosTabelaPeriodica`
            SET
            `objeto` = :objeto
            WHERE `sigla` = :sigla;
            ";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":sigla", $elemento->getSigla());
            $p_sql->bindValue(":objeto", $elemento->getObjeto());

            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_ElementosTabelaPeriodica.php -> Editar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Deletar($sigla)
    {
        try {
            $sql = "DELETE FROM `ElementosTabelaPeriodica` WHERE `sigla` = :sigla;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":sigla", $sigla);
            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_ElementosTabelaPeriodica.php -> Deletar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAll()
    {
        try {
            $sql = "SELECT `sigla`, `objeto` FROM `ElementosTabelaPeriodica`;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $elementos = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($elementos, $this->rowToPojo($row));
            }
            return $elementos;
        } catch (Exception $e) {
            error_log(print_r("Dao_ElementosTabelaPeriodica.php -> GetAll", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAllNames()
    {
        try {
            $sql = "SELECT `sigla`, `objeto`->>\"\$.Nome\" as `nome` FROM `ElementosTabelaPeriodica`";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $elementos = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($elementos, ["sigla" => $row["sigla"], "nome" => $row["nome"]]);
            }
            return $elementos;
        } catch (Exception $e) {
            error_log(print_r("Dao_ElementosTabelaPeriodica.php -> GetAllNames", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindBySigla($sigla)
    {
        try {
            $sql = "SELECT `sigla`, `objeto` FROM `ElementosTabelaPeriodica` WHERE `sigla` = :sigla;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":sigla", $sigla);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_ElementosTabelaPeriodica.php -> FindBySigla", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithSigla($sigla)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `ElementosTabelaPeriodica` WHERE `sigla` = :sigla) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":sigla", $sigla);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_ElementosTabelaPeriodica.php -> hasWithSigla", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function getClassificacoes()
    {
        try {
            $sql = "SELECT DISTINCT `objeto`->>\"\$.Classificação\" as cl FROM `ElementosTabelaPeriodica`";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $classificacoes = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($classificacoes, $row["cl"]);
            }
            return $classificacoes;
        } catch (Exception $e) {
            error_log(print_r("Dao_ElementosTabelaPeriodica.php -> getClassificacoes", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function getGrupos()
    {
        try {
            $sql = 'SELECT DISTINCT `objeto`->>"$.Grupo.Nome" as gn FROM `ElementosTabelaPeriodica`';
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $grupos = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($grupos, $row["gn"]);
            }
            return $grupos;
        } catch (Exception $e) {
            error_log(print_r("Dao_ElementosTabelaPeriodica.php -> getGrupos", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    private function rowToPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_ElementosTabelaPeriodica::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }
}

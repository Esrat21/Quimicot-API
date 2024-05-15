<?php

require_once __DIR__ . "/../../Controllers/FaseController.php";
require_once __DIR__ . "/../Pojos/Pojo_Fase.php";

class Dao_Fase
{
    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new Dao_Fase();
        return self::$instance;
    }

    public function Inserir(Pojo_Fase $fase)
    {
        try {
            $sql = "INSERT INTO `Fase`
            (`nome`,`url`,`criador`,`dificuldade`,`tempo_medio_seg`,`contem`,`vars`)
            VALUES
            (:nome,:url,:criador,:dificuldade,:tempo_medio_seg,:contem,:vars);";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":nome", $fase->getNome());
            $p_sql->bindValue(":url", $fase->getUrl());
            $p_sql->bindValue(":criador", $fase->getCriador());
            $p_sql->bindValue(":dificuldade", $fase->getDificuldade());
            $p_sql->bindValue(":tempo_medio_seg", $fase->getTempo_medio_seg());
            $p_sql->bindValue(":contem", json_encode($fase->getContem()));
            $p_sql->bindValue(":vars", $fase->getVars());

            //return $p_sql->execute();

            #Retornar o id inserido
            $p_sql->execute();
            $p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Fase.php -> Inserir", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Editar(Pojo_Fase $fase)
    {
        try {
            $sql = "UPDATE `Fase`
            SET
            `nome` = :nome,
            `url` = :url,
            `criador` = :criador,
            `dificuldade` = :dificuldade,
            `tempo_medio_seg` = :tempo_medio_seg,
            `contem` = :contem,
            `vars` = :vars
            WHERE `id` = :id;";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":id", $fase->getId());
            $p_sql->bindValue(":nome", $fase->getNome());
            $p_sql->bindValue(":url", $fase->getUrl());
            $p_sql->bindValue(":criador", $fase->getCriador());
            $p_sql->bindValue(":dificuldade", $fase->getDificuldade());
            $p_sql->bindValue(":tempo_medio_seg", $fase->getTempo_medio_seg());
            $p_sql->bindValue(":contem", $fase->getContem());
            $p_sql->bindValue(":vars", $fase->getVars());

            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Fase.php -> Editar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Deletar($id)
    {
        try {
            $sql = "DELETE FROM `Fase` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Fase.php -> Deletar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAll()
    {
        try {
            $sql = "SELECT `id`,`nome`,`url`,`criador`,`dificuldade`,`tempo_medio_seg`,`contem`,`vars` FROM `Fase`;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $fases = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($fases, $this->rowToPojo($row));
            }
            return $fases;
        } catch (Exception $e) {
            error_log(print_r("Dao_Fase.php -> GetAll", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAllIntoArray($array)
    {
        try {
            $arrStr = implode(", ", $array);
            $sql = "SELECT `id`,`nome`,`url`,`criador`,`dificuldade`,`tempo_medio_seg`,`contem`,`vars` FROM `Fase` WHERE id IN('" . $arrStr . "');";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $fases = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($fases, $this->rowToPojo($row));
            }
            return $fases;
        } catch (Exception $e) {
            error_log(print_r("Dao_Fase.php -> GetAllIntoArray", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindById($id)
    {
        try {
            $sql = "SELECT `id`,`nome`,`url`,`criador`,`dificuldade`,`tempo_medio_seg`,`contem`,`vars` FROM `Fase` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Fase.php -> FindById", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithId($id)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Fase` WHERE `id` = :id) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Fase.php -> hasWithId", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    private function rowToPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_Fase::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }
}

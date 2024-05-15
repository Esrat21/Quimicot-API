<?php

require_once __DIR__ . "/../../Controllers/Turma_FaseController.php";
require_once __DIR__ . "/../Pojos/Pojo_Turma_Fase.php";

class Dao_Turma_Fase
{
    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new Dao_Turma_Fase();
        return self::$instance;
    }

    public function Inserir(Pojo_Turma_Fase $turma_Fase)
    {
        try {
            $sql = "INSERT INTO `Turma_Fase`
            (`turma`,`fase`)
            VALUES
            (:turma,:fase);";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":turma", $turma_Fase->getTurma());
            $p_sql->bindValue(":fase", $turma_Fase->getFase());

            //return $p_sql->execute();

            #Retornar o id inserido
            $p_sql->execute();
            $p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma_Fase.php -> Inserir", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Editar(Pojo_Turma_Fase $turma_Fase)
    {
        try {
            $sql = "UPDATE `Turma_Fase`
            SET
            `turma` = :turma,
            `fase` = :fase 
            WHERE `id` = :id;";

            $p_sql = Conexao::getInstance()->prepare($sql);

            $p_sql->bindValue(":id", $turma_Fase->getId());
            $p_sql->bindValue(":turma", $turma_Fase->getTurma());
            $p_sql->bindValue(":fase", $turma_Fase->getFase());

            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma_Fase.php -> Editar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function Deletar($id)
    {
        try {
            $sql = "DELETE FROM `Turma_Fase` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            return $p_sql->execute();
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma_Fase.php -> Deletar", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetAll()
    {
        try {
            $sql = "SELECT `id`,`turma`,`fase` FROM `Turma_Fase`;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->execute();
            $alunos = array();
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($alunos, $this->rowToPojo($row));
            }
            return $alunos;
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma_Fase.php -> GetAll", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindById($id)
    {
        try {
            $sql = "SELECT `id`,`turma`,`fase` FROM `Turma_Fase` WHERE `id` = :id;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma_Fase.php -> FindById", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindByTurma($id_turma)
    {
        try {
            $sql = "SELECT `id`,`turma`,`fase` FROM `Turma_Fase` WHERE `turma` = :turma;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":turma", $id_turma);
            $p_sql->execute();
            $ligacoes = [];
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($ligacoes, $this->rowToPojo($row));
            }
            return $ligacoes;
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma_Fase.php -> FindByTurma", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function GetFasesByTurma($id_turma, $elemento = null)
    {
        try {
            $sql = "SELECT `Turma_Fase`.`id` as `Turma_Fase_id`, `Fase`.`id` as `Fase_id`, `Fase`.`nome` as `Fase_nome`, `Fase`.`url` as `Fase_url`, `Fase`.`vars` as `Fase_vars`, `Fase`.`contem` as `Fase_contem` FROM `Turma_Fase`, `Fase` WHERE `Turma_Fase`.`turma` = :turma AND `Turma_Fase`.`fase` = `Fase`.`id`";
            if ($elemento !== null) {
                $elemento = preg_replace("/[^a-zA-Z]+/", "", $elemento);
                $sql .= " AND JSON_CONTAINS(JSON_EXTRACT(`Fase`.`contem`, '$.elementos'),'\"$elemento\"','$')";
            }
            $sql .= ";";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":turma", $id_turma);
            $p_sql->execute();
            $fases = [];
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                $row["Fase_contem"] = json_decode($row["Fase_contem"]);
                $row["Fase_id"] = intval($row["Fase_id"]);
                $row["Turma_Fase_id"] = intval($row["Turma_Fase_id"]);
                array_push($fases, $row);
            }
            return $fases;
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma_Fase.php -> GetFasesByTurma", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindFasesByTurma($id_turma)
    {
        try {
            $sql = "SELECT `fase` FROM `Turma_Fase` WHERE `turma` = :turma;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":turma", $id_turma);
            $p_sql->execute();
            $fases = [];
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($fases, $row["fase"]);
            }
            return $fases;
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma_Fase.php -> FindFasesByTurma", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function FindByFase($id_fase)
    {
        try {
            $sql = "SELECT `id`,`turma`,`fase` FROM `Turma_Fase` WHERE `fase` = :fase;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":fase", $id_fase);
            $p_sql->execute();
            return $this->rowToPojo($p_sql->fetch(PDO::FETCH_ASSOC));
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma_Fase.php -> FindByFase", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasWithId($id)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Turma_Fase` WHERE `id` = :id) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":id", $id);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma_Fase.php -> hasWithId", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function hasThisConn($id_turma, $id_fase)
    {
        try {
            $sql = "SELECT (SELECT COUNT(*) FROM `Turma_Fase` WHERE `turma` = :turma AND `fase` = :fase) > 0 as res;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":turma", $id_turma);
            $p_sql->bindValue(":fase", $id_fase);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["res"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma_Fase.php -> hasThisConn", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function getThisConn($id_turma, $id_fase)
    {
        try {
            $sql = "SELECT `id`,`turma`,`fase` FROM `Turma_Fase` WHERE `turma` = :turma AND `fase` = :fase";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":turma", $id_turma);
            $p_sql->bindValue(":fase", $id_fase);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $this->rowToPojo($row);
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma_Fase.php -> getThisConn", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function TurmaFasePertenceAoProf(int $id_turma_fase, int $id_prof)
    {
        try {
            $sql = "SELECT COUNT(`id`) > 0 as `Pertence` FROM `Professor` WHERE `id` IN (
                SELECT `professor` FROM `Turma` WHERE `id` IN (
                    SELECT `turma` FROM `Turma_Fase` WHERE `id` = :turma_fase
                )
            ) AND `id` = :professor;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":turma_fase", $id_turma_fase);
            $p_sql->bindValue(":professor", $id_prof);
            $p_sql->execute();
            $row = $p_sql->fetch(PDO::FETCH_ASSOC);
            return $row["Pertence"];
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma_Fase.php -> TurmaFasePertenceAoProf", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    public function getTurmas_Fases_Detalhados_Por_Prof($id_prof)
    {
        try {
            $sql = "SELECT `Turma_Fase`.`id` as `Turma_Fase_id`,
            `Turma`.`id` as `Turma_id`,
            `Turma`.`nome` as `Turma_nome`,
            `Turma`.`ano` as `Turma_ano`,
            `Turma`.`escola` as `Turma_escola`,
            `Turma_Fase`.`fase` as `Turma_Fase_fase` 
            FROM `Turma_Fase`,`Turma` 
            WHERE `Turma`.`id` = `Turma_Fase`.`turma`
            AND `Turma_Fase`.`turma` IN (SELECT `Turma`.`id` FROM `Turma` WHERE `Turma`.`professor` = :professor)
            ORDER BY `Turma_Fase_fase`;";
            $p_sql = Conexao::getInstance()->prepare($sql);
            $p_sql->bindValue(":professor", $id_prof);
            $p_sql->execute();
            $tfs = [];
            while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
                array_push($tfs, $row);
            }
            return $tfs;
        } catch (Exception $e) {
            error_log(print_r("Dao_Turma_Fase.php -> getTurmas_Fases_Detalhados_Por_Prof", 1));
            error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
        }
    }

    private function rowToPojo($row)
    {
        if (isset($row) && $row != null) {
            $pojo = Pojo_Turma_Fase::FromData($row);
            return $pojo;
        } else {
            return "{}";
        }
    }
}

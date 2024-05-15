<?php

declare(strict_types=1);

class Conexao
{

    public static $instance;

    private function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new PDO(
                'mysql:host=localhost;
                dbname=chemical',
                'root',
                '100senha',
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
            self::$instance->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
            self::$instance->setAttribute(
                PDO::ATTR_ORACLE_NULLS,
                PDO::NULL_EMPTY_STRING
            );
        }

        return self::$instance;
    }
}

function Inserir($sigla, $elemento)
{
    try {
        $sql = "INSERT INTO `ElementosTabelaPeriodica`
                    (`sigla`,`objeto`)
                    VALUES
                    (:sigla,:objeto);";
        $p_sql = Conexao::getInstance()->prepare($sql);
        $p_sql->bindValue(":sigla", $sigla);
        $p_sql->bindValue(":objeto", $elemento);
        $p_sql->execute();
        return true;
    } catch (Exception $e) {
        error_log(print_r("upJsons.php", true));
        error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), true));
        return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
    }
}

final class DB_Prepare_Test extends PHPUnit\Framework\TestCase
{
    public function test_clearDB(): void
    {
        $sql = "SELECT CONCAT('DROP TABLE IF EXISTS `', table_name, '`;') as `K`
        FROM information_schema.tables
        WHERE table_schema = 'chemical';";

        $p_sql = Conexao::getInstance()->prepare($sql);
        $p_sql->execute();
        $arr = [];
        while ($row = $p_sql->fetch(PDO::FETCH_ASSOC)) {
            array_push($arr, $row);
        }
        $sql = "SET FOREIGN_KEY_CHECKS = 0;";
        $p_sql = Conexao::getInstance()->prepare($sql);
        $p_sql->execute();
        foreach ($arr as $sql) {
            $p_sql = Conexao::getInstance()->prepare($sql["K"]);
            $p_sql->execute();
        }
        $sql = "SET FOREIGN_KEY_CHECKS = 1;";
        $p_sql = Conexao::getInstance()->prepare($sql);
        $this->assertEquals($p_sql->execute(), 1);
    }

    /**
     * @depends test_clearDB
     */
    public function test_createDB(): void
    {
        $sql = file_get_contents(__DIR__ . "/../../Docs/IC.sql");
        $p_sql = Conexao::getInstance()->prepare($sql);
        $this->assertEquals($p_sql->execute(), 1);
    }

    /**
     * @depends test_createDB
     */
    public function test_createAdmin(): void
    {
        $sql = 'INSERT INTO `chemical`.`administrador`
        (`id`,
        `nome`,
        `email`,
        `cpf`,
        `telefone`,
        `senha`,
        `tipo`)
        VALUES
        (1,
        "Administrador",
        "admin@email.br",
        "11122233345",
        "",
        "$2y$10$dXtMdAJbjPiwGAEJi03djuVwJuBtew8bOvaEbeuMd4QQ0TzTkywti",
        "DEV");';
        $p_sql = Conexao::getInstance()->prepare($sql);
        $this->assertEquals($p_sql->execute(), 1);
    }

    /**
     * @depends test_createDB
     */
    public function test_inflate_elementos(): void
    {
        $path = __DIR__ . "/../../JSONS_Elementos/";
        $temp_files = scandir($path);
        natsort($temp_files);

        foreach ($temp_files as $file) {
            if ($file != "." && $file != ".." && $file != basename(__FILE__)) {
                $contentString = file_get_contents($path . $file);
                $jsonObject = json_decode($contentString);
                $jsonSigla = substr($file, 0, -5);
                $jsonMinified = json_encode($jsonObject);

                $this->assertEquals(Inserir($jsonSigla, $jsonMinified), true);
            }
        }
        
    }
}

<?php
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

$path = "./JSONS_Elementos/";
$temp_files = scandir($path);
natsort($temp_files);

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

        //return $p_sql->execute();

        #Retornar o id inserido
        $p_sql->execute();
        /*$p_sql = Conexao::getInstance()->prepare("SELECT LAST_INSERT_ID() as ID;");
            $p_sql->execute();
            return ($p_sql->fetch(PDO::FETCH_ASSOC));*/
        return true;
    } catch (Exception $e) {
        error_log(print_r("upJsons.php", 1));
        error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
        return "Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage();
    }
}

foreach ($temp_files as $file) {
    if ($file != "." && $file != ".." && $file != basename(__FILE__)) {
        $contentString = file_get_contents($path . $file);
        $jsonObject = json_decode($contentString);
        $jsonSigla = substr($file, 0, -5);
        $jsonMinified = json_encode($jsonObject);

        Inserir($jsonSigla, $jsonMinified);
    }
}

echo "Sucesso";
<?php

class Pojo_Escola implements JsonSerializable
{

    private $id;
    private $nome;

    public static function FromData($data)
    {
        $instance = new self();
        if (isset($data)) {
            if (isset($data["id"])) {
                $instance->setId($data["id"]);
            }
            if (isset($data["nome"])) {
                $instance->setNome($data["nome"]);
            }
        }
        return $instance;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => intval($this->getId()),
                'nome' => $this->getNome()
            ];
    }
}

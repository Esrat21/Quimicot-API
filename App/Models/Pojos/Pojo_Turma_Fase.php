<?php

class Pojo_Turma_Fase implements JsonSerializable
{

    private $id;
    private $turma;
    private $fase;

    public static function FromData($data)
    {
        $instance = new self();
        if (isset($data)) {
            if (isset($data["id"])) {
                $instance->setId($data["id"]);
            }
            if (isset($data["turma"])) {
                $instance->setTurma($data["turma"]);
            }
            if (isset($data["fase"])) {
                $instance->setFase($data["fase"]);
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

    public function getTurma()
    {
        return $this->turma;
    }

    public function setTurma($turma)
    {
        $this->turma = $turma;
    }

    public function getFase()
    {
        return $this->fase;
    }

    public function setFase($fase)
    {
        $this->fase = $fase;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => intval($this->getId()),
                'turma' => $this->getTurma(),
                'fase' => $this->getFase()
            ];
    }
}

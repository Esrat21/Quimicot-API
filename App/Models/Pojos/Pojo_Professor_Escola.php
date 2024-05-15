<?php

class Pojo_Professor_Escola implements JsonSerializable
{

    private $id;
    private $escola;
    private $professor;

    public static function FromData($data)
    {
        $instance = new self();
        if (isset($data)) {
            if (isset($data["id"])) {
                $instance->setId($data["id"]);
            }
            if (isset($data["escola"])) {
                $instance->setEscola($data["escola"]);
            }
            if (isset($data["professor"])) {
                $instance->setProfessor($data["professor"]);
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

    public function getEscola()
    {
        return $this->escola;
    }

    public function setEscola($escola)
    {
        $this->escola = $escola;
    }
    
    public function getProfessor()
    {
        return $this->professor;
    }

    public function setProfessor($professor)
    {
        $this->professor = $professor;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => intval($this->getId()),
                'escola' => $this->getEscola(),
                'professor' => $this->getProfessor()
            ];
    }
}

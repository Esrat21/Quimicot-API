<?php

class Pojo_Turma implements JsonSerializable
{

    private $id;
    private $nome;
    private $ano;
    private $escola;
    private $professor;
    private $senha;

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
            if (isset($data["ano"])) {
                $instance->setAno($data["ano"]);
            }
            if (isset($data["escola"])) {
                $instance->setEscola($data["escola"]);
            }
            if (isset($data["professor"])) {
                $instance->setProfessor($data["professor"]);
            }
            if (isset($data["senha"])) {
                $instance->setSenha($data["senha"]);
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

    public function getAno()
    {
        return $this->ano;
    }

    public function setAno($ano)
    {
        $this->ano = $ano;
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
        $this->professor = strtolower($professor);
    }

    public function getSenha()
    {
        return $this->senha;
    }

    public function setSenha($senha)
    {
        $this->senha = $senha;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => intval($this->getId()),
                'nome' => $this->getNome(),
                'ano' => $this->getAno(),
                'escola' => $this->getEscola(),
                'professor' => $this->getProfessor()
            ];
    }
}

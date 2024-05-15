<?php

class Pojo_Aluno_Turma implements JsonSerializable
{

    private $id;
    private $aluno;
    private $turma;
    private $dados_aluno;
    private $dados_turma;

    public static function FromData($data)
    {
        $instance = new self();
        if (isset($data)) {
            if (isset($data["id"])) {
                $instance->setId($data["id"]);
            }
            if (isset($data["aluno"])) {
                $instance->setAluno($data["aluno"]);
            }
            if (isset($data["turma"])) {
                $instance->setTurma($data["turma"]);
            }
            if (isset($data["dados_aluno"])) {
                $instance->setDados_aluno($data["dados_aluno"]);
            }
            if (isset($data["dados_turma"])) {
                $instance->setDados_turma($data["dados_turma"]);
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

    public function getAluno()
    {
        return $this->aluno;
    }

    public function setAluno($aluno)
    {
        $this->aluno = $aluno;
    }

    public function getTurma()
    {
        return $this->turma;
    }

    public function setTurma($turma)
    {
        $this->turma = $turma;
    }

    public function getDados_aluno()
    {
        return $this->dados_aluno;
    }

    public function setDados_aluno($dados_aluno)
    {
        $this->dados_aluno = $dados_aluno;
    }

    public function getDados_turma()
    {
        return $this->dados_turma;
    }

    public function setDados_turma($dados_turma)
    {
        $this->dados_turma = $dados_turma;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => intval($this->getId()),
                'aluno' => $this->getAluno(),
                'turma' => $this->getTurma(),
                'dados_aluno' => $this->getDados_aluno(),
                'dados_turma' => $this->getDados_turma()
            ];
    }
}

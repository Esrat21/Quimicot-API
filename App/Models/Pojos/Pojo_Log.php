<?php

class Pojo_Log implements JsonSerializable
{

    private $id;
    private $aluno;
    private $turma_fase;
    private $detalhes;
    private $objeto;
    private $tipo;
    private $comeco;
    private $fim;

    public function __construct()
    {
        $date = new DateTime();
        $this->setFim($date->format('Y-m-d H:i:s'));
    }

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
            if (isset($data["turma_fase"])) {
                $instance->setTurma_fase($data["turma_fase"]);
            }
            if (isset($data["detalhes"])) {
                $instance->setDetalhes($data["detalhes"]);
            }
            if (isset($data["objeto"])) {
                $instance->setObjeto($data["objeto"]);
            }
            if (isset($data["tipo"])) {
                $instance->setTipo($data["tipo"]);
            }
            if (isset($data["comeco"])) {
                $instance->setComeco($data["comeco"]);
            }
            if (isset($data["fim"])) {
                $instance->setFim($data["fim"]);
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

    public function getTurma_fase()
    {
        return $this->turma_fase;
    }

    public function setTurma_fase($turma_fase)
    {
        $this->turma_fase = $turma_fase;
    }

    public function getDetalhes()
    {
        return $this->detalhes;
    }

    public function setDetalhes($detalhes)
    {
        $this->detalhes = $detalhes;
    }

    public function getObjeto()
    {
        return $this->objeto;
    }

    public function setObjeto($objeto)
    {
        $this->objeto = $objeto;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    public function getComeco()
    {
        return $this->comeco;
    }

    public function setComeco($comeco)
    {
        $this->comeco = $comeco;
    }

    public function getFim()
    {
        if ($this->fim)
            return $this->fim;
    }

    public function setFim($fim)
    {
        $this->fim = $fim;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => intval($this->getId()),
                'aluno' => $this->getAluno(),
                'turma_fase' => $this->getTurma_fase(),
                'detalhes' => $this->getDetalhes(),
                'objeto' => $this->getObjeto(),
                'tipo' => $this->getTipo(),
                'comeco' => $this->getComeco(),
                'fim' => $this->getFim()
            ];
    }
}

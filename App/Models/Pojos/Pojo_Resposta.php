<?php

class Pojo_Resposta implements JsonSerializable
{

    private $id;
    private $escolha;
    private $data_hora;
    private $certo;
    private $quiz;
    private $aluno;

    public static function FromData($data)
    {
        $instance = new self();
        if (isset($data)) {
            if (isset($data["id"])) {
                $instance->setId($data["id"]);
            }
            if (isset($data["escolha"])) {
                $instance->setEscolha($data["escolha"]);
            }
            if (isset($data["data_hora"])) {
                $instance->setData_hora($data["data_hora"]);
            }
            if (isset($data["certo"])) {
                $instance->setCerto($data["certo"]);
            }
            if (isset($data["quiz"])) {
                $instance->setQuiz($data["quiz"]);
            }
            if (isset($data["aluno"])) {
                $instance->setAluno($data["aluno"]);
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

    public function getEscolha()
    {
        return $this->escolha;
    }

    public function setEscolha($escolha)
    {
        $this->escolha = $escolha;
    }

    public function getData_hora()
    {
        return $this->data_hora;
    }

    public function setData_hora($data_hora)
    {
        $this->data_hora = $data_hora;
    }

    public function isCerto()
    {
        return $this->certo;
    }

    public function setCerto($certo)
    {
        $this->certo = $certo;
    }

    public function getQuiz()
    {
        return $this->quiz;
    }

    public function setQuiz($quiz)
    {
        $this->quiz = $quiz;
    }

    public function getAluno()
    {
        return $this->aluno;
    }

    public function setAluno($aluno)
    {
        $this->aluno = $aluno;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => intval($this->getId()),
                'escolha' => $this->getEscolha(),
                'data_hora' => $this->getData_hora(),
                'certo' => $this->isCerto(),
                'quiz' => $this->getQuiz(),
                'aluno' => $this->getAluno()
            ];
    }
}

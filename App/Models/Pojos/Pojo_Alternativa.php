<?php

class Pojo_Alternativa implements JsonSerializable
{

    private $id;
    private $quiz;
    private $alt_correta;
    private $descricao;
    private $justificativa;

    public static function FromData($data)
    {
        $instance = new self();
        if (isset($data)) {
            if (isset($data["id"])) {
                $instance->setId($data["id"]);
            }
            if (isset($data["quiz"])) {
                $instance->setQuiz($data["quiz"]);
            }
            if (isset($data["alt_correta"])) {
                $instance->setAlt_correta($data["alt_correta"]);
            } else {
                $instance->setAlt_correta(false);
            }
            if (isset($data["descricao"])) {
                $instance->setDescricao($data["descricao"]);
            }
            if (isset($data["justificativa"])) {
                $instance->setJustificativa($data["justificativa"]);
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

    public function getQuiz()
    {
        return $this->quiz;
    }

    public function setQuiz($quiz)
    {
        $this->quiz = $quiz;
    }

    public function isAlt_correta()
    {
        return $this->alt_correta ? 1 : 0;
    }

    public function setAlt_correta($alt_correta)
    {
        $this->alt_correta = boolval($alt_correta);
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }

    public function getJustificativa()
    {
        return $this->justificativa;
    }

    public function setJustificativa($justificativa)
    {
        $this->justificativa = $justificativa;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => intval($this->getId()),
                'quiz' => $this->getQuiz(),
                'alt_correta' => $this->isAlt_correta(),
                'descricao' => $this->getDescricao(),
                'justificativa' => $this->getJustificativa()
            ];
    }
}

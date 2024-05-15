<?php

class Pojo_Quiz implements JsonSerializable
{

    private $id;
    private $turma_fase;
    private $pergunta;
    private $contem;

    public static function FromData($data)
    {
        $instance = new self();
        if (isset($data)) {
            if (isset($data["id"])) {
                $instance->setId($data["id"]);
            }
            if (isset($data["turma_fase"])) {
                $instance->setTurma_Fase($data["turma_fase"]);
            }
            if (isset($data["pergunta"])) {
                $instance->setPergunta($data["pergunta"]);
            }
            if (isset($data["contem"])) {
                $instance->setContem($data["contem"]);
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

    public function getTurma_Fase()
    {
        return $this->turma_fase;
    }

    public function setTurma_Fase($turma_fase)
    {
        $this->turma_fase = $turma_fase;
    }

    public function getPergunta()
    {
        return $this->pergunta;
    }

    public function setPergunta($pergunta)
    {
        $this->pergunta = $pergunta;
    }

    public function getContem()
    {
        return $this->contem;
    }

    public function setContem($contem)
    {
        $temp = $contem;
        if(is_string($temp)) {
            try {
                $temp = json_decode($contem, true);
            } catch (Exception $e) {
                $temp = [];
                error_log(print_r("Pojo_Quiz.php -> setContem", 1));
                error_log(print_r("Ocorreu um Erro ao executar esta ação:<br>" . $e->getMessage(), 1));
            }
        }
        $this->contem = $temp;
    }



    public function jsonSerialize()
    {
        return
            [
                'id' => intval($this->getId()),
                'turma_fase' => $this->getTurma_Fase(),
                'pergunta' => $this->getPergunta()
            ];
    }
}

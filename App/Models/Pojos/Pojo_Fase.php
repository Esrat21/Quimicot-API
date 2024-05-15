<?php

class Pojo_Fase implements JsonSerializable
{

    private $id;
    private $nome;
    private $url;
    private $criador;
    private $dificuldade;
    private $tempo_medio_seg;
    private $contem;
    private $vars;

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
            if (isset($data["url"])) {
                $instance->setUrl($data["url"]);
            }
            if (isset($data["criador"])) {
                $instance->setCriador($data["criador"]);
            }
            if (isset($data["dificuldade"])) {
                $instance->setDificuldade($data["dificuldade"]);
            }
            if (isset($data["tempo_medio_seg"])) {
                $instance->setTempo_medio_seg($data["tempo_medio_seg"]);
            }
            if (isset($data["contem"])) {
                $instance->setContem($data["contem"]);
            }
            if (isset($data["vars"])) {
                $instance->setVars($data["vars"]);
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

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getCriador()
    {
        return $this->criador;
    }

    public function setCriador($criador)
    {
        $this->criador = $criador;
    }

    public function getDificuldade()
    {
        return $this->dificuldade;
    }

    public function setDificuldade($dificuldade)
    {
        $this->dificuldade = $dificuldade;
    }

    public function getTempo_medio_seg()
    {
        return $this->tempo_medio_seg;
    }

    public function setTempo_medio_seg($tempo_medio_seg)
    {
        $this->tempo_medio_seg = $tempo_medio_seg;
    }

    public function getContem()
    {
        return $this->contem;
    }

    public function setContem($contem)
    {
        try {
            $this->contem = json_decode($contem, true);
        } catch (\Exception $e) {
            error_log($e->getMessage);
            $this->contem = $contem;
        }
        
    }

    public function getVars()
    {
        return $this->vars;
    }

    public function setVars($vars)
    {
        $this->vars = $vars;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => intval($this->getId()),
                'nome' => $this->getNome(),
                'url' => $this->getUrl(),
                'criador' => $this->getCriador(),
                'dificuldade' => $this->getDificuldade(),
                'tempo_medio_seg' => $this->getTempo_medio_seg(),
                'contem' => $this->getContem(),
                'vars' => $this->getVars()
            ];
    }
}

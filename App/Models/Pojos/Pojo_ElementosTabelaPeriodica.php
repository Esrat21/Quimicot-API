<?php

class Pojo_ElementosTabelaPeriodica implements JsonSerializable
{

    private $sigla;
    private $objeto;

    public static function FromData($data)
    {
        $instance = new self();
        if (isset($data)) {
            if (isset($data["sigla"])) {
                $instance->setSigla($data["sigla"]);
            }
            if (isset($data["objeto"])) {
                $instance->setObjeto($data["objeto"]);
            }
        }
        return $instance;
    }

    public function getSigla()
    {
        return $this->sigla;
    }

    public function setSigla($sigla)
    {
        $this->sigla = $sigla;
    }

    public function getObjeto()
    {
        return $this->objeto;
    }

    public function setObjeto($objeto)
    {
        if (is_string($objeto)) {
            $this->objeto = json_decode($objeto);
        } else if (is_array($objeto)) {
            $this->objeto = $objeto;
        } else {
            throw new \InvalidArgumentException("O objeto deve ser uma String JSON ou um PHP Array");
        }
    }

    public function jsonSerialize()
    {
        return
            [
                'sigla' => $this->getSigla(),
                'objeto' => $this->getObjeto()
            ];
    }
}

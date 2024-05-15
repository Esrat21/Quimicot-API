<?php

class Pojo_Professor implements JsonSerializable
{

    private $id;
    private $nome;
    private $email;
    private $cpf;
    private $telefone;
    private $senha;
    private $cad_pendente;

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
            if (isset($data["email"])) {
                $instance->setEmail($data["email"]);
            }
            if (isset($data["cpf"])) {
                $instance->setCpf($data["cpf"]);
            }
            if (isset($data["telefone"])) {
                $instance->setTelefone($data["telefone"]);
            }
            if (isset($data["senha"])) {
                $instance->setSenha($data["senha"]);
            }
            if (isset($data["cad_pendente"])) {
                $instance->setCad_pendente($data["cad_pendente"]);
            } else {
                $instance->setCad_pendente(1);
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

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getCpf()
    {
        return $this->cpf;
    }

    public function setCpf($cpf)
    {
        $this->cpf = $cpf;
    }

    public function getTelefone()
    {
        return $this->telefone;
    }

    public function setTelefone($telefone)
    {
        $this->telefone = $telefone;
    }

    public function getSenha()
    {
        return $this->senha;
    }

    public function setSenha($senha)
    {
        $this->senha = $senha;
    }

    public function getCad_pendente()
    {
        //error_log(print_r($this->cad_pendente, TRUE));
        if ($this->cad_pendente != true && $this->cad_pendente != 1) {
            return 0;
        }
        return 1;
    }

    public function setCad_pendente($cad_pendente)
    {
        if ($cad_pendente != 0 && $cad_pendente != false) {
            $this->cad_pendente = 1;
        } else {
            $this->cad_pendente = 0;
        }
    }

    public function jsonSerialize()
    {
        if ($this->getSenha() != null) {
            return [
                'id' => intval($this->getId()),
                'nome' => $this->getNome(),
                'email' => $this->getEmail(),
                'cpf' => $this->getCpf(),
                'telefone' => $this->getTelefone(),
                'senha' => $this->getSenha(),
                'cad_pendente' => $this->getCad_pendente(),
            ];
        }
        return [
            'id' => intval($this->getId()),
            'nome' => $this->getNome(),
            'email' => $this->getEmail(),
            'cpf' => $this->getCpf(),
            'telefone' => $this->getTelefone(),
            'cad_pendente' => $this->getCad_pendente(),
        ];
    }
}

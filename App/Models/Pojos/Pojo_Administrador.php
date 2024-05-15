<?php

class Pojo_Administrador implements JsonSerializable
{

    private $id;
    private $nome;
    private $email;
    private $cpf;
    private $telefone;
    private $senha;
    private $tipo;

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
            if (isset($data["tipo"])) {
                $instance->setTipo($data["tipo"]);
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

    public function getTipo()
    {
        return $this->tipo;
    }

    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
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
                'tipo' => $this->getTipo(),
            ];
        }
        return [
            'id' => intval($this->getId()),
            'nome' => $this->getNome(),
            'email' => $this->getEmail(),
            'cpf' => $this->getCpf(),
            'telefone' => $this->getTelefone(),
            'tipo' => $this->getTipo(),
        ];
    }
}

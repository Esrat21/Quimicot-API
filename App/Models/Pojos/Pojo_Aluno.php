<?php

class Pojo_Aluno implements JsonSerializable
{

    private $id;
    private $nome;
    private $email;
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
            if (isset($data["email"])) {
                $instance->setEmail($data["email"]);
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

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
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
                'email' => $this->getEmail(),
                'senha' => $this->getSenha()
            ];
    }
}

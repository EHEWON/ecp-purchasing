<?php

namespace App\Common\Jwt;

class Claims {

    public $id;
    public $name;
    public $role;
    public $iat;
    public $exp;

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getRole() {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role) {
        $this->role = $role;
    }

    /**
     * @return mixed
     */
    public function getIat() {
        return $this->iat;
    }

    /**
     * @param mixed $iat
     */
    public function setIat($iat) {
        $this->iat = $iat;
    }

    /**
     * @return mixed
     */
    public function getExp() {
        return $this->exp;
    }

    /**
     * @param mixed $exp
     */
    public function setExp($exp) {
        $this->exp = $exp;
    }

}

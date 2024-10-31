<?php

namespace App\Common\Jwt;

use Illuminate\Support\Facades\Config;

class Token {

    public $claims;
    public $sign;

    public function __construct(Claims $claims) {
        $this->claims = $claims;
    }

    public function setId($id) {
        $this->claims->setId($id);
        return $this;
    }

    public function setName($name) {
        $this->claims->setName($name);
        return $this;
    }

    public function setRole($role) {
        $this->claims->setRole($role);
        return $this;
    }

    public function getToken() {
        $this->claims->setIat(time());
        $this->claims->setExp($this->getExpTime());
        $payload = base64_encode(json_encode($this->claims));
        $this->sign = $this->genSign();
        return $payload . '.' . $this->sign;
    }

    public function verifyToken($token) {
        $this->parseToken($token);
        //token过期判断
        if ($this->claims->getExp() < time()) {
            Err('TOKEN_EXP');
        }

        if ($this->sign != $this->genSign()) {
            Err('TOKEN_SIGN_ERROR');
        }
        return $this->claims;
    }

    public function genSign() {
        $key = Config::get('app.token_key');
        $keyPlain = $this->claims->getId() . $this->claims->getIat() . $key;
        return md5($keyPlain);
    }

    public function parseToken($token = null) {

        $ret = explode('.', $token);

        if (count($ret) > 1) {
            $claims = json_decode(base64_decode($ret[0]));
            $this->claims->setId($claims->id);
            $this->claims->setIat($claims->iat);
            $this->claims->setExp($claims->exp);
            $this->claims->setName($claims->name);
            $this->claims->setRole($claims->role);
            $this->sign = $ret[1];
        } else {
            Err("TOKEN_ERROR");
        }
    }

    public function getExpTime() {
        $period = Config::get('app.token_exp');
        return $this->claims->getIat() + $period;
    }

}

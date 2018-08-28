<?php

namespace Dolondro\GoogleAuthenticator;

class Secret
{
    protected $issuer;
    protected $accountName;
    protected $secretKey;

    /**
     * Secret constructor.
     *
     * @param $issuer
     * @param $accountName
     * @param $secretKey
     */
    public function __construct($issuer, $accountName, $secretKey)
    {
        // As per spec sheet
        if (strpos($issuer.$accountName, ":") !== false) {
            throw new \InvalidArgumentException("Neither the 'Issuer' parameter nor the 'AccountName' parameter may contain a colon");
        }

        $this->issuer = $issuer;
        $this->accountName = $accountName;
        $this->secretKey = $secretKey;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return "otpauth://totp/".rawurlencode($this->getLabel())."?secret=".$this->getSecretKey()."&issuer=".rawurlencode($this->getIssuer());
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->issuer.":".$this->accountName;
    }

    /**
     * @return mixed
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * @return mixed
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }
}

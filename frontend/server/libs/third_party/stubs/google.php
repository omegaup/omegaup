<?php

class Google_Client {
    public function setClientId(string $clientId) : void {}
    public function setClientSecret(string $clientSecret) : void {}
    public function verifyIdToken(?string $token = null) : Google_Auth_LoginTicket {}
}

class Google_Auth_LoginTicket {
    /**
     * @return array{envelope: array, payload: array{email: string, email_verified: int, name?: string, picture: string, locale: string}}
     */
    public function getAttributes() : array {}
}

class Google_Exception extends Exception {
}

class Google_Auth_Exception extends Google_Exception {
}

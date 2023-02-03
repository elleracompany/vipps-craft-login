<?php


namespace vippsas\login\models;

use DateTime;
use Exception;
use vippsas\login\VippsLogin;

class Session
{
    // Properties
    // =========================================================================
    private string $access_token;
    private int $token_duration;
    private string $id_token;
    private array $scopes;
    private string $token_type;
    private int $expires_at;
    private array $data;

    // Public Methods
    // =========================================================================

    public function __construct(mixed $response)
    {
        $this->access_token = $response->access_token;
        $this->token_duration = $response->expires_in;
        $this->id_token = $response->id_token;
        $this->scopes = explode(' ', $response->scope);
        $this->token_type = $response->token_type;
        $this->expires_at = time()+$response->expires_in;
        $this->getDataFromVipps();
    }

    public function isExpired(): bool
    {
        return $this->getExpiresIn() > 0;
    }

    public function getExpiresIn(): int
    {
        return $this->expires_at - time();
    }

    public function getAddresses(): array|null
    {
        return $this->getFieldFromData('address');
    }

    public function getEmail(): string|null
    {
        return $this->getFieldFromData('email');
    }

    public function isEmailVerified(): bool|null
    {
        return $this->getFieldFromData('email_verified');
    }

    public function getGivenName(): string|null
    {
        return $this->getFieldFromData('given_name');
    }

    public function getFamilyName(): string|null
    {
        return $this->getFieldFromData('family_name');
    }

    public function getName(): string|null
    {
        return $this->getFieldFromData('name');
    }

    public function getPhoneNumber(): string|null
    {
        return $this->getFieldFromData('phone_number');
    }

    public function getSid(): string|null
    {
        return $this->getFieldFromData('sid');
    }

    public function getSub(): string|null
    {
        return $this->getFieldFromData('sub');
    }

    public function getNin(): string|null
    {
        return $this->getFieldFromData('nin');
    }

    public function getBirthdate(): DateTime|null
    {
        $birthdate = $this->getFieldFromData('birthdate');
        if($birthdate) return DateTime::createFromFormat('Y-m-d', $this->getFieldFromData('birthdate'));
        return $birthdate;
    }

    // Protected Methods
    // =========================================================================



    // Private Methods
    // =========================================================================

    private function getDataFromVipps() : void
    {
        if(!$this->data)
        {
            /** @var $response \Psr\Http\Message\ResponseInterface */
            $response = VippsLogin::getInstance()->vippsLogin->getUserInfo($this->access_token);
            $this->data = json_decode($response->getBody()->getContents());
        }
    }

    private function getFieldFromData($field): mixed
    {
        if(!$this->data) $this->getDataFromVipps();
        if(!isset($this->data->$field)) return null;
        return $this->data->$field;
    }
}
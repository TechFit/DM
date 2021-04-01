<?php


namespace SteamSignup\ValueObject;

/**
 * Class ActivationValuesFromEmail
 * @package SteamSignup\ValueObject
 */
class ActivationValuesFromEmail
{
    /**
     * @var string
     */
   private string $creationid;

    /**
     * @var string
     */
   private string $activationLink;

    /**
     * ActivationValuesFromEmail constructor.
     * @param string $creationid
     * @param string $activationLink
     */
    public function __construct(string $creationid, string $activationLink)
    {
        $this->creationid = $creationid;
        $this->activationLink = $activationLink;
    }

    /**
     * @return string
     */
    public function getCreationid(): string
    {
        return $this->creationid;
    }

    /**
     * @return string
     */
    public function getActivationLink(): string
    {
        return $this->activationLink;
    }
}
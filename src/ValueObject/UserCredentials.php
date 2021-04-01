<?php


namespace SteamSignup\ValueObject;

/**
 * Class UserCredentials
 */
class UserCredentials
{
    /** your real email for getting activation mail */
    private string $email = '';
    /** password for email */
    private string $emailPassword = '';
    /** unique login for new steam user */
    private string $newSteamAccountName = '';
    /** password for new steam user, at least 8 characters */
    private string $newSteamAccountPassword = '';

    /**
     * UserCredentials constructor.
     * @param string $email
     * @param string $emailPassword
     * @param string $newSteamAccountName
     * @param string $newSteamAccountPassword
     */
    public function __construct(
        string $email,
        string $emailPassword,
        string $newSteamAccountName,
        string $newSteamAccountPassword
    )
    {
        $this->email = $email;
        $this->emailPassword = $emailPassword;
        $this->newSteamAccountName = $newSteamAccountName;
        $this->newSteamAccountPassword = $newSteamAccountPassword;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getEmailPassword(): string
    {
        return $this->emailPassword;
    }

    /**
     * @return string
     */
    public function getNewSteamAccountName(): string
    {
        return $this->newSteamAccountName;
    }

    /**
     * @return string
     */
    public function getNewSteamAccountPassword(): string
    {
        return $this->newSteamAccountPassword;
    }
}
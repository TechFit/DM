<?php

namespace ExampleApp;

/**
 * Class Steam
 * @package ExampleApp
 */
class Steam
{
    /** your real email for getting activation mail */
    private $_email = '';
    /** password for email */
    private $_email_password = '';
    /** unique login for new steam user */
    private $_new_steam_account_name = '';
    /** password for new steam user, at least 8 characters */
    private $_new_steam_account_password = '';

    /** for example, Gmail Imap */
    private $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';

    public function join()
    {
        if (!$this->_email ||
            !$this->_email_password ||
            !$this->_new_steam_account_name ||
            !$this->_new_steam_account_password ||
            !$this->hostname
        ) {
            die("Please fill out all required fields.");
        }

        $handler = new JoinHandler();

        $new_user = $handler->makeNewUser($this->_email, $this->_email_password, $this->_new_steam_account_name, $this->_new_steam_account_password, $this->hostname);

        if ($new_user) {

            $login_user = self::login($this->_new_steam_account_name, $this->_new_steam_account_password);

            echo $login_user;

        } else {
            die('Join error');
        }
    }

    public function login(string $email, string $password)
    {
        $handler = new LoginHandler();

        return $handler->makeLogin($email, $password);
    }
}
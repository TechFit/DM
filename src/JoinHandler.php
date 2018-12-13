<?php

namespace ExampleApp;

use Unirest\Request;

/**
 * Class JoinHandler
 * @package ExampleApp
 */
class JoinHandler
{
    const STEAM_JOIN_PAGE = 'https://store.steampowered.com/join';
    const STEAM_CREATE_ACCOUNT = 'https://store.steampowered.com/join/createaccount';

    public function makeNewUser(string $email, string $email_password, string $new_steam_account_name, string $new_steam_account_password, string $hostname)
    {
        /** Open Steam join page for getting headers. */
        $join_page_response = Request::get(self::STEAM_JOIN_PAGE);

        $headers = new Headers();

        $headers = $headers->generateHeaders($join_page_response);

        $captcha = new Captcha();

        $captcha_image_link = $captcha->getCaptchaLink($join_page_response);

        $mail_handler = new Mail();

        /** [creationid, activation_link] */
        $data_from_email = $mail_handler->verifyAndActivate($headers, $email, $email_password, $hostname, $captcha_image_link['captchagid'], $captcha_image_link['captcha_text']);

        Request::get($data_from_email['activation_link'], $headers);

        echo $data_from_email['creationid'] . "\n";

        return $this->createAccount($headers, $new_steam_account_name, $new_steam_account_password, $data_from_email['creationid']);

    }

    private function createAccount(array $headers, string $new_steam_account_name, string $new_steam_account_password, string $creationid)
    {
        echo "Creating account... \n";

        $registration_request = Request::post(self::STEAM_CREATE_ACCOUNT, $headers, [
            'accountname' => $new_steam_account_name,
            'password' => $new_steam_account_password,
            'count' => 3,
            'lt' => 0,
            'creation_sessionid' => $creationid,
        ]);

        $registration_response = json_decode($registration_request->raw_body, true);

        if ($registration_response['bSuccess'] === false) {
            die($registration_response['details']);
        } else if ($registration_response['bSuccess'] === true) {
            echo "Registered \n" . "login:" . $new_steam_account_name . "\n password:" . $new_steam_account_password . "\n";
            return true;
        }
    }
}
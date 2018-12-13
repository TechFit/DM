<?php

namespace ExampleApp;

use Math_BigInteger;
use phpseclib\Crypt\RSA;
use Unirest\Request;

/**
 * Class LoginHandler
 * @package ExampleApp
 */
class LoginHandler
{
    const STEAM_JOIN_PAGE = 'https://store.steampowered.com/join';
    const RSA_ADDRESS = 'https://store.steampowered.com/login/getrsakey/';
    const DO_LOGIN_ADDRESS = 'https://store.steampowered.com/login/dologin/';

    public function makeLogin(string $account_name, string $account_password)
    {
        $join_page_response = Request::get(self::STEAM_JOIN_PAGE);

        $headers = new Headers();

        $headers = $headers->generateHeaders($join_page_response);

        $rsa_key_link = Request::post(self::RSA_ADDRESS, $headers,
            ['username' => $account_name]);

        $rsa_key_response = json_decode($rsa_key_link->raw_body, true);

        if  ($rsa_key_response['success'] === true) {
            $publickey_exp = $rsa_key_response['publickey_exp'];
            $publickey_mod = $rsa_key_response['publickey_mod'];
            $RSA = new RSA();
            $key = [
                'modulus'        => new Math_BigInteger($publickey_mod, 16),
                'publicExponent' => new Math_BigInteger($publickey_exp, 16),
            ];

            $RSA->loadKey($key, RSA::PUBLIC_FORMAT_RAW);
            $RSA->setPublicKey($key);
            $RSA->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
            $encryptedPassword = base64_encode($RSA->encrypt($account_password));
        }

        $do_login = Request::post(self::DO_LOGIN_ADDRESS, $headers,
            [
                'password' => $encryptedPassword,
                'username' => $account_name,
                'twofactorcode',
                'emailauth',
                'loginfriendlyname',
                'captchagid' => -1,
                'captcha_text',
                'emailsteamid',
                'rsatimestamp' => $rsa_key_response['timestamp'],
                'remember_login' => false
            ]);

            $login_data = json_decode($do_login->raw_body, true);

            if  ($login_data['success'] === true) {
                echo "Login success: SteamId - " . $login_data['transfer_parameters']['steamid'];

                return $login_data['transfer_parameters']['steamid'];
            } else {
                die ($login_data['message']);
            }
    }
}
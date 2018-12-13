<?php

namespace ExampleApp;

use Unirest\Request;

/**
 * Class Mail
 * @package ExampleApp
 */
class Mail
{
    public function verifyAndActivate(array $headers, string $email, string $email_password, string $hostname, string $captchagid, string $captcha_text) {

        $verify_email = self::verifyEmail($headers, $email,  $captchagid,  $captcha_text);

        if ($verify_email['success'] === 1) {
            return self::readEmail($hostname, $email, $email_password);
        } else {
            die($verify_email['details']);
        }
    }

    private function verifyEmail(array $headers, string $email, string $captchagid, string $captcha_text) : array
    {
        echo "Verify email... \n";

        $verify = Request::post('https://store.steampowered.com/join/ajaxverifyemail/', $headers,
            [
                'email' => $email,
                'captchagid' => $captchagid,
                'captcha_text' => $captcha_text,
                'details' => true
            ]);

        return json_decode($verify->raw_body, true);
    }

    private function readEmail(string $hostname, string $email, string $password) : array
    {
        echo "Wait for message... \n";
        sleep(20);

        $creationid = '';
        $activation_link = '';

        try {
            $inbox = imap_open($hostname, $email, $password) or die('Cannot connect to email: ' . imap_last_error());
        } catch (\Exception $e) {
            die($e->getMessage() . "\n") ;
        }

        $emails = imap_search($inbox,'SUBJECT "New Steam Account Email Verification"');

        if($emails) {

            rsort($emails);

            $email_body = imap_body($inbox, $emails[0]);

            /** find all links in current email */
            $links = self::links($email_body);

            $activation_link = $links[1];

            $creationid = self::parseLink($activation_link);
        }
        imap_close($inbox);

        return [
            'creationid' => $creationid,
            'activation_link' => $activation_link
        ];
    }

    private function links(string $str) : array
    {
        $pattern = '~[a-z]+://\S+~';

        if($num_found = preg_match_all($pattern, $str, $out))
        {
            return $out[0];
        }
    }


    private function parseLink(string $link) : string
    {
        $parts = parse_url($link);
        parse_str($parts['query'], $query);

        return $query['creationid'];
    }

}
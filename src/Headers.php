<?php

namespace ExampleApp;

class Headers
{
    private $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.139 Safari/537.36';

    public function generateHeaders(\Unirest\Response $response) : array
    {
        echo "Getting headers... \n";

        if (isset($response->headers['Set-Cookie'])) {
            $cookies = static::parseCookies($response->headers['Set-Cookie']);
        } else {
            $cookies = static::parseCookies($response->headers['set-cookie']);
        }

        if (isset($cookies['browserid'])) {
            $browserid = $cookies['browserid'];
        } else {
            $browserid = '';
            echo "Empty browserid \n";
        }

        if (isset($cookies['browserid'])) {
            $steamCountry = $cookies['steamCountry'];
        } else {
            $steamCountry = '';
            echo "Empty steamCountry \n";
        }

        if (isset($cookies['sessionid'])) {
            $sessionid = $cookies['sessionid'];
        } else {
            $sessionid = '';
            echo "Empty sessionid \n";
        }

        return [
            'cookie' => "ig_cb=1; browserid=$browserid;steamCountry=$steamCountry;sessionid=$sessionid;",
            'user-agent' => $this->getUserAgent(),
        ];
    }


    private function getUserAgent() : string
    {
        return $this->userAgent;
    }

    private static function parseCookies(array $rawCookies) : array
    {
        if (!is_array($rawCookies)) {
            $rawCookies = [$rawCookies];
        }

        $not_secure_cookies = [];
        $secure_cookies = [];

        foreach ($rawCookies as $cookie) {
            $cookie_array = 'not_secure_cookies';
            $cookie_parts = explode(';', $cookie);
            foreach ($cookie_parts as $cookie_part) {
                if (trim($cookie_part) == 'Secure') {
                    $cookie_array = 'secure_cookies';
                    break;
                }
            }
            $value = array_shift($cookie_parts);
            $parts = explode('=', $value);
            if (sizeof($parts) >= 2 && !is_null($parts[1])) {
                ${$cookie_array}[$parts[0]] = $parts[1];
            }
        }

        $cookies = $secure_cookies + $not_secure_cookies;
        return $cookies;
    }
}
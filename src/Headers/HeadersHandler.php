<?php

namespace SteamSignup\Headers;

use SteamSignup\ValueObject\HeadersObject;
use Unirest\Response;

/**
 * Class HeadersHandler
 * @package SteamSignup\Headers
 */
class HeadersHandler implements HeadersInterface
{
    /**
     * @param Response $response
     * @return HeadersObject
     */
    public function generateHeaders(Response $response): HeadersObject
    {
        $setCookie = $response->headers['Set-Cookie'] ?? $response->headers['set-cookie'];
        $cookies = self::parseCookies($setCookie);
        $browserId = $cookies['browserid'] ?? '';
        $steamCountry = $browserId ? $cookies['steamCountry'] : '';
        $sessionId = $cookies['sessionid'] ?? '';
        $cookie = "ig_cb=1; browserid=$browserId;steamCountry=$steamCountry;sessionid=$sessionId;";

        return new HeadersObject($cookie, $_ENV['USER_AGENT']);
    }

    /**
     * @param array $rawCookies
     * @return array
     */
    private function parseCookies(array $rawCookies) : array
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
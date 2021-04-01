<?php

namespace SteamSignup\Headers;

use SteamSignup\ValueObject\HeadersObject;
use Unirest\Response;

/**
 * Interface HeadersInterface
 * @package SteamSignup\Headers
 */
interface HeadersInterface
{
    public function generateHeaders(Response $response): HeadersObject;
}
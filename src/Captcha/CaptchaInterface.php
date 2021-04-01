<?php


namespace SteamSignup\Captcha;

use SteamSignup\ValueObject\ResolvedCaptcha;
use Unirest\Response;

/**
 * Interface CaptchaInterface
 * @package SteamSignup\Interface
 */
interface CaptchaInterface
{
    public function resolve(Response $response): ResolvedCaptcha;
}
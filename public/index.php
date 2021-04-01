<?php

use SteamSignup\Captcha\CaptchaHandler;
use SteamSignup\Headers\HeadersHandler;
use SteamSignup\JoinSteamService;
use SteamSignup\MailBox;
use SteamSignup\ValueObject\UserCredentials;

require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$captcha = new CaptchaHandler();
$headers = new HeadersHandler();
$mailBox = new MailBox($captcha);
$userCredentials = new UserCredentials(
    "user@email",
    "userPass",
    "AccName",
    "AccPass"
);

(new JoinSteamService($headers, $mailBox, $captcha, $userCredentials))->join();
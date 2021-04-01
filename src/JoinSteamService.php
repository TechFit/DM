<?php

namespace SteamSignup;

use SteamSignup\Captcha\CaptchaInterface;
use SteamSignup\Headers\HeadersInterface;
use SteamSignup\ValueObject\UserCredentials;
use Unirest\Request;

/**
 * Class JoinSteamService
 * @package SteamSignup
 */
class JoinSteamService
{
    /**
     * @var HeadersInterface
     */
    private HeadersInterface $headers;

    /**
     * @var CaptchaInterface
     */
    private CaptchaInterface $captcha;
    /**
     * @var MailBox
     */
    private MailBox $mailBox;
    /**
     * @var UserCredentials
     */
    private UserCredentials $userCredentials;

    /**
     * @param HeadersInterface $headers
     * @param MailBox $mailBox
     * @param CaptchaInterface $captcha
     * @param UserCredentials $userCredentials
     */
    public function __construct(
        HeadersInterface $headers,
        MailBox $mailBox,
        CaptchaInterface $captcha,
        UserCredentials $userCredentials
    )
    {
        $this->headers = $headers;
        $this->mailBox = $mailBox;
        $this->captcha = $captcha;
        $this->userCredentials = $userCredentials;
    }

    /**
     * @throws \Exception
     */
    public function join(): void
    {
        /** Open Steam join page for getting headers. */
        $joinPageResponse = Request::get($_ENV['STEAM_JOIN_PAGE']);

        $headers = $this->headers->generateHeaders($joinPageResponse);

        $resolvedCaptcha = $this->captcha->resolve($joinPageResponse);

        $activationLink = $this->mailBox->getActivationLink($headers, $this->userCredentials, $resolvedCaptcha);

        Request::get($activationLink->getActivationLink(), $headers->getArray());

        Request::post($_ENV['STEAM_CREATE_ACCOUNT'], $headers->getArray(), [
            'accountname' => $this->userCredentials->getNewSteamAccountName(),
            'password' => $this->userCredentials->getNewSteamAccountPassword(),
            'count' => 3,
            'lt' => 0,
            'creation_sessionid' => $activationLink->getCreationid(),
        ]);
    }
}
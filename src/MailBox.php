<?php

namespace SteamSignup;

use SteamSignup\Captcha\CaptchaInterface;
use SteamSignup\ValueObject\ActivationValuesFromEmail;
use SteamSignup\ValueObject\HeadersObject;
use SteamSignup\ValueObject\ResolvedCaptcha;
use SteamSignup\ValueObject\UserCredentials;
use Unirest\Request;

/**
 * Class Mail
 */
class MailBox
{
    private const SUBJECT = 'SUBJECT "New Steam Account Email Verification"';

    /**
     * @var CaptchaInterface
     */
    private CaptchaInterface $captcha;

    /**
     * @param CaptchaInterface $captcha
     */
    public function __construct(CaptchaInterface $captcha)
    {
        $this->captcha = $captcha;
    }

    /**
     * @param HeadersObject $headers
     * @param UserCredentials $userCredentials
     * @param ResolvedCaptcha $resolvedCaptcha
     * @return ActivationValuesFromEmail
     * @throws \Exception
     */
    public function getActivationLink(
        HeadersObject $headers,
        UserCredentials $userCredentials,
        ResolvedCaptcha $resolvedCaptcha
    ): ActivationValuesFromEmail
    {
        if (!$this->verifyEmail($headers, $userCredentials, $resolvedCaptcha)) {
            throw new \Exception('Email not verified');
        }

        return $this->getActivationDataFromEmail($userCredentials);
    }

    /**
     * @param HeadersObject $headers
     * @param UserCredentials $userCredentials
     * @param ResolvedCaptcha $resolvedCaptcha
     * @return bool
     */
    private function verifyEmail(HeadersObject $headers, UserCredentials $userCredentials, ResolvedCaptcha $resolvedCaptcha): bool
    {
        $verify = Request::post($_ENV['VERIFICATE_EMAIL_LINK'], $headers->getArray(),
            [
                'email' => $userCredentials->getEmail(),
                'captchagid' => $resolvedCaptcha->getCaptchagId(),
                'captcha_text' => $resolvedCaptcha->getCaptchaText(),
                'details' => true
            ]);

        return json_decode($verify->raw_body, true)['success'] === true;
    }

    /**
     * @param UserCredentials $userCredentials
     * @return ActivationValuesFromEmail|null
     * @throws \Exception
     */
    private function getActivationDataFromEmail(UserCredentials $userCredentials): ?ActivationValuesFromEmail
    {
        // Wait for message income
        sleep(20);

        try {
            $inbox = imap_open($_ENV('HOSTNAME'), $userCredentials->getEmail(), $userCredentials->getEmailPassword());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage() . "\n") ;
        }

        $emails = imap_search($inbox, self::SUBJECT);

        if(!$emails) {
            throw new \Exception('Email not found');
        }

        rsort($emails);

        $email_body = imap_body($inbox, $emails[0]);

        /** find all links in current email */
        $links = self::links($email_body);

        imap_close($inbox);

        return new ActivationValuesFromEmail(self::parseLink($links[1]), $links[1]);
    }

    /**
     * @param string $str
     * @return array
     * @throws \Exception
     */
    private function links(string $str) : array
    {
        $pattern = '~[a-z]+://\S+~';

        if($num_found = preg_match_all($pattern, $str, $out))
        {
            return $out[0];
        } else {
            throw new \Exception('Links not found');
        }
    }


    /**
     * @param string $link
     * @return string
     */
    private function parseLink(string $link) : string
    {
        $parts = parse_url($link);
        parse_str($parts['query'], $query);

        return $query['creationid'];
    }
}
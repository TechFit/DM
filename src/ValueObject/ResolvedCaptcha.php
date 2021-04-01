<?php


namespace SteamSignup\ValueObject;

/**
 * Class ResolvedCaptcha
 * @package SteamSignup\ValueObject
 */
class ResolvedCaptcha
{
    /**
     * @var string
     */
    private string $captchagId;
    /**
     * @var string
     */
    private string $captchaText;

    /**
     * ResolvedCaptcha constructor.
     * @param string $captchagId
     * @param string $captchaText
     */
    public function __construct(string $captchagId, string $captchaText)
    {
        $this->captchagId = $captchagId;
        $this->captchaText = $captchaText;
    }

    /**
     * @return string
     */
    public function getCaptchagId(): string
    {
        return $this->captchagId;
    }

    /**
     * @return string
     */
    public function getCaptchaText(): string
    {
        return $this->captchaText;
    }
}
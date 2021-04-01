<?php


namespace SteamSignup\ValueObject;

/**
 * Class HeadersObject
 * @package SteamSignup\ValueObject
 */
class HeadersObject
{
    /**
     * @var string
     */
    private string $cookie;
    /**
     * @var string
     */
    private string $userAgent;

    /**
     * Headers constructor.
     * @param string $cookie
     * @param string $userAgent
     */
    public function __construct(string $cookie, string $userAgent)
    {
        $this->cookie = $cookie;
        $this->userAgent = $userAgent;
    }

    /**
     * @return string
     */
    public function getCookie(): string
    {
        return $this->cookie;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return [
            'cookie' => $this->cookie,
            'user-agent' => $this->userAgent,
        ];
    }
}
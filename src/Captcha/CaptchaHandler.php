<?php

namespace SteamSignup\Captcha;

use SteamSignup\ValueObject\ResolvedCaptcha;
use Unirest\Request;
use Unirest\Response;

/**
 * Class CaptchaHandler
 * @package SteamSignup\Captcha
 */
class CaptchaHandler implements CaptchaInterface
{
    /**
     * @param Response $response
     * @return ResolvedCaptcha
     * @throws \Exception
     */
    public function resolve(Response $response): ResolvedCaptcha
    {
        $captchaImg = [];
        $document = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $document->loadHTML($response->body);
        $xpath = new \DOMXPath($document);
        $query = "//img[@id='captchaImg']";
        $entries = $xpath->query($query);

        if ($entries->length === 1) {
            return new ResolvedCaptcha(
                self::getCaptchaId($captchaImg['img_link']),
                self::resolveCaptcha($captchaImg['b64captcha'])
            );
        } else {
            throw new \Exception("Error with captcha image link.");
        }
    }

    /**
     * @param string $captcha_link
     * @return string
     * @throws \Exception
     */
    private function getCaptchaId(string $captcha_link): string
    {
        if (empty($captcha_link)) {
            throw new \Exception("Empty captcha link");
        }

        preg_match_all('!\d+!', $captcha_link, $matches);

        if (isset($matches[0]) && isset($matches[0][0])) {
            return $matches[0][0];
        } else {
            throw new \Exception("Can\'t get captcha image id.");
        }
    }

    /**
     * @param string $b64captcha
     * @return string
     * @throws \Exception
     */
    private function resolveCaptcha(string $b64captcha): string
    {
        if (empty($b64captcha)) {
            throw new \Exception("Empty b64captcha.");
        }

        $sendImg = Request::post($_ENV['RU_CAPTCHA_IN'], '', [
            'key' => $_ENV['RU_CAPTCHA_KEY'],
            'method' => 'base64',
            'body' => $b64captcha,
            'phrase' => 0,
            'regsense' => 1,
            'numeric' => 0,
            'calc' => 0,
            'json' => 1,
        ]);

        $sendImgResponse = json_decode($sendImg->raw_body, true);

        if ($sendImgResponse['status'] === 1) {
            sleep(30);

            $getImgResponse = self::getCaptchaSolutionResponse($sendImgResponse);

            if ($getImgResponse['status'] === 1) {
                return str_replace(' ', '', htmlspecialchars_decode($getImgResponse['request']));
            } else if ($getImgResponse['status'] === 0) {
                sleep(30);
                $getImgResponse = self::getCaptchaSolutionResponse($sendImgResponse);
                return str_replace(' ', '', htmlspecialchars_decode($getImgResponse['request']));

            } else {
                throw new \Exception($getImgResponse['request']);
            }
        } else {
            throw new \Exception($sendImgResponse['request']);
        }
    }

    /**
     * @param array $sendImgResponse
     * @return array
     */
    private function getCaptchaSolutionResponse(array $sendImgResponse) : array
    {
        $request = Request::post($_ENV['RU_CAPTCHA_RES'], '', [
            'key' => $_ENV['RU_CAPTCHA_KEY'],
            'action' => 'get',
            'id' => $sendImgResponse['request'],
            'json' => 1,
        ]);

        return json_decode($request->raw_body, true);
    }
}
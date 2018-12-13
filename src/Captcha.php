<?php

namespace ExampleApp;

use Unirest\Request;

/**
 * Class Captcha
 * @package ExampleApp
 */
class Captcha
{
    const RU_CAPTCHA_IN = 'http://rucaptcha.com/in.php';
    const RU_CAPTCHA_RES = 'http://rucaptcha.com/res.php';
    const RU_CAPTCHA_KEY = '';

    public function getCaptchaLink(\Unirest\Response $response): array
    {
        echo "Get captcha data... \n";

        $captcha_img = [];

        $document = new \DOMDocument('1.0', 'UTF-8');

        libxml_use_internal_errors(true);

        $document->loadHTML($response->body);

        $xpath = new \DOMXPath($document);
        $query = "//img[@id='captchaImg']";
        $entries = $xpath->query($query);

        if ($entries->length === 1) {

            echo "Captcha found... \n";

            $captcha_img['img_link'] = $entries->item(0)->getAttribute('src');
            $captcha_img['b64captcha'] = base64_encode(file_get_contents($captcha_img['img_link']));
            $captcha_img['captchagid'] = self::getCaptchaId($captcha_img['img_link']);
            $captcha_img['captcha_text'] = self::resolveCaptcha($captcha_img['b64captcha']);

            return $captcha_img;

        } else {
            die("Error with captcha image link.");
        }
    }


    private function getCaptchaId(string $captcha_link): string
    {
        if (empty($captcha_link)) {
            die("Empty captcha link");
        }

        preg_match_all('!\d+!', $captcha_link, $matches);

        if (isset($matches[0]) && isset($matches[0][0])) {
            return $matches[0][0];
        } else {
            die("Can\'t get captcha image id.");
        }
    }

    private function resolveCaptcha(string $b64captcha): string
    {
        echo "Creating account... \n";

        if (empty($b64captcha)) {
            die("Empty b64captcha.");
        }

        $send_img = Request::post(self::RU_CAPTCHA_IN, '', [
            'key' => self::RU_CAPTCHA_KEY,
            'method' => 'base64',
            'body' => $b64captcha,
            'phrase' => 0,
            'regsense' => 1,
            'numeric' => 0,
            'calc' => 0,
            'json' => 1,
        ]);

        $send_img_response = json_decode($send_img->raw_body, true);

        if ($send_img_response['status'] === 1) {

            echo "Waiting for resolving captcha by service... \n";

            sleep(30);

            $get_img_response = self::getCaptchaSolutionResponse($send_img_response);

            if ($get_img_response['status'] === 1) {

                return str_replace(' ', '', htmlspecialchars_decode($get_img_response['request']));

            } else if ($get_img_response['status'] === 0) {

                echo "Waiting once more... \n";

                sleep(30);

                $get_img_response = self::getCaptchaSolutionResponse($send_img_response);

                return str_replace(' ', '', htmlspecialchars_decode($get_img_response['request']));

            } else {
                die($get_img_response['request']);
            }
        } else {
            die($send_img_response['request']);
        }
    }

    private function getCaptchaSolutionResponse(array $send_img_response) : array
    {
        $request = Request::post(self::RU_CAPTCHA_RES, '', [
            'key' => self::RU_CAPTCHA_KEY,
            'action' => 'get',
            'id' => $send_img_response['request'],
            'json' => 1,
        ]);

        return json_decode($request->raw_body, true);
    }
}
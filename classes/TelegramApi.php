<?php

const API_KEY = 'YOUR-TELEGRAM-BOT-API-KEY';
const API_URL = 'https://api.telegram.org/bot' . API_KEY . '/';

const POOL_ID = 'YOUR-COURIER-CHAT-ID';
const BOT_HOST = 'YOUR-BOT-HOST-ID';

require_once('./Keyboard.php');
require_once('./Button.php');


class TelegramApi {

    private $apiKey;


    public function __construct() {
        $this->apiKey = API_KEY;
    }


    private function getApiKey() {
        return $this->apiKey;
    }


    public function sendMessage($chatId, $text, $replyMarkup=null) {
        // specify API method
        $method = 'sendMessage';

        // specify request params
        $requestParams = http_build_query([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => $replyMarkup,
            'parse_mode' => 'html'
        ]);

        // make request and get response
        $response = file_get_contents(API_URL . $method . '?' . $requestParams);
        
    }

    public function notify($message) {
        $this->sendMessage(POOL_ID, $message);
    }


    public function getArgs($input, $argNum) {
        $args = explode(" ", $input);
        return $args[$argNum];
    }


    public function sendPhoto($chatId, $photoUrl) {
        // specify method
        $method = 'sendPhoto';

        // specify request params
        $requestParams = http_build_query([
            'chat_id' => $chatId,
            'photo' => urlencode($photoUrl)
        ]);

        // make request and get response
        $response = file_get_contents(API_URL . $method . '?' . $requestParams);
        return $response;
    }

}

<?php

class Keyboard {

    public $buttons;

    public function __construct($buttons) {
        $this->buttons = $buttons;
    }

    public function prepare() {
        return json_encode(['keyboard' => $this->buttons]);
    }

    public function remove() {
        return json_encode(['remove_keyboard' => true]);
    }

}

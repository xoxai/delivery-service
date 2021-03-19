<?php

require_once('./classes/TelegramApi.php');
$tg = new TelegramApi();
// $order = $_POST['order'];

// $order = 'hello there';
$name = $_POST['name'];
$phone = $_POST['phone'];
$notes = $_POST['notes'];
$address = $_POST['address'];
// $phone = $_POST['phone'];
$orderJson = $_POST['order'];

// echo "".$name.$phone.$notes.$orderJson;

$orderContains = "";
$totalOrderSum = 0;
foreach(json_decode($orderJson) as $orderItem) {
    $itemName = str_replace("<br>", " ", $orderItem->name);
    $itemPrice = $orderItem->price;
    $itemCount = $orderItem->count;
    $orderContains .= "‣ Блюдо: $itemName (цена: $itemPrice, штук: $itemCount)\n";
    $totalOrderSum += $itemPrice * $itemCount;
}
$orderContains .= "\n<b>Итого, сумма заказа</b>: $totalOrderSum";

$orderInfo = "<b>🍔 НОВЫЙ ЗАКАЗ [CHUFA]"." 🍔</b>\n\n<b>Клиент</b>: $name\n<b>Телефон</b>: $phone\n<b>Адрес</b>: $address\n<b>Заметки к адресу</b>: $notes\n\n<b>Состав заказа</b>:\n$orderContains";

// send order in couriers chat
$tg->sendMessage(POOL_ID, $orderInfo);
// echo $orderInfo;
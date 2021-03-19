<?php

// header('Content-Type: ')

// INCLUDE GMAPS AND TG API
require_once('./classes/Client.php');
require_once('./classes/GoogleMapsApi.php');
require_once('./classes/TelegramApi.php');
require_once('./classes/Courier.php');
require_once('./classes/Admin.php');


const ADMIN_ID = 777;

// GET RESPONSE
$obj = json_decode(file_get_contents('php://input'));
$chatId = $obj->message->chat->id;
$message = $obj->message->text;

$tg = new TelegramApi();

// not accepting messages from groups
if ($chatId < 0) {
    // $tg->sendMessage($chatId, );
    return 0;
}

// Creating objects, identifying bot command
$map = new MapsApi();
$client = new Client($chatId);
$mode = $client->getMode();
$cmd = mb_strtolower($tg->getArgs($message, 0));

if ($chatId == ADMIN_ID) {
    // ADMIN PANEL
    $admin = new Admin();
    switch(mb_strtolower($cmd)) {
        case "все":
            $adminKeyboard = new Keyboard([['Все курьеры', 'Свободные курьеры'], ['Клиенты', 'Статистика'], ['Заказы']]);
            $couriersList = $admin->getCouriers();
            $tg->sendMessage($chatId, "Зарегистрированные курьеры:\n$couriersList", $adminKeyboard->prepare());
        break;

        case "свободные":
            $freeCouriers = $admin->getFreeCouriers();
            $tg->sendMessage($chatId, "<b>Свободные курьеры</b>:\n".$freeCouriers);
        break;

        case "заказы":
            $orders = $admin->getOrders();
            $tg->sendMessage($chatId, "<b>Заказы</b>:\n$orders");
        break;

        case "клиенты":
            $clientsList = $admin->getClients();
            $tg->sendMessage($chatId, "<b>Клиенты</b>:\n $clientsList");
        break;
        
        case "статистика":
            $tg->sendMessage($chatId, "Пользователей всего: ".$admin->getUsersCount()."\nЗаказов доставлено: ".$admin->getRealOrdersCount()."\nНа общую сумму: ".$admin->getTotalSum());
        break;

        case "привязать":
            // parse arguments
            $orderId = $tg->getArgs($message, 1);
            $courierId = $tg->getArgs($message, 2);

            // attach order to courier
            $admin->attachOrderToCourier($orderId, $courierId);
            $tg->sendMessage($chatId, "Заказ <b>Order[N$orderId]</b> теперь привязан к <b>Courier[$courierId]</b>");

            // inform courier in PM
            // get client id by order id
            $clientId = $admin->getClientIdByOrderId($orderId);

            // make needed entities
            $client = new Client($admin->getClientTgIdById($clientId));
            $courier = new Courier($admin->getCourierTgIdById($courierId));

            // get order info
            $orderInfo = "<b>🍔 НОВЫЙ ЗАКАЗ N".$orderId."[$courierId] 🍔</b>\n<b>Клиент</b>: <a href=\"tg://user?id=".$client->getTgId()."\">".$client->getName()."</a>\n<b>Телефон</b>: ".$client->getPhone()."\n<b>Адрес</b>: ".$client->getAddress()."\n<b>Заметки к адресу</b>: ".$client->getAddressNotes()."\n<b>Описание заказа</b>: ".$client->getOrderDescription();

            // inform courier
            $tg->sendMessage($courier->getTgId(), "[XDS Important]\n" . $orderInfo);
        break;

        case "отвязать":
            $orderId = $tg->getArgs($message, 1);
            $admin->freeOrder($orderId);
            $tg->sendMessage($chatId, "Заказ $orderId успешно отвязан от курьера.");
        break;

        case "цена":
            $orderId = $tg->getArgs($message, 1);
            $price = $tg->getArgs($message, 2);
            $admin->setOrderPrice($orderId, $price);
            $tg->sendMessage($chatId, "Цена установлена!");
        break;

    }
}

switch ($mode) {
    case 0:
        // welcoming block
        if ($cmd == '/start') {
            $tg->sendMessage($chatId, 'Привет! Рады видеть тебя. Давай пройдём быструю регистрацию и уже через 20 секунд ты сможешь осуществить свой первый заказ вкусной еды! Пиши <b>регистрация</b>, чтобы зарегистриоваться как пользователь или <b>стать курьером</b>, чтобы разносить заказы :)');
            // set user in registration mode
        } else if (mb_strtolower($cmd) == 'регистрация') {
            $tg->sendMessage($chatId, 'Напиши, пожалуйста, своё имя!');
            $client->setMode(1);
        } else if (mb_strtolower($message) == 'стать курьером') {
            $courier = new Courier($chatId);
            $client->setMode(4);
            $tg->sendMessage($chatId, "Хочешь стать курьером? Круто! Отправь для начала мне своё имя.");
        } else {
            $tg->sendMessage($chatId, 'Не понял, напиши <b>регистрация</b>, чтобы зарегистрироваться.');
        }
    break;

    case 1:
        // user registration block
        switch($client->getLayer()) {
            // NAME
            case 1:
                $client->setName($message);
                $tg->sendMessage($chatId, 'Я запомнил! Напиши, пожалуйста, свой телефон, чтобы наш курьер смог дозвониться в случае чего :)');
                $client->setLayer(2);
            break;

            // PHONE
            case 2:
                $client->setPhone($message);
                $tg->sendMessage($chatId, 'Отлично! На какой адрес будете принимать заказы?');
                $client->setLayer(3);
            break;

            // ADDRESS
            case 3:
                $clientAddress = $client->setAddress($message);
                if ($clientAddress) {
                    $tg->sendMessage($chatId, "Понял-принял! Доставляем на адрес $clientAddress! Если я неправильно что-то понял, то напиши изменить адрес и попробуй добавить город! Напиши ОК, если я правильно распознал место доставки.");
                    $client->setLayer(4);
                } else {
                    $tg->sendMessage($chatId, 'Что-то не то. Я такого адреса не нашёл. Попробуй ещё раз :)');
                }
            break;

            // ADDRESS SETTING ERROR
            case 4:
                if (mb_strtolower($message) == 'изменить адрес') {
                    $client->setLayer(3);
                    $tg->sendMessage($chatId, 'Ничего страшного, ошибки случаются. Просто напиши адрес ещё раз.');
                } else if (mb_strtolower($message) == 'ок') {
                    $client->setLayer(5);
                    $tg->sendMessage($chatId, 'Ок, идём дальше. Напиши комментарии к адресу, если требуется: номер домофона, квартиры, как пройти к дому и прочее, что посчитаешь нужным.');
                } else {
                    $tg->sendMessage($chatId, 'Кажется, я тебя не понял. Напиши <b>ОК</b>, если адрес верен или <b>изменить адрес</b>, чтобы ввести новое место доставки.');
                }
            break;

            // ADDRESS NOTES
            case 5:
                $addressNotes = $message;
                $client->setAddressNotes($addressNotes);
                $tg->sendMessage($chatId, "Здорово, мы закончили регистрацию! Чтобы сделать свой первый заказ, скорее напиши мне \"заказ\"");
                // $client->createOrder();
                // $client->setLayer(6);
                $client->setMode(2);
            break;
        }
    break;

    case 2:
        // user main menu block (order interface)
        // switch($cmd) {

        switch($client->getLayer()) {
            case 5:
                if (mb_strtolower($cmd) == 'заказ') {
                    $tg->sendMessage($chatId, "Переходим к самому заказу! Введи то, что (перечень блюд) и откуда (адрес заведения) тебе доставить!");
                    $client->createOrder();
                    $client->setLayer(6);  
                } else if (mb_strtolower($message) == 'мои заказы') {
                    $tg->sendMessage($chatId, 'Я перевёл тебя в режим просмотра и отмены заказов. Для просмотра и отмены заказов напиши <b>отменить заказ</b>.');
                    $client->setMode(3);
                } else if (mb_strtolower($message) == 'стать курьером') {
                    $courier = new Courier($chatId);
                    $client->setMode(4);
                    $tg->sendMessage($chatId, "Хочешь стать курьером? Круто! Отправь для начала мне своё имя.");
                } else {
                    $tg->sendMessage($chatId, "Вы готовы сделать заказ! Просто напишите мне <b>заказ</b>! Для управления заказами напишите <b>мои заказы</b>. Чтобы стать курьером, напиши <b>стать курьером</b>.");
                }
            break;

            case 6:
                $orderDescription = $message;
                // set last order id
                $thisOrderId = $client->getActiveOrderIds()[0];
                $client->setLastOrderId($thisOrderId);
                $client->setOrderDescription($orderDescription);
                $tg->sendMessage($chatId, 'Принято! Мы уже начали работать над вашим заказом. С вами свяжется курьер и уточнит все детали! Если хотите снова перейти в режим заказа, просто напишите "заказ".');
                $orderInfo = "<b>🍔 НОВЫЙ ЗАКАЗ N$thisOrderId 🍔</b>\n<b>Клиент</b>: <a href=\"tg://user?id=".$client->getTgId()."\">".$client->getName()."</a>\n<b>Телефон</b>: ".$client->getPhone()."\n<b>Адрес</b>: ".$client->getAddress()."\n<b>Заметки к адресу</b>: ".$client->getAddressNotes()."\n<b>Описание заказа</b>: ".$client->getOrderDescription();
                $tg->notify($orderInfo);
                $client->setMode(3);
                // $client->setLayer(5);
            break;

            case 7:
                // for previous users
                $tg->sendMessage($chatId, "Твой заказ уже принят! Ожидай, когда с тобой свяжется курьер! Он напишет личное сообщение в Телеграме.");
                $client->setMode(3);
            break;
        }

    break;

    case 3:
        // in-order (have active order) client interface
        switch(mb_strtolower($message)) {

            case "отменить заказ":
                $activeOrders = $client->getActiveOrders();
                $tg->sendMessage($chatId, "Вот какие активные заказы на твоё имя я нашёл:\n\n".$activeOrders."\nВыбери среди них тот, который надо отменить и отправь мне его номер!");
            break;

            case "меню":
                $tg->sendMessage($chatId, "Переходим в главное меню. Здесь ты снова можешь делать заказы!");
                $client->setMode(2);
                $client->setLayer(5);
            break;

            default:
                if (is_numeric($message)) {
                    $activeOrderIds = $client->getActiveOrderIds();
                    if (in_array($message, $activeOrderIds)) {
                        // cancel order (0 -- CANCELED)
                        $client->setOrderStatus($message, 0);
                        $tg->sendMessage($chatId, "Спасибо! Заказ N$message успешно отменён. Для возврата в меню заказа напиши <b>меню</b>.");
                    } else {
                        $tg->sendMessage($chatId, "Кажется, ты не можешь отменить этот заказ. Попробуй ещё раз.");
                    }
                } else if (count($client->getActiveOrderIds()) > 0) {
                    $tg->sendMessage($chatId, "У тебя есть активный заказ. Чтобы его отменить, напиши <b>отменить заказ</b>. Чтобы сделать новый, напиши сначала <b>меню</b>, а потом <b>заказ</b>.");
                } else {
                    $tg->sendMessage($chatId, 'Не понимаю! В любой непонятной ситуации пиши <b>меню</b>.');
                }
            break;
        }
    break;

    case 4:
        $courier = new Courier($chatId); 
        $courier->setName($message);
        $client->setMode(5);
        $tg->sendMessage($chatId, "Добро пожаловать, $message! Теперь отправь мне свою точку старта, прикрепи отметку на карте!");
    break;

    case 5:
        $courier = new Courier($chatId);
        if (isset($obj->message->location)) {
            // trying to get lat and lng
            $loc = $obj->message->location;
            $lat = $loc->latitude;
            $lng = $loc->longitude;

            // create and set courier start point
            // $courier->createStartPoint();
            $courier->setStartPoint($lat, $lng);

            // set information to courier
            $tg->sendMessage($chatId, "Координаты успешно переданы, спасибо. Вы готовы принимать заказы!");

            // change courier status
            // 2 -- ACTIVE AND WAITING FOR ORDERS
            $courier->setStatus(2);
            $client->setMode(6);
        } else {
            $tg->sendMessage($chatId, "В этом сообщении точно есть геоточка? Попробуй ещё разок :)");
        }

    break;

    case 6:
        $courier = new Courier($chatId);
        // accepting order
        // changing order statuses
        // view orders
        $keyboard = new Keyboard([
                                  ['Принять заказ'], 
                                  ['Прибыл в ресторан', 'Забрал заказ'], 
                                  ['Прибыл к клиенту', 'Передал заказ']
                                ]);
        $keyboardJson = $keyboard->prepare();

        switch($courier->getStatus()) {
            case 2:
                // COURIER ACCEPT ORDER
                if (mb_strtolower($message) == 'принять заказ') {
                    if ($courier->act(3)) {
                        // if it is possible to accept some order
                        // it means that order is already attached by operator
                        // and it is permitted!
                        $courier->setStatus(4);
                        $tg->sendMessage($chatId, "Статус заказа успешно изменён на <b>курьер принял заказ</b>.");
                    } else {
                        $tg->sendMessage($chatId, "Похоже, за тобой пока не закреплено заказов. Обратись к менеджеру по распределению!");
                    }
                    // $courier->act(3);
                } else {
                    $tg->sendMessage($chatId, "Это основное меня курьера. Если ты здесь, значит всё ок! Если тебе придёт заказ, то нажми на кнопку <b>принять заказ</b>.", $keyboardJson);
                }
            break;

            case 4:
            // COURIER IN RESTAURANT
                if (mb_strtolower($message) == 'прибыл в ресторан') {
                    $tg->sendMessage($chatId, "Статус заказа успешно изменён на <b>курьер прибыл в ресторан</b>.");
                    $courier->setStatus(5);
                    $courier->act(4);
                } else {
                    $tg->sendMessage($chatId, "Не удалось изменить статус заказа. Проверьте правильность запроса");
                }
            break;

            case 5:
            // COURIER RECEIVE ORDER
                if (mb_strtolower($message) == 'забрал заказ') {
                    $tg->sendMessage($chatId, "Статус заказа успешно изменён на <b>курьер забрал заказ</b>.");
                    $courier->setStatus(6);
                    $courier->act(5);
                } else {
                    $tg->sendMessage($chatId, "Не удалось изменить статус заказа. Проверьте правильность запроса");
                }
            break;

            case 6:
                // COURIER NEAR CLIENT
               if (mb_strtolower($message) == 'прибыл к клиенту') {
                    $tg->sendMessage($chatId, "Статус заказа успешно изменён на <b>курьер прибыл к клиенту</b>.");
                    $courier->setStatus(7);
                    $courier->act(6);
                } else {
                    $tg->sendMessage($chatId, "Не удалось изменить статус заказа. Проверьте правильность запроса");
                }
            break;

            case 7:
                // CLIENT ACCEPT ORDER
                if (mb_strtolower($message) == 'передал заказ') {
                    $tg->sendMessage($chatId, "Статус заказа успешно изменён на <b>курьер передал заказ клиенту</b>.");
                    $tg->sendMessage($chatId, 'Заказ успешно завершён, спасибо. Как только поступит новый заказ, просто нажмите <b>принять заказ</b>.');
                    // set courier status to active again
                    $courier->setStatus(2);
                    $courier->act(7);
                    $courier->completeOrder();
                } else {
                    $tg->sendMessage($chatId, "Не удалось изменить статус заказа. Проверьте правильность запроса");
                }
            break;

            case 10:
                // CALCULATE DISTANCE & TIME
                $pointFrom = $courier->getStartPoint();
                $fromLat = $pointFrom['latitude'];
                $fromLng = $pointFrom['longitude'];

                // GET RESTAURANT POINT COORDS
                $restaurantAddress = $message;
                if ($map->isAddressValid($restaurantAddress)) {
                    $pointTo = $map->getCoordinates($restaurantAddress);
                    $toLat = $pointTo['latitude'];
                    $toLng = $pointTo['longitude'];

                    // GET WALKING PARAMETERS FROM POINT A (COURIER START POINT) TO B (REST LOC)
                    $walkingParams = $map->getWalkingParams([$fromLat, $fromLng], [$toLat, $toLng]);
                    $distance = $walkingParams['distance'];
                    $time = $walkingParams['time'];

                    $tg->sendMessage($chatId, "По моим подсчётам тебе идти $time, а пройдёшь ты $distance. Успехов :)");
                } else {
                    $tg->sendMessage($chatId, "Некорректный адрес. Повтори попытку");
                }
            break;
        }
    break;
}
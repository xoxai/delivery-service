<?php

require_once('./Database.php');

class Admin {

    public $db;


    public function __construct() {
        $this->db = new Database();
    }


    public function getCouriers() {
        $names = $this->db->selectAll('x_couriers', 'name');
        $tgIds = $this->db->selectAll('x_couriers', 'tg_id');
        $result = "";
        foreach ($names as $id => $name) {
            $result .= "<a href=\"tg://user?id=".$tgIds[$id]."\">$name</a>\n";
        }
        return $result;
    }

    public function getClients() {
        $result = "";
        $cond = "name!='Unknown' AND phone!=0";
        $names = $this->db->selectAll('x_clients', 'name', $cond);
        $tgIds = $this->db->selectAll('x_clients', 'tg_id', $cond);
        $phones = $this->db->selectAll('x_clients', 'phone', $cond);
        foreach ($names as $id => $name) {
            $result .= "<a href=\"tg://user?id=".$tgIds[$id]."\">$name</a>: ".$phones[$id]."\n";
        }
        return $result;
    }

    public function getFreeCouriers() {
        $cond = 'status_id=2';
        $names = $this->db->selectAll('x_couriers', 'name', $cond);
        $links = $this->db->selectAll('x_couriers', 'tg_id', $cond);
        $ids = $this->db->selectAll('x_couriers', 'id', $cond);
        $freeCouriersList = "";
        foreach ($names as $id => $name) {
            $freeCouriersList .= "[".$ids[$id]."]: <a href=\"tg://user?id=".$links[$id]."\">$name</a>\n";
        }
        return $freeCouriersList;
    }
    
    public function getUsersCount() {
           return $this->db->connect()->
                             query("SELECT COUNT(*) AS num FROM x_clients WHERE 1")->
                             fetch(PDO::FETCH_LAZY)['num'];   
    }

    public function getOrders() {
        $orders = $this->db->selectAllSeveralCols("x_orders as orders LEFT JOIN x_clients as clients ON 
            orders.client_id = clients.id AND name!='Unknown'", "orders.description as description, orders.id as id, clients.name as name, clients.phone as phone, clients.tg_id as tg_id");
        $ordersList = "";
        foreach ($orders as $row) {
            $ordersList .= "[Заказ N".$row['id']."]\nКлиент: <a href=\"tg://user?id=".$row['tg_id']."\">".$row['name']."</a>\n"."Телефон: ".$row['phone']."\nОписание: ".$row['description']."\n\n";
        }
        return $ordersList;
    }

    public function getRealOrdersCount() {
        return $this->db->connect()->query("SELECT COUNT(*) AS num FROM x_orders WHERE price > 0")->
                                     fetch(PDO::FETCH_LAZY)['num'];
    }

    public function getTotalSum() {
        $orderPrices = $this->db->selectAll('x_orders', 'price', 'price > 0');
        $sum = 0;
        foreach ($orderPrices as $p) {
            $sum += $p;
        }
        return $sum;
    }

    public function attachOrderToCourier($orderId, $courierId) {
        // attaching
        $this->db->update('x_orders', 'courier_id', $courierId, 'id=' . $orderId);
    }

    public function getFreeOrderIds() {
        $freeOrderIds = $this->db->selectAll('x_orders', 'id', 'ISNULL(courier_id) AND status_id=1');
        return implode(',', $freeOrderIds);
    }

    public function freeOrder($orderId) {
        $this->db->update('x_orders', 'courier_id', null, 'id=' . $orderId);
    }


    public function setOrderPrice($orderId, $price) {
        return $this->db->update('x_orders', 'price', $price, 'id=' . $orderId);
    }


    public function getOrderById() {
        // 
    }

    public function getClientIdByOrderId($orderId) {
        return $this->db->select('x_orders', 'client_id', 'id=' . $orderId);
    }

    public function getCourierTgIdById($id) {
        return $this->db->select('x_couriers', 'tg_id', 'id=' . $id);
    }

    public function getClientTgIdById($id) {
         return $this->db->select('x_clients', 'tg_id', 'id=' . $id);
    }

}

<?php


require_once('./Database.php');
require_once('./GoogleMapsApi.php');


class Client {

    public $db;
    public $id;
    public $tgId;
    public $phone;
    public $name;
    public $layer;
    public $address;
    public $coords;


    public function __construct($tgId) {
        // Create database instance
        $this->db = new Database();
        // echo var_dump($this->db);

        // Set Telegram id
        $this->tgId = $tgId;
        // echo var_dump($this->tgId);
        
        // Check client existance
        if (!$this->isExists()) {
            // Client initialization
            $this->create();
            $this->createAddress();
            // echo 'created and addressed';
        }
    }


    public function create() {
        return $this->db->insert('x_clients', 'NULL,' . $this->getTgId() . ',0,DEFAULT,1,0,0');
    }


    public function getMode() {
        return $this->db->select('x_clients', 'mode_id', 'tg_id=' . $this->getTgId());
    }


    public function setMode($mode) {
        return $this->db->update('x_clients', 'mode_id', $mode, 'tg_id=' . $this->getTgId());
    }


    public function isExists() {
        return $this->db->isExists('x_clients', 'tg_id=' . $this->getTgId());
    }


    public function getId() {
        return $this->db->select('x_clients', 'id', 'tg_id=' . $this->getTgId());
    }


    public function getTgId() {
        return $this->tgId;
    }


    public function getName() {
        return $this->db->select('x_clients', 'name', 'tg_id=' . $this->getTgId());
    }

    
    public function getPhone() {
        return $this->db->select('x_clients', 'phone', 'tg_id=' . $this->getTgId());
    }


    public function setPhone($phone) {
        return $this->db->update('x_clients', 'phone', $phone, 'tg_id=' . $this->getTgId());
    }


    public function getLayer() {
        return $this->db->select('x_clients', 'layer', 'tg_id=' . $this->getTgId());
    }

    public function setLayer($layer) {
        return $this->db->update('x_clients', 'layer', $layer, 'tg_id=' . $this->getTgId());
    }


    public function getAddress() {
        return $this->db->select('x_addresses', 'place', 'client_id=' . $this->getId());
    }


    public function getAddressNotes() {
        return $this->db->select('x_addresses', 'notes', 'client_id=' . $this->getId());
    }


    public function setName($name) {
        return $this->db->update('x_clients', 'name', $name, 'tg_id=' . $this->getTgId());
    }


    public function createAddress() {
        $this->db->insert('x_addresses', 'NULL,'.$this->getId().',DEFAULT,DEFAULT,DEFAULT,DEFAULT,1');
    }


    public function setAddress($place) {
        // check if it is valid
        $map = new MapsApi();
        $formattedAddress = $map->isAddressValid($place);
        if ($formattedAddress) {
            // put in db if valid
            $this->db->update('x_addresses', 'place', $place, 'client_id=' . $this->getId());

            // put coordinates in db
            $coords = $map->getCoordinates($formattedAddress);
            $this->setCoords($coords['latitude'], $coords['longitude']);

            // return address to check
            return $formattedAddress;
        }
    }


    public function setCoords($lat, $lng) {
        $this->db->update('x_addresses', 'latitude', $lat, 'client_id='.$this->getId());
        $this->db->update('x_addresses', 'longitude', $lng, 'client_id='.$this->getId());
    }


    public function getCoords() {
        $lat = $this->db->select('x_addresses', 'latitude', 'client_id='.$this->getId());
        $lng = $this->db->select('x_addresses', 'longitude', 'client_id='.$this->getId());
        return [$lat, $lng];
    }


    public function setAddressNotes($notes) {
        $this->db->update('x_addresses', 'notes', $notes, 'client_id='.$this->getId());
    }


    public function getActiveOrders() {
        // select only active orders from db
        $activeOrderIds = $this->getActiveOrderIds();
        $result = "";
        foreach ($activeOrderIds as $orderId) {
            $result .= "<b>Заказ N</b>".$orderId.": ".$this->getOrderDescriptionById($orderId)."\n";
        }
        if ($result == "") {
            $result = "Кажется, ты ещё ничего не заказывал или мы уже всё доставили\n";
        }
        return $result;
    }

    public function getLastOrderId() {
        return $this->db->select('x_clients', 'last_order_id', 'id=' . $this->getId());
    }

    public function setLastOrderId($id) {
        $this->db->update('x_clients', 'last_order_id', $id, 'id=' . $this->getId());
    }


    public function getActiveOrderIds() {
        return $this->db->selectAll('x_orders', 'id', 'client_id='.$this->getId()." AND status_id=1 ORDER BY deadline DESC");
    }


    public function createOrder() {
        $this->db->insert('x_orders', 'NULL,'.$this->getId().',DEFAULT,DEFAULT,0,NULL,NULL,1');
    }


    public function setOrderDescription($description) {
        // status_id=1 -- active order
        // set description for the last ordered
        $lastOrderId = $this->getLastOrderId();
        $this->db->update('x_orders', 'description', $description, 'client_id='.$this->getId()." AND status_id=1 AND id=$lastOrderId");
    }

    // DEPRECATED
    public function getOrderDescription() {
        return $this->db->select('x_orders', 'description', 'client_id='.$this->getId().' ORDER BY deadline DESC LIMIT 1');
    }


    public function getOrderDescriptionById($orderId) {
        return $this->db->select('x_orders', 'description', 'client_id='.$this->getId()." AND id=".$orderId);
    }


    public function setOrderStatus($orderId, $status) {
        $this->db->update('x_orders', 'status_id', $status, 'id='.$orderId);
    }


}

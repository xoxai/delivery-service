<?php


require_once('./Database.php');


class Courier {
    
    public $tgId;
    public $db;
    public $table;


    public function __construct($tgId) {
        $this->db = new Database();
        $this->table = 'x_couriers';
        $this->tgId = $tgId;
        if (!$this->isExists()) {
            $this->create();
            // create associated start point
            $this->createStartPoint();
        }
    }


    public function isExists() {
        return $this->db->isExists($this->table, 'tg_id=' . $this->getTgId());
    }


    public function create() {
        // create courier with default status_id=1
        return $this->db->insert($this->table, 'NULL,'.$this->getTgId().',DEFAULT,1');
    }


    public function getId() {
        return $this->db->select($this->table, 'id', 'tg_id=' . $this->getTgId());
    }


    public function getTgId() {
        return $this->tgId;
    }


    public function getStatus() {
        return $this->db->select($this->table, 'status_id', 'tg_id=' . $this->getTgId());
    }


    public function setStatus($status) {
        $this->db->update($this->table, 'status_id', $status, 'tg_id=' . $this->getTgId());
    }


    public function getStartPoint() {
        $lat = $this->db->select('x_courier_points', 'latitude', 'courier_id=' . $this->getId());
        $lng = $this->db->select('x_courier_points', 'longitude', 'courier_id=' . $this->getId());
        return ['latitude' => $lat, 'longitude' => $lng];
    }


    public function setStartPoint($lat, $lng) {
        // update latitude and longitude of a start point
        $this->db->update('x_courier_points', 'latitude', $lat, 'courier_id=' . $this->getId());
        $this->db->update('x_courier_points', 'longitude', $lng, 'courier_id=' . $this->getId());
    }


    public function createStartPoint() {
        $this->db->insert('x_courier_points', "NULL,".$this->getId().",0,0");
    }


    public function setName($name) {
        $this->db->update('x_couriers', 'name', $name, 'id=' . $this->getId());
    }


    public function getName() {
        return $this->db->select('x_couriers', 'name', 'id=' . $this->getId());
    }

    public function act($actionId) {
        // insert an action in courier_actions table
        // SELECT id from x_orders WHERE courier_id=$this->getId() AND status_id=1
        $orderId = $this->db->select('x_orders', 'id', 'courier_id='.$this->getId()." AND status_id=1");
        if ($orderId) {
            $this->db->insert('x_courier_actions', 'NULL,'.$this->getId().",$actionId,$orderId,DEFAULT");
            return true;
        } else {
            return false;
        }
    }

    public function getOrder() {
        return $this->db->select('x_orders', 'id', 'courier_id=' . $this->getId() . ' AND status_id=1');
    }

    public function completeOrder() {
        $orderId = $this->getOrder();
        return $this->db->update('x_orders', 'status_id', 0, 'id=' . $orderId);
    }

}

<?php
class Elasticsearch extends CApplicationComponent {
    public $host;
    public $port;
    public $prefix;
    public $max_result_window = 10000;
    public $checkAvailability = false;
    private $url;
    public function init() {
        parent::init();
        if(empty($this->host) == true) {
            throw new Exception('Адрес сервера не указан', 500);
        }
        if(empty($this->port) == true) {
            throw new Exception('Номер порта не указан', 500);
        }
        if(empty($this->prefix) == true) {
            throw new Exception('Префикс для индекса не указан', 500);
        }
        $this->url = 'http://' . $this->host . ':' . $this->port;
        $this->checkAvailability();
    }

    private function checkAvailability() {
        if($this->checkAvailability == false) {
            return;
        }
        $fp = fsockopen($this->host, $this->port, $errno, $errstr);
        if (!$fp) {
            throw new Exception('Указанный в настройках сервер недоступен', 500);
        }
        else {
            fclose($fp);
        }
    }

    public function getUrl() {
        return $this->url;
    }

    public function getIndices($simple = true) {
        $this->checkAvailability();
        $response = Yii::app()->curl->get($this->getUrl() . '/_cat/indices', []);
        if($simple == true) {
            return $response;
        }
        return explode("\n", $response);
    }

    public function ifIndexExist($index) {
        $this->checkAvailability();
        $index = $this->prefix . $index;
        $response = $this->getIndices(false);
        $filter = array_filter($response,function($x) use($index) {
            if(empty($x) == true) {
                return false;
            }
            $tmp = explode(' ', $x);
            $tmp = array_values(array_filter($tmp));
            if($tmp[2] == $index) {
                return true;
            }
            return false;
        });
        if(empty($filter) == true) {
            return false;
        }
        return true;
    }

    public function deleteIndex($index) {
        $this->checkAvailability();
        if(empty($index) == true) {
            throw new Exception('Название индекса не может быть пустым', 500);
        }
        $response = Yii::app()->curl->delete($this->getUrl() . '/' . $this->prefix . $index, []);
        $this->checkError($response);
        return $response;
    }

    public function createIndex($index, $data) {
        $this->checkAvailability();
        if(empty($index) == true) {
            throw new Exception('Название индекса не может быть пустым', 500);
        }
        Yii::app()->curl->setOption(CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=UTF-8']);
        $response = Yii::app()->curl->put($this->getUrl() . '/' . $this->prefix . $index, json_encode($data));
        $this->checkError($response);
        return $response;
    }

    public function insert($index, $type, $data) {
        $this->checkAvailability();
        Yii::app()->curl->setOption(CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=UTF-8']);
        $response = Yii::app()->curl->post($this->getUrl() . '/' . $this->prefix . $index . '/' . $type, json_encode($data));
        $this->checkError($response);
        return $response;
    }

    public function search($index, $type, $criteria, $decoded = true) {
        $this->checkAvailability();
        Yii::app()->curl->setOption(CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=UTF-8']);
        $response = Yii::app()->curl->post($this->getUrl() . '/' . $this->prefix . $index . '/' . $type . '/_search', json_encode($criteria));
        $this->checkError($response);
        if($decoded == true) {
            return json_decode($response, true);
        }
        return $response;
    }

    public function createMapping($index, $type, $data) {
        $this->checkAvailability();
        Yii::app()->curl->setOption(CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=UTF-8']);
        $response = Yii::app()->curl->put($this->getUrl() . '/' . $this->prefix . $index . '/' . $type . '/_mapping', json_encode($data));
        $this->checkError($response);
        return $response;
    }

    public function deleteByQuery($index, $type, $data) {
        $this->checkAvailability();
        Yii::app()->curl->setOption(CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=UTF-8']);
        $response = Yii::app()->curl->post($this->getUrl() . '/' . $this->prefix . $index . '/' . $type . '/_delete_by_query', json_encode($data));
        $this->checkError($response);
        return $response;
    }

    public function checkError($response) {
        $decoded = json_decode($response, true);
        if(isset($decoded['error'])) {
            if(is_array($decoded['error'])) {
                throw new Exception(var_export($decoded['error'], true), $decoded['status']);
            }
            else {
                throw new Exception($decoded['error'], $decoded['status']);
            }
        }
    }



}
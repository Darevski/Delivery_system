<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 23.03.16
 * Time: 19:28
 */

namespace Application\Units;

use Application\Core\System\Config;
use Application\Exceptions\Curl_Except;
use Application\Exceptions\Server_Error_Except;
use Application\Exceptions\UFO_Except;

/**
 * Class Yandex_Geo_Api
 * API class of geocoding points based on Yandex.maps service
 * @package Application\Units
 */
class Yandex_Geo_Api {
    // Values that used for creating queries
    private $version = '1.x';
    private $language = 'ru-RU';
    private $city = 'Минск';
    private $result_limit = 1;

    /**
     * Custom Users parameters that sets the request
     * @var array
     */
    protected $_data_array = array();
    /**
     * Response object from Yandex.maps Service
     * @var Yandex_Response_Object
     */
    protected $_server_response = null;

    /**
     * Sets configuration values from config
     * Yandex_geo_api constructor.
     */
    public function __construct(){
        $config = Config::get_instance()->get_yandex_unit();
        $this->version = $config['version'];
        $this->language = $config['language'];
        $this->city = $config['city'];
        $this->result_limit = $config['result_limit'];
        $this->clear();
    }

    /**
     * Request curl with $data_array to yandex.maps geocoder
     * @throws Curl_Except
     * @throws Server_Error_Except
     * @throws UFO_Except
     */
    public function start_request(){
        $YandexApiUrl = sprintf('https://geocode-maps.yandex.ru/%s/?%s',
                                        $this->version, http_build_query($this->_data_array));
        $curl = curl_init($YandexApiUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        $data = curl_exec($curl);
        $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (curl_errno($curl)){
            curl_close($curl);
            throw new Curl_Except("Curl error: ".curl_error($curl));
        }
        curl_close($curl);
        //!!!!
        if (in_array($response_code,array(500,502,503,504)))
            throw new Server_Error_Except ("Server error: ".$data,$response_code);

        $data = json_decode($data, true);
        if (is_null($data)){
            $error_message = 'Invalid or missing JSON from'.$YandexApiUrl;
            throw new UFO_Except($error_message,400);
        }
        $this->_server_response = new Yandex_Response_Object($data);
        return $this;
    }

    /**
     * Return response object from Yandex maps geocoder
     * @return Yandex_Response_Object
     */
    public function Get_Response(){
        return $this->_server_response;
    }

    /**
     * Clear user parameters from data_array
     */
    public function clear(){
        $this->_data_array = array('format' => 'json','lang'=>$this->language);
        $this->Set_Limit($this->result_limit);
        $this->_server_response = null;
        return $this;
    }

    /**
     * Set limit of point detection
     * @param int $limit
     * @return Yandex_Geo_Api
     */
    public function Set_Limit($limit = 1){
        $this->_data_array['results'] = $limit;
        return $this;
    }

    /**
     * Set Point with coordinates longitude / latitude
     * @param $longitude
     * @param $latitude
     * @return Yandex_Geo_Api
     */
    public function Set_Point($longitude, $latitude){
        $this->clear();
        $longitude = (float)$longitude;
        $latitude = (float)$latitude;
        $this->_data_array['geocode'] = sprintf('%f,%f', $longitude, $latitude);
        return $this;
    }

    /**
     * Set Point with address Query
     * @param $query
     * @return Yandex_Geo_Api
     */
    public function SetGeoCode($query){
        $this->clear();
        $query = $this->city." ".$query;
        $this->_data_array['geocode'] = (string)$query;
        return $this;
    }
}
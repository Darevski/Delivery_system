<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 23.03.16
 * Time: 20:51
 */

namespace Application\Units;

/**
 * Class Yandex_Geo_Object
 * used https://github.com/yandex-php/php-yandex-geo/
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @author Darevski
 * @license The MIT License (MIT)
 * @package Application\Units
 */
class Yandex_Geo_Object{
    /**
     * Address + point location data
     * @var array
     */
    protected $_data;

    /**
     * Select Address + coordinates of point
     * Yandex_Geo_Object constructor.
     * @param array $rawData
     */
    public function __construct(array $rawData){
        $data = array(
            'Address' => $rawData['metaDataProperty']['GeocoderMetaData']['text'],
        );
        array_walk_recursive($rawData, function($value, $key) use(&$data) {
            if (in_array($key, array('ThoroughfareName', 'PremiseNumber'))) {
                $data[$key] = $value;
            }
        });
        if (isset($rawData['Point']['pos'])) {
            $pos = explode(' ', $rawData['Point']['pos']);
            $data['Longitude'] = (float)$pos[0];
            $data['Latitude'] = (float)$pos[1];
        }
        $this->_data = $data;
    }


    /**
     * Latitude in degrees with 7 numbers after dot
     * @return float|null
     */
    public function getLatitude(){
        return isset($this->_data['Latitude']) ? $this->_data['Latitude'] : null;
    }

    /**
     * Longitude in degrees with 7 number after dot
     * @return float|null
     */
    public function getLongitude(){
        return isset($this->_data['Longitude']) ? $this->_data['Longitude'] : null;
    }

    /**
     * Full address
     * @return string|null
     */
    public function getAddress(){
        return isset($this->_data['Address']) ? $this->_data['Address'] : null;
    }

    /**
     * Return street
     * @return string|null
     */
    public function getThoroughfareName(){
        return isset($this->_data['ThoroughfareName']) ? $this->_data['ThoroughfareName'] : null;
    }

    /**
     * return number of house
     * @return string|null
     */
    public function getPremiseNumber(){
        return isset($this->_data['PremiseNumber']) ? $this->_data['PremiseNumber'] : null;
    }
}
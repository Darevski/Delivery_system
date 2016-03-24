<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 23.03.16
 * Time: 20:04
 */

namespace Application\Units;

/**
 * Class Yandex_Response_Object
 * used https://github.com/yandex-php/php-yandex-geo/
 * @author Dmitry Kuznetsov <kuznetsov2d@gmail.com>
 * @author Darevski
 * @license The MIT License (MIT)
 * @package Application\Units
 */
class Yandex_Response_Object{
    /**
     * Response data from Server yandex.maps Geocoder
     * @var array
     */
    protected $_data;
    /**
     * List of Geo Objects
     * @var Yandex_Geo_Object[]
     */
    protected $_list_GEO_objects;

    /**
     * Select Geo object from response
     * Yandex_Response_Object constructor.
     * @param array $data
     */
    public function __construct(array $data){
        $this->_data = $data;
        if (isset($data['response']['GeoObjectCollection']['featureMember'])) {
            foreach ($data['response']['GeoObjectCollection']['featureMember'] as $entry) {
                $this->_list_GEO_objects[] = new Yandex_Geo_Object($entry['GeoObject']);
            }
        }
    }

    /**
     * Return list of Geo Object`s
     * @return Yandex_Geo_Object[]
     */
    public function getList_GEO_objects(){
        return $this->_list_GEO_objects;
    }

    /**
     * Return request query
     * @return string|null
     */
    public function getQuery(){
        $result = null;
        if (isset($this->_data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['request'])) {
            $result = $this->_data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['request'];
        }
        return $result;
    }

    /**
     * Return count of found point`s
     * @return int|null
     */
    public function getFoundCount()
    {
        $result = null;
        if (isset($this->_data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'])) {
            $result = (int)$this->_data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'];
        }
        return $result;
    }
}
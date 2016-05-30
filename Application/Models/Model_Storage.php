<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 28.05.16
 * Time: 11:34
 */

namespace Application\Models;


use Application\Core\Model;
use Application\Exceptions\Model_Except;
use Application\Units\Yandex_Geo_Api;

class Model_Storage extends Model{

    /**
     * @var Model_Company
     */
    private $Model_company;

    /**
     * Model_Storage constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->Model_company = new Model_Company();
    }

    /**
     * return list of user storages by id
     * @param $company_id
     * @return array
     * @throws Model_Except
     */
    public function get_all_user_storages($company_id){

        if (!$this->Model_company->isset_user($company_id))
            throw new Model_Except("индетификатора компании не существует в БД");

        $query = "SELECT * FROM Storages WHERE company_id=?i";
        return $this->database->getAll($query,$company_id);
    }


    /**
     * Add storage with params into database
     * @param $name
     * @param $street
     * @param $house
     * @param $note
     * @throws Model_Except
     */
    public function add_storage($name,$street,$house,$note,$company_id){

        if (!$this->Model_company->isset_user($company_id))
            throw new Model_Except("индетификатора компании не существует в БД");
        //Convert street and number house to coordinates on map
        $address_info = $this->convert_address($street,$house);

        if (!empty($address_info['street']) and !empty($address_info['house'])){
            $query = "INSERT INTO Storages (name,note,street,house,Latitude,Longitude,company_id) VALUES (?s,?s,?s,?s,?s,?s,?i)";
            $this->database->query($query,$name,$note,$address_info['street'],$address_info['house'],$address_info['latitude'],$address_info['longitude'],$company_id);
        }
        else
            throw new Model_Except("Не найден адрес",404);

    }

    /**
     * Update storage in database by id
     * @param $storage_id
     * @param $name
     * @param $street
     * @param $house
     * @param $note
     * @throws Model_Except
     */
    public function update_storage($storage_id,$name,$street,$house,$note,$company_id){

        if (!$this->Model_company->isset_user($company_id))
            throw new Model_Except("индетификатора компании не существует в БД");

        //Convert street and number house to coordinates on map
        $address_info = $this->convert_address($street,$house);

        if (!$this->isset_storge($storage_id,$company_id))
            throw new Model_Except("выбранного склада не существует в базе данных");

        if (!empty($address_info['street']) and !empty($address_info['house'])){
            $query = "UPDATE Storages SET name=?s,note=?s,street=?s,house=?s,Latitude=?s,Longitude=?s WHERE id=?s and company_id=?i";
            $this->database->query($query,$name,$note,$address_info['street'],$address_info['house'],$address_info['latitude'],$address_info['longitude'],$storage_id,$company_id);
        }
        else
            throw new Model_Except("Не найден адрес",404);

    }

    /**
     * Delete Storage From DataBase
     * @param $id
     * @param $company_id
     * @throws Model_Except
     */
    public function delete_storage($id,$company_id){

        if (!$this->Model_company->isset_user($company_id))
            throw new Model_Except("индетификатора компании не существует в БД");

        if (!$this->isset_storge($id,$company_id))
            throw new Model_Except("выбранного склада не существует в базе данных");
        $query = "DELETE FROM Storages WHERE id=?s and company_id=?i";
        $this->database->query($query,$id,$company_id);
    }


    /**
     * Get coordinates by street + house and checks spelling name of street
     * @param $street
     * @param $house
     * @return mixed {string street, string house, float latitude, float longitude}
     * @throws \Application\Exceptions\Curl_Except
     * @throws \Application\Exceptions\Server_Error_Except
     * @throws \Application\Exceptions\UFO_Except
     */
    private function convert_address($street,$house){

        $yandex = new Yandex_Geo_Api();
        $point_info = $yandex->SetGeoCode(sprintf("%s,%s",$street,$house))
            ->start_request()
            ->Get_Response()->getList_GEO_objects()[0];
        //Get correctly street name from Yandex GeoCoder
        $result['street'] = $point_info->getThoroughfareName();
        $result['house'] = $point_info->getPremiseNumber();
        //Get Geo coordinates
        $result['latitude'] =$point_info->getLatitude();
        $result['longitude'] = $point_info->getLongitude();

        return $result;
    }


    /**
     * Checks thats storage are exists in database
     * @param $storage_id
     * @param $company_id
     * @return bool
     */
    public function isset_storge($storage_id,$company_id){
        $query = "SELECT 1 FROM Storages WHERE id = ?s and company_id=?i LIMIT 1";
        $result =  $this->database->query($query,$storage_id,$company_id);
        $count = $this->database->numRows($result);
        return ($count > 0) ? true : false;
    }
}
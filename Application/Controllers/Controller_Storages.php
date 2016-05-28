<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 28.05.16
 * Time: 11:30
 */

namespace Application\Controllers;


use Application\Core\Controller;
use Application\Core\View;
use Application\Models\Model_Storage;


/**
 * Class Controller_Storages
 * Controls actions with users storages
 * @package Application\Controllers
 */
class Controller_Storages extends Controller{

    /**
     * @var Model_Storage
     */
    private $Model_storage;

    /**
     * Controller_Storages constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->Authentication->access_check(1);
        $this->Model_storage = new Model_Storage();
    }

    /**
     * return list of user storages
     */
    public function action_get_storages(){
        $storages = $this->Model_storage->get_all_user_storages($_SESSION['id']);
        if(!is_null($storages))
            $result['storages'] = $storages;
        else
            $result['storages'] = array();

        $result['state'] = 'success';
        View::output_json($result);
    }


    /**
     * Add new storage into database
     * Structure of JSON_Input
     * address{
     *  string name
     *  string street
     *  string house - house with block for ex. 'Рафиева 83к1' or 'Рафиева 113'
     *  string entry
     *  string note
     * }
     * @throws \Application\Exceptions\Model_Except
     * @throws \Application\Exceptions\UFO_Except
     */
    public function action_add_storage(){

        // Example Post Request
        /*
        $input_json = '{"name":"запасной",
                       "street":"рафиева",
                       "house":"93к2",
                       "note":"тест"
                       }';
        */
        $input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);

        // checks validity and decode input JSON
        $decoded_json = $this->Filter_unit->decode_Json($input_json);

        $validate_map = array(
            'name' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW),
            'street' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW),
            'house' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW),
            'note' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW)
        );

        $valid_arr = $this->Filter_unit->filter_array($decoded_json,$validate_map);
        
        $this->Model_storage->add_storage($valid_arr['name'],
                                          $valid_arr['street'],
                                          $valid_arr['house'],
                                          $valid_arr['note'],
                                          $_SESSION['id']);

        View::output_json(array("state"=>"success"));
    }

    /**
     * Update storage in Database
     * Structure of JSON_Input
     * address{
     *  string storage_id
     *  string street
     *  string house - house with block for ex. 'Рафиева 83к1' or 'Рафиева 113'
     *  string entry
     *  string note
     * }
     * @throws \Application\Exceptions\UFO_Except
     */
    public function action_update_storage(){

        // Example Post Request
        /*
        $input_json = '{"storage_id":8,
                       "name":"запасной",
                       "street":"рафиева",
                       "house":"113",
                       "note":"тест"
                       }';
        */
        $input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);

        // checks validity and decode input JSON
        $decoded_json = $this->Filter_unit->decode_Json($input_json);

        $validate_map = array(
            'storage_id' => array('filter'=>FILTER_VALIDATE_INT, 'flags'=>FILTER_NULL_ON_FAILURE),
            'name' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW),
            'street' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW),
            'house' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW),
            'note' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW)
        );

        $valid_arr = $this->Filter_unit->filter_array($decoded_json,$validate_map);

        $this->Model_storage->update_storage($valid_arr['storage_id'],$valid_arr['name'],$valid_arr['street'],$valid_arr['house'],$valid_arr['note'],$_SESSION['id']);
        View::output_json(array("state"=>"success"));
    }

    /**
     * Delete storage from Database
     * @throws \Application\Exceptions\Model_Except
     * @throws \Application\Exceptions\UFO_Except
     */
    public function action_delete_storage(){

        // Example Post Request
        /*
        $input_json = '{"storage_id":8}';
        */

        $input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);

        // checks validity and decode input JSON
        $decoded_json = $this->Filter_unit->decode_Json($input_json);

        $storage_id = filter_var($decoded_json['storage_id'],FILTER_VALIDATE_INT,FILTER_NULL_ON_FAILURE);

        $this->Model_storage->delete_storage($storage_id,$_SESSION['id']);

        View::output_json(array("state"=>"success"));
    }
}
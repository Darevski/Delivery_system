<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 11.04.16
 * Time: 23:56
 */

namespace Application\Models;


use Application\Exceptions\Model_Except;
use Dompdf\Dompdf;

/**
 * Class Model_Reports
 * @package Application\Models
 */
class Model_Reports{

    /**
     * @var string html template with info route
     */
    private $html_route_info_template;

    /**
     * @var string html template with row of list delivery points
     */
    private $html_list_points_row_template;

    /**
     * @var string html template with list of delivery_points
     */
    private $html_list_points_template;

    /**
     * @var string default template of report doc
     */
    private $html_base_template;

    /**
     * @var string html template with info about delivery point
     */
    private $html_point_info_template;

    /**
     * @var string html template with list orders of delivery point
     */
    private $html_list_orders_template;

    /**
     * @var Model_Delivery_Points
     */
    private $model_Delivery_Points;

    /**
     * @var Model_Orders
     */
    private $model_Orders;

    /**
     * Model_Reports constructor.
     * Load templates from files
     */
    public function __construct(){
        $this->html_base_template = file_get_contents('Application/Views/Skeletons/Route_Report.html');
        $this->html_list_points_row_template = file_get_contents('Application/Views/Skeletons/Route_List_Point_Row.html');
        $this->html_list_points_template = file_get_contents('Application/Views/Skeletons/Route_List_Points.html');
        $this->html_route_info_template = file_get_contents('Application/Views/Skeletons/Route_Info.html');
        $this->html_point_info_template = file_get_contents('Application/Views/Skeletons/Route_Delivery_Point.html');
        $this->html_list_orders_template = file_get_contents('Application/Views/Skeletons/Route_Order_Row.html');
        $this->model_Delivery_Points = new Model_Delivery_Points();
        $this->model_Orders = new Model_Orders();
    }

    /**
     * Generate PDF document by route from DB
     * Feature is in development
     * Don`t use it
     */
    public function get_pdf_routeInfo($date){
        $format_date = date("Y-m-d",$date);

        $route_manager = new Model_Route();

        $routes = $route_manager->get_route_by_date($date);
        // if there are no routes in database - stop generation
        if (count($routes) == 0)
            throw new Model_Except("Отсутствует маршрут");

        $route_number = 0;
        $html_route = '';
        $count_routes = count($routes);
        foreach ($routes as $route){
            $route_number+=1;
            $route_time = date('H:i',mktime(0,0,$route['total_time']));
            $points_count = count($route['points']);
            $html_route_header = $this->generate_route_header($route_number,$format_date,$route_time,$points_count);
            $html_route_points = $this->generate_route_points($route);
            $this->template_replace('{points_list}',"\n".$html_route_points."\n <hr>\n",$html_route_header);

            $html_route.= $html_route_header;

            $html_delivery_point = '';
            foreach($route['points'] as $point)
                $html_delivery_point.=$this->generate_delivery_point($point['point_id'])."\n";

            // if not last add page_break
            $html_route.= $html_delivery_point;
            $html_route.= ($route_number != $count_routes) ? "\n <hr>\n" : '';
        }
        $result = str_replace("{pages}",$html_route,$this->html_base_template);

        $pdf = new Dompdf();

        $pdf->loadHtml($result);
        $pdf->render();
        $pdf->stream("route.pdf",array('Attachment'=>0));
    }



    private function generate_delivery_point($point_id){
        $point_template = $this->html_point_info_template;
        $point_info = $this->model_Delivery_Points->get_info_about_point($point_id);

        $this->template_replace('{identifier}',$point_info['identifier_order'].' ('.$point_id.')',$point_template);

        $entry = ($point_info['entry']) ? ' под.'.$point_info['entry'] : '';
        $floor = ($point_info['floor']) ? ' эт.'.$point_info['floor'] : '';
        $flat = ($point_info['flat']) ? ' кв.'.$point_info['flat'] : '';
        $address =$point_info['street']. ' д.'. $point_info['house'].$entry.$floor.$flat;

        $this->template_replace('{address}',$address,$point_template);

        $this->template_replace('{phone_number}',$point_info['phone_number'],$point_template);
        $this->template_replace('{note}',$point_info['note'],$point_template);
        $this->template_replace('{time_start}',$point_info['time_start'],$point_template);
        $this->template_replace('{time_end}',$point_info['time_end'],$point_template);
        $this->template_replace('{total_cost}',$point_info['total_cost'],$point_template);

        $html_orders = $this->generate_orders($point_id);


        $this->template_replace('{orders_list}',$html_orders,$point_template);

        return $point_template;
    }

    private function generate_orders($point_id){
        $orders = $this->model_Orders->get_list_orders_by_point_id($point_id);
        $index = 0;
        $html_orders = '';
        foreach ($orders as $order){
            $template = $this->html_list_orders_template;
            $index+=1;
            $row_index = ($index&1) ? 'even_row' : 'odd_row';
            $this->template_replace('{row_index}',$row_index,$template);
            $this->template_replace('{index}',$index,$template);
            $this->template_replace('{description}',$order['description'],$template);
            $this->template_replace('{cost}',$order['cost'],$template);
            $html_orders.= $template."\n";
        }
        return $html_orders;
    }



    /**
     * Making html code for delivery points in route list by route
     * @param $route - single route from DB
     * @return mixed|string html code
     * @throws Model_Except
     */
    private function generate_route_points($route){
        // copy templates
        $points_template = $this->html_list_points_template;
        $row_template = $this->html_list_points_row_template;

        $count = 0;
        $total_cost = 0;
        $points = '';

        foreach ($route['points'] as $point){
            // use a template of delivery points table in pdf doc
            $point_row = $row_template;
            $count+=1;
            // change background color in pdf doc
            $row_index = ($count & 1) ? "even_row" : "odd_row";

            // get info about selected point
            $point_info = $this->model_Delivery_Points->get_info_about_point($point['point_id']);

            $flat = ($point_info['flat']) ? ' кв.'.$point_info['flat'] : '';
            $address = $point_info['street']." д.".$point_info['house']. $flat;

            $total_cost+=$point_info['total_cost'];

            $best_time = date('H:i:s',mktime(0,0,$point['time']));

            // insert values into template
            $this->template_replace("{row_index}",$row_index,$point_row);
            $this->template_replace("{count}",$count,$point_row);
            $this->template_replace("{cost}",$point_info['total_cost'],$point_row);
            $this->template_replace("{address}",$address,$point_row);
            $this->template_replace("{best_time}",$best_time,$point_row);
            $this->template_replace("{time_start}",$point_info['time_start'],$point_row);
            $this->template_replace("{time_end}",$point_info['time_end'],$point_row);
            $points.= $point_row."\n";
        }

        $this->template_replace("{route_points}",$points,$points_template);
        $this->template_replace("{total_cost}",$total_cost,$points_template);

        return $points_template;
    }

    /**
     * Making html code for main header route map by input parameters
     * @param $route_number int - route number in pdf doc
     * @param $format_date string
     * @param $route_time string  H:i - total time of route
     * @param $points_count int count of points in route
     * @return mixed|string html code
     */
    private function generate_route_header($route_number, $format_date, $route_time, $points_count){
        // copy template
        $route_info = $this->html_route_info_template;
        $route_index = $format_date.'#'.$route_number;

        // Insert values into template
        $this->template_replace("{route_index}",$route_index,$route_info);
        /* TODO добавить название организации в config */
        $this->template_replace("{corporation}","ООО Курсач",$route_info);
        $this->template_replace("{points_count}",$points_count,$route_info);
        $this->template_replace("{route_time}",$route_time,$route_info);

        return $route_info;
    }

    private function template_replace($find,$replace,&$source){
        $source = str_replace($find,$replace,$source);
    }

}

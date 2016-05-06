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
     * @var string default template of report doc
     */
    private $html_base;

    public function __construct(){
        $this->html_base = file_get_contents('Application/Views/Skeletons/Route_Report.html');
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
        $html_route_info = '';
        foreach ($routes as $route){
            $route_number+=1;
            $route_time = date('H:i',mktime(0,0,$route['total_time']));
            $points_count = count($route['points']);
            $html_route_header = $this->route_header($route_number,$format_date,$route_time,$points_count);
            $html_route_points = $this->route_points($route);
            $html_route_info.= $html_route_header."\n".$html_route_points."<hr>\n";
        }
        $result = str_replace("{pages}",$html_route_info,$this->html_base);

        $pdf = new Dompdf();

        $pdf->loadHtml($result);
        $pdf->render();
        $pdf->stream("route.pdf",array('Attachment'=>0));
    }


    /**
     * Making html code for delivery points in route list by route
     * @param $route - single route from DB
     * @return mixed|string html code
     * @throws Model_Except
     */
    private function route_points($route){
        // Load templates
        $row_template = file_get_contents('Application/Views/Skeletons/Route_Point_Row.html');
        $points_template = file_get_contents('Application/Views/Skeletons/Route_Points.html');
        $count = 0;
        $total_cost = 0;
        $points = '';
        $model_points = new Model_Delivery_Points();

        foreach ($route['points'] as $point){
            // use a template of delivery points table in pdf doc
            $point_row = $row_template;
            $count+=1;
            // change background color in pdf doc
            $row_index = ($count & 1) ? "even_row" : "odd_row";

            // get info about selected point
            $point_info = $model_points->get_info_about_point($point['point_id']);

            $flat = ($point_info['flat']) ? ' кв.'.$point_info['flat'] : '';
            $address = $point_info['street']." д.".$point_info['house']. $flat;

            $total_cost+=$point_info['total_cost'];

            $best_time = date('H:i:s',mktime(0,0,$point['time']));

            // insert values into template
            $point_row = str_replace("{row_index}",$row_index,$point_row);
            $point_row = str_replace("{count}",$count,$point_row);
            $point_row = str_replace("{cost}",$point_info['total_cost'],$point_row);
            $point_row = str_replace("{address}",$address,$point_row);
            $point_row = str_replace("{best_time}",$best_time,$point_row);
            $point_row = str_replace("{time_start}",$point_info['time_start'],$point_row);
            $point_row = str_replace("{time_end}",$point_info['time_end'],$point_row);
            $points.= $point_row."\n";
        }

        $points_template = str_replace("{route_points}",$points,$points_template);
        $points_template = str_replace("{total_cost}",$total_cost,$points_template);

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
    private function route_header($route_number,$format_date,$route_time,$points_count){
        // get template
        $route_info = file_get_contents('Application/Views/Skeletons/Route_Info.html');
        $route_index = $format_date.'#'.$route_number;

        // Insert values into template
        $route_info = str_replace("{route_index}",$route_index,$route_info);
        /* TODO добавить название организации в config */
        $route_info = str_replace("{corporation}","ООО Курсач",$route_info);
        $route_info = str_replace("{points_count}",$points_count,$route_info);
        $route_info = str_replace("{route_time}",$route_time,$route_info);

        return $route_info;
    }


}

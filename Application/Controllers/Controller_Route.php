<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 09.04.16
 * Time: 16:06
 */

namespace Application\Controllers;

use Application\Core\Controller;
use Application\Core\View;

class Controller_Route extends Controller
{
    public function action_calculation(){
        // Example Post Request
        /*
        $input_json = '{"points": [ {"point_id":2,"time_start":72000,"time_end":79200},
                                    {"point_id":4,"time_start":64800,"time_end":72000},
                                    {"point_id":7,"time_start":61200,"time_end":79200},
                                    {"point_id":8,"time_start":61200,"time_end":79200},
                                    {"point_id":10,"time_start":72000,"time_end":73800},
                                    {"point_id":12,"time_start":68400,"time_end":79200},
                                    {"point_id":13,"time_start":61200,"time_end":79200},
                                    {"point_id":14,"time_start":68400,"time_end":79200},
                                    {"point_id":15,"time_start":66600,"time_end":68400}],
                        "timeMatrix": [ [null,1407.31,339.93,701.94,1119.18,1418.12,833.56,471.91,905.59,1314.73],
                                        [null,null,1520.57,1181.38,1683.86,2156.03,1827.48,1676.71,1572.4,1552.03],
                                        [null,1467.08,null,782,1056.5,1200.51,607.04,168.01,833.72,1252.05],
                                        [null,1224.55,730.01,null,1282.46,1807.89,1216.95,861.99,1155.44,1569.32],
                                        [null,1751.15,1202.96,1492.54,null,1813.59,1465.44,1240.12,496.93,856.35],
                                        [null,2171.67,1174.31,1794.26,1847.74,null,996.52,1172.18,1606.48,1459.85],
                                        [null,1714.15,535.53,1075.83,1259.28,1016.37,null,527.67,1036.5,1278.68],
                                        [null,1429.02,250.58,870.35,1099.8,1031.49,446.93,null,870.36,1162.62],
                                        [null,1534.33,943.87,1308.6,373.02,1454.9,1206.35,981.03,null,639.36],
                                        [null,1086.36,1341.26,1447.05,794,1504.86,1390.44,1358.14,705.9,null]]
        }';
        */
    }

    /**
     * Display route calculation interface
     * @api 'Server/Route'
     */
    public function action_start(){
        View::display('routes.html');
    }
}
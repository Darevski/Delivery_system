<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 11.04.16
 * Time: 23:56
 */

namespace Application\Models;


use Application\Units\PDF_Generator;

class Model_Reports{


    /**
     * Feature is in development
     * Don`t use it
     */
    public function get_pdf_routeInfo($date){
        $pdf= new PDF_Generator();
        $pdf->SetAuthor('Система доставки');
        $pdf->SetTitle('Маршрутные листы');



        $pdf->AddPage();
        $pdf->SetFont('roboto','',12);
        //$pdf->writeHTMLCell(0,0,0,0,$header);



        //$pdf->setHeaderData('/css/icons/edit.png');
        //$result = $pdf->getHeaderData();



       // $pdf->Write(5,"КЕК");
      // $pdf->AddPage();
        $pdf->Output('doc');

/*
         $model_orders = new Model_Orders();
         $pdf_unit->SetTitle('Маршруты доставки');
         $pdf_unit->SetAuthor('Delivery_System');
         $pdf_unit->SetTextColor(50,60,100);

         $routes = $this->get_route_by_date($date);
         $height = 20;
         foreach ($routes as $key=>$route){
             $hours = floor($route['total_time']/3600);
             $minutes = floor(($route['total_time'] - $hours*3600)/60);
             $route_number = $key+1;

             $pdf_unit->AddPage('P');
             $pdf_unit->SetFont('Helvetica','B',20);
             $pdf_unit->SetXY(20,$height);
             $pdf_unit->Write(10,"Маршрут №$route_number занимает времени: $hours:$minutes");

             $height+=20;
             foreach ($route['points'] as $point){
                 $pdf_unit->SetXY(50,$height);
                 $pdf_unit->SetFont('Helvetica','',14);
                 $pdf_unit->Write(10,'Адрес доставки: ');
                 $model_orders->get_list_orders_by_point_id($point['point_id']);
             }
         }
*/

    }



}
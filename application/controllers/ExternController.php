<?php

/**
 * Extern controller
 * @author vpriem
 * @since 23.02.2011
 */
class ExternController extends Default_Controller_Auth {

    /**
     * Studivz
     * @author vpriem
     * @since 23.02.2011
     */
    public function studivzAction() {

        $request = $this->getRequest();

        // print xml for flash chart
        $xml = $request->getParam("xml");
        if ($xml == "on") {

            $col = array();
            $cat = array();
            $dat = array();

            $orders = Yourdelivery_Model_Order::allSaleChannel("vz.lieferando.de");
            foreach ($orders as $o) {
                $cat[] = $o['date'];
                $col[$o['saleChannel']] = Default_Helpers_Random::color();
                $dat[$o['saleChannel']][$o['date']] = $o['orders'];
            }
            $cat = array_unique($cat);
            sort($cat);
            foreach ($cat as $c) {
                foreach ($dat as $u => $d) {
                    if (!array_key_exists($c, $d)) {
                        $dat[$u][$c] = 0;
                    }
                    ksort($dat[$u]);
                }
            }

            $this->view->col = $col;
            $this->view->cat = $cat;
            $this->view->dat = $dat;
            $this->render("studivzxml");
        }
    }

}

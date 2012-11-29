<?php

/**
 * Secure Download controller
 * @author vpriem
 * @since 01.10.2010
 */
class DownloadController extends Default_Controller_Base {

    /**
     * @var string
     */
    private $_hash;
    /**
     * @var string
     */
    private $_ext;

    /**
     * Init
     * @author vpriem
     * @since 08.10.2010
     */
    public function init() {

        // disable view
        $this->_helper->viewRenderer->setNoRender(true);

        // get hash
        $hash = $this->_request->getParam('hash');
        if ($hash === null) {
            return $this->_forward("forbidden");
        }
        $this->_hash = $hash;

        // get ext
        $this->_ext = $this->_request->getParam('ext');
    }

    /**
     * 404 Not found
     * @author vpriem
     * @since 06.10.2010
     */
    public function notfoundAction() {

        $this->_helper->viewRenderer->setNoRender(false);
        $this->getResponse()->setHttpResponseCode(404);
        $this->view->url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * 403 Forbidden
     * @author vpriem
     * @since 06.10.2010
     */
    public function forbiddenAction() {

        $this->_helper->viewRenderer->setNoRender(false);
        $this->getResponse()->setHttpResponseCode(403);
        $this->view->url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * Force download
     * @author vpriem
     * @since 01.10.2010
     * @param string|array $file
     * @param string $filename
     */
    private function _download($file, $filename = null) {

        // if an array of files was provided,
        // pack them all
        if (is_array($file)) {
            $files = $file;
            $file = tempnam('/tmp', time());

            $zip = new ZipArchive();
            $zip->open($file, ZIPARCHIVE::CREATE);
            foreach ($files as $f) {
                if (file_exists($f) && is_file($f)) {
                    $zip->addFile($f, basename($f));
                }
            }
            $zip->close();
        }

        if (file_exists($file) && is_file($file)) {
            if ($filename === null) {
                $filename = basename($file);
            }

            $ext = strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
            switch ($ext) {
                case "CSV":
                    $type = "application/csv-tab-delimited-table";
                    break;

                case "HTML":
                    $type = "text/html";
                    break;

                case "PDF":
                    $type = "application/pdf";
                    break;

                case "TXT":
                    $type = "text/plain";
                    break;

                case "ZIP":
                    ini_set('zlib.output_compression', 'Off');
                    $type = "application/zip";
                    break;

                default:
                    $type = "application/force-download";
            }

            $this->getResponse()
                    ->setHttpResponseCode(200)
                    ->setHeader('Content-Type', $type)
                    ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                    //->setHeader('Content-Length',filesize($file))
                    ->setHeader('Content-Transfer-Encoding', 'binary')
                    ->setHeader('Expires', '0')
                    ->setHeader('Pragma', 'no-cache');

            readfile($file);
            return true;
        }

        // 404 if file not found
        $this->logger->err(sprintf('Could not provide %s for downloading: not found', $file));
        return $this->_forward("notfound");
    }

    /**
     * Order downloading
     * @author vpriem
     * @since 01.10.2010
     */
    public function orderAction() {

        // get order
        $order = Yourdelivery_Model_Order::createFromHash($this->_hash);
        if (!is_object($order)) {
            return $this->_forward("notfound");
        }

        // user who logged into partner backend
        // can only download their own orders
        if ($this->session->partnerRestaurantId !== null && $this->session_admin->admin === null) {
            if ($this->session->partnerRestaurantId != $order->getRestaurantId()) {
                return $this->_forward("forbidden");
            }
        }
        // user who logged into admin backend 
        // have no restrictions
        elseif ($this->session_admin->admin === null) {
            return $this->_forward("forbidden");
        }
        
        // download
        $this->_download(
            $order->getPdf(), "Bestellung-" . $order->getNr() . ".pdf"
        );
    }

    /**
     * Courier Order downloading
     * @author vpriem
     * @since 01.10.2010
     */
    public function courierAction() {

        // get order
        $order = Yourdelivery_Model_Order::createFromHash($this->_hash);
        if (!is_object($order)) {
            return $this->_forward("notfound");
        }

        // if is not admin
        if ($this->session_admin->admin === null) {
            return $this->_forward("forbidden");
        }

        // download
        $this->_download(
            $order->getCourierPdf(),
            "Kurier-Bestellung-" . $order->getNr() . ".pdf"
        );
    }

    /**
     * Bill downloading
     * @author vpriem, mlaug
     * @since 01.10.2010
     */
    public function billAction() {

        // get bill
        $bill = Yourdelivery_Model_Billing::createFromHash($this->_hash);
        if (!is_object($bill)) {
            return $this->_forward("notfound");
        }
        
        /**
         * @todo: add restrictions
         * company
         * restaurant
         * courier
         * admin / user seperation!
         */
        if ($this->session->partnerRestaurantId !== null) {
            if ($this->session->partnerRestaurantId != $bill->getObject()->getId()) {
                return $this->_forward("forbidden");
            }
        }
        elseif ($this->session_admin->admin === null) {
            $customer = $this->getCustomer();

            // is user logged in
            if (!$customer->isLoggedIn()) {
                return $this->_forward("forbidden");
            }

            if ($bill->getMode() != "company") {
                return $this->_forward("forbidden");
            }

            if ($customer->getCompany()->getId() != $bill->getObject()->getId()) {
                return $this->_forward("forbidden");
            }

            if (!$customer->isCompanyAdmin()) {
                return $this->_forward("forbidden");
            }
        }
        
        // download
        switch ($this->_ext) {
            case "pdf":
                $this->_download($bill->getPdf());
                break;
            
            case "voucher":
                $this->_download($bill->getVoucherPdf());
                break;
            
            case "asset":
                $this->_download($bill->getAssetPdf());
                break;

            case "csv":
                $this->_download($bill->getCsv());
                break;

            default: // zip
                $files = array_merge(array($bill->getPdf()), $bill->getAdditionalFiles(true, true));
                $this->_download($files, $bill->getNumber() . '.zip');
        }
    }

    /**
     * Sub bill downloading
     * @author vpriem, mlaug
     * @since 01.10.2010
     */
    public function subbillAction(){

        // get bill
        $bill = Yourdelivery_Model_Billing::createFromHash($this->_hash);
        if (!is_object($bill)) {
            return $this->_forward("notfound");
        }

        // if is not admin
        if ($this->session_admin->admin === null) {
            return $this->_forward("forbidden");
        }

        // download, use ext like an id
        $this->_download($bill->getSubPdf($this->_ext));

    }

    /**
     * Voucher downloading
     * @author vpriem, mlaug
     * @since 01.10.2010
     */
    public function voucherAction() {

        // get bill
        $bill = Yourdelivery_Model_Billing::createFromHash($this->_hash);
        if (!is_object($bill)) {
            return $this->_forward("notfound");
        }

        // if is not admin
        if ($this->session_admin->admin === null) {
            $this->_forward("forbidden");
        }

        // download
        $this->_download($bill->getVoucherPdf());
    }
    
    /**
     * Asset downloading
     * @author mlaug
     * @since 20.03.2011
     */
    public function assetAction() {

        // get bill
        $bill = Yourdelivery_Model_Billing::createFromHash($this->_hash);
        if (!is_object($bill)) {
            return $this->_forward("notfound");
        }

        // if is not admin
        if ($this->session_admin->admin === null) {
            $this->_forward("forbidden");
        }

        // download
        $this->_download($bill->getAssetPdf());
    }

}

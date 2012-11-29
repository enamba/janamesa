<?php

require_once 'Zend/Service/Amazon/S3.php';

/**
 * @author Alex Vait <vait@lieferando.de>
 */
class Default_Helpers_AmazonS3 {
    
    private static $_accessKey = "KEY";
    
    private static $_secretKey = "SECRET";

    /**
     * save file to the amazon bucket
     * @author Alex Vait <vait@lieferando.de>
     * @since 06.12.2011
     * @param string $domain  - "lieferando.de", "taxiresto.fr"
     * @param string $objectName  - object in the bucket
     * @param string $file - path to the local file, which shall be put to the amazon s3
     * @return boolen success
     */
    public static function putObject($domain, $objectName, $file){
        if (!IS_PRODUCTION) {
            $domain = $domain . '.testing';
        }
        
        $logger = Zend_Registry::get('logger');
            
        for($i=0;$i<3;$i++){
            try{

                $s3 = new Zend_Service_Amazon_S3(self::$_accessKey, self::$_secretKey);
                $buckets = $s3->getBuckets();
                $logger->debug('S3: getting bucket from S3 storage');

                if (!in_array($domain, $buckets)) {
                    $status = $s3->createBucket($domain, 'EU');

                    if (!$status) {
                        return false;
                    }
                }

                $logger->debug(sprintf('S3: uploading file %s to S3 bucket %s',$objectName, $domain));
                return $s3->putObject($domain . "/" . $objectName, 
                            file_get_contents($file), 
                            array(  Zend_Service_Amazon_S3::S3_ACL_HEADER =>
                                    Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ)
                            );          
            }
            catch ( Zend_Http_Client_Adapter_Exception $e ){
                $logger->warn(sprintf('S3: failed to upload to s3 storage: %s', $e->getMessage()));
                $logger->debug('S3: sleeping for 5 seconds');
                sleep(5);
                continue;
            }
        }
        
        $logger->crit('S3: tried 3 times to upload to s3 storage, quiting');
    }
    
    /**
     * remove file from the amazon bucket
     * @author Alex Vait <vait@lieferando.de>
     * @since 06.12.2011
     * @param string $domain  - "lieferando.de", "taxiresto.fr"
     * @param string $objectName  - object in the bucket
     */
    public static function removeObject($domain, $objectName){
        if (!IS_PRODUCTION) {
            $domain = $domain . '.testing';
        }
        
        $s3 = new Zend_Service_Amazon_S3(self::$_accessKey, self::$_secretKey);        
        $s3->removeObject($domain . "/" . $objectName);
    }    
}

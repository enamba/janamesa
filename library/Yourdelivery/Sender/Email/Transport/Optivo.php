<?php
/**
 * @author vpriem
 * @since 21.06.2011
 */
class Yourdelivery_Sender_Email_Transport_Optivo{
    
    /**
     * @author vpriem
     * @since 21.06.2011
     * @param Yourdelivery_Sender_Email_Abstract $email 
     * @return boolean
     */
    public function send(Yourdelivery_Sender_Email_Abstract $email) {
        
        $config = Zend_Registry::get('configuration');
        if (!$config->sender->email->optivo) {
            return false;
        }
        $params = $config->sender->email->optivo->toArray();
        if (!$params['enabled']) {
            return false;
        }
        
        $optivo = new Yourdelivery_Api_Optivo($params['id'], $params['username'], $params['password']);

        $mailing = $optivo->createMailing();
        $mailing->create("regular", null, $mimeType, $email->getRecipients(), $email->getFrom(), $email->getFromName());
        $mailing->setSubject($email->getSubject());
        $mailing->setContent($email->getBodyMime(), $email->getBodyRaw());

        if ($email->hasAttachments) {
            $parts = $email->getParts();
            foreach ($parts as $part) {
                $attachement = $optivo->createAttachement();
                $attachement->create(null, $part->type, $part->filename, $part->getContentRaw());
                $mailing->addAttachment($attachment);
            }
        }
        
        $mailing->start();
        return true;
        
    }
    
}

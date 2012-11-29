<?php

/**
 * Description of Invite
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
class Yourdelivery_Model_Invite {

    function __construct() {
        require_once(APPLICATION_PATH . '/../library/Yourdelivery/Invite/abimporter/abi.php');
        require_once(APPLICATION_PATH . '/../library/Yourdelivery/Invite/inviter/ozinviter.php');
    }

    function init() {
        //Set captcha folder and uri path (if not set, create a "captcha" subdirectory in current directory"
        oz_set_config('captcha_file_path', dirname(__FILE__) . '/../inviter/captcha');
        oz_set_config('captcha_uri_path', '../inviter/captcha');
        #oz_set_config('facebook.post_to_wall_only', FALSE);
        //Override inviter default settings. Full list is in ozinviter_config.php
        ozi_set_config('selector_ab_max_icons', 100);
        ozi_set_config('selector_sn_max_icons', 100);
        ozi_set_config('selector_group_icons', FALSE);
        #ozi_set_config('facebook_classicmode', FALSE);
        //Enable the new "add as friend" page which displays list of existing members of your website
        //Requires modification to oz_filter_contacts().
        #ozi_set_config('use_add_as_friend',TRUE);

        $session = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('configuration');

        ozi_set_config('default_personal_message', __('Hey, ich bestelle seit neuestem 
            auf %s mein Essen online. yourdelivery.de ist eine Seite bei der 
            man unkompliziert bei über 600 Lieferservices in Deutschland Essen bestellen kann, 
            ausserdem gibt es eine 100% Liefergarantie und man kann auch mit Kreditkarte bezahlen. 
            Vielleicht hast du ja mal Hunger und nichts im Kühlschrank, dann kannst du ja einfach mal 
            %s testen!
            Viele Grüße, %s', $config->domain->base, $config->domain->base, $session->customer->getFullname()));

        //If you have FB Connect API key, uncomment and customize the items below
        //ozi_set_config('web_name','MyWebsite!');	//Your website name
        //ozi_set_config('facebook_connect.api_key','83b391be2e4b18eb1e0b767a40235a03');	//Your FB Api Key
        //ozi_set_config('facebook_connect.receiver_path','xd_receiver.htm');	//Relative path ot xd_receiver.htm file
    }

    //--------------------------------------------------
    //THIS IS A CUSTOMIZABLE FUNCTION!
    //
    //Generate the invite message. You can modify this
    //to generate messages with personalized links, etc
    //--------------------------------------------------
    function oz_get_invite_message($from_name = NULL, $from_email = NULL, $personal_message = NULL) {
        //NOTE: You can build actual messages in real time containing referral/member id here.
        //{PERSONALMESSAGE} is replaced with the personal message if present, or blank otherwise.

        $url = 'http://www.yourdelivery.de/';

        //Add tracking redirection to url
        $url = ozi_process_url($url);

        if ($personal_message == NULL)
            $personal_message = '';
        $msg = array(
            //'web_name'=>"My Website Name",
            'subject' => "Einladung von Yourdelivery.de!",
            'text_body' => "This is a test message.   Hi! \r\n\nCome and take a look at a new interesting website " . $url . ".\r\n\r\n" . $personal_message,
            'html_body' => "This is a test message.   <b>Hi!</b> <br/><br/>Come and take a look at a new interesting website <a href='" . $url . "'>" . $url . "</a>.<br/><br/>" . htmlentities($personal_message, ENT_COMPAT, 'UTF-8'),
            'fbml_body' => "This is a test message.   <b>Hi!</b> Come and take a look at a new interesting website! " . htmlentities($personal_message, ENT_COMPAT, 'UTF-8'),
            'title' => 'Invitation link', //This is used as the title for link sharing on social bookmarks
            'url' => $url,
            //Uncomment the line below to allow a message specific for Twitter direct messages
            'text_body.is_twitter' => 'THIS IS A TWITTER SPECIFIC MESSAGE!',
                //
                //Available suffixes for now:
                //is_facebook, is_myspace, is_twitter, is_friendster, is_hi5, is_xing, is_bebo, is_blackplanet, is_meinvz, is_hyves
        );
        return $msg;
    }

    function oz_add_friends($member_ids) {
        //$member_ids is an array of member_id of the contacts.
        //Add member a,b,c as friends
        echo '<pre>';
        echo htmlentities(print_r($member_ids, TRUE));
        echo '</pre>';
    }

    //--------------------------------------------------
    //THIS IS A CUSTOMIZABLE FUNCTION!
    //
    //Filter/modify contacts list
    //
    //$contacts is a reference to an array of Contacts, where each Contact is an associative array containing the following values:
    //
    //	'name' => Name of the contact (may be blank)
    //	'email' => Email address of the contact (only for email contacts)
    //	'id' => An identifier for the contact (email address or social network user ID)
    //	'uid' => Social network user ID of the contact (not present in pure email contact)
    //	'image' => Absolute url to the thumbnail image of the contact (optional)
    //
    //This function can then perform the following
    //	1) Remove/inject contacts into $contacts
    //	2) For each contact, inject special attributes, as follows
    //
    //		'x-nocheckbox' => If set to true, disables the ability for the user to select the contact. Must be set to true if hyperlinks are present in contact row.
    //		'x-namehtml' => If set, this is html snippet used in place of the name for display. You may modify the html code to generate hyperlinks, etc.
    //		'x-emailhtml' => If set, this is html snippet used in place of the email for display.
    //
    //This is useful in cases where the contact is already a member of the website, and we would prefer to provide
    //the user the option to add the contact as a friend rather than sending an invitation emai.
    //--------------------------------------------------
    function oz_filter_contacts(&$contacts) {
        $cl = array(); //New contact list

        $n = count($contacts);
        for ($i = 0; $i < $n; $i++) {
            $c = &$contacts[$i];

            //DO WHATEVER PROCESSING YOU NEED ON THE CONTACT RECORD
            //The following disables selection checkbox for the contact
            //$c['x-nocheckbox'] = true;
            //The following makes a name hyperlinked (you can also inject buttons,etc)
            //$c['x-namehtml']='<a href="http://www.google.com" target="_blank">'.htmlentities(isset($c['name']) ? $c['name'] : '(no name)',ENT_COMPAT,'UTF-8').'</a>';
            //If this is an email contact (not a social contact), you can attempt to lookup the member in your system from the email address
            if (!isset($c['uid'])) {
                $email = $c['email'];

                //Let's assume that this user is an existing member of the website.
                //We'll hyperlink the name, remove the checkbox, and add a profile image.
                //$c['x-nocheckbox']=true;
                //$c['x-namehtml']='<a href="http://www.google.com" target="_blank">'.htmlentities(isset($c['name']) ? $c['name'] : '',ENT_COMPAT,'UTF-8').'</a>';
                //$c['image'] = 'http://static.ak.fbcdn.net/pics/q_silhouette.gif';
                //OR
                //If your website has a membership system, try to lookup the member id from the given email address, then set:
                //$c['image'] with the url to the member thumbnail
                //$c['name'] with the actual member name
                //$c['member_id'] with the id of the member in your system.
                //The following sets the image for the contact
                //$c['image'] = 'http://static.ak.fbcdn.net/pics/q_silhouette.gif';
            }


            //Add to new list. Skipping this basically causes the contact to be omitted.
            //(useful if the contact is already a friend, for example)
            $cl[] = $c;
        }
        $contact = $cl;
    }

    function oz_presend_invites(&$contacts) {
        //Here we have a chance to save/modify the invite list prior to sending
        /*
          $count = 0;
          $n = count($contacts);
          for ($i=0; $i<$n; $i++) {
          $c = &$contacts[$i];

          //Social network contacts do not have an email address
          if (isset($c['email'])) {
          $name = isset($c['name'])?$c['name']:NULL;
          $email = $c['email'];

          //TODO: Save invited contact to database
          }
          }
         */
    }

    /*


      function oz_get_select_limit($serviceId) {
      return ozi_get_default_select_limit($serviceId);
      }
     */

    public function renderInviter() {
        return oz_render_inviter('http://www.yourdelivery.de/media/js/library', 'http://www.yourdelivery.de/media/js/library');
    }

}


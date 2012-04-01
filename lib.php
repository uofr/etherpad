<?PHP  // $Id: lib.php,v 1.0 2012/03/28 18:30:00 Serafim Panov Exp $


$etherpadcfg = get_config('etherpad');

if (!isset($etherpadcfg->etherpad_apikey))
  set_config('etherpad_apikey', 'EtherpadFTW', 'etherpad');
if (!isset($etherpadcfg->etherpad_baseurl))
  set_config('etherpad_baseurl', 'http://beta.etherpad.org:9001', 'etherpad');
  

function etherpad_add_instance($etherpad) {
    global $CFG, $USER, $DB;

    $etherpad->timemodified = time();
    
    $id = $DB->insert_record("etherpad", $etherpad);

    $etherpadcfg = get_config('etherpad');
    $pprefix = etherpad_pad_prefix();
    
    require_once("etherpad-lite-client.php");
    
    $epad = new EtherpadLiteClient($etherpadcfg->etherpad_apikey,$etherpadcfg->etherpad_baseurl.'/api');
    $epad->createPad($pprefix.$id, strip_tags($etherpad->intro));

    return $id;
}


function etherpad_update_instance($etherpad) {
    global $CFG, $USER, $DB;
    
    $etherpad->timemodified = time();
    $etherpad->id = $etherpad->instance;
    
    return $DB->update_record("etherpad", $etherpad);
}


function etherpad_delete_instance($id) {
    global $CFG, $USER, $DB;
    
    if (! $etherpad = $DB->get_record("etherpad", array("id" => $id))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("etherpad", array("id" => $etherpad->id))) {
        $result = false;
    }
    
    $etherpadcfg = get_config('etherpad');
    $pprefix = etherpad_pad_prefix();
    
    require_once("etherpad-lite-client.php");
    
    $epad = new EtherpadLiteClient($etherpadcfg->etherpad_apikey,$etherpadcfg->etherpad_baseurl.'/api');
    $epad->deletePad($pprefix.$etherpad->id);

    return $result;
}

function etherpad_user_outline($course, $user, $mod, $etherpad) {
    return $return;
}

function etherpad_user_complete($course, $user, $mod, $etherpad) {
    return true;
}

function etherpad_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

function etherpad_cron () {
    global $CFG;

    return true;
}

function etherpad_grades($etherpadid) {
   return NULL;
}

function etherpad_get_participants($etherpadid) {
    return false;
}

function etherpad_scale_used ($etherpadid,$scaleid) {
    $return = false;

    return $return;
}


function etherpad_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

function etherpad_pad_prefix(){
  global $CFG;
  
  return str_replace(array("http://", "/", "."), "", $CFG->wwwroot);
}


function etherpad_activate_session(){
    global $USER;
    
    require_once("etherpad-lite-client.php");
    
    $etherpadcfg = get_config('etherpad');
  
    $epad = new EtherpadLiteClient($etherpadcfg->etherpad_apikey,$etherpadcfg->etherpad_baseurl.'/api');

    try {
      $mappedGroup = $epad->createGroupIfNotExistsFor($USER->email);
      $groupID = $mappedGroup->groupID;
    } catch (Exception $e) {}

    try {
      $author = $epad->createAuthorIfNotExistsFor($USER->firstname.' '.$USER->lastname, $USER->username);
      $authorID = $author->authorID;
    } catch (Exception $e) {}
    
    $validUntil = mktime(0, 0, 0, date("m"), date("d")+1, date("y"));
    $sessionID = $epad->createSession($groupID, $authorID, $validUntil);
    $sessionID = $sessionID->sessionID;
    setcookie("sessionID",$sessionID); 
}


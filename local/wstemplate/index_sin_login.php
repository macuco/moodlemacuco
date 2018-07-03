<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_once($CFG->dirroot.'/login/lib.php');
require_once("$CFG->dirroot/webservice/rest/locallib.php");
require_once($CFG->libdir.'/externallib.php');
require_once($CFG->libdir.'/classes/event/webservice_login_failed.php');

$CFG->debug=0;
//HTTPS is required in this page when $CFG->loginhttps enabled
//$PAGE->https_required();
/// Initialize variables

$idcourse = required_param('courseid',PARAM_INT);
$token = required_param('token',PARAM_RAW);
$errormsg = '';
$errorcode = 0;



/// auth plugins may override these - SSO anyone?
$frm  = false;
$user = false;


 
//print_object($USER);exit;
try{
	if(isset($USER) && $USER->id > 0){
		$sesskey = $USER->sesskey;
		$authsequence = get_enabled_auth_plugins(); // auths, in sequence
		confirm_sesskey($sesskey);
		foreach($authsequence as $authname) {
			$authplugin = get_auth_plugin($authname);
			$authplugin->logoutpage_hook();
		}
		require_logout_sisi();
	}
	
	
		$user = authenticate_by_token($token);
		
}catch(Exception $e){
	print_object($e);
	print_object($SESSION);
	$SESSION->loginerrormsg = $e->getMessage()."<br/>".$e->debuginfo;
	$SESSION->wantsurl = "j";
	//$_SESSION = $SESSION;
	//print_object($SESSION);exit;
	redirect(new moodle_url($CFG->httpswwwroot . '/login/index.php'));
}

$frm = $user;
//$frm->username = $user;
//$frm->password = "M@cuco88";



//print_object($user); exit;

//print_object($frm);exit;

// Restore the #anchor to the original wantsurl. Note that this
// will only work for internal auth plugins, SSO plugins such as
// SAML / CAS / OIDC will have to handle this correctly directly.
/*if ($anchor && isset($SESSION->wantsurl) && strpos($SESSION->wantsurl, '#') === false) {
    $wantsurl = new moodle_url($SESSION->wantsurl);
    $wantsurl->set_anchor(substr($anchor, 1));
    $SESSION->wantsurl = $wantsurl->out();
}*/

/// Check if the user has actually submitted login data to us


            //$user = authenticate_user_login($frm->username, $frm->password, false, $errorcode);
            /// Check for timed out sessions
            if (!empty($SESSION->has_timed_out)) {
            	$session_has_timed_out = true;
            	unset($SESSION->has_timed_out);
            } else {
            	$session_has_timed_out = false;
            }
   
    if ($user) {

        // language setup
        if (isguestuser($user)) {
            // no predefined language for guests - use existing session or default site lang
            unset($user->lang);

        } else if (!empty($user->lang)) {
            // unset previous session language - use user preference instead
            unset($SESSION->lang);
        }

    /// Let's get them all set up.
        complete_user_login($user);

        \core\session\manager::apply_concurrent_login_limit($user->id, session_id());

        // sets the username cookie
        if (!empty($CFG->nolastloggedin)) {
            // do not store last logged in user in cookie
            // auth plugins can temporarily override this from loginpage_hook()
            // do not save $CFG->nolastloggedin in database!

        } else if (empty($CFG->rememberusername) or ($CFG->rememberusername == 2 and empty($frm->rememberusername))) {
            // no permanent cookies, delete old one if exists
            set_moodle_cookie('');

        } else {
            set_moodle_cookie($USER->username);
        }
 
    }

/// Detect problems with timedout sessions
if ($session_has_timed_out and !data_submitted()) {
    $errormsg = get_string('sessionerroruser', 'error');
    $errorcode = 4;
}

/// First, let's remember where the user was trying to get to before they got here

if (empty($SESSION->wantsurl)) {
    $SESSION->wantsurl = null;
    $referer = get_local_referer(false);
    if ($referer &&
            $referer != $CFG->wwwroot &&
            $referer != $CFG->wwwroot . '/' &&
            $referer != $CFG->httpswwwroot . '/login/' &&
            strpos($referer, $CFG->httpswwwroot . '/login/?') !== 0 &&
            strpos($referer, $CFG->httpswwwroot . '/login/index.php') !== 0) { // There might be some extra params such as ?lang=.
        $SESSION->wantsurl = $referer;
    }
}


// make sure we really are on the https page when https login required
//$PAGE->verify_https_required();

/// Generate the login page with forms

if (!isset($frm) or !is_object($frm)) {
    $frm = new stdClass();
}

if (empty($frm->username) ) {  // See bug 5184
    if (!empty($_GET["username"])) {
        $frm->username = clean_param($_GET["username"], PARAM_RAW); // we do not want data from _POST here
    } else {
        $frm->username = get_moodle_cookie();
    }

    $frm->password = "";
}

if (!empty($frm->username)) {
    $focus = "password";
} else {
    $focus = "username";
}



$urltogo = $CFG->wwwroot.'/course/view.php?id='.$idcourse;

// test the session actually works by redirecting to self
$SESSION->wantsurl = $urltogo;

redirect(new moodle_url(get_login_url(), array('testsession'=>$USER->id)));



function authenticate_by_token($tokene){
	global $DB;

	$loginfaileddefaultparams = array(
			'context' => context_system::instance(),
			'other' => array(
					'method' => 1,//WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN
					'reason' => null
			)
	);
	
	if (!$token = $DB->get_record('external_tokens', array('token'=>$tokene, 'tokentype'=>0))) {
		// Log failed login attempts.
		$params = $loginfaileddefaultparams;
		$params['other']['reason'] = 'invalid_token';
		$event = \core\event\webservice_login_failed::create($params);
		
		$event->set_legacy_logdata(array(SITEID, 'webservice', get_string('tokenauthlog', 'webservice'), '' ,
				get_string('failedtolog', 'webservice').": ".$tokene. " - ".getremoteaddr() , 0));
		$event->trigger();
		
		throw new moodle_exception('invalidtoken', 'webservice');
		exit;
	}
	
	if ($token->validuntil and $token->validuntil < time()) {
		$DB->delete_records('external_tokens', array('token'=>$tokene, 'tokentype'=>0));
		throw new webservice_access_exception('Invalid token - token expired - check validuntil time for the token');
	}
	
	if ($token->sid){//assumes that if sid is set then there must be a valid associated session no matter the token type
		if (!\core\session\manager::session_exists($token->sid)){
			$DB->delete_records('external_tokens', array('sid'=>$token->sid));
			throw new webservice_access_exception('Invalid session based token - session not found or expired');
		}
	}

	if ($token->iprestriction and !address_in_subnet(getremoteaddr(), $token->iprestriction)) {
		$params = $loginfaileddefaultparams;
		$params['other']['reason'] = 'ip_restricted';
		$params['other']['tokenid'] = $token->id;
		$event = \core\event\webservice_login_failed::create($params);
		$event->add_record_snapshot('external_tokens', $token);
		$event->set_legacy_logdata(array(SITEID, 'webservice', get_string('tokenauthlog', 'webservice'), '' ,
				get_string('failedtolog', 'webservice').": ".getremoteaddr() , 0));
		$event->trigger();
		throw new webservice_access_exception('Invalid service - IP:' . getremoteaddr()
				. ' is not supported - check this allowed user');
	}

	$restricted_context = context::instance_by_id($token->contextid);
	$restricted_serviceid = $token->externalserviceid;

	$user = $DB->get_record('user', array('id'=>$token->userid), '*', MUST_EXIST);

	// log token access
	$DB->set_field('external_tokens', 'lastaccess', time(), array('id'=>$token->id));

	return $user;

}


function require_logout_sisi() {
	global $USER, $DB;

	if (!isloggedin()) {
		// This should not happen often, no need for hooks or events here.
		\core\session\manager::terminate_current();
		return;
	}

	// Execute hooks before action.
	$authplugins = array();
	$authsequence = get_enabled_auth_plugins();
	foreach ($authsequence as $authname) {
		$authplugins[$authname] = get_auth_plugin($authname);
		$authplugins[$authname]->prelogout_hook();
	}

	// Store info that gets removed during logout.
	$sid = session_id();
	$event = \core\event\user_loggedout::create(
			array(
					'userid' => $USER->id,
					'objectid' => $USER->id,
					'other' => array('sessionid' => $sid),
			)
			);
	if ($session = $DB->get_record('sessions', array('sid'=>$sid))) {
		$event->add_record_snapshot('sessions', $session);
	}

	// Clone of $USER object to be used by auth plugins.
	$user = fullclone($USER);

	// Delete session record and drop $_SESSION content.
	\core\session\manager::terminate_current();

	// Trigger event AFTER action.
	//$event->trigger();

	// Hook to execute auth plugins redirection after event trigger.
	foreach ($authplugins as $authplugin) {
		//$authplugin->postlogout_hook($user);
	}
}
<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * user signup page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../config.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->dirroot . '/mod/inea/inealib.php');
require_once($CFG->libdir . '/authlib.php');

$id_rol	= required_param('id_rol', PARAM_INT);
$rfe	= optional_param('rfe', 0, PARAM_INT);
$saltar	= optional_param('saltar', 0, PARAM_INT);
$finalizar = optional_param('finalizar', 0, PARAM_INT);

if(!isset($REG)) {
	unset($REG);
	global $REG;
	$REG = new stdClass();
}

if (!$authplugin = signup_is_enabled()) {
    print_error('notlocalisederrormessage', 'error', '', 'Sorry, you may not use this page.');
}
	
$PAGE->set_url('/login/signup.php', array('id_rol'=>$id_rol));
$PAGE->set_context(context_system::instance());

// If wantsurl is empty or /login/signup.php, override wanted URL.
// We do not want to end up here again if user clicks "Login".
if (empty($SESSION->wantsurl)) {
    $SESSION->wantsurl = $CFG->wwwroot . '/';
} else {
    $wantsurl = new moodle_url($SESSION->wantsurl);
    if ($PAGE->url->compare($wantsurl, URL_MATCH_BASE)) {
        $SESSION->wantsurl = $CFG->wwwroot . '/';
    }
}
if (isloggedin() and !isguestuser()) {
    // Prevent signing up when already logged in.
    echo $OUTPUT->header();
    echo $OUTPUT->box_start();
    $logout = new single_button(new moodle_url('/login/logout.php',
        array('sesskey' => sesskey(), 'loginpage' => 1)), get_string('logout'), 'post');
    $continue = new single_button(new moodle_url('/'), get_string('cancel'), 'get');
    echo $OUTPUT->confirm(get_string('cannotsignup', 'error', fullname($USER)), $logout, $continue);
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    exit;
}

if($saltar == 1){ // Formulario de inicio
	require_once($CFG->dirroot.'/login/signup_form.php');
    $REG->rfe = $rfe;
} else {
	switch($id_rol) {
		case ASESOR : $rol_form = '_asesor'; break;
		case EDUCANDO : $rol_form = '_educando'; break;
		default: $rol_form = ''; break;
	}
    require_once($CFG->dirroot.'/login/signup_form'.$rol_form.'.php'); // lo manda aarchivo de fomulario de acuerdo al ID del rol
}    	
$action = $CFG->wwwroot."/login/signup.php?id_rol=$id_rol";

$mform_signup = new login_signup_form($action);

if ($mform_signup->is_cancelled()) {
    redirect(get_login_url());
} 

// Boton Registrar presionado
//print_object($_POST);
if($finalizar) {
	//echo "<BR>Usuario registrado ...";
	if($user = $mform_signup->get_data()) {	
		//echo "<BR>Datos validados?";
		$user->confirmed   = 0;
		$user->lang        = current_language();
		$user->firstaccess = 0;
		$user->timecreated = time();
		$user->mnethostid  = $CFG->mnet_localhost_id;
		$user->secret      = random_string(15);
		$user->auth        = $CFG->registerauth;
        $user->id 		   = $user->id_user;
		// INEA: insertaR las lineas para introducir el municipio, plaza y zona al objeto user
		$user->city = $user->location[1];
		$user->skype = $user->location[2];
		$user->zona = inea_get_zona_by_plaza($user->skype); // Obtener la zona segun la plaza
		// Crear campos necesarios para el nombre
		$user->lastnamephonetic = '';
		$user->firstnamephonetic = '';
		$user->middlename = '';
		$user->alternatename = '';
		
        if(count(explode("/", $user->aim)) == 1)
        	$user->aim = date("d/m/Y", $user->aim); //MACUCO
		//print_object($user);
        //print_object($authplugin);
		//exit;
		//RUDY: insertamos registro en tabla user
        $authplugin->inea_user_signup($user, true, $id_rol);
		exit; //never reached
	}
}

/*if(!$REG->registrar) { // Es el paso final para registrar?
	$mform_signup->prevent_submit();
}*/
//print_object($mform_signup);

/*if ($mform_signup->is_cancelled()) {
    redirect(get_login_url());
} else if($mform_signup->is_submitted()) {
	echo "Data Submited";
}*/
/*} else if ($user = $mform_signup->get_data()) {
    echo "Entro aqui?";
	// Add missing required fields.
    $user = signup_setup_new_user($user);
    $authplugin->user_signup($user, true); // prints notice and link to login/index.php
    exit; //never reached
}*/

$title = get_string('registro', 'inea');
//$newaccount = get_string('newaccount');
//$login      = get_string('login');
//$PAGE->navbar->add($login);
//$PAGE->navbar->add($newaccount);
$PAGE->set_pagelayout('embedded');
$PAGE->set_title($title);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
$mform_signup->display();
echo $OUTPUT->footer();
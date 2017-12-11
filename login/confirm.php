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
 * Confirm self registered user.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/mod/inea/inealib.php');

$data = optional_param('data', '', PARAM_RAW);  // Formatted as:  secret/username

$p = optional_param('p', '', PARAM_ALPHANUM);   // Old parameter:  secret
$s = optional_param('s', '', PARAM_RAW);        // Old parameter:  username
$redirect = optional_param('redirect', '', PARAM_LOCALURL);    // Where to redirect the browser once the user has been confirmed.

// INEA - ID del usuario para confirmar e ID del rol para el enrolado
$id_user = optional_param('id_user', '', PARAM_CLEAN);  // El id del usuario a confirmar.
$id_rol = optional_param('id_rol', '', PARAM_CLEAN);  // Asignacion del rol en un curso.

$PAGE->set_url('/login/confirm.php', array('id_user'=>$id_user, 'id_rol'=>$id_rol));
$PAGE->set_context(context_system::instance());

if (!$authplugin = signup_get_user_confirmation_authplugin()) {
    throw new moodle_exception('confirmationnotenabled');
}

if (!empty($data) || (!empty($p) && !empty($s))) {

    if (!empty($data)) {
        $dataelements = explode('/', $data, 2); // Stop after 1st slash. Rest is username. MDL-7647
        $usersecret = $dataelements[0];
        $username   = $dataelements[1];
    } else {
        $usersecret = $p;
        $username   = $s;
    } 
	
	switch($id_rol) {
		case ASESOR : $es_educando = false; break;
		case EDUCANDO : $es_educando = true; break;
		default: break;
	}
	
	if($es_educando){
		//El usuario ya existe?
        if(!$user = $DB->get_record('user', array('id'=>$id_user))) {//Macuco si no esta registrado.
			print_error("Este usuario no se ha dado de alta");
        }
		
        $id_user = $user->id; //Macuco Se obtiene el id del usuario que se agrego en la tabla User
    }
	
    $confirmed = $authplugin->user_confirm($username, $usersecret);

    if ($confirmed == AUTH_CONFIRM_ALREADY) {
        $user = get_complete_user_data('username', $username);
        $PAGE->navbar->add(get_string("alreadyconfirmed"));
        $PAGE->set_title(get_string("alreadyconfirmed"));
        $PAGE->set_heading($COURSE->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
        echo "<p>".get_string("alreadyconfirmed")."</p>\n";
		if(!empty($id_user) and  !empty($id_rol)) { // Agregar valores al url para enrolar
			$params = array('id_user'=>$id_user, "id_rol"=>$id_rol);
            echo $OUTPUT->single_button(new moodle_url('/login/enrol.php', $params), get_string('courses'), 'POST');
        } else {
            echo $OUTPUT->single_button("$CFG->wwwroot/course/", get_string('courses'));
        }
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        exit;

    } else if ($confirmed == AUTH_CONFIRM_OK) {

        // The user has confirmed successfully, let's log them in

        if (!$user = get_complete_user_data('username', $username)) {
            print_error('cannotfinduser', '', '', s($username));
        }
		
		// INEA -- Poner el id del rol en el campo url
		$user->url = $id_rol;
		if(!$DB->update_record('user', $user)) {
			print_error('cannotfinduser', '', '', s($username));
		}
		
		// INEA -- Eliminar los registros de la tabla mdl_inea_user
		if(isset($id_inea) && !empty($id_inea) && $es_educando) {
			$DB->delete_records('inea_user', array('id'=>$id_inea)); //Macuco Se elimina el registro de INEA_USER
		}
		
        if (!$user->suspended) {
            complete_user_login($user);

            \core\session\manager::apply_concurrent_login_limit($user->id, session_id());

            // Check where to go, $redirect has a higher preference.
            if (empty($redirect) and !empty($SESSION->wantsurl) ) {
                $redirect = $SESSION->wantsurl;
                unset($SESSION->wantsurl);
            }

            if (!empty($redirect)) {
                redirect($redirect);
            }
        }

        $PAGE->navbar->add(get_string("confirmed"));
        $PAGE->set_title(get_string("confirmed"));
        $PAGE->set_heading($COURSE->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
        echo "<h3>".get_string("thanks").", ". fullname($USER) . "</h3>\n";
        echo "<p>".get_string("confirmed")."</p>\n";
		if(!empty($id_user) and  !empty($id_rol)) { // Agregar valores al url para enrolar
			$params = array('id_user'=>$id_user, "id_rol"=>$id_rol);
            echo $OUTPUT->single_button(new moodle_url('/login/enrol.php', $params), get_string('courses'), 'POST');
			echo '<input type="hidden" name="id_user" value="'.$id_user.'"/>';
            echo '<input type="hidden" name="id_rol" value="'.$id_rol.'"/>';
        } else {
            echo $OUTPUT->single_button("$CFG->wwwroot/course/", get_string('courses'));
        }
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        exit;
    } else {
        print_error('invalidconfirmdata');
    }
} else {
    print_error("errorwhenconfirming");
}

redirect("$CFG->wwwroot/");

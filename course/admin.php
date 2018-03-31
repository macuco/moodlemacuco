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
 * Listing of the course administration pages for this course.
 *
 * @copyright 2016 Damyon Wiese
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../config.php");
if(file_exists($CFG->dirroot . '/mod/inea/inealib.php')){
	require_once($CFG->dirroot . '/mod/inea/inealib.php');
}

$courseid = required_param('courseid', PARAM_INT);

$PAGE->set_url('/course/admin.php', array('courseid'=>$courseid));

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);
$context = context_course::instance($course->id);

$PAGE->set_pagelayout('incourse');

// INEA - Verificar si es responsable estatal
$isresponsable = false;
$currentuser = $DB->get_record('user', array('id' => $USER->id), '*', MUST_EXIST);
if($myroles = inea_get_system_roles($currentuser->id)) {
	foreach($myroles as $id_rol=>$nombre_rol) {
		// Es responasable estatal ?
		if($id_rol == RESPONSABLE) {
			$isresponsable = true;
			break;
		}
	}
}

// INEA - Mostrar opcion de filtrado por entidad si es administrador
$isadmin = false;
$admins = get_admins();
foreach($admins as $admin) {
	if ($USER->id == $admin->id) {
		$isadmin = true;
		break;
	}
}

if ($courseid == $SITE->id) {
    $title = get_string('frontpagesettings');
    $node = $PAGE->settingsnav->find('frontpage', navigation_node::TYPE_SETTING);
} else {
	$title = get_string('courseadministration');
    $node = $PAGE->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
	
	if($node) {
		
	}
}
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($title);
echo $OUTPUT->header();
echo $OUTPUT->heading($title);
//ini_set('memory_limit', '-1');
if ($node) {
	// INEA - Mostrar lista de concluidos solo a Admin y Responsable Estatal
	if($isadmin || $isresponsable) {
		$concluidotag = get_string('listaconcluidos', 'inea');
		if($course->id > 1) {
			$concluidourl = new moodle_url('/mod/inea/usuarioconcluido.php?courseid='.$course->id);
		} else {
			$concluidourl = new moodle_url('/mod/inea/usuarioconcluido.php');
		}
		$concluidonode = navigation_node::create(
			$concluidotag,
            $concluidourl,
            navigation_node::TYPE_COURSE);
		$node->add_node($concluidonode);
	}
    echo $OUTPUT->render_from_template('core/settings_link_page', ['node' => $node]);
}

echo $OUTPUT->footer();

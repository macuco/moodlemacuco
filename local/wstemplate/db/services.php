<?php

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
 * Web service local plugin template external functions and service definitions.
 *
 * @package    localwstemplate
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
        'local_wstemplate_hello_world' => array(
                'classname'   => 'local_wstemplate_external',
                'methodname'  => 'hello_world',
                'classpath'   => 'local/wstemplate/externallib.php',
		'component'   => 'local',
                'description' => 'Return Hello World FIRSTNAME. Can change the text (Hello World) sending a new text as parameter',
                'type'        => 'read',
        ),
         // Grade related functions.

    'local_grades_get_grades_group' => array(
        'classname'     => 'core_grades_group_external',
        'methodname'    => 'get_grades',
        'description'   => 'Returns student course total grade and grades for activities.
                                This function does not return category or manual items.
                                This function is suitable for managers or teachers not students.',
        'classpath'   => 'local/wstemplate/externallib_grades_group.php',
        'type'          => 'read',
        'component'   => 'local',
        'capabilities'  => 'moodle/grade:view, moodle/grade:viewall, moodle/grade:viewhidden',
    ),
    
    'local_change_numsections' => array(
        'classname'     => 'local_sections',
        'methodname'    => 'change_numsections',
        'description'   => 'Incrementa o decrementa el numero de secciones de un curso',
        'classpath'   => 'local/wstemplate/externallib_sections.php',
        'type'          => 'write',
        'component'   => 'local',
        'capabilities'  => 'moodle/course:update',
    ),
    
    'local_get_section' => array(
        'classname'     => 'local_sections',
        'methodname'    => 'get_section',
        'description'   => 'Obtiene una o todas las secciones de un curso',
        'classpath'   => 'local/wstemplate/externallib_sections.php',
        'type'          => 'read',
        'component'   => 'local',
        'capabilities'  => 'moodle/course:update',
    ),
    
    'local_update_sections' => array(
        'classname'     => 'local_sections',
        'methodname'    => 'update_sections',
        'description'   => 'Actualiza una o mas secciones de un curso',
        'classpath'   => 'local/wstemplate/externallib_sections.php',
        'type'          => 'write',
        'component'   => 'local',
        'capabilities'  => 'moodle/course:update',
    ),
		'local_delete_sections' => array(
				'classname'     => 'local_sections',
				'methodname'    => 'delete_sections',
				'description'   => 'Elimina una seccion de un curso, junto con su contenido',
				'classpath'   => 'local/wstemplate/externallib_sections.php',
				'type'          => 'write',
				'component'   => 'local',
				'capabilities'  => 'moodle/course:update',
		),
		
		'local_courses_without_idnumber' => array(
				'classname'     => 'local_courses',
				'methodname'    => 'get_courses_without_idnumber',
				'description'   => 'Obtiene los cursos que tienen el idnumber vacio',
				'classpath'   => 'local/wstemplate/externallib_courses.php',
				'type'          => 'read',
				'component'   => 'local',
				'capabilities'  => 'moodle/course:update, moodle/course:viewhiddencourses, moodle/course:view',
		),
		'local_courses_create_notvisible' => array(
				'classname'     => 'local_courses',
				'methodname'    => 'create_course_notvisible',
				'description'   => 'Crea un curso invisible y regresa el id generado',
				'classpath'   => 'local/wstemplate/externallib_courses.php',
				'type'          => 'write',
				'component'   => 'local',
				'capabilities'  => 'moodle/course:create,moodle/course:visibility',
		),
		
		'local_mods_create_forum' => array(
				'classname'     => 'local_mods',
				'methodname'    => 'create_forum',
				'description'   => 'Crea un foro en una seccion de un curso y regresa el id del foro',
				'classpath'   => 'local/wstemplate/externallib_mods.php',
				'type'          => 'write',
				'component'   => 'local',
				'capabilities'  => 'moodle/course:update,moodle/grade:managegradingforms',
		),
		
		'local_mods_delete' => array(
				'classname'     => 'local_mods',
				'methodname'    => 'delete_mod',
				'description'   => 'Elimina cualquier mod (Foro, Chat, SCORM, ETC )',
				'classpath'   => 'local/wstemplate/externallib_mods.php',
				'type'          => 'write',
				'component'   => 'local',
				'capabilities'  => 'moodle/course:update,moodle/course:manageactivities',
		),
		
		'local_mods_create_scorm' => array(
				'classname'     => 'local_mods',
				'methodname'    => 'create_scorm',
				'description'   => 'Crea un scorm en una seccion de un curso y regresa el id del scorm',
				'classpath'   => 'local/wstemplate/externallib_mods.php',
				'type'          => 'write',
				'component'   => 'local',
				'capabilities'  => 'moodle/course:update',
		),
		
		'local_courses_create_tags' => array(
				'classname'     => 'local_courses',
				'methodname'    => 'courses_create_tags',
				'description'   => 'Crea una lista de tags del curso, eliminando las existentes. Los tagas son mostrados como competencias del curso',
				'classpath'   => 'local/wstemplate/externallib_courses.php',
				'type'          => 'write',
				'component'   => 'local',
				'capabilities'  => 'moodle/course:update',
		),
		
		'local_courses_duplicate_course' => array(
				'classname'     => 'local_courses',
				'methodname'    => 'courses_duplicate_course',
				'description'   => 'Duplica un curso con o sin datos de usuarios',
				'classpath'   => 'local/wstemplate/externallib_courses.php',
				'type'          => 'write',
				'component'   => 'local',
				'capabilities'  => 'moodle/course:update,moodle/course:create,moodle/course:visibility',
		),
		
		'local_forums_delete_discussion' => array(
				'classname'     => 'local_mods',
				'methodname'    => 'delete_discussion',
				'description'   => 'Elimina una discución de un foro',
				'classpath'   => 'local/wstemplate/externallib_mods.php',
				'type'          => 'write',
				'component'   => 'local',
				'capabilities'  => 'mod/forum:deleteownpost,mod/forum:deleteanypost',
		),
		
		'local_courses_avance_oas' => array(
				'classname'     => 'local_courses',
				'methodname'    => 'courses_avance_oas',
				'description'   => 'Obtiene el avance de los OAs o medallas (Es decir el valor que tiene la barra de progreso de las medallas)',
				'classpath'   => 'local/wstemplate/externallib_courses.php',
				'type'          => 'read',
				'component'   => 'local',
				'capabilities'  => 'moodle/grade:view',
		),
		
		'local_add_discussion_news' => array(
				'classname'     => 'local_mods',
				'methodname'    => 'add_discussion_news',
				'description'   => 'Registra una discusión en el foro de novedades',
				'classpath'   => 'local/wstemplate/externallib_mods.php',
				'type'          => 'write',
				'component'   => 'local',
				'capabilities'  => '',
		),
		
		'local_role_is_user_enrolled' => array(
				'classname'     => 'local_role',
				'methodname'    => 'is_user_enrolled',
				'description'   => 'Verifica si un usuario se encuentra enrolado',
				'classpath'   => 'local/wstemplate/externallib_role.php',
				'type'          => 'read',
				'component'   => 'local',
		),
		
		'local_update_sections_sequence' => array(
				'classname'     => 'local_sections',
				'methodname'    => 'update_sections_sequence',
				'description'   => 'Actualiza una o mas secciones de un curso',
				'classpath'   => 'local/wstemplate/externallib_sections.php',
				'type'          => 'write',
				'component'   => 'local',
				'capabilities'  => 'moodle/course:update',
		),
		
		'local_courses_backup_course' => array(
				'classname'     => 'local_courses',
				'methodname'    => 'courses_backup_course',
				'description'   => 'Obtiene una URL para descargar un backup de un curso',
				'classpath'   => 'local/wstemplate/externallib_courses.php',
				'type'          => 'write',
				'component'   => 'local',
				'capabilities'  => 'moodle/restore:restorecourse,moodle/restore:uploadfile',
		),
		
		
		
);

/*
 
 	INSERT INTO `mdl_external_functions` (`id`, `name`, `classname`, `methodname`, `classpath`, `component`, `capabilities`) 
  			VALUES (NULL, 'local_courses_backup_course', 'local_courses', 'courses_backup_course', 
  			'local/wstemplate/externallib_courses.php', 'local_wstemplate', 
  			'');
  			
  	
 
 	INSERT INTO `mdl_external_functions` (`id`, `name`, `classname`, `methodname`, `classpath`, `component`, `capabilities`) 
  			VALUES (NULL, 'local_update_sections_sequence', 'local_sections', 'update_sections_sequence', 
  			'local/wstemplate/externallib_sections.php', 'local_wstemplate', 'moodle/course:update');
 
  INSERT INTO `mdl_external_functions` (`id`, `name`, `classname`, `methodname`, `classpath`, `component`, `capabilities`) VALUES (NULL, 'local_mods_create_forum', 'local_mods', 'create_forum', 'local/wstemplate/externallib_mods.php', 'local_wstemplate', 'moodle/course:update');
  INSERT INTO `mdl_external_functions` (`id`, `name`, `classname`, `methodname`, `classpath`, `component`, `capabilities`) VALUES (NULL, 'local_mods_delete', 'local_mods', 'delete_mod', 'local/wstemplate/externallib_mods.php', 'local_wstemplate', 'moodle/course:update,moodle/course:manageactivities');
  INSERT INTO `mdl_external_functions` (`id`, `name`, `classname`, `methodname`, `classpath`, `component`, `capabilities`) VALUES (NULL, 'local_mods_create_scorm', 'local_mods', 'create_scorm', 'local/wstemplate/externallib_mods.php', 'local_wstemplate', 'moodle/course:update');
  INSERT INTO `mdl_external_functions` (`id`, `name`, `classname`, `methodname`, `classpath`, `component`, `capabilities`) 
  			VALUES (NULL, 'local_courses_create_tags', 'local_courses', 'courses_create_tags', 'local/wstemplate/externallib_courses.php', 'local_wstemplate', 'moodle/course:update');
  			
  INSERT INTO `mdl_external_functions` (`id`, `name`, `classname`, `methodname`, `classpath`, `component`, `capabilities`) 
  			VALUES (NULL, 'local_courses_duplicate_course', 'local_courses', 'courses_duplicate_course', 'local/wstemplate/externallib_courses.php', 'local_wstemplate', 'moodle/course:update,moodle/course:create,moodle/course:visibility');
  			
  INSERT INTO `mdl_external_functions` (`id`, `name`, `classname`, `methodname`, `classpath`, `component`, `capabilities`) 
  			VALUES (NULL, 'local_forums_delete_discussion', 'local_mods', 'delete_discussion', 'local/wstemplate/externallib_mods.php', 
  			'local_wstemplate', 'mod/forum:deleteownpost,mod/forum:deleteanypost');
  
  INSERT INTO `mdl_external_functions` (`id`, `name`, `classname`, `methodname`, `classpath`, `component`, `capabilities`) 
  			VALUES (NULL, 'local_add_discussion_news', 'local_mods', 'add_discussion_news', 
  			'local/wstemplate/externallib_mods.php', 'local_wstemplate', 
  			'');
  			
  			
  INSERT INTO `mdl_external_functions` (`id`, `name`, `classname`, `methodname`, `classpath`, `component`, `capabilities`) 
  			VALUES (NULL, 'local_courses_avance_oas', 'local_courses', 'courses_avance_oas', 
  			'local/wstemplate/externallib_courses.php', 'local_wstemplate', 
  			'moodle/grade:view');
  			
  			
  			
  			INSERT INTO `mdl_external_functions` (`id`, `name`, `classname`, `methodname`, `classpath`, `component`, `capabilities`) 
  			VALUES (NULL, 'local_role_is_user_enrolled', 'local_role', 'is_user_enrolled', 
  			'local/wstemplate/externallib_role.php', 'local_wstemplate', 
  			'');
  			
  	
  		
 */

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'WSSISI' => array(
                'functions' => array (
                		'mod_forum_get_forums_by_courses', 'mod_forum_add_discussion', 'servicios_web_lesson_create_lesson', 
                		'servicios_web_page_create_page', 'servicios_web_glosarry_create_glosarry', 'servicios_web_resource_get_section', 
                		'servicios_web_resource_get_activity', 'local_forums_delete_discussion', 'local_mods_create_forum',
                		'local_mods_create_scorm', 'local_mods_delete', 'local_courses_without_idnumber', 'local_courses_create_notvisible',
                		'local_courses_duplicate_course', 'local_courses_create_tags', 'local_courses_avance_oas', 'local_grades_get_grades_group', 
                		'local_change_numsections', 'local_get_section', 'local_update_sections', 'core_group_create_groups', 'core_group_delete_groups',
                		'core_group_get_groups', 'core_group_add_group_members', 'core_group_delete_group_members', 'core_course_create_categories', 
                		'core_course_search_courses', 'core_course_create_courses', 'core_course_get_courses', 'core_course_update_courses', 
                		'core_course_delete_courses', 'core_course_get_contents', 'core_course_get_categories', 'core_course_delete_categories', 
                		'core_enrol_get_enrolled_users', 'core_user_create_users', 'core_user_get_users', 'enrol_manual_enrol_users', 
                		'enrol_manual_unenrol_users', 'gradereport_user_get_grades_table','local_delete_sections', 'core_grades_get_grades',
                		'local_add_discussion_news', 'local_role_is_user_enrolled', 'local_update_sections_sequence', 'core_user_update_users',
                		''
                ),
                'restrictedusers' => 0,
        		'shortname' => 'planetmediaws',
                'enabled'=>1,
        )
);



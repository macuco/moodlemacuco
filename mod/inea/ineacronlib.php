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
 * INEA Cron functions.
 *
 * @package    inea
 * @subpackage mod
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute cron tasks
 */
function cron_run() {
    global $DB, $CFG, $OUTPUT;

    if (CLI_MAINTENANCE) {
        echo "CLI maintenance mode active, cron execution suspended.\n";
        exit(1);
    }

    if (moodle_needs_upgrading()) {
        echo "Moodle upgrade pending, cron execution suspended.\n";
        exit(1);
    }
	
    require_once($CFG->libdir.'/adminlib.php');

    if (!empty($CFG->showcronsql)) {
        $DB->set_debug(true);
    }
    if (!empty($CFG->showcrondebugging)) {
        set_debugging(DEBUG_DEVELOPER, true);
    }

    core_php_time_limit::raise();
    $starttime = microtime();

    // Increase memory limit
    raise_memory_limit(MEMORY_EXTRA);

    // Emulate normal session - we use admin accoutn by default
    cron_setup_user();

    // Start output log
    $timenow  = time();
    mtrace("Server Time: ".date('r', $timenow)."\n\n");

    // Run all scheduled tasks.
    while (!\core\task\manager::static_caches_cleared_since($timenow) &&
           $task = \core\task\manager::get_next_scheduled_task($timenow)) {
        cron_run_inner_scheduled_task($task);
        unset($task);
    }

    // Run all adhoc tasks.
    while (!\core\task\manager::static_caches_cleared_since($timenow) &&
           $task = \core\task\manager::get_next_adhoc_task($timenow)) {
        mtrace("Execute adhoc task: " . get_class($task));
        cron_trace_time_and_memory();
        $predbqueries = null;
        $predbqueries = $DB->perf_get_queries();
        $pretime      = microtime(1);
        try {
            get_mailer('buffer');
            $task->execute();
            if ($DB->is_transaction_started()) {
                throw new coding_exception("Task left transaction open");
            }
            if (isset($predbqueries)) {
                mtrace("... used " . ($DB->perf_get_queries() - $predbqueries) . " dbqueries");
                mtrace("... used " . (microtime(1) - $pretime) . " seconds");
            }
            mtrace("Adhoc task complete: " . get_class($task));
            \core\task\manager::adhoc_task_complete($task);
        } catch (Exception $e) {
            if ($DB && $DB->is_transaction_started()) {
                error_log('Database transaction aborted automatically in ' . get_class($task));
                $DB->force_transaction_rollback();
            }
            if (isset($predbqueries)) {
                mtrace("... used " . ($DB->perf_get_queries() - $predbqueries) . " dbqueries");
                mtrace("... used " . (microtime(1) - $pretime) . " seconds");
            }
            mtrace("Adhoc task failed: " . get_class($task) . "," . $e->getMessage());
            if ($CFG->debugdeveloper) {
                 if (!empty($e->debuginfo)) {
                    mtrace("Debug info:");
                    mtrace($e->debuginfo);
                }
                mtrace("Backtrace:");
                mtrace(format_backtrace($e->getTrace(), true));
            }
            \core\task\manager::adhoc_task_failed($task);
        }
        get_mailer('close');
        unset($task);
    }
	
	//INEA: Procedemiento para depurar a los usuarios inactivos
	if (isset($CFG->limpiar_plataforma) && $CFG->limpiar_plataforma) {
		inea_clean_usuarios_inactivos();
	}
	
	//INEA: Procedimiento para crear las estadisticas (XLS) del sistema
	if( $CFG->crear_estadisticas) {
		inea_crear_estadisticas();
	}

    mtrace("Cron script completed correctly");

    gc_collect_cycles();
    mtrace('Cron completed at ' . date('H:i:s') . '. Memory used ' . display_size(memory_get_usage()) . '.');
    $difftime = microtime_diff($starttime, microtime());
    mtrace("Execution took ".$difftime." seconds");
}

/**
 * Funcion que elimina los datos de los usuarios que han estado inactivos durante cierto tiempo,
 * el calculo lo determina el admin especificando la cantidad de días estimados para que un usuario
 * se considere inactivo en la plataforma.
 *
 */
function inea_clean_usuarios_inactivos() {
	global $CFG;
	
	// INEA: Importar la libreria de inea
	require_once($CFG->dirroot . '/mod/inea/inealib.php');
	
	//Execute backup's cron
	//Perhaps a long time and memory could help in large sites
	@set_time_limit(0);
	@raise_memory_limit("192M");
   
	// Todos los cursos en la plataforma
	$courses = get_courses("all", "c.id");
	//$s_rol = get_student_role(true);
	//$t_rol = get_teacher_role(true);

	// Calcular el ultimo acceso en dias
	$ultimoacceso_20 = time()-(20 * 24 * 60 * 60); // 20 dias
	$ultimoacceso_30 = time()-(30 * 24 * 60 * 60); // 30 dias
	$ultimoacceso_90 = time()-(90 * 24 * 60 * 60); // 90 dias
	
	// Buscar en cada curso a los usuarios
	foreach($courses as $course) {
		// Limpiar a los usuarios que han aprobado un curso
		$aprobados = inea_get_usuarios_aprobados($course->id, ESTUDIANTE);
		course_delete_users_with_role($aprobados, $course->id, ESTUDIANTE, 2);
	
		// Limpiar a los usuarios que han estado inactivos por mas de 30 dias
		//$inactivity_students = get_inactivity_users($course->id, $s_rol, $lastaccess30);
// DAVE se comentan las sig 2 lineas para que no borren a los asesores
		//$inactivity_students = get_inactivity_users($course->id, $s_rol, $lastaccess90);
		//course_delete_users_with_role($inactivity_students, $course->id, $s_rol, 1);
	
		// Limpiar a los asesores que han estado inactivos por mas de 30 dias
		//$inactivity_teachers = get_inactivity_users($course->id, $t_rol, $lastaccess30);
// DAVE se comentan las sig 2 lineas para que no borren a los asesores
		//$inactivity_teachers = get_inactivity_users($course->id, $t_rol, $lastaccess90);
		//course_delete_users_with_role($inactivity_teachers, $course->id, $t_rol, 1);
	
		// Limpiar a los aducandos que han cambiado de modalidad
		$cambiomodalidad = inea_get_usuarios_cambio_modalidad($course->id, ESTUDIANTE);
		course_delete_users_with_role($mode_change_students, $course->id, $s_rol, 4);
	
		// Notificar a los tutores, reponsables y admin de la inactividad de un asesor por mas de 20 dias
		//$inactivity_teachers_20days = get_inactivity_users($course->id, $t_rol, $lastaccess20);
		//notify_teachers_inactivity($inactivity_teachers_30days, $course->id);
	}
	
	//Ludwick:140610 -> Limpiar los grupos vacios
	$groups_deleted = groups_delete_empty_groups();
 }
 
 /**
 * Funcion que crea las estadisticas de la plataforma en archivos XLS de Excel, estos archivos son
 * creados en la carpeta "estadisticas" dentro de moodledata. 
 *
 */
 function inea_crear_estadisticas() {
	global $CFG;
	
	$monthtoday = date('M', time());
	$monthtomorrow = date('M', (time()+(1 * 24 * 60 * 60)));
	$yeartoday = date('Y', time());
	//echo "<br>Mes hoy: ".$monthtoday." Mes mañ: ".$monthtomorrow." Añ".$yeartoday;
	$directorio = $CFG->dataroot."/estadisticas";
	if($monthtomorrow != $monthtoday) { // Hay un cambio de mes
		for($i=0; $i<=8; $i++) {
			$st_names[$i] = "$directorio/estadistica_0".($i+1)."_$monthtoday"."_$yeartoday.xls";
		}

		$estadisticas[0] = statistic_print_students_by_entity();
		$estadisticas[1] = statistic_print_status_users_by_course();
		$estadisticas[2] = statistic_print_users_report_by_entity_and_course();
		$estadisticas[3] = statistic_print_general_user_report_by_entity();
		$estadisticas[4] = statistic_print_general_plaza_report_by_entity();
		$estadisticas[5] = statistic_print_active_users_by_gender();
		$estadisticas[6] = statistic_print_active_users_by_age();
		$estadisticas[7] = statistic_print_active_users_by_occupation();

		// Salvar las estadisticas en un archivo xls
		foreach($estadisticas as $id_st=>$estadistica) {
			file_put_contents($st_names[$id_st], $estadistica);
		}
	}
 }

/**
 * INEA - Obtiene a los usuarios que hayan aprobado/acreditado un curso (calificacion SASA).
 *
 * @param int $courseid - Id del curso
 * @param int $roleid - El id del rol
 * @param int grade - El filtro de calificacion (por default 5+)
 * @param int lastaccess - numero de dias transcurridos
 * @return array $users - un arreglo con los usuarios
 * 
 */
function inea_get_usuarios_aprobados($courseid, $roleid=0, $grade=5, $lastaccess=0) {
	global $CFG, $DB;
	
	if(empty($courseid)) {
		return false;
	}
	
	$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
	if(!$context = context_course::instance($course->id)) {
		return false;
	}
	
	$select = "SELECT u.id, u.firstname, u.lastname, u.email, ul.timeaccess AS lastaccess, u.institution AS clventidad, u.city AS clvmunicipio, u.zona AS clvzona, u.skype AS clvplaza, gm.fecha_concluido AS completiondate, gm.fecha_acreditado AS approvaldate, gm.calificacion AS grade, gm.fecha_aplicacion AS applicationdate, u.yahoo AS gender, (YEAR(CURRENT_DATE) - YEAR(str_to_date(u.aim, '%d/%m/%Y'))) - (RIGHT(CURRENT_DATE,5) < RIGHT(str_to_date(u.aim, '%d/%m/%Y'),5)) AS age, u.msn AS occupation, ia.firstactivity, ia.lastactivity "; // Obtener todos los usuarios
	$from   = "FROM {user} u 
		INNER JOIN {role_assignments} r ON r.userid = u.id 
		INNER JOIN {user_lastaccess} ul ON ul.userid = u.id 
		INNER JOIN {context} cx ON (cx.instanceid = ul.courseid AND cx.contextlevel = 50 AND cx.id = r.contextid)
		INNER JOIN {groups_members} gm ON gm.userid = u.id
		INNER JOIN {groups} g ON (g.id = gm.groupid AND g.courseid = ul.courseid)
		LEFT OUTER JOIN (
			SELECT ir.userid, ie.courseid, MIN(ir.timemodified) AS firstactivity, MAX(ir.timemodified) AS lastactivity 
			FROM {inea_respuestas} ir
			INNER JOIN {inea_ejercicios} ie ON ie.id = ir.ejercicios_id
			GROUP BY ie.courseid, ir.userid) ia ON (ia.courseid = g.courseid AND ia.userid = u.id) ";
	
	$where  = "WHERE g.courseid = ?
   		AND u.deleted = 0
        AND u.username != 'guest'";
	
	if($roleid > 0) {
        $where .= " AND r.roleid = ? ";
	}

	if(is_int($grade) && $grade>0) {
    	$where .= " AND gm.calificacion > ? ";
	}
	
	if($lastaccess > 0) {
		$where .= " AND gm.fecha_acreditado <= (UNIX_TIMESTAMP()-(? * 24 * 60 * 60)) ";
	}
	
	$orderby = " ORDER BY lastaccess DESC";
	
	$params = array($courseid, $roleid, $grade, $lastaccess);
	
	$query = $select.$from.$where.$orderby;        

	//echo " <br><br>Consulta: ".$query;
	//exit;
	return $DB->get_records_sql($query, $params);
}

/**
 * INEA - Obtiene a los usuarios que no han entrado a un curso en un tiempo determinado.
 *
 * @param int $courseid - El id del curso
 * @param int $roleid - El id del rol
 * @param int lastaccess - El numero de dias transcurridos
 * @return array $users - un arreglo con los usuarios inactivos
 * 
 */
function inea_get_usuarios_inactivos($courseid, $roleid=0, $lastaccess=0) {
	global $CFG, $DB;
	
	if(empty($courseid)) {
		return false;
	}
	
	$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
	if(!$context = context_course::instance($course->id)) {
		return false;
	}
	
	$select = "SELECT u.id, u.firstname, u.lastname, u.email, ul.timeaccess AS lastaccess, u.institution AS clventidad, u.city AS clvmunicipio, u.zona AS clvzona, u.skype AS clvplaza, u.yahoo AS gender, (YEAR(CURRENT_DATE) - YEAR(str_to_date(u.aim, '%d/%m/%Y'))) - (RIGHT(CURRENT_DATE,5) < RIGHT(str_to_date(u.aim, '%d/%m/%Y'),5)) AS age, u.msn AS occupation, ia.firstactivity, ia.lastactivity ";
	
	$from   = "FROM {user} u 
	INNER JOIN {role_assignments} r ON r.userid = u.id 
	INNER JOIN {user_lastaccess} ul ON ul.userid = u.id 
	INNER JOIN {context} cx ON (cx.instanceid = ul.courseid AND cx.contextlevel = 50 AND cx.id = r.contextid) ";
	
	$from   .= "LEFT OUTER JOIN (
					SELECT ir.userid, ie.courseid, MIN(ir.timemodified) AS firstactivity, MAX(ir.timemodified) AS lastactivity 
					FROM {inea_respuestas} ir
					INNER JOIN {inea_ejercicios} ie ON ie.id=ir.ejercicios_id
					GROUP BY ie.courseid,ir.userid) ia ON (ia.courseid=ul.courseid AND ia.userid=u.id) ";
	
	$where  = "WHERE ul.courseid = ?
				AND u.deleted = 0
				AND u.username != 'guest' ";
	
	if($roleid > 0) {
        $where .= " AND r.roleid = ? ";
	}

	$where .= " AND (ia.lastactivity <= DATE_SUB(CURRENT_DATE(), INTERVAL ? DAY) OR ul.timeaccess <= (UNIX_TIMESTAMP()-(? * 24 * 60 * 60))) ";
	
	$orderby = " ORDER BY lastaccess DESC";
	
	$params = array($courseid, $roleid, $lastaccess, $lastaccess);
	
	$query = $select.$from.$where.$orderby;        

	//echo " <br><br>Consulta: ".$query;
    //exit;
	return $DB->get_records_sql($query, $params);
}

/**
 * INEA - Obtiene a los usuarios que han cambiado de modalidad en un curso.
 *
 * @param int $courseid - Id del curso
 * @param int roleid - id del rol (Estudiante por defecto)
 * @return array $users - un arreglo con los usuarios
 * 
 */
function inea_get_usuarios_cambio_modalidad($courseid, $roleid=5) {
	global $CFG, $DB;
	
	if(empty($courseid)) {
		return false;
	}
	
	$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
	if(!$context = context_course::instance($course->id)) {
		return false;
	}
	
	$select = "SELECT u.id, u.firstname, u.lastname, u.email, ul.timeaccess AS lastaccess, u.institution AS clventidad, u.city AS clvmunicipio, u.zona AS clvzona, u.skype AS clvplaza, gm.modalidad, u.yahoo AS gender, (YEAR(CURRENT_DATE) - YEAR(str_to_date(u.aim, '%d/%m/%Y'))) - (RIGHT(CURRENT_DATE,5) < RIGHT(str_to_date(u.aim, '%d/%m/%Y'),5)) AS age, u.msn AS occupation "; // Obtener todos los educandos por cambio de modalidad
	$from   = "FROM {user} u 
		INNER JOIN {role_assignments} r ON r.userid = u.id 
		INNER JOIN {user_lastaccess} ul ON ul.userid = u.id
		INNER JOIN {groups_members} gm ON gm.userid = u.id
		INNER JOIN {groups} g ON (g.id = gm.groupid AND g.courseid = ul.courseid) 
		INNER JOIN {context} cx ON (cx.instanceid = g.courseid  AND cx.contextlevel = 50 AND cx.id = r.contextid) ";
		
	$where  = "WHERE g.courseid = ?
   		AND u.deleted = 0
        AND u.username != 'guest'";
	
	if($roleid > 0) {
        $where .= " AND r.roleid = ? ";
	}
	
	$where .= " AND (gm.modalidad IS NOT NULL AND gm.modalidad <> 1) ";
	
	$orderby = " ORDER BY lastaccess DESC";
	
	$params = array($courseid, $roleid);
	
	$query = $select.$from.$where.$orderby;        

	echo " <br><br>Consulta: ".$query;
    exit;
	return $DB->get_records_sql($query, $params);
}

/**
 * INEA - Procedimiento para eliminar a los usuarios dentro de un curso.
 *
 * @param Object $user - Un objeto con la informacion del usuario
 * @param int $courseid - El id del curso
 * @param int $roleid - El id del rol del usuario 
 * @param boolean $message - Condicional para imprimir mensajes de error/advertencia 
 * @return boolean $success - Verdadero si ha terminado con exito, falso en caso contrario
 * 
 */
function course_delete_users_with_role($users, $courseid, $roleid, $type=1) {
	global $CFG, $DB;
	
	// Verificar usuarios
	if(empty($users) || !is_array($users)) {
		return false;
	}
	
	// Verificar curso y rol
	if(empty($courseid) || empty($roleid)) {
		return false;
	}
	
	$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
	if(!$context = context_course::instance($course->id)) {
		return false;
	}
	
	//echo "<br><br>Usuarios: ".count($users)." Tipo: $type  Rol id: $roleid";
	foreach($users as $user) {
		$user = set_historial_values_for_user($user, $courseid, $roleid, $type);
		if($roleid == $s_rol) { // Borrar las actividades de un estadiante
			//echo "<br>Estudiante: $user->id_user";
			//print_object($user);
			delete_user_activities($user, $courseid, $roleid);
		} else if($roleid == $t_rol) { // Borrar las actividades y todo su grupo del asesor
			$id_grupo = $user->group;
			if(!empty($id_grupo) && $id_grupo>0) {
				if($miembros = groups_get_members($id_grupo)) {
					// Desmatricular a todos los usuarios del grupo
					foreach($miembros as $id_user) {
						$miembro = new object();
						$miembro->id = $id_user;
	
						$role = get_user_role_in_context($miembro->id, $context);
						$v_role = each($role);
						$id_role = (isset($v_role[0]) && !empty($v_role[0]))? $v_role[0] : 0;
						$type = ($id_role==$t_rol)? 1 : 3;
						$miembro = set_historial_values_for_user($miembro, $courseid, $id_role, $type);
						//echo "<br>Miembro del grupo :$id_grupo rol: ".$v_role[1];
						//print_object($miembro);
						delete_user_activities($miembro, $courseid, $id_role);
					}
				}
				// Eliminar el grupo que esta vacio
				if(!groups_delete_group($id_grupo)){
					notify("No se pudo borrar el grupo $id_grupo");
				}
			} else { // Borrar solo las actividades del asesor
				//echo "<br>Asesor: $user->id_user";
				//print_object($user);
				delete_user_activities($user, $courseid, $roleid);
			}
		}
	}	
}

/**
 * INEA - Obtiene los valores del historial de un usuario
 *
 * @param Object $user - Un objeto con la informacion del usuario
 * @param int $courseid - El id del curso
 * @param int $roleid - El id del rol del usuario 
 * @param int $type - Codigo del tipo de eliminacion que se esta procesando. 
 * @return Object $user - El usuario con los parametros necesarios en el historial
 * 
 */
function set_historial_values_for_user($user, $courseid, $roleid, $type){
	if(empty($user) || empty($courseid) || empty($roleid)) {
		return false;
	}
	
	$s_rol = get_student_role(true); // Obtener el id del rol del estudiante
	$primer_login = get_user_data_from_log($courseid, $user->id, "Acceso al curso");
	$mis_grupos = user_group($courseid, $user->id);
	$mi_grupo = (count($mis_grupos)>0)? array_shift($mis_grupos) : 0;
	$id_grupo = (isset($mi_grupo->id))? $mi_grupo->id : 0;
	//echo "<br>Id Grupo: ".$id_grupo;
	$id_asesor = obtener_asesor_grupo($id_grupo);
	//echo "<br>Id Asesor: ".$id_asesor."<br>";
	
	$usuario = new object();
	$usuario->id = null;
	$usuario->userid = $user->id;
	$usuario->courseid = $courseid;
	$usuario->roleid = $roleid;
	$usuario->firstaccess = (isset($user->firstaccess))? $user->firstaccess : 0;
	$usuario->lastaccess = (isset($user->lastaccess))? $user->lastaccess : 0;
	$usuario->firstlogin = (isset($primer_login->time))? $primer_login->time : 0;
	$usuario->clventidad = (isset($user->clventidad))? $user->clventidad : 0;
	$usuario->clvplaza = (isset($user->clvplaza))? $user->clvplaza : 0;
	$usuario->clvmunicipio = (isset($user->clvmunicipio))? $user->clvmunicipio : 0;
	$usuario->clvzona = (isset($user->clvzona))? $user->clvzona : 0;
	$usuario->gender = (isset($user->gender))? $user->gender : "";
	$usuario->age = (isset($user->age))? $user->age : 0;
	$usuario->occupation = (isset($user->occupation))? $user->occupation : 0;	
	$usuario->teacherid = $id_asesor;
	$usuario->groupid = $id_grupo;
	if($s_rol == $roleid) { // Registrar campos solo para educandos
		$usuario->grade = (isset($user->grade))? $user->grade : 0;
		$usuario->approvaldate = (isset($user->approvaldate))? $user->approvaldate : 0;
		$usuario->completiondate = (isset($user->completiondate))? $user->completiondate : 0;
		$usuario->applicationdate = (isset($user->applicationdate))? $user->applicationdate : 0;
		$usuario->modalidad = (isset($user->modalidad))? $user->modalidad : 1;
	}
	$usuario->sessionnumber = 0; // Numero en sesiones
	$usuario->sessiontime = 0; // Tiempo en sesiones
	if($s_rol == $roleid) { // Registrar campos solo para educandos
		$usuario->firstactivity = (isset($user->firstactivity))? $user->firstactivity : 0; // Primera actividad del educando
		$usuario->lastactivity = (isset($user->lastactivity))? $user->lastactivity : 0; // Ultima actividad del educando
		$usuario->answeredactivities = user_get_inea_answers($usuario->userid, $courseid); // Numero de actividades del educando
	}
	$usuario->timemodified = time(); // Fecha en que se crea el registro en el historial
	$usuario->type = $type;
	
	return $usuario;
}
 
/**
 * Shared code that handles running of a single scheduled task within the cron.
 *
 * Not intended for calling directly outside of this library!
 *
 * @param \core\task\task_base $task
 */
function cron_run_inner_scheduled_task(\core\task\task_base $task) {
    global $CFG, $DB;

    $fullname = $task->get_name() . ' (' . get_class($task) . ')';
    mtrace('Execute scheduled task: ' . $fullname);
    cron_trace_time_and_memory();
    $predbqueries = null;
    $predbqueries = $DB->perf_get_queries();
    $pretime = microtime(1);
    try {
        get_mailer('buffer');
        $task->execute();
        if ($DB->is_transaction_started()) {
            throw new coding_exception("Task left transaction open");
        }
        if (isset($predbqueries)) {
            mtrace("... used " . ($DB->perf_get_queries() - $predbqueries) . " dbqueries");
            mtrace("... used " . (microtime(1) - $pretime) . " seconds");
        }
        mtrace('Scheduled task complete: ' . $fullname);
        \core\task\manager::scheduled_task_complete($task);
    } catch (Exception $e) {
        if ($DB && $DB->is_transaction_started()) {
            error_log('Database transaction aborted automatically in ' . get_class($task));
            $DB->force_transaction_rollback();
        }
        if (isset($predbqueries)) {
            mtrace("... used " . ($DB->perf_get_queries() - $predbqueries) . " dbqueries");
            mtrace("... used " . (microtime(1) - $pretime) . " seconds");
        }
        mtrace('Scheduled task failed: ' . $fullname . ',' . $e->getMessage());
        if ($CFG->debugdeveloper) {
            if (!empty($e->debuginfo)) {
                mtrace("Debug info:");
                mtrace($e->debuginfo);
            }
            mtrace("Backtrace:");
            mtrace(format_backtrace($e->getTrace(), true));
        }
        \core\task\manager::scheduled_task_failed($task);
    }
    get_mailer('close');
}

/**
 * Runs a single cron task. This function assumes it is displaying output in pseudo-CLI mode.
 *
 * The function will fail if the task is disabled.
 *
 * Warning: Because this function closes the browser session, it may not be safe to continue
 * with other processing (other than displaying the rest of the page) after using this function!
 *
 * @param \core\task\scheduled_task $task Task to run
 * @return bool True if cron run successful
 */
function cron_run_single_task(\core\task\scheduled_task $task) {
    global $CFG, $DB, $USER;

    if (CLI_MAINTENANCE) {
        echo "CLI maintenance mode active, cron execution suspended.\n";
        return false;
    }

    if (moodle_needs_upgrading()) {
        echo "Moodle upgrade pending, cron execution suspended.\n";
        return false;
    }

    // Check task and component is not disabled.
    $taskname = get_class($task);
    if ($task->get_disabled()) {
        echo "Task is disabled ($taskname).\n";
        return false;
    }
    $component = $task->get_component();
    if ($plugininfo = core_plugin_manager::instance()->get_plugin_info($component)) {
        if ($plugininfo->is_enabled() === false && !$task->get_run_if_component_disabled()) {
            echo "Component is not enabled ($component).\n";
            return false;
        }
    }

    // Enable debugging features as per config settings.
    if (!empty($CFG->showcronsql)) {
        $DB->set_debug(true);
    }
    if (!empty($CFG->showcrondebugging)) {
        set_debugging(DEBUG_DEVELOPER, true);
    }

    // Increase time and memory limits.
    core_php_time_limit::raise();
    raise_memory_limit(MEMORY_EXTRA);

    // Switch to admin account for cron tasks, but close the session so we don't send this stuff
    // to the browser.
    session_write_close();
    $realuser = clone($USER);
    cron_setup_user(null, null, true);

    // Get lock for cron task.
    $cronlockfactory = \core\lock\lock_config::get_lock_factory('cron');
    if (!$cronlock = $cronlockfactory->get_lock('core_cron', 1)) {
        echo "Unable to get cron lock.\n";
        return false;
    }
    if (!$lock = $cronlockfactory->get_lock($taskname, 1)) {
        $cronlock->release();
        echo "Unable to get task lock for $taskname.\n";
        return false;
    }
    $task->set_lock($lock);
    if (!$task->is_blocking()) {
        $cronlock->release();
    } else {
        $task->set_cron_lock($cronlock);
    }

    // Run actual tasks.
    cron_run_inner_scheduled_task($task);

    // Go back to real user account.
    cron_setup_user($realuser, null, true);

    return true;
}

/**
 * Output some standard information during cron runs. Specifically current time
 * and memory usage. This method also does gc_collect_cycles() (before displaying
 * memory usage) to try to help PHP manage memory better.
 */
function cron_trace_time_and_memory() {
    gc_collect_cycles();
    mtrace('... started ' . date('H:i:s') . '. Current memory use ' . display_size(memory_get_usage()) . '.');
}

/**
 * Executes cron functions for a specific type of plugin.
 *
 * @param string $plugintype Plugin type (e.g. 'report')
 * @param string $description If specified, will display 'Starting (whatever)'
 *   and 'Finished (whatever)' lines, otherwise does not display
 */
function cron_execute_plugin_type($plugintype, $description = null) {
    global $DB;

    // Get list from plugin => function for all plugins
    $plugins = get_plugin_list_with_function($plugintype, 'cron');

    // Modify list for backward compatibility (different files/names)
    $plugins = cron_bc_hack_plugin_functions($plugintype, $plugins);

    // Return if no plugins with cron function to process
    if (!$plugins) {
        return;
    }

    if ($description) {
        mtrace('Starting '.$description);
    }

    foreach ($plugins as $component=>$cronfunction) {
        $dir = core_component::get_component_directory($component);

        // Get cron period if specified in version.php, otherwise assume every cron
        $cronperiod = 0;
        if (file_exists("$dir/version.php")) {
            $plugin = new stdClass();
            include("$dir/version.php");
            if (isset($plugin->cron)) {
                $cronperiod = $plugin->cron;
            }
        }

        // Using last cron and cron period, don't run if it already ran recently
        $lastcron = get_config($component, 'lastcron');
        if ($cronperiod && $lastcron) {
            if ($lastcron + $cronperiod > time()) {
                // do not execute cron yet
                continue;
            }
        }

        mtrace('Processing cron function for ' . $component . '...');
        cron_trace_time_and_memory();
        $pre_dbqueries = $DB->perf_get_queries();
        $pre_time = microtime(true);

        $cronfunction();

        mtrace("done. (" . ($DB->perf_get_queries() - $pre_dbqueries) . " dbqueries, " .
                round(microtime(true) - $pre_time, 2) . " seconds)");

        set_config('lastcron', time(), $component);
        core_php_time_limit::raise();
    }

    if ($description) {
        mtrace('Finished ' . $description);
    }
}

/**
 * Used to add in old-style cron functions within plugins that have not been converted to the
 * new standard API. (The standard API is frankenstyle_name_cron() in lib.php; some types used
 * cron.php and some used a different name.)
 *
 * @param string $plugintype Plugin type e.g. 'report'
 * @param array $plugins Array from plugin name (e.g. 'report_frog') to function name (e.g.
 *   'report_frog_cron') for plugin cron functions that were already found using the new API
 * @return array Revised version of $plugins that adds in any extra plugin functions found by
 *   looking in the older location
 */
function cron_bc_hack_plugin_functions($plugintype, $plugins) {
    global $CFG; // mandatory in case it is referenced by include()d PHP script

    if ($plugintype === 'report') {
        // Admin reports only - not course report because course report was
        // never implemented before, so doesn't need BC
        foreach (core_component::get_plugin_list($plugintype) as $pluginname=>$dir) {
            $component = $plugintype . '_' . $pluginname;
            if (isset($plugins[$component])) {
                // We already have detected the function using the new API
                continue;
            }
            if (!file_exists("$dir/cron.php")) {
                // No old style cron file present
                continue;
            }
            include_once("$dir/cron.php");
            $cronfunction = $component . '_cron';
            if (function_exists($cronfunction)) {
                $plugins[$component] = $cronfunction;
            } else {
                debugging("Invalid legacy cron.php detected in $component, " .
                        "please use lib.php instead");
            }
        }
    } else if (strpos($plugintype, 'grade') === 0) {
        // Detect old style cron function names
        // Plugin gradeexport_frog used to use grade_export_frog_cron() instead of
        // new standard API gradeexport_frog_cron(). Also applies to gradeimport, gradereport
        foreach(core_component::get_plugin_list($plugintype) as $pluginname=>$dir) {
            $component = $plugintype.'_'.$pluginname;
            if (isset($plugins[$component])) {
                // We already have detected the function using the new API
                continue;
            }
            if (!file_exists("$dir/lib.php")) {
                continue;
            }
            include_once("$dir/lib.php");
            $cronfunction = str_replace('grade', 'grade_', $plugintype) . '_' .
                    $pluginname . '_cron';
            if (function_exists($cronfunction)) {
                $plugins[$component] = $cronfunction;
            }
        }
    }

    return $plugins;
}
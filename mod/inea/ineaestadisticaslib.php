<?php
/****** ESTADISTICAS ******/
/**
 * INEA - Estadistica No. 1 : Personas Activas por Entidad
 *
 * @param int $estado - El id de la entidad federativa
 * @return Object $statistic1: Un arreglo con la informacion de la estadistica 1
 * 
 */
function inea_get_estadistica_1_personas_por_entidad($estado) {
	$statistic1 = array();
	
	// Buscamos a los alumnos que estan enrolados en un numero especificos de cursos
	$n_courses = inea_sql_get_students_enroled_by_course($estado);
	//print_object($n_courses);
	$enroled_c = array();
	foreach($n_courses as $n_c=>$val) {
		$enroled_c[] = $n_c;
	}
	//print_object($enroled_c);
	
	foreach($enroled_c as $num_in_c) {
		$in_course = inea_sql_get_students_enroled_by_entity($num_in_c, $estado);
		//print_object($in_course);
		if(!empty($in_course)) {
			foreach($in_course as $entity=>$in_c) {
				$mvar = "in_".$num_in_c."_course";
				$statistic1[$entity]->$mvar = $in_c->usuarios;
				$statistic1[$entity]->total += $in_c->usuarios;
			}
		}
	}
		
	return $statistic1;
}

/**
 * INEA - Estadistica No. 1 : Obtiene el numero de estudiantes por curso, los agrupa por curso
 * @param int $estado - El id de entidad federativa
 * @return Array $arr1 : Un arreglo con los educandos que cumplen con el criterio de busqueda
 */
function inea_sql_get_students_enroled_by_course($estado=0) {
	global $CFG, $DB;
	
	$params = array(EDUCANDO);
	$condicion = "";
	
	if(!empty($estado)) {
		$condicion = " AND u.institution = ? ";
		array_push($params, $estado);
	}
	
	$sql1 = "SELECT  nums_in_c, COUNT(nums_in_c) AS usuarios
			FROM (
				SELECT u.id, u.institution as id_entidad, COUNT(u.id) AS nums_in_c
				FROM {user} u
				INNER JOIN {groups_members} gm ON (u.id = gm.userid)
				INNER JOIN {groups} g ON (gm.groupid = g.id)
				INNER JOIN {role_assignments} ra ON (u.id = ra.userid)
    			INNER JOIN {context} cx ON (cx.instanceid = g.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
				INNER JOIN {user_lastaccess} ul ON (ul.courseid = g.courseid AND ul.userid = u.id)
    			WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND ra.roleid = ?
				AND g.courseid IS NOT NULL 
				AND gm.concluido = 0
    			AND gm.acreditado = 0
				AND FROM_UNIXTIME(gm.timeadded) < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
				AND FROM_UNIXTIME(ul.timeaccess) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
				".$condicion."
				GROUP BY u.id ORDER BY u.institution) enroled 
				GROUP BY nums_in_c ORDER BY nums_in_c";

	$sql2 = "SELECT  nums_in_c, COUNT(nums_in_c) AS usuarios
			FROM (
				SELECT u.id, u.institution as id_entidad, COUNT(u.id) AS nums_in_c
				FROM {user} u
				INNER JOIN {groups_members} gm ON (u.id = gm.userid)
				INNER JOIN {groups} gc ON (gm.groupid = g.id)
				INNER JOIN {role_assignments} ra ON (u.id = ra.userid)
				INNER JOIN {context} cx ON (cx.instanceid = g.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
				INNER JOIN {user_lastaccess} ul ON (ul.courseid = g.courseid AND ul.userid = u.id)
				WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND ra.roleid = ?
				AND g.courseid IS NOT NULL 
				AND FROM_UNIXTIME(gm.timeadded) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
				AND FROM_UNIXTIME(ul.timeaccess) <= CURRENT_DATE()
				".$condicion."
    			GROUP BY u.id ORDER BY u.institution) incorporaciones 
				GROUP BY nums_in_c ORDER BY nums_in_c";
			
	//echo "<br>".$sql1;
	//echo "<br>".$sql2;
	//exit;
	if(!$arr1 = $DB->get_records_sql($sql1, $params)) {
		return false;
	}
	
	if(!$arr2 = $DB->get_records_sql($sql2, $párams)) {
		return false;
	}
	//print_object($arr1);
	//print_object($arr2);
	foreach($arr2 as $key=>$val){
		$arr1[$key]->usuarios += $val->usuarios;
	}
	//print_object($arr1);
	return $arr1;
	//return get_records_sql($sql);
}


/**
 * INEA - Estadistica No. 1 : Obtiene a los educandos activos (aquellos que tienen
 * un grupo, asesor y han accesado a alguna actividad dentro de 30 dias) y los clasifica
 * si pertenecen a un curso o a dos.
 * @param int $incourse - Numero de usuarios en el curso
 * @param int $estado - El id de entidad federativa
 * @return Array $arr1 : Un arreglo con los educandos que cumplen con el criterio de busqueda
 */
function inea_sql_get_students_enroled_by_entity($incourse=0, $estado=0) {
	global $CFG;

	$params = array(EDUCANDO);
	$condicion = "";
	
	if(!empty($estado)) {
		$condicion = " AND u.institution = ? ";
		array_push($params, $estado);
	}
	array_push($params, $incourse);
	
	$sql1 = "SELECT id_entidad, COUNT(nums_in_c) AS usuarios
			FROM (
				SELECT u.id, u.institution as id_entidad, COUNT(u.id) AS nums_in_c
				FROM {user} u
				INNER JOIN {groups_members] gm ON (u.id = gm.userid)
				INNER JOIN {groups} g ON (gm.groupid = g.id)
				INNER JOIN {role_assignments] ra ON (u.id = ra.userid)
    			INNER JOIN {context} cx ON (cx.instanceid = g.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
				INNER JOIN {user_lastaccess} ul ON (ul.courseid = g.courseid AND ul.userid = u.id)
    			WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND ra.roleid = ?
				AND g.courseid IS NOT NULL 
				AND gm.concluido = 0
    			AND gm.acreditado = 0
				AND FROM_UNIXTIME(gm.timeadded) < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
				AND FROM_UNIXTIME(ul.timeaccess) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
				".$condicion."
				GROUP BY u.id ORDER BY u.institution) enroled 
			WHERE nums_in_c = ?  
			GROUP BY id_entidad ORDER BY id_entidad, nums_in_c";
	
	$sql2 = "SELECT id_entidad, COUNT(nums_in_c) AS usuarios
			FROM (
				SELECT u.id, u.institution as id_entidad, COUNT(u.id) AS nums_in_c    			
				FROM {user} u
				INNER JOIN {groups_members} gm ON (u.id = gm.userid)
				INNER JOIN {groups} g ON (gm.groupid = g.groupid)
				INNER JOIN {role_assignments} ra ON (u.id = ra.userid)
				INNER JOIN {context} cx ON (cx.instanceid = g.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
				INNER JOIN {user_lastaccess} ul ON (ul.courseid = g.courseid AND ul.userid = u.id)
				WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND ra.roleid = ?
				AND g.courseid IS NOT NULL 
				AND FROM_UNIXTIME(gm.timeadded) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
				AND FROM_UNIXTIME(ul.timeaccess) <= CURRENT_DATE()
				".$condicion." 
    			GROUP BY u.id ORDER BY u.institution) incorporaciones 
			WHERE nums_in_c = ?  
			GROUP BY id_entidad ORDER BY id_entidad, nums_in_c";
		
	//echo "<br>".$sql1;
	//echo "<br>".$sql2;
	if(!$arr1 = $DB->get_records_sql($sql1, $params)) {
		return false;
	}
	
	if(!$arr2 = $DB->get_records_sql($sql2, $párams)) {
		return false;
	}

	//print_object($arr1);
	//print_object($arr2);
	foreach($arr2 as $key=>$val){
		$arr1[$key]->usuarios += $val->usuarios;
	}
	//print_object($arr1);
	return $arr1;
	//return get_records_sql($sql);
}

/**
 * INEA - Estadistica No. 1 : Imprimir en HTML la tabla con la informacion de la estadistica 
 * @param int $estado - El id de entidad federativa
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 */
function inea_print_html_estadistica_1($estado=0) {
	global $CFG, $DB;
	
	$est1 = inea_get_estadistica_1_personas_por_entidad($estado);
	$st_table = "";
	
	// Ludwick: Buscamos a los alumnos que estan enrolados en un numero especificos de cursos
	$n_courses = inea_sql_get_students_enroled_by_course($estado);
	$enroled_c = array();
	foreach($n_courses as $n_c=>$val) {
		$enroled_c[] = $n_c;
	}
	//print_object($enroled_c);
	$st_table .= "<h2 class='main'>Educandos por Entidad y No. de Cursos<br> (Del ".date("d/m/Y", strtotime(('now').'-1 month'))." al ".date("d/m/Y").")</h2>";
	$st_table .= "<table id='reportes-cursos' border='2' align='center' clas='flexible generaltable generalbox' width='90%'>";
	$st_table .= "<tr class='r0'>
          	<th class='header c0'>Entidad</th>
            <th class='header c0'>Total de educandos</th>";
	foreach($enroled_c as $e_c) {
    	$st_table .= "<th class='header c0'>En $e_c curso</th>";
	}
    $st_table .= "<th class='header c0'>Total de cursos</th>";
    $st_table .= "</tr>";

	$table = 'inea_entidad';
	$select = 'icvepais = 1';
	if(!empty($estado)) {
		$select = ' AND icveentfed = '.$estado;
	}
	
	if(!$entidades = $DB->get_records_select($table, $select)) {
		return false;
	}
	
	$totales = 0;
	$total_en_n_curso = array();
	$total_en_cursos = array();
	foreach($entidades as $ident=>$entidad) {
		$total = isset($est1[$ident]->total)? $est1[$ident]->total : 0;
		foreach($enroled_c as $e_c) {
			$mvar = "in_".$e_c."_course";
			$total_en_n_curso[$mvar] = isset($est1[$ident]->$mvar)? $est1[$ident]->$mvar:0;
			$total_en_cursos[$mvar] += $total_en_n_curso[$mvar];
		}
		//$total_en_dos_cursos = isset($est1[$ident]->in_two_course)? $est1[$ident]->in_two_course:0;

		$st_table .= "<tr class='r0'>
        			<th class='header c0' align='left'>$entidad->cdesentfed</th>
        				<td align='center'>".$total."</td>";
						$total_cursos = 0;
						$i = 1;
        				foreach($total_en_n_curso as $en_n_curso) {
							$st_table .= "<td align='center'>".$en_n_curso."</td>";
							$total_cursos = $total_cursos + ($i * $en_n_curso);
							$i++;
						}
		$st_table .= "<td align='center'>".$total_cursos."</td>";
		$st_table .= "</tr>";

		$totales += $total;
	}

	$st_table .= "<tr class='r0'>
     			<th class='header c0' >TOTALES </th>
    	 		<th class='header c0' >$totales</th>";
				$i=1;
    	 		foreach($total_en_cursos as $total_c) {
					$st_table .= "<th class='header c0' >$total_c</th>";
					$gran_total_cursos = $gran_total_cursos + ($i * $total_c);
					$i++;
				}
	$st_table .= "<th class='header c0' >$gran_total_cursos</th>";
	$st_table .= "</tr></table><br>";
	
	return $st_table;
}

/**
 * INEA - Estadistica No. 1 : Imprimir en HTML la tabla con la informacion de la estadistica (CSV)
 * @param int $estado - El id de entidad federativa
 * @param xmlObject $xmlObject - Objeto xml donde se almacenan las cadenas de datos
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 */
function inea_print_csv_estadistica_1($xmlObject, $estado=0) {
	global $CFG, $DB;
	
	$est1 = inea_get_estadistica_1_personas_por_entidad($estado);
	$xmlPart = "";
	
	// Ludwick: Buscamos a los alumnos que estan enrolados en un numero especificos de cursos
	$n_courses = inea_sql_get_students_enroled_by_course($estado);
	$enroled_c = array();
	foreach($n_courses as $n_c) {
		$enroled_c[] = $n_c->nums_in_c;
	}
	
	$worksheetName = "Personas activas por Entidad";
	$headerValues = "Entidad, Total";
	foreach($enroled_c as $e_c) {
    	$headerValues .= ",En $e_c curso";
	}
	
	$table = 'inea_entidad';
	$select = 'icvepais = 1';
	if(!empty($estado)) {
		$select = ' AND icveentfed = '.$estado;
	}
	
	if(!$entidades = $DB->get_records_select($table, $select)) {
		return false;
	}
	
	//echo $headerValues."<br>";
	$xmlPart .= $xmlObject->AddWorkSheet($worksheetName, $headerValues, 's72');
	
	$totales = 0;
	$total_en_n_curso = array();
	$total_en_cursos = array();
	foreach($entidades as $ident=>$entidad) {
		$total = isset($est1[$ident]->total)? $est1[$ident]->total : 0;
		foreach($enroled_c as $e_c) {
			$mvar = "in_".$e_c."_course";
			$total_en_n_curso[$mvar] = isset($est1[$ident]->$mvar)? $est1[$ident]->$mvar:0;
			$total_en_cursos[$mvar] += $total_en_n_curso[$mvar];
		}
		//$total_en_dos_cursos = isset($est1[$ident]->in_two_course)? $est1[$ident]->in_two_course:0;

		$DataValues = "$entidad->cdesentfed, $total";
        foreach($total_en_n_curso as $en_n_curso) {
			$DataValues .= ", $en_n_curso";
		}
		//echo $DataValues."<br>";
		$totales += $total;
		$xmlPart .= $xmlObject->getColumnData($DataValues, 's42', true);
	}

	$DataValues = "TOTALES, $totales";
    foreach($total_en_cursos as $total_c) {
		$DataValues .= ", $total_c";
	}
	//echo $DataValues."<br>";
	$xmlPart .= $xmlObject->getColumnData($DataValues, 's73');
	$xmlPart .= "</Table>";
	$xmlPart .= $xmlObject->GetFooter(); 
	
	return $xmlPart;
}

/**
 * INEA - Estadistica No. 2 : Personas por Curso
 *
 * @return Object $statistic2: Un arreglo con la informacion de la estadistica 2
 */
function inea_get_estadistica_2_personas_por_curso() {
	$statistic2 = array();
	
	$_thisdate = time();
	$_30daysbefore = time()-(30 * 24 * 60 * 60);
	
	// Ludwick: 1. Buscamos a los usuarios que estan activos por curso
	$active_users = inea_sql_get_active_users_by_course();
	//print_object($active_users);
	if(!empty($active_users)) {
		foreach($active_users as $id_course=>$active_user) {
			//$acreditados = isset($accredited_users[$id_course]->num_acreditados)? $accredited_users[$id_course]->num_acreditados : 0;
			//$bajas = isset($inactive_users[$id_course]->num_bajas)? $inactive_users[$id_course]->num_bajas : 0;
			$statistic2[$id_course]->actives = $active_user->num_usuarios;
		}
	}
	
	// Ludwick: 2. Buscamos a los usuarios que estan inactivos por curso
	$inactive_users = inea_sql_get_inactive_users_by_course();
	if(!empty($inactive_users)) {
		foreach($inactive_users as $id_course=>$inactive_user) {
			$statistic2[$id_course]->inactives = $inactive_user->num_bajas;
		}
	}
	
	// Ludwick: 3. Buscamos a los usuarios que han concluido el modulo del curso
	$completed_users = inea_sql_get_completed_users_by_course()
	//print_object($completed_users);
	if(!empty($completed_users)) {
		foreach($completed_users as $id_course=>$completed_user) {
			$statistic2[$id_course]->completed = $completed_user->num_concluidos;
		}
	}
	
	// ************************ AQUI ME QUEDE ......
	// Ludwick: 4. Buscamos a los usuarios que han acreditado el curso
	$accredited_users = bd_get_accredited_users_by_course();
	if(!empty($accredited_users)) {
		foreach($accredited_users as $id_course=>$accredited_user) {
			$statistic2[$id_course]->accredited = $accredited_user->num_acreditados;
		}
	}
	
	// Ludwick: Buscamos las incorporaciones hasta la fecha
	$added_users = bd_get_added_user_by_course();
	if(!empty($added_users)) {
		foreach($added_users as $id_course=>$added_user) {
			$statistic2[$id_course]->added = $added_user->num_incorporaciones;
		}
	}
	
	// Ludwick: Buscamos a los usuarios que seran atendidos dentro de 30 dias posteriores
	if(!empty($statistic2)) {
		foreach($statistic2 as $id_course=>$section) {
			$activos = isset($section->actives)? $section->actives : 0;
			$incorporaciones = isset($section->added)? $section->added : 0;
			
			$acreditados = isset($section->accredited)? $section->accredited : 0;
			$bajas = isset($section->inactives)? $section->inactives : 0;
			
			$statistic2[$id_course]->attended_next = ($activos + $incorporaciones);
			$statistic2[$id_course]->attended_prev = ($activos + $acreditados + $bajas);
		}
	}
	
	// Obtener a los usarios que han presentado examen activos
	$reviewed_users = db_get_reviewed_user_by_course();
	if(!empty($reviewed_users)) {
		foreach($reviewed_users as $id_course=>$reviewed_user) {
			$statistic2[$id_course]->revieweds = $reviewed_user->num_presentaron;
		}
	}
	
	return $statistic2;
}

/**
 * INEA - Estadistica No. 2 : Obtiene a los educandos activos (aquellos que tienen
 * un grupo y han accesado a alguna actividad dentro de 30 dias) y los agrupa por curso.
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 */
function inea_sql_get_active_users_by_course() {
	global $CFG, $DB;
	
	$sql = "SELECT g.courseid, COUNT(u.id) as num_usuarios
    		FROM {user} u
			INNER JOIN {groups_members} gm ON (u.id = gm.userid)
			INNER JOIN {groups} g ON (gm.groupid = g.id)
			INNER JOIN {role_assignments} ra ON (u.id = ra.userid)
    		INNER JOIN {context} cx ON (cx.instanceid = g.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			INNER JOIN (SELECT ir.userid, ie.courseid, MIN(ir.timemodified) AS firstactivity, MAX(ir.timemodified) AS lastactivity 
				FROM {inea_respuestas} ir
				INNER JOIN {inea_ejercicios} ie ON ie.id = ir.ejercicios_id
				GROUP BY ie.courseid, ir.userid) ia ON (ia.courseid = g.courseid AND ia.userid = u.id)
    		WHERE u.deleted = 0 
			AND u.username != 'guest'
			AND ra.roleid = ?
			AND g.courseid IS NOT NULL 
			AND gm.concluido = 0
    		AND gm.acreditado = 0
    		AND ia.firstactivity < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) 
			AND ia.lastactivity BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE() 
    		GROUP BY g.courseid ORDER BY g.courseid";
	//echo "<br>".$sql;
	
	return $DB->get_records_sql($sql, array(EDUCANDO));
}

/**
 * INEA - Estadistica No. 2 : Obtiene a los educandos inactivos (aquellos que no han
 * accesado a alguna actividad dentro de los ultimos 30 dias pero que si lo estuvieron dentro
 * de los 30 - 60 dias anteriores), los agrupa por curso.
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 */
function inea_sql_get_inactive_users_by_course() {
	global $CFG, $DB;
	
	$sql = "SELECT h.courseid, COUNT(h.userid) AS num_bajas
			FROM {inea_historial} h
			WHERE h.roleid = ? 
			AND h.courseid IS NOT NULL
			AND h.timemodified BETWEEN (UNIX_TIMESTAMP()-(30 * 24 * 60 * 60)) AND UNIX_TIMESTAMP()
			GROUP BY h.courseid ORDER BY h.courseid";
	//echo $sql; 
	
	return $DB->get_records_sql($sql, array(EDUCANDO));
}

/**
 * INEA - Estadistica No. 2 : Obtiene a los educandos activos que han concluido 
 * el modulo, los agrupa por curso
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 */
function inea_sql_get_completed_users_by_course() {
	global $CFG, $DB;
	
	// Ludwick: Obtener a los educandos que han concluido
	$sql1 = "SELECT g.courseid, COUNT(gm.concluido) as num_concluidos
			FROM {user} u
			INNER JOIN {groups_members gm ON (u.id = gm.userid)
			INNER JOIN {groups} g ON (gm.groupid = g.id)
			INNER JOIN {role_assignments} ra ON (u.id = ra.userid)
    		INNER JOIN {context} cx ON (cx.instanceid = g.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			WHERE u.deleted = 0 
			AND u.username != 'guest'
			AND ra.roleid = ?
			AND g.courseid IS NOT NULL 
			AND gm.concluido = 1
			AND gm.fecha_concluido BETWEEN (UNIX_TIMESTAMP()-(30 * 24 * 60 * 60)) AND UNIX_TIMESTAMP()
    		GROUP BY g.courseid ORDER BY g.courseid";
	
	$sql2 = "SELECT h.courseid, COUNT(h.userid) as num_concluidos
			FROM {inea_historial} h
			WHERE h.roleid = ? 
			AND h.courseid IS NOT NULL
			AND h.completiondate BETWEEN (UNIX_TIMESTAMP()-(30 * 24 * 60 * 60)) AND UNIX_TIMESTAMP()
			GROUP BY h.courseid ORDER BY h.courseid";
	
	if(!$arr1 = $DB->get_records_sql($sql1, array(EDUCANDO))) {
		return false;
	}
	
	if(!$arr2 = $DB->get_records_sql($sql2, array(EDUCANDO))) {
		return false;
	}

	/*print_object($arr1);
	echo "<br>2:";
	print_object($arr2);*/
	
	foreach($arr2 as $key=>$val){
		$arr1[$key]->num_concluidos += $val->num_concluidos;
	}
	//print_object($arr1);
	return $arr1;	
	//return get_records_sql($sql);
}

/**
 * Ludwick: Estadistica No. 2 : Imprimir en Web la tabla con la informacion de la estadistica
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_status_users_by_course() {
	$est2 = statistic_get_status_users_by_course();
	$st_table = "";
	
	$st_table .= "<h2 class='main'>Educandos por Curso <br> (Del ".date("d/m/Y", strtotime(('now').'-1 month'))." al ".date("d/m/Y").")</h2>";
	$st_table .= "<table id='reportes-cursos' border='2' align='center' clas='flexible generaltable generalbox' width='90%'>";
	$st_table .= "<tr class='r0'>
          	<th class='header c0'>Modulos</th>
          	<th class='header c0'>Atenci贸n para este mes</th>
            <th class='header c0'>Concluyeron m贸dulo</th>
            <th class='header c0'>Presentaron examen</th>
            <th class='header c0'>Acreditados</th>
            <th class='header c0'>Bajas</th>
            <th class='header c0'>Activos</th>
            <th class='header c0'>Incorporaciones</th>
            <th class='header c0'>Atenci贸n para el mes siguiente</th>
         	</tr>";

	$totales = array();

	// Ludwick: Empezamos por mostrar los cursos basicos
	$cursos_basicos = get_courses(3, "c.id");
	$orden_cursos = array(0=>"B2ESL", 1=>"B2ELE", 2=>"B2CVC", 3=>"B2CVM", 4=>"B2MCU", 5=>"B2MFM", 6=>"B3MFP", 7=>"B3EHE", 8=>"B3MIG", 9=>"B3CNH", 10=>"B3CNP", 11=>"B3MOA", 12=>"B3ESA", 13=>"B3EVE");
	$cursos_ordenados = array();
	foreach($cursos_basicos as $curso) {
		$indice = array_shift(array_keys($orden_cursos, $curso->shortname));
		$cursos_ordenados[$indice] = $curso;
	}
	ksort($cursos_ordenados);
	$st_table .= "<tr class='r0'>
        			<th class='header c0' align='left'>B谩sicos</th>
        				<td>&nbsp;</td>
       					<td>&nbsp;</td>
        				<td>&nbsp;</td>
        				<td>&nbsp;</td>
        				<td>&nbsp;</td>
        				<td>&nbsp;</td>
        				<td>&nbsp;</td>
				</tr>";
	foreach($cursos_ordenados as $curso) {
		$atentido_anterior = isset($est2[$curso->id]->attended_prev)? $est2[$curso->id]->attended_prev : 0;
		$conluido = isset($est2[$curso->id]->completed)? $est2[$curso->id]->completed : 0;
		$presentaron = isset($est2[$curso->id]->revieweds)? $est2[$curso->id]->revieweds : 0;
		$acreditado = isset($est2[$curso->id]->accredited)? $est2[$curso->id]->accredited : 0;
		$activo = isset($est2[$curso->id]->actives)? $est2[$curso->id]->actives:0;
		$inactivo = isset($est2[$curso->id]->inactives)? $est2[$curso->id]->inactives:0;
		$incorporacion = isset($est2[$curso->id]->added)? $est2[$curso->id]->added:0;
		$atentido_siguiente = isset($est2[$curso->id]->attended_next)? $est2[$curso->id]->attended_next : 0;

		$st_table .= "<tr class='r0'>
        			<td class='header c0' align='left'>$curso->summary</td>
        				<th>".$atentido_anterior."</th>
        				<th>".$conluido."</th>
        				<th>".$presentaron."</th>
        				<th>".$acreditado."</th>
        				<th>".$inactivo."</th>
        				<th>".$activo."</th>
        				<th>".$incorporacion."</th>
        				<th>".$atentido_siguiente."</th>
				</tr>";

		$totales[0] += $atentido_anterior;
		$totales[1] += $conluido;
		$totales[2] += $presentaron;
		$totales[3] += $acreditado;
		$totales[4] += $inactivo;
		$totales[5] += $activo;
		$totales[6] += $incorporacion;
		$totales[7] += $atentido_siguiente;
	}

	// Ludwick: Mostramos los cursos diversificados
	$cursos_diversificados = get_courses(4, "c.id");
	$orden_cursos = array(12=>"D4SAG", 13=>"D4FEH", 14=>"D4END", 15=>"D4FEC", 16=>"D4JSX", 17=>"D4FHV", 18=>"D10OH");
	$cursos_ordenados = array();
	foreach($cursos_diversificados as $curso) {
		$indice = array_shift(array_keys($orden_cursos, $curso->shortname));
		$cursos_ordenados[$indice] = $curso;
	}
	ksort($cursos_ordenados);
	$st_table .= "<tr class='r0'>
        			<th class='header c0' align='left'>Diversificados</th>
        				<td>&nbsp;</td>
       					<td>&nbsp;</td>
        				<td>&nbsp;</td>
        				<td>&nbsp;</td>
        				<td>&nbsp;</td>
        				<td>&nbsp;</td>
        				<td>&nbsp;</td>
				</tr>";
	foreach($cursos_ordenados as $curso) {
		$atentido_anterior = isset($est2[$curso->id]->attended_prev)? $est2[$curso->id]->attended_prev : 0;
		$conluido = isset($est2[$curso->id]->completed)? $est2[$curso->id]->completed : 0;
		$presentaron = isset($est2[$curso->id]->revieweds)? $est2[$curso->id]->revieweds : 0;
		$acreditado = isset($est2[$curso->id]->accredited)? $est2[$curso->id]->accredited : 0;
		$activo = isset($est2[$curso->id]->actives)? $est2[$curso->id]->actives:0;
		$inactivo = isset($est2[$curso->id]->inactives)? $est2[$curso->id]->inactives:0;
		$incorporacion = isset($est2[$curso->id]->added)? $est2[$curso->id]->added:0;
		$atentido_siguiente = isset($est2[$curso->id]->attended_next)? $est2[$curso->id]->attended_next : 0;

		$st_table .= "<tr class='r0'>
        			<td class='header c0' align='left'>$curso->summary</td>
        				<th>".$atentido_anterior."</th>
        				<th>".$conluido."</th>
       					<th>".$presentaron."</th>
        				<th>".$acreditado."</th>
        				<th>".$inactivo."</th>
        				<th>".$activo."</th>
        				<th>".$incorporacion."</th>
        				<th>".$atentido_siguiente."</th>
				</tr>";

		$totales[0] += $atentido_anterior;
		$totales[1] += $conluido;
		$totales[2] += $presentaron;
		$totales[3] += $acreditado;
		$totales[4] += $inactivo;
		$totales[5] += $activo;
		$totales[6] += $incorporacion;
		$totales[7] += $atentido_siguiente;
	}

	$st_table .= "<tr class='r0'>
     			<th class='header c0' >TOTALES </th>
    	 		<th class='header c0' >".$totales[0]."</th>
     			<th class='header c0' >".$totales[1]."</th>
     			<th class='header c0' >".$totales[2]."</th>
     			<th class='header c0' >".$totales[3]."</th>
     			<th class='header c0' >".$totales[4]."</th>
     			<th class='header c0' >".$totales[5]."</th>
     			<th class='header c0' >".$totales[6]."</th>
     			<th class='header c0' >".$totales[7]."</th></tr>";
	$st_table .= "</table><br>";
	
	return $st_table;
}

/**
 * RUDY: Estadistica 2013 : Imprimir en Web la tabla con la informacion de la estadistica
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_status_asesores_by_course() {
	$est2 = statistic_get_status_users_by_course();
	$st_table = "";
	
	$st_table .= "<h2 class='main'>Educandos con/sin Asesor por Curso <br> (Del ".date("d/m/Y", strtotime(('now').'-1 month'))." al ".date("d/m/Y").")</h2>";
	$st_table .= "<table id='reportes-cursos' border='2' align='center' clas='flexible generaltable generalbox' width='90%'>";
	$st_table .= "<tr class='r0'>
          	<th class='header c0'>Cursos</th>
            <th class='header c0'>En 1 curso</th>
            <th class='header c0'>En 2 cursos</th>
         	</tr>";

	$totales = array();

	// Ludwick: Empezamos por mostrar los cursos basicos
	$cursos_basicos = get_courses(3, "c.id");
	$orden_cursos = array(0=>"B2ESL", 1=>"B2ELE", 2=>"B2CVC", 3=>"B2CVM", 4=>"B2MCU", 5=>"B2MFM", 6=>"B3MFP", 7=>"B3EHE", 8=>"B3MIG", 9=>"B3CNH", 10=>"B3CNP", 11=>"B3MOA", 12=>"B3ESA", 13=>"B3EVE");
	$cursos_ordenados = array();
	foreach($cursos_basicos as $curso) {
		$indice = array_shift(array_keys($orden_cursos, $curso->shortname));
		$cursos_ordenados[$indice] = $curso;
	}
	ksort($cursos_ordenados);
	$st_table .= "<tr class='r0'>
        			<th class='header c0' align='left'>B谩sicos</th>
        				<td>&nbsp;</td>
        				<td>&nbsp;</td>
				</tr>";
	foreach($cursos_ordenados as $curso) {
		//$atentido_anterior = isset($est2[$curso->id]->attended_prev)? $est2[$curso->id]->attended_prev : 0;
		//$conluido = isset($est2[$curso->id]->completed)? $est2[$curso->id]->completed : 0;
		//$presentaron = isset($est2[$curso->id]->revieweds)? $est2[$curso->id]->revieweds : 0;		
		//$acreditado = isset($est2[$curso->id]->accredited)? $est2[$curso->id]->accredited : 0;
		//$inactivo = isset($est2[$curso->id]->inactives)? $est2[$curso->id]->inactives:0;
		$activo = isset($est2[$curso->id]->actives)? $est2[$curso->id]->actives:0;
		$incorporacion = isset($est2[$curso->id]->added)? $est2[$curso->id]->added:0;
		//$atentido_siguiente = isset($est2[$curso->id]->attended_next)? $est2[$curso->id]->attended_next : 0;

		$st_table .= "<tr class='r0'>
        			<td class='header c0' align='left'>$curso->summary</td>
        				<th>".$activo."</th>
        				<th>".$incorporacion."</th>
				</tr>";

		//$totales[0] += $atentido_anterior;
		//$totales[1] += $conluido;
		//$totales[2] += $presentaron;
		//$totales[3] += $acreditado;
		//$totales[4] += $inactivo;
		$totales[5] += $activo;
		$totales[6] += $incorporacion;
		//$totales[7] += $atentido_siguiente;
	}

	// Ludwick: Mostramos los cursos diversificados
	$cursos_diversificados = get_courses(4, "c.id");
	$orden_cursos = array(12=>"D4SAG", 13=>"D4FEH", 14=>"D4END", 15=>"D4FEC", 16=>"D4JSX", 17=>"D4FHV", 18=>"D10OH");
	$cursos_ordenados = array();
	foreach($cursos_diversificados as $curso) {
		$indice = array_shift(array_keys($orden_cursos, $curso->shortname));
		$cursos_ordenados[$indice] = $curso;
	}
	ksort($cursos_ordenados);
	$st_table .= "<tr class='r0'>
        			<th class='header c0' align='left'>Diversificados</th>
        				<td>&nbsp;</td>
        				<td>&nbsp;</td>
				</tr>";
	foreach($cursos_ordenados as $curso) {
		//$atentido_anterior = isset($est2[$curso->id]->attended_prev)? $est2[$curso->id]->attended_prev : 0;
		//$conluido = isset($est2[$curso->id]->completed)? $est2[$curso->id]->completed : 0;
		//$presentaron = isset($est2[$curso->id]->revieweds)? $est2[$curso->id]->revieweds : 0;
		//$acreditado = isset($est2[$curso->id]->accredited)? $est2[$curso->id]->accredited : 0;
		//$inactivo = isset($est2[$curso->id]->inactives)? $est2[$curso->id]->inactives:0;
		$activo = isset($est2[$curso->id]->actives)? $est2[$curso->id]->actives:0;
		$incorporacion = isset($est2[$curso->id]->added)? $est2[$curso->id]->added:0;
		//$atentido_siguiente = isset($est2[$curso->id]->attended_next)? $est2[$curso->id]->attended_next : 0;

		$st_table .= "<tr class='r0'>
        			<td class='header c0' align='left'>$curso->summary</td>
        				<th>".$activo."</th>
        				<th>".$incorporacion."</th>
				</tr>";

		//$totales[0] += $atentido_anterior;
		//$totales[1] += $conluido;
		//$totales[2] += $presentaron;
		//$totales[3] += $acreditado;
		//$totales[4] += $inactivo;
		$totales[5] += $activo;
		$totales[6] += $incorporacion;
		//$totales[7] += $atentido_siguiente;
	}

	$st_table .= "<tr class='r0'>
     			<th class='header c0' >TOTALES </th>
     			<th class='header c0' >".$totales[5]."</th>
     			<th class='header c0' >".$totales[6]."</th></tr>";
	$st_table .= "</table><br>";
	
	return $st_table;
}


/**
 * Ludwick: Estadistica No. 2 : Imprimir en Web la tabla con la informacion de la estadistica (CSV) 
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_status_users_by_course_csv($xmlObject) {
	$est2 = statistic_get_status_users_by_course();
	$xmlPart = "";
	
	$worksheetName = "Personas por Curso";
	$headerValues = "Modulos,Atenci贸n para este mes,Concluyeron m贸dulo,Presentaron examen,Acreditados,Bajas,Activos,Incorporaciones,Atenci贸n para el mes siguiente";
	$totales = array();
	$xmlPart .= $xmlObject->AddWorkSheet($worksheetName,$headerValues, 's72');
	
	// Ludwick: Empezamos por mostrar los cursos basicos (Categoria 3)
	$cursos_basicos = get_courses(3, "c.id");
	$orden_cursos = array(0=>"B2ESL", 1=>"B2CVC", 2=>"B2CVM", 3=>"B3MFP", 4=>"B3EHE", 5=>"B3MIG", 6=>"B3CNH", 7=>"B3CNP", 8=>"B3MOA", 9=>"B3ESA", 11=>"B3EVE");
	$cursos_ordenados = array();
	foreach($cursos_basicos as $curso) {
		$indice = array_shift(array_keys($orden_cursos, $curso->shortname));
		$cursos_ordenados[$indice] = $curso;
	}
	ksort($cursos_ordenados);
	$DataValues = "B谩sicos nivel intermedio";
	$xmlPart .= $xmlObject->getColumnData($DataValues, 's72');
	
	foreach($cursos_ordenados as $curso) {
		$atentido_anterior = isset($est2[$curso->id]->attended_prev)? $est2[$curso->id]->attended_prev : 0;
		$conluido = isset($est2[$curso->id]->completed)? $est2[$curso->id]->completed : 0;
		$presentaron = isset($est2[$curso->id]->revieweds)? $est2[$curso->id]->revieweds : 0;
		$acreditado = isset($est2[$curso->id]->accredited)? $est2[$curso->id]->accredited : 0;
		$activo = isset($est2[$curso->id]->actives)? $est2[$curso->id]->actives:0;
		$inactivo = isset($est2[$curso->id]->inactives)? $est2[$curso->id]->inactives:0;
		$incorporacion = isset($est2[$curso->id]->added)? $est2[$curso->id]->added:0;
		$atentido_siguiente = isset($est2[$curso->id]->attended_next)? $est2[$curso->id]->attended_next : 0;
		$nombre_curso = str_replace(",", "", $curso->summary);
	
		$DataValues = $nombre_curso.",".$atentido_anterior.",".$conluido.",".$presentaron.",".$acreditado.",".$inactivo.",".$activo.",".$incorporacion.",".$atentido_siguiente;
		$xmlPart .= $xmlObject->getColumnData($DataValues, 's42', true);
		
		$totales[0] += $atentido_anterior;
		$totales[1] += $conluido;
		$totales[2] += $presentaron;
		$totales[3] += $acreditado;
		$totales[4] += $inactivo;
		$totales[5] += $activo;
		$totales[6] += $incorporacion;
		$totales[7] += $atentido_siguiente;
	}

	// Ludwick: Mostramos los cursos diversificados (Categoria 4)
	$cursos_diversificados = get_courses(4, "c.id");
	$orden_cursos = array(12=>"D4SAG", 13=>"D4FEH", 14=>"D4END", 15=>"D4UNV", 16=>"D4FEC", 17=>"D4JSX", 18=>"D4FHV");
	$cursos_ordenados = array();
	foreach($cursos_diversificados as $curso) {
		$indice = array_shift(array_keys($orden_cursos, $curso->shortname));
		$cursos_ordenados[$indice] = $curso;
	}
	ksort($cursos_ordenados);
	$DataValues = "Diversificados";
	$xmlPart .= $xmlObject->getColumnData($DataValues, 's72');
	
	foreach($cursos_ordenados as $curso) {
		$atentido_anterior = isset($est2[$curso->id]->attended_prev)? $est2[$curso->id]->attended_prev : 0;
		$conluido = isset($est2[$curso->id]->completed)? $est2[$curso->id]->completed : 0;
		$presentaron = isset($est2[$curso->id]->revieweds)? $est2[$curso->id]->revieweds : 0;
		$acreditado = isset($est2[$curso->id]->accredited)? $est2[$curso->id]->accredited : 0;
		$activo = isset($est2[$curso->id]->actives)? $est2[$curso->id]->actives:0;
		$inactivo = isset($est2[$curso->id]->inactives)? $est2[$curso->id]->inactives:0;
		$incorporacion = isset($est2[$curso->id]->added)? $est2[$curso->id]->added:0;
		$atentido_siguiente = isset($est2[$curso->id]->attended_next)? $est2[$curso->id]->attended_next : 0;
		$nombre_curso = str_replace(",", "", $curso->summary);
		
		$DataValues = $nombre_curso.",".$atentido_anterior.",".$conluido.",".$presentaron.",".$acreditado.",".$inactivo.",".$activo.",".$incorporacion.",".$atentido_siguiente;
		$xmlPart .= $xmlObject->getColumnData($DataValues, 's42', true);

		$totales[0] += $atentido_anterior;
		$totales[1] += $conluido;
		$totales[2] += $presentaron;
		$totales[3] += $acreditado;
		$totales[4] += $inactivo;
		$totales[5] += $activo;
		$totales[6] += $incorporacion;
		$totales[7] += $atentido_siguiente;
	}

	$DataValues = "TOTALES,".$totales[0].",".$totales[1].",".$totales[2].",".$totales[3].",".$totales[4].",".$totales[5].",".$totales[6].",".$totales[7];
	$xmlPart .= $xmlObject->getColumnData($DataValues, 's73');
	
	$xmlPart .= "</Table>";
	$xmlPart .= $xmlObject->GetFooter(); 
	
	return $xmlPart;
}

/**
 * Ludwick:190510 Estadistica No. 2 : Obtiene a los educandos activos (aquellos que tienen
 * un grupo, asesor y han accesado a alguna actividad dentro de 30 dias), que han concluido 
 * el modulo y lo han aprobado con una calificacion > 5, los agrupa por curso.
 *
 * @deprecated - Funcion personalizada.
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_accredited_users_by_course() {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true);
	
	$sql1 = "SELECT gc.courseid, COUNT(gm.fecha_aplicacion) AS num_acreditados
			FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			WHERE u.deleted = 0 
			AND u.username != 'guest'
			AND ra.roleid = ".$s_rol."
			AND gc.courseid IS NOT NULL
			AND gm.concluido = 1
    		AND gm.calificacion > 5
			AND gm.fecha_aplicacion <> ''
			AND gm.fecha_aplicacion BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
    		GROUP BY gc.courseid ORDER BY gc.courseid";
	
	$sql2 = "SELECT h.courseid, COUNT(h.userid) AS num_acreditados
			FROM {$CFG->prefix}historial h
			WHERE h.roleid = ".$s_rol." 
			AND h.courseid IS NOT NULL
			AND h.grade > 5
			AND h.applicationdate BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
			GROUP BY h.courseid ORDER BY h.courseid";
	
	$arr1 = get_records_sql($sql1);
	$arr2 = get_records_sql($sql2);
	/*print_object($arr1);
	echo "<br>2:";
	print_object($arr2);*/
	
	foreach($arr2 as $key=>$val){
		$arr1[$key]->num_acreditados += $val->num_acreditados;
	}
	//print_object($arr1);
	return $arr1;
	
	//echo "<br>".$sql;
	//return get_records_sql($sql);
}

/**
 * Ludwick:190510 Estadistica No. 2 : Obtiene a las educandos incorporados al curso
 * (aquellos que han contestado una actividad dentro del mes o 30 dias), los agrupa por curso.
 *
 * @deprecated - Funcion personalizada.
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_added_user_by_course($timerange=0) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true);
	
	$sql = "SELECT gc.courseid, COUNT(u.id) as num_incorporaciones
    		FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			INNER JOIN (SELECT ir.userid, ie.courseid, MIN(ir.timemodified) AS firstactivity, MAX(ir.timemodified) AS lastactivity 
				FROM {$CFG->prefix}inea_respuestas ir
				INNER JOIN {$CFG->prefix}inea_ejercicios ie ON ie.id=ir.ejercicios_id
				GROUP BY ie.courseid,ir.userid) ia ON (ia.courseid=gc.courseid AND ia.userid=u.id)    		
    		WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND ra.roleid = ".$s_rol."
				AND gc.courseid IS NOT NULL 
    			AND ia.firstactivity >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) 
				AND ia.lastactivity <= CURRENT_DATE()
    			GROUP BY gc.courseid ORDER BY gc.courseid";
				
	//echo "<br>".$sql;
	return get_records_sql($sql);
}

/**
 * Ludwick:190510 Estadistica No. 2 : Obtiene a los educandos que han presentado examen
 * (aquellos que han recibido una calificacion), los agrupa por curso.
 *
 * @deprecated - Funcion personalizada.
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function db_get_reviewed_user_by_course() {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true);
	
	// Calcular fecha desde MYSQL
	$sql1 = "SELECT gc.courseid, COUNT(gm.fecha_aplicacion) AS num_presentaron
			FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			WHERE u.deleted = 0 
			AND u.username != 'guest'
			AND ra.roleid = ".$s_rol."
			AND gc.courseid IS NOT NULL 
			AND gm.fecha_aplicacion <> ''
			AND gm.fecha_aplicacion BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
    		GROUP BY gc.courseid ORDER BY gc.courseid";
	
	$sql2 = "SELECT h.courseid, COUNT(h.userid) AS num_presentaron
			FROM {$CFG->prefix}historial h
			WHERE h.roleid = 5 
			AND h.courseid IS NOT NULL
			AND h.applicationdate BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
			GROUP BY h.courseid ORDER BY h.courseid";
	
	$arr1 = get_records_sql($sql1);
	$arr2 = get_records_sql($sql2);
	/*print_object($arr1);
	echo "<br>2:";
	print_object($arr2);*/
	
	foreach($arr2 as $key=>$val){
		$arr1[$key]->num_presentaron += $val->num_presentaron;
	}
	//print_object($arr1);
	return $arr1;
	
	//return get_records_sql($sql);
}

/**
 * Ludwick:190510 Estadistica No. 2 : Obtiene a los educandos en el historial que presentaron
 * el examen (aquellos que han recibido una calificacion), los agrupa por curso.
 *
 * @deprecated - Funcion personalizada.
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function db_get_reviewed_user_historial_by_course() {
	global $CFG;
	
	$s_rol = get_student_role(true); // Id del rol del educando
	
	$sql = "SELECT DISTINCT id, userid, courseid, approvaldate
		FROM {$CFG->prefix}historial
		WHERE approvaldate IS NOT NULL
		AND grade IS NOT NULL
		AND type = 1
		ORDER BY courseid";
	
	return get_records_sql($sql);
}

/**
 * Ludwick: Estadistica No. 3 : Informe General
 *
 * @deprecated - Funcion personalizada.
 * @return Object $statistic3: Un arreglo con la informacion de la estadistica 3
 * 
 */
function statistic_get_users_report_by_entity_and_course($estado) {
	$statistic3 = array();
	
	// Obtenemos todos los cursos disponibles en el sistema
	$courses = get_courses("all", "c.id");
	
	foreach($courses as $course) {
		// Ludwick: Buscamos a los usuarios que estan activos por curso
		$active_users = bd_get_active_users_by_entity_and_course($course->id,$estado);
		//print_object($active_users);
		if(!empty($active_users)) {
			foreach($active_users as $id_entity=>$active_user) {
				//$acreditados = isset($accredited_users[$id_entity]->num_acreditados)? $accredited_users[$id_entity]->num_acreditados : 0;
				//$bajas = isset($inactive_users[$id_entity]->num_bajas)? $inactive_users[$id_entity]->num_bajas : 0;
				$statistic3[$id_entity][$course->id]->actives = $active_user->num_usuarios;
			}
		}
		
		// Ludwick: Buscamos a los usuarios que estan inactivos por curso
		$inactive_users = bd_get_inactive_users_by_entity_and_course($course->id,$estado);
		if(!empty($inactive_users)) {
			foreach($inactive_users as $id_entity=>$inactive_user) {
				$statistic3[$id_entity][$course->id]->inactives = $inactive_user->num_bajas;
			}
		}

		// RUDY: Buscamos a los usuarios que han concluido el curso
		$concluded_users = bd_get_concluded_users_by_entity_and_course($course->id,$estado);
		if(!empty($concluded_users)) {
			foreach($concluded_users as $id_entity=>$concluded_user) {
				$statistic3[$id_entity][$course->id]->concluded = $concluded_user->num_concluidos;
			}
		}
		
		// Ludwick: Buscamos a los usuarios que han acreditado el curso
		$accredited_users = bd_get_accredited_users_by_entity_and_course($course->id,$estado);
		if(!empty($accredited_users)) {
			foreach($accredited_users as $id_entity=>$accredited_user) {
				$statistic3[$id_entity][$course->id]->accredited = $accredited_user->num_acreditados;
			}
		}
		
		// Ludwick: Buscamos las incorporaciones hasta la fecha
		$added_users = bd_get_added_user_by_entity_and_course($course->id,$estado);
		if(!empty($added_users)) {
			foreach($added_users as $id_entity=>$added_user) {
				$statistic3[$id_entity][$course->id]->added = $added_user->num_incorporaciones;
			}
		}
		
		// Ludwick: Buscamos a los usuarios que seran atendidos dentro de 30 dias anteriores y posteriores
		if(!empty($statistic3)) {
			foreach($statistic3 as $id_entity=>$section) {
				$activos = isset($section[$course->id]->actives)? $section[$course->id]->actives : 0;
				$incorporaciones = isset($section[$course->id]->added)? $section[$course->id]->added : 0;
				
				$acreditados = isset($section[$course->id]->accredited)? $section[$course->id]->accredited : 0;
				$bajas = isset($section[$course->id]->inactives)? $section[$course->id]->inactives : 0;
		
				$atencion_previo = ($activos + $acreditados + $bajas);
				$atencion_siguiente = ($activos + $incorporaciones);
				if($atencion_siguiente > 0)
					$statistic3[$id_entity][$course->id]->attended_next = $atencion_siguiente;
					
				if($atencion_previo > 0)
					$statistic3[$id_entity][$course->id]->attended_prev = $atencion_previo;
			}
		}
	}
	
	return $statistic3;
}


/**
 * Rudy: Estadistica No. 3 : Informe General
 *
 * @deprecated - Funcion personalizada.
 * @return Object $statistic3: Un arreglo con la informacion de la estadistica 3
 * 
 */
function statistic_get_users_report_by_entity($estado) {
	$statistic3 = array();
	
		$active_users = bd_get_active_users_by_entity($estado);
		//print_object($active_users);
		if(!empty($active_users)) {
			foreach($active_users as $id_entity=>$active_user) {
				//$acreditados = isset($accredited_users[$id_entity]->num_acreditados)? $accredited_users[$id_entity]->num_acreditados : 0;
				//$bajas = isset($inactive_users[$id_entity]->num_bajas)? $inactive_users[$id_entity]->num_bajas : 0;
				$statistic3[$id_entity]->actives = $active_user->num_usuarios;
			}
		}
		
		// Ludwick: Buscamos a los usuarios que estan inactivos por curso
		$inactive_users = bd_get_inactive_users_by_entity($estado);
		if(!empty($inactive_users)) {
			foreach($inactive_users as $id_entity=>$inactive_user) {
				$statistic3[$id_entity]->inactives = $inactive_user->num_bajas;
			}
		}

		// RUDY: Buscamos a los usuarios que han concluido el curso
		$concluded_users = bd_get_concluded_users_by_entity($estado);
		if(!empty($concluded_users)) {
			foreach($concluded_users as $id_entity=>$concluded_user) {
				$statistic3[$id_entity]->concluded = $concluded_user->num_concluidos;
			}
		}
		
		// Ludwick: Buscamos a los usuarios que han acreditado el curso
		$accredited_users = bd_get_accredited_users_by_entity($estado);
		if(!empty($accredited_users)) {
			foreach($accredited_users as $id_entity=>$accredited_user) {
				$statistic3[$id_entity]->accredited = $accredited_user->num_acreditados;
			}
		}
		
		// Ludwick: Buscamos las incorporaciones hasta la fecha
		$added_users = bd_get_added_user_by_entity($estado);
		//print_object($added_users);
		if(!empty($added_users)) {
			foreach($added_users as $id_entity=>$added_user) {
				$statistic3[$id_entity]->added = $added_user->num_incorporaciones;
			}
		}
		
		// Ludwick: Buscamos a los usuarios que seran atendidos dentro de 30 dias anteriores y posteriores
		if(!empty($statistic3)) {
			foreach($statistic3 as $id_entity=>$section) {
				$activos = isset($section->actives)? $section->actives : 0;
				$incorporaciones = isset($section->added)? $section->added : 0;
				
				$concluidos = isset($section->concluded)? $section->concluded : 0;
				$bajas = isset($section->inactives)? $section->inactives : 0;
		
				$atencion_previo = ($activos + $concluidos + $bajas);
				$atencion_siguiente = ($activos + $incorporaciones);
				if($atencion_siguiente > 0)
					$statistic3[$id_entity]->attended_next = $atencion_siguiente;
					
				if($atencion_previo > 0)
					$statistic3[$id_entity]->attended_prev = $atencion_previo;
			}
		}
	
	return $statistic3;
}


/**
 * RUDY: Estadistica 2013 : Imprimir en Web la tabla con la informacion de la estadistica
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_users_report_by_entity($estado) {
	$est3 = statistic_get_users_report_by_entity($estado);
			
	$st_table = "";
	
	$st_table .= "<h2 class='main'>Cursos por Entidad <br> (Del ".date('d/m/Y', strtotime(('now').'-1 month'))." al ".date('d/m/Y').")</h2>";
	$st_table .= "<table id='reportes-cursos' border='2' align='center' clas='flexible generaltable generalbox' width='90%'>";
	$st_table .= "<tr class='r0'>
			<th class='header c0' scope='col'>Entidad</th>";
	$st_table .= "<th class='header c0' scope='col'>Evidencias</th>
    		<th class='header c0' scope='col'>Bajas</th>
    		<th class='header c0' scope='col'>Activos</th>
    		<th class='header c0'>Vinculados</th>
            <th class='header c0'>Activos para el siguiente mes</th></tr>";

	$totales = array();
	if($estado != 0){
		$estados = get_records_select("inea_entidad", "icvepais = 1 AND icveentfed = ".$estado);
	}else{ 
		$estados = get_records("inea_entidad", "icvepais", 1);
	}
	
	foreach($estados as $ident=>$estado) {
		//echo $estado->cdesentfed;
		
		$concluded = isset($est3[$ident]->concluded) ? $est3[$ident]->concluded : 0;
		$inactives = isset($est3[$ident]->inactives) ? $est3[$ident]->inactives : 0;
		$actives = isset($est3[$ident]->actives) ? $est3[$ident]->actives : 0;
		$added = isset($est3[$ident]->added) ? $est3[$ident]->added : 0;
		$attended_next = isset($est3[$ident]->attended_next) ? $est3[$ident]->attended_next : 0;
		
		
		$totalrow = "";
			$st_table .= "<tr class='r0'>";
			
			$st_table .= "<th class='header c0' align='left'>".$estado->cdesentfed."</th>";
			$st_table .= "<td align='center'>".$concluded."</td>";
			$st_table .= "<td align='center'>".$inactives."</td>";
			$st_table .= "<td align='center'>".$actives."</td>";
			$st_table .= "<td align='center'>".$added."</td>";
			$st_table .= "<td align='center'>".$attended_next."</td>";

			//$totalrow .= "<td>".$t_campo_entidad[$ident][$cmp]."</td>";
	}
	
			$st_table .= "</tr>";

	// Imprimir el resumen por curso
	// RUDY: Comentado hasta que se programen sumas parciales 
/*		$st_table .= "<tr class='r0'>";
		$totalrow = "";
		$st_table .= "<th class='header c0' align='left'>TOTALES</th>";
	foreach($campos as $cmp=>$campo) {
		$totalrow .= "<td class='header c0'>".$t_campo_total[$cmp]."</td>";
			
	}
		$st_table .= "</tr>";
*/	
	
	$st_table .= "</table><br>";
	
	return $st_table;
}


function statistic_print_users_report_by_entity_and_course($estado) {
	$est3 = statistic_get_users_report_by_entity_and_course($estado);
	$st_table = "";
	
	$courses = get_courses("all", "c.category, c.id");
	//print_object($courses);
	//exit;
	
	// Ordenamos los cursos en base a la preferencia en el nombre corto de cada curso
	$orden_cursos = array(0=>"B2ESL", 1=>"B2CVC", 2=>"B2CVM", 3=>"B3MFP", 4=>"B3EHE", 5=>"B3MIG", 6=>"B3CNH", 7=>"B3CNP", 8=>"B3MOA", 9=>"B3ESA", 11=>"B3EVE", 12=>"D4SAG", 13=>"D4FEH", 14=>"D4END", 15=>"D4UNV", 16=>"D4FEC", 17=>"D4JSX", 18=>"D4FHV", 19=>"B2ELE", 20=>"B2MCU", 21=>"B2MFM");
	$cursos_ordenados = array();
	foreach($courses as $curso) {
		$indice = array_shift(array_keys($orden_cursos, $curso->shortname));
		$cursos_ordenados[$indice] = $curso;
	}
	ksort($cursos_ordenados);

	$st_table .= "<h2 class='main'>Desglose de cursos por Entidad <br> (Del ".date("d/m/Y", strtotime(('now').'-1 month'))." al ".date("d/m/Y").")</h2>";
	$st_table .= "<table id='reportes-cursos' border='2' align='center' clas='flexible generaltable generalbox' width='90%'>";
	$st_table .= "<tr class='r0'>
			<th class='header c0' scope='col'>Entidad</th>
    		<th class='header c0' scope='col'>&nbsp;</th>";
	foreach($cursos_ordenados as $course) {
		if($course->category==0) continue;

		$st_table .= "<th class='header c0' scope='col'><div title='$course->fullname'>&nbsp;$course->shortname&nbsp;</div></th>";
	}
	$st_table .= "<th class='header c0' scope='col'>Activos</th>
    		<th class='header c0'>Vinculados</th>
            <th class='header c0'>Total</th></tr>";

	$totales = array();
	if($estado != 0){
		$estados = get_records_select("inea_entidad", "icvepais = 1 AND icveentfed = ".$estado);
		$resumen = false;
	}else{ 
		$estados = get_records("inea_entidad", "icvepais", 1);
		$resumen = true;
	}
	$campos = array("actives"=>"Activos", "added"=>"Vinculados", "attended_next"=>"TOTAL");
	$rspan = count($campos);
	$t_campo_entidad = array();
	$t_campo_curso = array();
	//print_object($cursos_ordenados);
	foreach($estados as $ident=>$estado) {
		$cambio = true;
		foreach($campos as $cmp=>$campo) {
			$st_table .= "<tr class='r0'>";
			if($cambio) {
				$st_table .= "<th class='header c0' align='left' rowspan='$rspan'>$estado->cdesentfed</th>";
				$cambio = false;
			}

			$st_table .= "<td>".$campo."</td>";
			foreach($cursos_ordenados as $course) {
				if($course->category==0) {
					continue;
				}

				$valor = isset($est3[$ident][$course->id]->$cmp)? $est3[$ident][$course->id]->$cmp : 0;
				$valor_to_p = ($valor!=0)? $valor : "&nbsp;";
				$st_table .= "<td>".$valor_to_p."</td>";
				$t_campo_entidad[$ident][$cmp] += $valor;
				//if($cmp == "attended_prev") {
					$t_campo_curso[$course->id][$cmp] += $valor;
				//}
				$t_campo_total[$cmp] += $valor;
			}
			// Para el Campo Activos se calcula de otra forma
			//$t_campo_entidad[$ident]["actives"] -= ($t_campo_entidad[$ident]["accredited"]+$t_campo_entidad[$ident]["inactives"]);
			
			switch($cmp) {
				//case "attended_prev": $totalrow="<td>".$t_campo_entidad[$ident][$cmp]."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>"; break;
				//case "accredited": $totalrow="<td>&nbsp;</td><td>".$t_campo_entidad[$ident][$cmp]."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>"; break;
				//case "inactives": $totalrow="<td>&nbsp;</td><td>&nbsp;</td><td>".$t_campo_entidad[$ident][$cmp]."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>"; break;
				case "actives": $totalrow="<td>".$t_campo_entidad[$ident][$cmp]."</td><td>&nbsp;</td><td>&nbsp;</td>"; break;
				case "added": $totalrow="<td>&nbsp;</td><td>".$t_campo_entidad[$ident][$cmp]."<td>&nbsp;</td></td>"; break;
				case "attended_next": $totalrow="<td>&nbsp;</td><td>&nbsp;</td><td>".$t_campo_entidad[$ident][$cmp]."</td>"; break;
				default: $totalrow="<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>"; break;
			}

			$st_table .= "$totalrow</tr>";
		}
	}

	// Imprimir el resumen por curso
	if(!$resumen){
		$st_table .= "</table><br>";
		
	}
	else{ 
	
	$cambio = true;
	foreach($campos as $cmp=>$campo) {
		$st_table .= "<tr class='r0'>";
		if($cambio) {
			$st_table .= "<th class='header c0' align='left' rowspan='$rspan'>Resumen por Curso</th>";
			$cambio = false;
		}

		$st_table .= "<td class='header c0'>".$campo."</td>";
		foreach($cursos_ordenados as $course) {
				if($course->category==0) {
					continue;
				}

				//$valor = isset($est3[$ident][$course->id]->$cmp)? $est3[$ident][$course->id]->$cmp : 0;
				//$valor_to_p = ($valor!=0)? $valor : "&nbsp;";
				$st_table .= "<td class='header c0'>".$t_campo_curso[$course->id][$cmp]."</td>";
				//$t_campo_entidad[$ident][$cmp] += $valor;
				//if($cmp == "attended_prev") {
				//	$t_campo_curso[$cmp][$course->id] += $valor;
				//}
				//$t_campo_total[$cmp] += $valor;
		}
		// Para el Campo Activos se calcula de otra forma
		//$t_campo_entidad[$ident]["actives"] -= ($t_campo_entidad[$ident]["accredited"]+$t_campo_entidad[$ident]["inactives"]);
			
		switch($cmp) {
			//case "attended_prev": $totalrow="<td class='header c0'>".$t_campo_total[$cmp]."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>"; break;
			//case "accredited": $totalrow="<td>&nbsp;</td><td class='header c0'>".$t_campo_total[$cmp]."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>"; break;
			//case "inactives": $totalrow="<td>&nbsp;</td><td>&nbsp;</td><td class='header c0'>".$t_campo_total[$cmp]."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>"; break;
			case "actives": $totalrow="<td class='header c0'>".$t_campo_total[$cmp]."</td><td>&nbsp;</td><td>&nbsp;</td>"; break;
			case "added": $totalrow="<td>&nbsp;</td><td class='header c0'>".$t_campo_total[$cmp]."<td>&nbsp;</td></td>"; break;
			case "attended_next": $totalrow="<td>&nbsp;</td><td>&nbsp;</td><td class='header c0'>".$t_campo_total[$cmp]."</td>"; break;
			default: $totalrow="<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>"; break;
		}

		$st_table .= "$totalrow</tr>";
	}
	
	/*$st_table .= "<tr class='r0'>
     			<th class='header c0' align='left' rowspan='$rspan'>Resumen por Curso</th>";
	foreach($t_campo_curso as $t_curso) {
		$st_table .= "<th class='header c0' >$t_curso</th>";
	}
	foreach($t_campo_total as $t_total) {
		$st_table .= "<th class='header c0' >$t_total</th>";
	}*/
	$st_table .= "</table><br>";
	
	} //llave cierre else
	
	//Descripcion de abreviaturas de cursos
	$st_table .= "<table>
	<tr><td>B2MNU: Los n煤meros</td></tr>
	<tr><td>B2ESL: Saber leer</td></tr>
	<tr><td>B2CVC: Vamos a conocernos</td></tr>
	<tr><td>B2CVM: Vivamos mejor</td></tr>
	<tr><td>B3MFP: Fracciones y porcentajes</td></tr>
	<tr><td>B3EHE: Hablando se entiende la gente</td></tr>
	<tr><td>B3MIG: Informaci贸n y gr谩ficas</td></tr>
	<tr><td>B3CNH: M茅xico nuestro hogar</td></tr>
	<tr><td>B3CNP: Nuestro planeta la Tierra</td></tr>
	<tr><td>B3MOA: Operaciones avanzadas</td></tr>
	<tr><td>B3ESA: Para seguir aprendiendo</td></tr>
	<tr><td>B3EVE: Vamos a escribir</td></tr>
	<tr><td>D4SAG: Aguas con las adicciones!</td></tr>
	<tr><td>D4FEH: La educaci贸n de nuestros hijos e hijas</td></tr>
	<tr><td>D4END: Nuestros documentos</td></tr>
	<tr><td>D4FEC: Ser padres, una experiencia compartida</td></tr>
	<tr><td>D4JSX: Sexualidad juvenil</td></tr>
	<tr><td>D4FHV: Un hogar sin violencia</td></tr>
	<tr><td>B2ELE: Leer y escribir</td></tr>
	<tr><td>B2MCU: Cuentas 煤tiles</td></tr>
	<tr><td>B2MFM: Figuras y medidas</td></tr>
	</table><br>";

	
	return $st_table;


}


/**
 * Ludwick: Estadistica No. 3 : Imprimir en Web la tabla con la informacion de la estadistica (CSV)
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_users_report_by_entity_and_course_csv($xmlObject) {
	$est3 = statistic_get_users_report_by_entity_and_course();
	$xmlPart = "";
	
	$courses = get_courses("all", "c.category, c.id");
	//print_object($courses);
	
	// Ordenamos los cursos en base a la preferencia en el nombre corto de cada curso
	$orden_cursos = array(0=>"B2ESL", 1=>"B2CVC", 2=>"B2CVM", 3=>"B3MFP", 4=>"B3EHE", 5=>"B3MIG", 6=>"B3CNH", 7=>"B3CNP", 8=>"B3MOA", 9=>"B3ESA", 11=>"B3EVE", 12=>"D4SAG", 13=>"D4FEH", 14=>"D4END", 15=>"D4UNV", 16=>"D4FEC", 17=>"D4JSX", 18=>"D4FHV");
	$cursos_ordenados = array();
	foreach($courses as $curso) {
		$indice = array_shift(array_keys($orden_cursos, $curso->shortname));
		$cursos_ordenados[$indice] = $curso;
	}
	ksort($cursos_ordenados);

	$worksheetName = "Personas por Entidad y Curso";
	$headerValues = "Entidad,";
	
	foreach($cursos_ordenados as $course) {
		if($course->category==0) continue;
		$headerValues .= ",".$course->shortname;
	}
	$headerValues .= ",Atenci贸n para este mes,Acreditados,Bajas,Activos,Incorporaciones,Atenci贸n para el mes siguiente";
	$xmlPart .= $xmlObject->AddWorkSheet($worksheetName,$headerValues, 's72');

	$totales = array();
	$estados = get_records("inea_entidad", "icvepais", 1);
	$campos = array("attended_prev"=>"Atenci贸n para este mes", "accredited"=>"Acreditados", "inactives"=>"Bajas", "actives"=>"Activos", "added"=>"Incorporaciones", "attended_next"=>"Atenci贸n para el mes siguiente");
	$rspan = count($campos);
	$t_campo_entidad = array();
	$t_campo_curso = array();
	//print_object($cursos_ordenados);
	foreach($estados as $ident=>$estado) {
		foreach($campos as $cmp=>$campo) {
			$DataValues = $estado->cdesentfed;
			$DataValues .= ",".$campo;
			foreach($cursos_ordenados as $course) {
				if($course->category==0) {
					continue;
				}

				$valor = isset($est3[$ident][$course->id]->$cmp)? $est3[$ident][$course->id]->$cmp : 0;
				$valor_to_p = ($valor!=0)? $valor : "";
				$DataValues .= ",".$valor_to_p;
				$t_campo_entidad[$ident][$cmp] += $valor;
				//if($cmp == "attended_prev") {
					$t_campo_curso[$course->id][$cmp] += $valor;
				//}
				$t_campo_total[$cmp] += $valor;
			}
			// Para el Campo Activos se calcula de otra forma
			//$t_campo_entidad[$ident]["actives"] -= ($t_campo_entidad[$ident]["accredited"]+$t_campo_entidad[$ident]["inactives"]);

			switch($cmp) {
				case "attended_prev": $DataValues .= ",".$t_campo_entidad[$ident][$cmp].",,,,,"; break;
				case "accredited": $DataValues .= ",,".$t_campo_entidad[$ident][$cmp].",,,,"; break;
				case "inactives": $DataValues .= ",,,".$t_campo_entidad[$ident][$cmp].",,,"; break;
				case "actives": $DataValues .= ",,,,".$t_campo_entidad[$ident][$cmp].",,"; break;
				case "added": $DataValues .= ",,,,,".$t_campo_entidad[$ident][$cmp].","; break;
				case "attended_next": $DataValues .= ",,,,,,".$t_campo_entidad[$ident][$cmp]; break;
				default: $DataValues .= ",,,,"; break;
			}

			$xmlPart .= $xmlObject->getColumnData($DataValues, 's42', true);
		}
	}

	// Imprimir el resumen por curso
	$cambio = true;
	foreach($campos as $cmp=>$campo) {
		$DataValues = "Resumen por Curso";
		$DataValues .= ",".$campo;
		foreach($cursos_ordenados as $course) {
			if($course->category==0) {
				continue;
			}

			//$valor = isset($est3[$ident][$course->id]->$cmp)? $est3[$ident][$course->id]->$cmp : 0;
			//$valor_to_p = ($valor!=0)? $valor : "&nbsp;";
			$DataValues .= ",".$t_campo_curso[$course->id][$cmp];
			//$t_campo_entidad[$ident][$cmp] += $valor;
			//if($cmp == "attended_prev") {
			//	$t_campo_curso[$cmp][$course->id] += $valor;
			//}
			//$t_campo_total[$cmp] += $valor;
		}
		// Para el Campo Activos se calcula de otra forma
		//$t_campo_entidad[$ident]["actives"] -= ($t_campo_entidad[$ident]["accredited"]+$t_campo_entidad[$ident]["inactives"]);
		switch($cmp) {
			case "attended_prev": $DataValues .= ",".$t_campo_total[$cmp].",,,,,"; break;
			case "accredited": $DataValues .= ",,".$t_campo_total[$cmp].",,,,"; break;
			case "inactives": $DataValues .= ",,,".$t_campo_total[$cmp].",,,"; break;
			case "actives": $DataValues .= ",,,,".$t_campo_total[$cmp].",,"; break;
			case "added": $DataValues .= ",,,,,".$t_campo_total[$cmp].","; break;
			case "attended_next": $DataValues .= ",,,,,,".$t_campo_total[$cmp]; break;
			default: $DataValues .= ",,,,"; break;
		}
		$xmlPart .= $xmlObject->getColumnData($DataValues, 's73');
	}
	
	$xmlPart .= "</Table>";
	$xmlPart .= $xmlObject->GetFooter(); 
	
	return $xmlPart;
}



/**
 * RUDY:170913 Estadistica No. 3 : Obtiene a los educandos activos (aquellos que tienen
 * un grupo, asesor y que no
 * son de nueva incorporacion).
 *
 * @deprecated - Funcion personalizada.
 * @param int $courseid : El id del curso para filtrarlos
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_active_users_by_entity($estado) {
	global $CFG;

	@set_time_limit(0);	//RUDY (020713): agregue esta linea para que no tuviera limite de tiempo la ejecuacion del script. De otro modo la pantalla se queda en blanco.
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true);
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
	
	$sql = "SELECT u.institution as id_entidad, COUNT(u.id) as num_usuarios
    		FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
			INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
    		INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
    		WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND u.institution <> 0
				AND ra.roleid = ".$s_rol."
				AND gc.courseid IS NOT NULL 
				AND gm.concluido = 0
    			AND gm.acreditado = 0
				AND FROM_UNIXTIME(gm.timeadded) < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
				AND FROM_UNIXTIME(ul.timeaccess) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
				".$condicion." 
    			GROUP BY u.institution ORDER BY u.institution";
	//echo "<br/>".$sql;
	return get_records_sql($sql);
}

/**
 * RUDY: 200713 Estadistica 2013 : Obtiene a los educandos activos que han concluido 
 * el modulo  (aquellos que han concluido el modulo y lo han aprobado con una calificacion 
 * > 5), los agrupa por entidad.
 *
 * @deprecated - Funcion personalizada.
 * @param int $courseid : El id del curso para filtrarlos
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_concluded_users_by_entity($estado) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true);
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
	
	$sql1 = "SELECT u.institution, COUNT(gm.fecha_concluido) AS num_concluidos
			FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			WHERE u.deleted = 0 
			AND u.username != 'guest'
			AND u.institution <> 0
			AND ra.roleid = ".$s_rol."
			AND gc.courseid IS NOT NULL
			AND gm.concluido = 1
			AND gm.fecha_concluido <> ''
			AND gm.fecha_concluido BETWEEN UNIX_TIMESTAMP(DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)) AND UNIX_TIMESTAMP(CURRENT_DATE())
			".$condicion."
    		GROUP BY u.institution ORDER BY u.institution";
		
	$arr1 = get_records_sql($sql1);
	//echo "<br>".$sql1;
	//print_object($arr1);
	
	return $arr1;
	//return get_records_sql($sql);
}


/**
 * RUDY: 190913 Estadistica 2013 : Obtiene listado de educandos y su detalle 
 *
 * @deprecated - Funcion personalizada.
 * @param int $courseid : El id del curso para filtrarlos
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */

function statistic_print_list_users($estado) {
	$est3 = statistic_get_list_users($estado);
	$st_table = "";
	
	$st_table .= "<h2 class='main'>Listado de educandos y curso<br> (Del ".date("d/m/Y", strtotime(('now').'-1 month'))." al ".date("d/m/Y").")</h2>";
	$st_table .= "<table id='reportes-cursos' border='2' align='center' clas='flexible generaltable generalbox' width='90%'>";
	$st_table .= "<tr class='r0'>
			<th class='header c0' scope='col'>Entidad</th>
			<th class='header c0' scope='col'>Zona</th>
			<th class='header c0' scope='col'>Plaza comunitaria</th>
			<th class='header c0'>RFE</th>
			<th class='header c0' scope='col'>Educando</th>
			<th class='header c0' scope='col'>Email educando</th>
    		<th class='header c0' scope='col'>Asesor</th>
			<th class='header c0' scope='col'>Email asesor</th>
    		<th class='header c0' scope='col'>Curso</th>
    		<!--<th class='header c0'>Actividades</th>-->
    		<th class='header c0'>1er acceso</th>
    		<th class='header c0'>Ult. acceso</th>
    		<th class='header c0'>Evidencia</th>
    		<th class='header c0'>Fecha de evidencia</th>
            <th class='header c0'>Calificacion</th></tr>";
	
	foreach($est3 as $registro) {
		//echo $estado->cdesentfed;
				$concluido = $registro->concluido == 1 ? 'Si' : 'No';
				$fecha_concluido = $registro->fecha_concluido == null ? '' : date('d/m/Y',$registro->fecha_concluido);
		
				$st_table .= "<td>$registro->cdesie</td>";
				$st_table .= "<td>$registro->cdescz</td>";
				$st_table .= "<td>$registro->cnomplaza</td>";
				$st_table .= "<td>$registro->idnumber</td>";
				$st_table .= "<td>$registro->Educando</td>";
				$st_table .= "<td>$registro->email_educando</td>";
				$st_table .= "<td>$registro->Asesor</td>";
				$st_table .= "<td>$registro->email_asesor</td>";
				$st_table .= "<td>$registro->fullname</td>";
			//	$st_table .= "<td>$registro->actividades</td>";
				$st_table .= "<td>".date('d/m/Y',$registro->timeadded)."</td>";
				$st_table .= "<td>".date('d/m/Y',$registro->timeaccess)."</td>";
				$st_table .= "<td>$concluido</td>";
				$st_table .= "<td>$fecha_concluido</td>";
				$st_table .= "<td>$registro->calificacion</td>";
				$st_table .= "</tr>";
				
			}


	$st_table .= "</table><br>";
	
	return $st_table;
}

/**
 * RUDY:190913 Estadistica No. 3 : Obtiene listado de educandos y su detalle 
 *
 * @deprecated - Funcion personalizada.
 * @param int $courseid : El id del curso para filtrarlos
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function statistic_get_list_users($estado) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true);
	
	if($estado != 0) $condicion = " AND mu.institution = ".$estado." ";

	//RUDY: NOTA: la consulta original alternativa para el siguiente sql, que es con vistas, es esta:
/*	$sql1 = "SELECT eg.id_grupo, eg.cdesie, eg.zona, eg.idnumber, eg.yahoo, eg.Educando, ag.Asesor, eg.email, eg.fullname, ea.primera_act, ea.ultima_act, ea.actividades, eg.groupid, eg.concluido, eg.fecha_aplicacion, eg.calificacion
			FROM educandos_generales eg
			LEFT JOIN 
			asesores_generales ag ON eg.groupid = ag.groupid
			GROUP BY eg.cdesie, eg.zona, eg.idnumber, eg.Educando, eg.fullname, eg.concluido, eg.fecha_aplicacion, eg.calificacion
			ORDER BY eg.cdesie, eg.zona, eg.idnumber";
*/	

	$sql1 = "SELECT eg.id_grupo, eg.cdesie, eg.cdescz, eg.cnomplaza, eg.idnumber, eg.Educando, eg.email AS email_educando, ag.Asesor, ag.email AS email_asesor, eg.fullname, eg.timeadded, eg.timeaccess, eg.concluido, eg.fecha_concluido, eg.calificacion
			FROM (SELECT mii.cdesie, mii.icveie, miz.cdescz, mip.cnomplaza, mu.idnumber, CONCAT(mu.firstname,' ',mu.lastname,' ',mu.icq) AS Educando, mu.email, mgm.groupid, mgm.id AS id_grupo, mc.fullname, mgm.timeadded, ul.timeaccess, mgm.concluido, mgm.fecha_concluido, mgm.calificacion
FROM ((({$CFG->prefix}course mc INNER JOIN (((({$CFG->prefix}user mu INNER JOIN {$CFG->prefix}inea_instituto mii ON mu.instituto = mii.icveie) INNER JOIN {$CFG->prefix}inea_plazas mip ON mu.skype = mip.id) INNER JOIN {$CFG->prefix}groups_members mgm ON mu.id = mgm.userid) INNER JOIN {$CFG->prefix}groups_courses_groups mgcg ON mgm.groupid = mgcg.groupid) ON mc.id = mgcg.courseid) INNER JOIN {$CFG->prefix}course_modules mcm ON mc.id = mcm.course) INNER JOIN {$CFG->prefix}user_lastaccess ul ON (mgm.userid = ul.userid) AND (mgcg.courseid = ul.courseid)) INNER JOIN {$CFG->prefix}inea_zona miz ON (mu.zona = miz.icvecz) AND (mu.instituto = miz.icveie)
WHERE mu.url = '5' AND mcm.module = 10 ".$condicion."
GROUP BY mii.cdesie, mii.icveie, miz.cdescz, mip.cnomplaza, mu.idnumber, Educando, mu.email, mgm.groupid, mc.fullname, mgm.timeadded, ul.timeaccess, mgm.concluido, mgm.fecha_concluido, mgm.calificacion
ORDER BY mii.icveie, miz.cdescz, mip.cnomplaza, mu.idnumber
) eg
			LEFT JOIN 
			(SELECT CONCAT(mu.firstname,' ',mu.lastname,' ',mu.icq) AS Asesor, mu.email, mgm.groupid
FROM {$CFG->prefix}user mu INNER JOIN {$CFG->prefix}groups_members mgm ON mu.id = mgm.userid
WHERE (((mu.url)='4') AND mu.deleted=0)
GROUP BY mu.firstname, mu.lastname, mu.email, mgm.groupid) ag ON eg.groupid = ag.groupid
			GROUP BY eg.cdesie, eg.cdescz, eg.cnomplaza, eg.idnumber, Educando, eg.fullname, eg.concluido, eg.fecha_concluido, eg.calificacion
			ORDER BY eg.cdesie, eg.cdescz, eg.cnomplaza, eg.idnumber";


/*	$sql1 = "SELECT eg.id_grupo, eg.cdesie, eg.zona, eg.idnumber, eg.yahoo, eg.Educando, ag.Asesor, eg.email, eg.fullname, eg.groupid, eg.concluido, eg.fecha_aplicacion, eg.calificacion
			FROM (SELECT mii.cdesie, mii.icveie, mu.zona, mu.id, mu.idnumber, mu.yahoo, CONCAT(mu.firstname,' ',mu.lastname,' ',mu.icq) AS Educando, mu.email, mc.fullname, mgm.groupid, 			mgm.id AS id_grupo, mgm.concluido, mgm.fecha_aplicacion, mgm.calificacion, mcm.course 
			FROM ({$CFG->prefix}course mc INNER JOIN ({$CFG->prefix}groups_courses_groups mgcg INNER JOIN ({$CFG->prefix}groups_members mgm INNER JOIN ({$CFG->prefix}user mu INNER JOIN {$CFG->prefix}inea_instituto mii ON 									mu.instituto = mii.icveie) ON mgm.userid = mu.id) ON mgcg.groupid = mgm.groupid) ON mc.id = mgcg.courseid) INNER JOIN {$CFG->prefix}course_modules mcm ON (mc.id = mcm.course) 
			WHERE mcm.module = 10 AND mu.url = '5'
			".$condicion."
			GROUP BY mii.cdesie, mu.zona, mu.id, mu.idnumber, Educando, mc.fullname, mgm.concluido, mgm.fecha_aplicacion, mgm.calificacion, mgm.concluido
			ORDER BY mii.cdesie, mu.zona, mu.idnumber) eg
			LEFT JOIN 
			(SELECT {$CFG->prefix}user.id, {$CFG->prefix}user.idnumber, CONCAT(firstname,' ',lastname,' ',icq) AS Asesor, {$CFG->prefix}groups_members.groupid
			FROM {$CFG->prefix}groups_members INNER JOIN (({$CFG->prefix}role_assignments INNER JOIN {$CFG->prefix}role ON {$CFG->prefix}role_assignments.roleid = {$CFG->prefix}role.id) INNER JOIN {$CFG->prefix}user ON 	{$CFG->prefix}role_assignments.userid = {$CFG->prefix}user.id) ON {$CFG->prefix}groups_members.userid = {$CFG->prefix}user.id
			WHERE ((({$CFG->prefix}role.name)='asesor'))
			GROUP BY {$CFG->prefix}user.id, {$CFG->prefix}user.idnumber, Asesor, {$CFG->prefix}groups_members.groupid
			ORDER BY {$CFG->prefix}user.id) ag ON eg.groupid = ag.groupid
			GROUP BY eg.cdesie, eg.zona, eg.idnumber, eg.Educando, eg.fullname, eg.concluido, eg.fecha_aplicacion, eg.calificacion
			ORDER BY eg.cdesie, eg.zona, eg.idnumber";
	*/

	//echo $sql1;
	
	$arr1 = get_records_sql($sql1);
	//print_object($arr1);
	
	return $arr1;
	//return get_records_sql($sql);
}



/**
 * Ludwick:220510 Estadistica No. 3 : Obtiene a los educandos activos que han aprobado 
 * el modulo  (aquellos que han concluido el modulo y lo han aprobado con una calificacion 
 * > 5), los agrupa por entidad.
 *
 * @deprecated - Funcion personalizada.
 * @param int $courseid : El id del curso para filtrarlos
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_accredited_users_by_entity($estado) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true);
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
	
	$sql1 = "SELECT u.institution, COUNT(gm.fecha_aplicacion) AS num_acreditados
			FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			WHERE u.deleted = 0 
			AND u.username != 'guest'
			AND u.institution <> 0
			AND ra.roleid = ".$s_rol."
			AND gc.courseid IS NOT NULL
			AND gm.concluido = 1
    		AND gm.calificacion > 5
			AND gm.fecha_aplicacion <> ''
			AND gm.fecha_aplicacion BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
			".$condicion."
    		GROUP BY u.institution ORDER BY u.institution";
	
	$sql2 = "SELECT u.institution, COUNT(h.userid) AS num_acreditados
			FROM {$CFG->prefix}historial h
			INNER JOIN {$CFG->prefix}user u ON (u.id = h.userid)
			WHERE u.institution <> 0
			AND h.roleid = ".$s_rol." 
			AND h.courseid IS NOT NULL
			AND h.courseid=".$courseid."
			AND h.type = 2 
			AND h.grade > 5
			AND h.applicationdate BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
			".$condicion."
			GROUP BY u.institution ORDER BY u.institution";
	
	$arr1 = get_records_sql($sql1);
	$arr2 = get_records_sql($sql2);
	/*print_object($arr1);
	echo "<br>2:";
	print_object($arr2);*/
	
	foreach($arr2 as $key=>$val){
		$arr1[$key]->num_acreditados += $val->num_acreditados;
	}
	//print_object($arr1);
	return $arr1;
	//return get_records_sql($sql);
}








/**
 * Ludwick:220510 Estadistica No. 3 : Obtiene a los educandos activos (aquellos que tienen
 * un grupo, asesor y han accesado a alguna actividad dentro de 30 dias anteriores y que no
 * son de nueva incorporacion), los agrupa por entidad.
 *
 * @deprecated - Funcion personalizada.
 * @param int $courseid : El id del curso para filtrarlos
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_active_users_by_entity_and_course($courseid=null, $estado) {
	global $CFG;

	@set_time_limit(0);	//RUDY (020713): agregue esta linea para que no tuviera limite de tiempo la ejecuacion del script. De otro modo la pantalla se queda en blanco.
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true);
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
	
	$sql = "SELECT u.institution as id_entidad, COUNT(u.id) as num_usuarios
    		FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
			INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
    		INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
    		WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND u.institution <> 0
				AND ra.roleid = ".$s_rol."
				AND gc.courseid IS NOT NULL 
				AND gc.courseid=".$courseid."
				AND gm.concluido = 0
    			AND gm.acreditado = 0
				AND FROM_UNIXTIME(gm.timeadded) < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
				AND FROM_UNIXTIME(ul.timeaccess) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
				".$condicion." 
    			GROUP BY u.institution ORDER BY u.institution";
	//echo $sql."<br/>";
	return get_records_sql($sql);
}



/**
 * RUDY: 200713 Estadistica 2013 : Obtiene a los educandos activos que han concluido 
 * el modulo  (aquellos que han concluido el modulo y lo han aprobado con una calificacion 
 * > 5), los agrupa por entidad.
 *
 * @deprecated - Funcion personalizada.
 * @param int $courseid : El id del curso para filtrarlos
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_concluded_users_by_entity_and_course($courseid=null, $estado) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true);
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
	
	$sql1 = "SELECT u.institution, COUNT(gm.fecha_concluido) AS num_concluidos
			FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			WHERE u.deleted = 0 
			AND u.username != 'guest'
			AND u.institution <> 0
			AND ra.roleid = ".$s_rol."
			AND gc.courseid IS NOT NULL
			AND gc.courseid=".$courseid."
			AND gm.concluido = 1
			AND gm.fecha_concluido <> ''
			AND gm.fecha_concluido BETWEEN UNIX_TIMESTAMP(DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)) AND UNIX_TIMESTAMP(CURRENT_DATE())
			".$condicion."
    		GROUP BY u.institution ORDER BY u.institution";
		
	$arr1 = get_records_sql($sql1);
	//echo "<br>".$sql1;
	//print_object($arr1);
	
	return $arr1;
	//return get_records_sql($sql);
}

/**
 * RUDY:170913 Estadistica No. 3 : Obtiene a los educandos inactivos (aquellos que 
 * no han accesado a actividad dentro de 30 dias anteriores pero si dentro del periodo
 * de 30 - 60 dias anteriores), los agrupa por entidad.
 *
 * @deprecated - Funcion personalizada.
 * @param int $courseid : El id del curso para filtrarlos
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_inactive_users_by_entity($estado) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes) 
	$s_rol = get_student_role(true); // Id del rol del educando
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
	
	$sql = "SELECT u.institution, COUNT(h.userid) AS num_bajas
			FROM {$CFG->prefix}historial h
			INNER JOIN {$CFG->prefix}user u ON (u.id = h.userid)
			WHERE u.institution <> 0
			AND h.roleid = ".$s_rol."
			AND h.courseid IS NOT NULL
			AND h.timemodified BETWEEN (UNIX_TIMESTAMP()-(30 * 24 * 60 * 60)) AND UNIX_TIMESTAMP()
			".$condicion."
			GROUP BY u.institution ORDER BY u.institution";
	
	return get_records_sql($sql);
}

/**
 * RUDY:190913 Estadistica No. 3 : Obtiene a los educandos de recien incorporacion 
 * (aquellos que han accesado por primera vez dentro de 30 dias contados), los agrupa por entidad.
 *
 * @deprecated - Funcion personalizada.
 * @param int $courseid : El id del curso para filtrarlos
 * @param int $timerange : Un timestamp para definir otro rango de tiempo diferente de 30 dias
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_added_user_by_entity($timerange=0, $estado) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true); // Id del asesor
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";

	$sql = "SELECT u.institution as id_entidad, COUNT(u.id) as num_incorporaciones
    		FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
    		INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
    		WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND u.institution <> 0
				AND ra.roleid = ".$s_rol."
				AND gc.courseid IS NOT NULL 
				AND FROM_UNIXTIME(gm.timeadded) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
				AND FROM_UNIXTIME(ul.timeaccess) <= CURRENT_DATE()
				".$condicion."
    			GROUP BY u.institution ORDER BY u.institution";
				
	//echo $sql;

	return get_records_sql($sql);
}



/**
 * Ludwick:220510 Estadistica No. 3 : Obtiene a los educandos activos que han aprobado 
 * el modulo  (aquellos que han concluido el modulo y lo han aprobado con una calificacion 
 * > 5), los agrupa por entidad.
 *
 * @deprecated - Funcion personalizada.
 * @param int $courseid : El id del curso para filtrarlos
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_accredited_users_by_entity_and_course($courseid=null, $estado) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true);
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
	
	$sql1 = "SELECT u.institution, COUNT(gm.fecha_aplicacion) AS num_acreditados
			FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			WHERE u.deleted = 0 
			AND u.username != 'guest'
			AND u.institution <> 0
			AND ra.roleid = ".$s_rol."
			AND gc.courseid IS NOT NULL
			AND gc.courseid=".$courseid."
			AND gm.concluido = 1
    		AND gm.calificacion > 5
			AND gm.fecha_aplicacion <> ''
			AND gm.fecha_aplicacion BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
			".$condicion."
    		GROUP BY u.institution ORDER BY u.institution";
	
	$sql2 = "SELECT u.institution, COUNT(h.userid) AS num_acreditados
			FROM {$CFG->prefix}historial h
			INNER JOIN {$CFG->prefix}user u ON (u.id = h.userid)
			WHERE u.institution <> 0
			AND h.roleid = ".$s_rol." 
			AND h.courseid IS NOT NULL
			AND h.courseid=".$courseid."
			AND h.type = 2 
			AND h.grade > 5
			AND h.applicationdate BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
			".$condicion."
			GROUP BY u.institution ORDER BY u.institution";
	
	$arr1 = get_records_sql($sql1);
	$arr2 = get_records_sql($sql2);
	/*print_object($arr1);
	echo "<br>2:";
	print_object($arr2);*/
	
	foreach($arr2 as $key=>$val){
		$arr1[$key]->num_acreditados += $val->num_acreditados;
	}
	//print_object($arr1);
	return $arr1;
	//return get_records_sql($sql);
}

/**
 * Ludwick:220510 Estadistica No. 3 : Obtiene a los educandos inactivos (aquellos que 
 * no han accesado a actividad dentro de 30 dias anteriores pero si dentro del periodo
 * de 30 - 60 dias anteriores), los agrupa por entidad.
 *
 * @deprecated - Funcion personalizada.
 * @param int $courseid : El id del curso para filtrarlos
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_inactive_users_by_entity_and_course($courseid=null, $estado) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes) 
	$s_rol = get_student_role(true); // Id del rol del educando
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
	
	$sql = "SELECT u.institution, COUNT(h.userid) AS num_bajas
			FROM {$CFG->prefix}historial h
			INNER JOIN {$CFG->prefix}user u ON (u.id = h.userid)
			WHERE u.institution <> 0
			AND h.roleid = ".$s_rol."
			AND h.courseid IS NOT NULL
			AND h.courseid=".$courseid."
			AND h.timemodified BETWEEN (UNIX_TIMESTAMP()-(30 * 24 * 60 * 60)) AND UNIX_TIMESTAMP()
			".$condicion."
			GROUP BY u.institution ORDER BY u.institution";
	
	return get_records_sql($sql);
}

/**
 * Ludwick:210510 Estadistica No. 3 : Obtiene a los educandos de recien incorporacion 
 * (aquellos que han accesado por primera vez dentro de 30 dias contados), los agrupa por entidad.
 *
 * @deprecated - Funcion personalizada.
 * @param int $courseid : El id del curso para filtrarlos
 * @param int $timerange : Un timestamp para definir otro rango de tiempo diferente de 30 dias
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_added_user_by_entity_and_course($courseid=null, $estado, $timerange=0) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true); // Id del asesor
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";

	$sql = "SELECT u.institution as id_entidad, COUNT(u.id) as num_incorporaciones
    		FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
    		WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND u.institution <> 0
				AND ra.roleid = ".$s_rol."
				AND gc.courseid IS NOT NULL 
				AND gc.courseid = ".$courseid."
				AND FROM_UNIXTIME(gm.timeadded) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
				AND FROM_UNIXTIME(ul.timeaccess) <= CURRENT_DATE()
				".$condicion."
    			GROUP BY u.institution ORDER BY u.institution";
	//echo $sql."<br/>";
	return get_records_sql($sql);
}


/**
 * Ludwick: Estadistica No. 4 : Datos Generales por Entidad (Usuarios)
 *
 * @deprecated - Funcion personalizada.
 * @return Object $statistic4: Un arreglo con la informacion de la estadistica 4
 * 
 */
function statistic_get_general_user_report_by_entity($estado) {
	$statistic4 = array();
	//echo "Estado: ".$estado;
	$s_rol = get_student_role(true); // Rol del estudiante
	$t_rol = get_teacher_role(true); // Rol del asesor
	$et_rol = get_tutor_role(true); // Rol del tutor
	$sa_rol = get_sasa_role(true); // Rol del CUSE (SASA)
	
	//$course = new object();
	//$course->id = 13;
	//Ludwick: Buscamos las plazas activas por entidad
	//NOTA: Solo las plazas de los educandos activos
	$all_roles = "$s_rol,$t_rol,$et_rol";
	$entity = db_get_active_plazas_by_entity_and_rol($s_rol, $estado);
	if(!empty($entity)) {
		foreach($entity as $id_entity=>$plazas) {
			$statistic4[$id_entity]->plazas += $plazas->num_plazas;
		}
	}
	
	// Ludwick: Buscamos a los estudiantes que estan activos por entidad
	$entity = bd_get_active_students_by_entity("",$estado);
	if(!empty($entity)) {
		foreach($entity as $id_entity=>$users) {
			$statistic4[$id_entity]->students += $users->num_educandos;
		}
	}

	// Ludwick: Buscamos a los asesores que estan activos por entidad
	//$entity = bd_get_active_users_by_entity_and_rol($t_rol);
	$entity = bd_get_active_teachers_by_entity("",$estado);
	if(!empty($entity)) {
		foreach($entity as $id_entity=>$users) {
			$statistic4[$id_entity]->teachers += $users->num_asesores;
		}
	}

	// Ludwick: Buscamos a los tutores que estan activos por entidad
	$entity = bd_get_active_users_by_entity_and_rol($et_rol,$estado);
	if(!empty($entity)) {
		foreach($entity as $id_entity=>$users) {
			$statistic4[$id_entity]->tutors += $users->num_users;
		}
	}
	
	// Ludwick: Buscamos a los CUSES(SASA) que estan activos por entidad
	$entity = bd_get_active_cuses_by_entity($estado);
	if(!empty($entity)) {
		foreach($entity as $id_entity=>$users) {
			$statistic4[$id_entity]->cuses += $users->cuses;
		}
	}
	
	return $statistic4;
}

/**
 * Ludwick: Estadistica No. 4 : Imprimir en Web la tabla con la informacion de la estadistica
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_general_user_report_by_entity($estado) {
	//echo "Estado: ".$estado;
	$est4 = statistic_get_general_user_report_by_entity($estado);
	$est5 = statistic_get_general_plaza_report_by_entity($estado);
	$st_table = "";
	
	$st_table .= "<h2 class='main'>Plazas, Asesores y Educandos por Entidad <br> (Del ".date("d/m/Y", strtotime(('now').'-1 month'))." al ".date("d/m/Y").")</h2>";
	$st_table .= "<table id='reportes-cursos' border='2' align='center' clas='flexible generaltable generalbox' width='90%'>";
	$st_table .= "<tr class='r0'>
			<th class='header c0'>Entidad</th>
    		<th class='header c0'>Plazas (Total)</th>
    		<th class='header c0'>Plazas Activas (% del Total)</th>
            <th class='header c0'>Asesores con actividad <br> en el periodo</th>
            <th class='header c0'>Educandos</th>
         	</tr>";

	$totales = array();
	if($estado != 0){
		$estados = get_records_select("inea_entidad", "icvepais = 1 AND icveentfed = ".$estado);
	}else{ 
		$estados = get_records("inea_entidad", "icvepais", 1);
	}
	foreach($estados as $ident=>$estado) {
		$numplazas = isset($est5[$ident]->num_plazas)? $est5[$ident]->num_plazas : 0;
		$actplazas = isset($est5[$ident]->active_plazas)? $est5[$ident]->active_plazas : 0;
			$porcentaje2 = ($numplazas>0)? round(($actplazas*100)/$numplazas,2) : 0;
		$asesores = isset($est4[$ident]->teachers)? $est4[$ident]->teachers : 0;
		$educandos = isset($est4[$ident]->students)? $est4[$ident]->students : 0;

		$st_table .= "<tr class='r0'>
        			<th class='header c0' align='left'>$estado->cdesentfed</th>
        				<td align='center'>".$numplazas."</td>
        				<td align='center'>".$actplazas." (".$porcentaje2."%)"."</td>
        				<td align='center'>".$asesores."</td>
        				<td align='center'>".$educandos."</td>
				</tr>";

		$totales[0] += $numplazas;
		$totales[1] += $actplazas;
		$totales[2] += $asesores;
		$totales[3] += $educandos;
	}

	$st_table .= "<tr class='r0'>
     			<th class='header c0' >TOTALES </th>";
	foreach($totales as $total) {
		$st_table .= "<th class='header c0' >$total</th>";
	}
	$st_table .= "</tr></table><br>";
	
	return $st_table;
}

/**
 * Ludwick: Estadistica No. 4 : Imprimir en Web la tabla con la informacion de la estadistica (CSV)
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_general_user_report_by_entity_csv($xmlObject) {
	$est4 = statistic_get_general_user_report_by_entity();
	$xmlPart = "";
	
	$worksheetName = "Datos Generales por Entidad";
	$headerValues = "Entidad,Unidades Operativas(Plazas),Figuras y educandos activos";
	$xmlPart .= $xmlObject->AddWorkSheet($worksheetName,$headerValues, 's72');
	
	$DataValues = ",,Tutores,Asesores,Educandos,CUSES";
	$xmlPart .= $xmlObject->getColumnData($DataValues, 's72');
	

	$totales = array();
	$estados = get_records("inea_entidad", "icvepais", 1);
	foreach($estados as $ident=>$estado) {
		$plazas = isset($est4[$ident]->plazas)? $est4[$ident]->plazas : 0;
		$tutores = isset($est4[$ident]->tutors)? $est4[$ident]->tutors : 0;
		$asesores = isset($est4[$ident]->teachers)? $est4[$ident]->teachers : 0;
		$educandos = isset($est4[$ident]->students)? $est4[$ident]->students : 0;
		$cuses = isset($est4[$ident]->cuses)? $est4[$ident]->cuses : 0;

		$DataValues = $estado->cdesentfed.",".$plazas.",".$tutores.",".$asesores.",".$educandos.",".$cuses;
		$xmlPart .= $xmlObject->getColumnData($DataValues, 's42', true);
		
		$totales[0] += $plazas;
		$totales[1] += $tutores;
		$totales[2] += $asesores;
		$totales[3] += $educandos;
		$totales[4] += $cuses;
	}

	$DataValues = "TOTALES";
	foreach($totales as $total) {
		$DataValues .= ",".$total;
	}
	$xmlPart .= $xmlObject->getColumnData($DataValues, 's73');
	$xmlPart .= "</Table>";
	$xmlPart .= $xmlObject->GetFooter(); 
	
	return $xmlPart;
}

/**
 * Ludwick:250510 Estadistica No. 4 : Obtiene a los usuarios que han accesido al modulo
 * dentro de al menos 30 dias, los agrupa por entidad.
 *
 * @deprecated - Funcion personalizada.
 * @param int $roleid : Definir el rol de los datos de las personas solicitados
 * @param int $gender : Filtrar a las personas por sexo
 * @param int $contextid : si se requiere un contexto en especifico
 * @return Object : Un arreglo con los usuarios que cumplen con el criterio de busqueda
 * 
 */
function bd_get_active_users_by_entity_and_rol($roleid, $estado, $gender="", $contextid=0) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
		
	$select = "SELECT u.institution, COUNT(u.id) as num_users "; // Obtener todos los usuarios
	$from   = "FROM {$CFG->prefix}user u 
    INNER JOIN {$CFG->prefix}role_assignments ra on u.id=ra.userid ";
	
	if(!$contextid) {
	    $from .= "INNER JOIN {$CFG->prefix}user_lastaccess ul ON ul.userid=ra.userid  
	    INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = ul.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid) "; 
	}
	
	$where  = "WHERE ";
	if($contextid) {
		$where .= "ra.contextid IN ($contextid) AND ";
	}
	
	$where .= "u.deleted = 0
        AND u.institution <> 0
        AND u.username != 'guest' ".$condicion;
	
	if(!$contextid) {
		//$where .= " AND ul.timeaccess >= ".$timerange." ";
		$where .= " AND ul.timeaccess BETWEEN (UNIX_TIMESTAMP()-(30 * 24 * 60 * 60)) AND UNIX_TIMESTAMP() ";
	}
	
	if(!empty($gender)) {
		$where .= " AND u.yahoo = '$gender' ";
	}
	
	if($roleid > 0) {
        $where .= " AND ra.roleid IN ($roleid) ";
	}
	
	$grouping = "GROUP BY u.institution ORDER BY u.institution";
	//echo " <br><br>Consulta: ".$select.$from.$where.$grouping;
    //exit;        
	return get_records_sql($select.$from.$where.$grouping);
}

/**
 * Ludwick:250510 Estadistica No. 4 : Obtiene a los asesores activos (tienen educandos activos), 
 * los agrupa por entidad.
 *
 * @deprecated - Funcion personalizada.
 * @param int $gender : Para filtrar por sexo a los asesores
 * @return Object : Un arreglo con los asesores que cumplen con el criterio de busqueda
 * 
 */
function bd_get_active_teachers_by_entity($gender="", $estado) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true); // Id del asesor
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
		
	//Ludwick: obtiene a los asesores de los educandos(activos)
	$sql1 = "
		SELECT asesores.institution, COUNT(DISTINCT(asesores.userid)) AS num_asesores
    			
			FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
			INNER JOIN (SELECT ua.id userid, gma.groupid, gca.courseid, ua.yahoo, ua.institution
				FROM {$CFG->prefix}user ua 
				INNER JOIN {$CFG->prefix}groups_members gma ON (ua.id = gma.userid) 
				INNER JOIN {$CFG->prefix}groups_courses_groups gca ON (gma.groupid = gca.groupid) 
				INNER JOIN {$CFG->prefix}role_assignments raa ON (ua.id=raa.userid) 
				INNER JOIN {$CFG->prefix}context cxa ON (cxa.instanceid = gca.courseid AND cxa.contextlevel = 50 AND cxa.id = raa.contextid) 
				WHERE ua.deleted = 0 AND ua.username != 'guest' AND raa.roleid = ".$t_rol.") asesores ON (asesores.groupid = gm.groupid AND asesores.courseid = gc.courseid)
    		WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND ra.roleid = ".$s_rol."
				AND gc.courseid IS NOT NULL 
				AND gm.concluido = 0
    			AND gm.acreditado = 0
				AND FROM_UNIXTIME(gm.timeadded) < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
				AND FROM_UNIXTIME(ul.timeaccess) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
				".$condicion;
				
			if(!empty($gender)) {
				$sql1 .= " AND asesores.yahoo = '$gender' ";
			}
        $sql1 .= "GROUP BY asesores.institution ORDER BY asesores.institution";
	
    //Ludwick: obtiene a los asesores de los educandos(incorporaciones)
	$sql2 = "
		SELECT asesores.institution, COUNT(DISTINCT(asesores.userid)) AS num_asesores
    			
			FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
			INNER JOIN (SELECT ua.id userid, gma.groupid, gca.courseid, ua.yahoo, ua.institution
				FROM {$CFG->prefix}user ua 
				INNER JOIN {$CFG->prefix}groups_members gma ON (ua.id = gma.userid) 
				INNER JOIN {$CFG->prefix}groups_courses_groups gca ON (gma.groupid = gca.groupid) 
				INNER JOIN {$CFG->prefix}role_assignments raa ON (ua.id=raa.userid) 
				INNER JOIN {$CFG->prefix}context cxa ON (cxa.instanceid = gca.courseid AND cxa.contextlevel = 50 AND cxa.id = raa.contextid) 
				WHERE ua.deleted = 0 AND ua.username != 'guest' AND raa.roleid = ".$t_rol.") asesores ON (asesores.groupid = gm.groupid AND asesores.courseid = gc.courseid)
    		WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND ra.roleid = ".$s_rol."
				AND gc.courseid IS NOT NULL
				AND FROM_UNIXTIME(gm.timeadded) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
				AND FROM_UNIXTIME(ul.timeaccess) <= CURRENT_DATE()
				".$condicion;
				
		if(!empty($gender)) {
			$sql2 .= " AND asesores.yahoo = '$gender' ";
		}
    $sql2 .= "GROUP BY asesores.institution ORDER BY asesores.institution";
        
    $arr1 = get_records_sql($sql1);
	$arr2 = get_records_sql($sql2);
	//print_object($arr1);
	//echo "<br>2:";
	//print_object($arr2);
	
	foreach($arr2 as $key=>$val){
		$arr1[$key]->num_asesores += $val->num_asesores;
	}
	//print_object($arr1);
	return $arr1;	
    //echo "<br>".$sql;
	//return get_records_sql($sql);
}

/**
 * Ludwick:250510 Estadistica No. 4 : Obtiene a los educandos activos (tienen un grupo y asesor,
 * han accesido a alguna actividad dentro de 30 dias anteriores y no son incorporaciones), 
 * los agrupa por entidad.
 *
 * @deprecated - Funcion personalizada.
 * @param int $gender : Para filtrar por sexo a los educandos
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_active_students_by_entity($gender="",$estado) {
	global $CFG;

	@set_time_limit(0);	//RUDY (020713): agregue esta linea para que no tuviera limite de tiempo la ejecuacion del script. De otro modo la pantalla se queda en blanco.
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true); // Id del asesor
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
	
	$sql1 = "SELECT id_entidad, COUNT(nums_in_c) AS num_educandos
			FROM (
				SELECT u.id, u.institution AS id_entidad, COUNT(u.id) AS nums_in_c
    			
				FROM {$CFG->prefix}user u
				INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
				INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
				INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    			INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
				INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
    			WHERE u.deleted = 0 
					AND u.username != 'guest'
					AND ra.roleid = ".$s_rol."
					AND gc.courseid IS NOT NULL 
					AND gm.concluido = 0
    				AND gm.acreditado = 0
					AND FROM_UNIXTIME(gm.timeadded) < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
					AND FROM_UNIXTIME(ul.timeaccess) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
					".$condicion;
					
				if(!empty($gender)) {
					$sql1 .= " AND u.yahoo = '$gender' ";
				}
    $sql1 .= "GROUP BY u.id ORDER BY u.institution) enroled 
				WHERE nums_in_c >= 1  
				GROUP BY id_entidad ORDER BY id_entidad, nums_in_c";
	
	$sql2 = "SELECT id_entidad, COUNT(nums_in_c) AS num_educandos
			FROM (
				SELECT u.id, u.institution AS id_entidad, COUNT(u.id) AS nums_in_c
    			
				FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
    		INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
    		WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND ra.roleid = ".$s_rol."
				AND gc.courseid IS NOT NULL 
				AND FROM_UNIXTIME(gm.timeadded) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
				AND FROM_UNIXTIME(ul.timeaccess) <= CURRENT_DATE()
				".$condicion;
				
    		if(!empty($gender)) {
				$sql2 .= " AND u.yahoo = '$gender' ";
			}
    	$sql2 .= "GROUP BY u.id ORDER BY u.institution) incorporaciones 
			WHERE nums_in_c >= 1
			GROUP BY id_entidad ORDER BY id_entidad, nums_in_c";
	//echo "<br><br>".$sql; 
	//print_object(get_records_sql($sql1));
	//print_object(get_records_sql($sql2));
	
	$arr1 = get_records_sql($sql1);
	$arr2 = get_records_sql($sql2);
	//print_object($arr1);
	//echo "<br>2:";
	//print_object($arr2);
	
	foreach($arr2 as $key=>$val){
		$arr1[$key]->num_educandos += $val->num_educandos;
	}
	//print_object($arr1);
	return $arr1;	
      // echo "<br>".$sql;
	//return get_records_sql($sql);
}

/**
 * Ludwick:250510 Estadistica No. 4 : Obtiene los CUSES que estan activos y los agrupa por entidad
 *
 * @deprecated - Funcion personalizada.
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_active_cuses_by_entity($estado) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true); // Id del asesor
	$sa_rol = get_sasa_role(true); // Rol del CUSE (SASA)
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
	
	//Ludwick: Obtener el numero de educandos activos y agruparlos por su plaza
	$sql1 = "SELECT u.institution as entidad, COUNT(u.zona) AS cuses
			FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
			INNER JOIN (
				SELECT u.institution as id_entidad, u.zona
    			
				FROM {$CFG->prefix}user u
				INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
				INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
				INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    			INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
				INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
    			WHERE u.deleted = 0 
					AND u.username != 'guest'
					AND u.zona <> 0
					AND ra.roleid = ".$s_rol."
					AND gc.courseid IS NOT NULL 
					AND gm.concluido = 0
    				AND gm.acreditado = 0
					AND FROM_UNIXTIME(gm.timeadded) < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
					AND FROM_UNIXTIME(ul.timeaccess) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
					".$condicion;
				
    $sql1 .= "GROUP BY u.zona, u.institution ORDER BY u.institution, u.zona) cuses ON (cuses.id_entidad = u.institution AND cuses.zona = u.zona)
			WHERE u.deleted = 0 
			AND u.username != 'guest'  
			AND ra.roleid = ".$sa_rol."
			AND u.lastaccess BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE() 
			GROUP BY entidad";
    
    //Ludwick: Obtener el numero de educandos activos y agruparlos por su plaza
	$sql2 = "SELECT u.institution as entidad, COUNT(u.zona) AS cuses
			FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
			INNER JOIN (
				SELECT u.institution as id_entidad, u.zona
    			
				FROM {$CFG->prefix}user u
				INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
				INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
				INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    			INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
				INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
    			WHERE u.deleted = 0 
					AND u.username != 'guest'
					AND u.zona <> 0
					AND ra.roleid = ".$s_rol."
					AND gc.courseid IS NOT NULL 
					AND FROM_UNIXTIME(gm.timeadded) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
					AND FROM_UNIXTIME(ul.timeaccess) <= CURRENT_DATE()
					".$condicion;
				
    $sql2 .= "GROUP BY u.zona, u.institution ORDER BY u.institution, u.zona) cuses ON (cuses.id_entidad = u.institution AND cuses.zona = u.zona)
			WHERE u.deleted = 0 
			AND u.username != 'guest'  
			AND ra.roleid = ".$sa_rol."
			AND u.lastaccess BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
			GROUP BY entidad";
    
	//echo "<br><br>".$sql1."<br><br>".$sql2; 
	//print_object(get_records_sql($sql1));
	//print_object(get_records_sql($sql2));
	
	$arr1 = get_records_sql($sql1);
	$arr2 = get_records_sql($sql2);
	/*print_object($arr1);
	echo "<br>2:";
	print_object($arr2);*/
	
	foreach($arr2 as $key=>$val){
		$arr1[$key]->cuses += $val->cuses;
	}
	//print_object($arr1);
	return $arr1;
}

/**
 * Ludwick:250510 Estadistica No. 4 : Obtiene los educandos que estan activos y los agrupa por plaza
 * para saber que plazas son activas.
 *
 * @deprecated - Funcion personalizada.
 * @param int $gender : Para filtrar por sexo a los educandos
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function db_get_active_plazas_by_entity_and_rol($roleid, $estado, $gender="") {
	global $CFG;

	@set_time_limit(0);	//RUDY (020713): agregue esta linea para que no tuviera limite de tiempo la ejecuacion del script. De otro modo la pantalla se queda en blanco.

	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true); // Id del asesor
	//echo "ESTADOOOOOOOOO: ".$estado;
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
	
	//Ludwick: Obtener el numero de educandos activos y agruparlos por su plaza
	$sql1 = "SELECT id_entidad, COUNT(plazas) AS num_plazas
			FROM (
				SELECT u.institution as id_entidad, COUNT(u.skype) as plazas
    			
				FROM {$CFG->prefix}user u
				INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
				INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
				INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    			INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
    			INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
    			WHERE u.deleted = 0 
					AND u.username != 'guest'
					AND u.skype <> 0
					AND ra.roleid = ".$s_rol."
					AND gc.courseid IS NOT NULL 
					AND gm.concluido = 0
    				AND gm.acreditado = 0
					AND FROM_UNIXTIME(gm.timeadded) < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
					AND FROM_UNIXTIME(ul.timeaccess) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
					".$condicion;
				if(!empty($gender)) {
					$sql1 .= " AND u.yahoo = '$gender' ";
				}
    $sql1 .= "GROUP BY u.skype ORDER BY u.skype) enroled 
				WHERE plazas >= 1  
				GROUP BY id_entidad ORDER BY id_entidad, num_plazas";
	//echo $sql1;
    //Ludwick: Obtener el numero de incorporaciones y agruparlos por su plaza
	$sql2 = "SELECT id_entidad, COUNT(plazas) AS num_plazas
			FROM (
				SELECT u.institution as id_entidad, COUNT(u.skype) as plazas
    			
				FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
    		WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND u.skype <> 0
				AND ra.roleid = ".$s_rol."
				AND gc.courseid IS NOT NULL 
				AND FROM_UNIXTIME(gm.timeadded) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
				AND FROM_UNIXTIME(ul.timeaccess) <= CURRENT_DATE()
				".$condicion;
				if(!empty($gender)) {
					$sql2 .= " AND u.yahoo = '$gender' ";
				}
		$sql2 .= "GROUP BY u.skype ORDER BY u.skype) incorporaciones 
				WHERE plazas >= 1
				GROUP BY id_entidad ORDER BY id_entidad, num_plazas";
	//echo "<br><br>".$sql1."<br><br>".$sql2; 
	//print_object(get_records_sql($sql1));
	//print_object(get_records_sql($sql2));
	
	$arr1 = get_records_sql($sql1);
	$arr2 = get_records_sql($sql2);
	/*print_object($arr1);
	echo "<br>2:";
	print_object($arr2);*/
	
	foreach($arr2 as $key=>$val){
		$arr1[$key]->num_plazas += $val->num_plazas;
	}
	//print_object($arr1);
	return $arr1;
}

/**
 * Ludwick: Estadistica No. 5 : Datos Generales por Entidad (Plazas)
 *
 * @deprecated - Funcion personalizada.
 * @return Object $statistic5: Un arreglo con la informacion de la estadistica 5
 * 
 */
function statistic_get_general_plaza_report_by_entity($estado) {
	$statistic5 = array();
	
	$s_rol = get_student_role(true); // Rol del estudiante
	$t_rol = get_teacher_role(true); // Rol del asesor
	$et_rol = get_tutor_role(true); // Rol del tutor
	$sa_rol = get_sasa_role(true); // Rol del CUSE (SASA)
	
	$all_roles = "$s_rol,$t_rol,$et_rol";

	// Ludwick: Buscamos el numero de coordinaciones de zona por entidad
	//LAS ZONAS DE LOS EDUCANDOS ACTIVOS
	$entity = db_get_zonas_by_entity($estado);
	if(!empty($entity)) {
		foreach($entity as $id_entity=>$zona) {
			$statistic5[$id_entity]->zonas += $zona->num_zonas;
		}
	}
	
	// Ludwick: Buscamos a los CUSES(SASA) que estan activos por entidad
	$entity = bd_get_active_cuses_by_entity($estado);
	if(!empty($entity)) {
		foreach($entity as $id_entity=>$users) {
			$statistic5[$id_entity]->cuses += $users->cuses;
		}
	}
	
	//Ludwick: Buscamos las plazas activas por entidad
	$entity = db_get_active_plazas_by_entity_and_rol($s_rol,$estado);
	if(!empty($entity)) {
		foreach($entity as $id_entity=>$plazas) {
			$statistic5[$id_entity]->active_plazas += $plazas->num_plazas;
		}
	}
	
	//Ludwick: Buscamos todas las plazas existentes por entidad
	$plazas = db_get_plazas_by_entity($estado);
	if(!empty($plazas)) {
		foreach($plazas as $id_entity=>$plaza) {
			$statistic5[$id_entity]->num_plazas += $plaza->num_plazas;
		}
	}
		
	return $statistic5;
}

/**
 * Ludwick: Estadistica No. 5 : Imprimir en Web la tabla con la informacion de la estadistica
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_general_plaza_report_by_entity() {
	$est5 = statistic_get_general_plaza_report_by_entity();
	$st_table = "";
	
	$st_table .= "<h2 class='main'>Unidades operativas por Entidad <br> (Del ".date("d/m/Y", strtotime(('now').'-1 month'))." al ".date("d/m/Y").")</h2>";
	$st_table .= "<table id='reportes-cursos' border='2' align='center' clas='flexible generaltable generalbox' width='90%'>";
	$st_table .= "<tr class='r0'>
            <th class='header c0'>Entidad</th>
            <th class='header c0'>No. de Coordinaciones de Zona</th>
            <th class='header c0'>CUSES activos</th>
            <th class='header c0'>Porcentaje</th>
            <th class='header c0'>No. de Plazas comunitarias</th>
            <th class='header c0'>Plazas comunitarias activas</th>
            <th class='header c0'>Porcentaje</th>
         	</tr>";

	$totales = array();
	$estados = get_records("inea_entidad", "icvepais", 1);
	foreach($estados as $ident=>$estado) {
		$zonas = isset($est5[$ident]->zonas)? $est5[$ident]->zonas : 0;
		$cuses = isset($est5[$ident]->cuses)? $est5[$ident]->cuses : 0;
		$numplazas = isset($est5[$ident]->num_plazas)? $est5[$ident]->num_plazas : 0;
		$actplazas = isset($est5[$ident]->active_plazas)? $est5[$ident]->active_plazas : 0;
		$porcentaje1 = ($zonas>0)? round(($cuses*100)/$zonas) : 0;
		$porcentaje2 = ($numplazas>0)? round(($actplazas*100)/$numplazas) : 0;

		$st_table .= "<tr class='r0'>
        			<th class='header c0' align='left'>$estado->cdesentfed</th>
        				<th>".$zonas."</th>
       					<th>".$cuses."</th>
       					<th>".$porcentaje1."</th>
        				<th>".$numplazas."</th>
        				<th>".$actplazas."</th>
        				<th>".$porcentaje2."</th>
				</tr>";

		$totales[0] += $zonas;
		$totales[1] += $cuses;
		$totales[2] = null;
		$totales[3] += $numplazas;
		$totales[4] += $actplazas;
		$totales[5] = null;
	}

	$st_table .= "<tr class='r0'>
     			<th class='header c0' >TOTALES </th>";
	foreach($totales as $total) {
		$st_table .= "<th class='header c0' >$total</th>";
	}
	$st_table .= "</tr></table><br>";
	
	return $st_table;
}

/**
 * Ludwick: Estadistica No. 5 : Imprimir en Web la tabla con la informacion de la estadistica (CSV)
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_general_plaza_report_by_entity_csv($xmlObject) {
	$est5 = statistic_get_general_plaza_report_by_entity();
	$xmlPart = "";
	
	$worksheetName = "Reporte General por Entidad";
	$headerValues = "Entidad,No. de Coordinaciones de Zona,CUSES activos,Porcentaje,No. de Plazas comunitarias,Plazas comunitarias activas,Porcentaje";
	$xmlPart .= $xmlObject->AddWorkSheet($worksheetName,$headerValues, 's72');

	$totales = array();
	$estados = get_records("inea_entidad", "icvepais", 1);
	foreach($estados as $ident=>$estado) {
		$zonas = isset($est5[$ident]->zonas)? $est5[$ident]->zonas : 0;
		$cuses = isset($est5[$ident]->cuses)? $est5[$ident]->cuses : 0;
		$numplazas = isset($est5[$ident]->num_plazas)? $est5[$ident]->num_plazas : 0;
		$actplazas = isset($est5[$ident]->active_plazas)? $est5[$ident]->active_plazas : 0;
		$porcentaje1 = ($zonas>0)? round(($cuses*100)/$zonas) : 0;
		$porcentaje2 = ($numplazas>0)? round(($actplazas*100)/$numplazas) : 0;

		$DataValues = $estado->cdesentfed.",".$zonas.",".$cuses.",".$porcentaje1.",".$numplazas.",".$actplazas.",".$porcentaje2;
		$xmlPart .= $xmlObject->getColumnData($DataValues, 's42', true);

		$totales[0] += $zonas;
		$totales[1] += $cuses;
		$totales[2] = null;
		$totales[3] += $numplazas;
		$totales[4] += $actplazas;
		$totales[5] = null;
	}

	$DataValues = "TOTALES";
	foreach($totales as $total) {
		$DataValues .= ",".$total;
	}
	$xmlPart .= $xmlObject->getColumnData($DataValues, 's73');
	$xmlPart .= "</Table>";
	$xmlPart .= $xmlObject->GetFooter(); 
	
	return $xmlPart;
}

/**
 * Ludwick:300510 Estadistica No. 5 : Obtiene el numero de plazas por entidad
 *
 * @deprecated - Funcion personalizada.
 * @return Object : Un arreglo con el numero de plazas por entidad
 * 
 */
function db_get_plazas_by_entity($estado) {
	global $CFG;
	
	if($estado != 0) $condicion = " WHERE icveie = ".$estado." ";
		
	$select = "SELECT icveie as id_entidad,  COUNT(DISTINCT(idplaza)) as num_plazas ";
	$from   = "FROM {$CFG->prefix}inea_plazas ".$condicion;
	$grouping = " GROUP BY icveie ORDER BY icveie";
	//echo " <br><br>Consulta: ".$select.$from.$where;
    //exit;        
	return get_records_sql($select.$from.$grouping);
}

/**
 * Ludwick:250510 Estadistica No. 5 : Obtiene el numero de zonas por entidad
 *
 * @deprecated - Funcion personalizada.
 * @return Object : Un arreglo con el numero de zonas por entidad
 * 
 */
function db_get_zonas_by_entity($estado) {
	global $CFG;
	
	if($estado != 0) $condicion = " AND icveie = ".$estado." ";
		
	$select = "SELECT icveie AS id_entidad, COUNT(DISTINCT(id)) AS num_zonas ";
	$from   = "FROM {$CFG->prefix}inea_zona ";
	$where  = "WHERE icveie > 0 AND icveie < 33 ".$condicion;
	$grouping = " GROUP BY icveie ORDER BY icveie";
	//echo " <br><br>Consulta: ".$select.$from.$where.$grouping;
    //exit;        
	return get_records_sql($select.$from.$where.$grouping);
}

/**
 * RUDY:100812 Obtiene la zona dada la plaza
 *
 * @deprecated - Funcion personalizada.
 * @return Object : Un valor con el id de la zona
 * 
 */
function db_get_zona_by_plaza($id_plaza=null) {
    $objeto_zona = get_record('inea_plazas','idplaza',$id_plaza);
	return $objeto_zona->icvecz;
}

/**
 * RUDY:100214 Obtiene el municipio dada la plaza
 *
 * @deprecated - Funcion personalizada.
 * @return Object : Un valor con el id del municipio
 * 
 */
function db_get_municipio_by_plaza($id_plaza=null) {
    $objeto_municipio = get_record('inea_plazas','idplaza',$id_plaza);
	return $objeto_municipio->icvemunicipio;
}


/**
 * Ludwick:250510 Estadistica No. 5 : Obtiene un arreglo con el contexto de cada curso
 *
 * @deprecated - Funcion personalizada.
 * @return Object : Una arreglo con los contextos
 * 
 */
function get_context_courses() {
	$contexts = array();
	
	$courses = get_courses("all", "c.id");
	
	foreach($courses as $course) {
		if(!$context = get_context_instance(CONTEXT_COURSE, $course->id)) {
			continue;
		}
		
		$contexts[$course->id] = $context;
	}
	
	return $contexts;
}

/**
 * Ludwick:300510 Estadistica No. 5 : Obtiene una cadena con una lista separada por comas de
 * todos los contextos de los cursos
 *
 * @deprecated - Funcion personalizada.
 * @return String : Una cadena con los contextos
 * 
 */
function get_context_list() {
	
	$contexts = get_context_courses();
	$list_of_context = "";
	
	foreach($contexts as $context) {
		$list_of_context .= "$context->id,";	
	}
	$list_of_context = substr($list_of_context, 0, -1);
	
	return $list_of_context;
}

/**
 * Ludwick: Estadistica No. 6 : Personas Activas por Sexo, Rol y Entidad
 *
 * @deprecated - Funcion personalizada.
 * @return Object $statistic6: Un arreglo con la informacion de la estadistica 6
 * 
 */
function statistic_get_active_users_by_gender($estado) {
	$statistic6 = array();
	
	$s_rol = get_student_role(true); // Rol del estudiante
	$t_rol = get_teacher_role(true); // Rol del asesor
	$et_rol = get_tutor_role(true); // Rol del tutor
	
	// Ludwick: Buscamos a las estudiantes que estan activas por entidad
	$st_women = bd_get_active_students_by_entity("Femenino",$estado);
	if(!empty($st_women)) {
		foreach($st_women as $id_entity=>$women) {
			$statistic6[$id_entity]->student_women += $women->num_educandos;
		}
	}

	// Ludwick: Buscamos a los asesores que estan activos por entidad
	//$entity = bd_get_active_users_by_entity_and_rol($t_rol);
	$st_men = bd_get_active_students_by_entity("Masculino",$estado);
	if(!empty($st_men)) {
		foreach($st_men as $id_entity=>$men) {
			$statistic6[$id_entity]->student_men += $men->num_educandos;
		}
	}
	
	// Ludwick: Buscamos a las asesoras que estan activas por entidad
	$st_women = bd_get_active_teachers_by_entity("Femenino",$estado);
	if(!empty($st_women)) {
		foreach($st_women as $id_entity=>$women) {
			$statistic6[$id_entity]->teacher_women += $women->num_asesores;
		}
	}

	// Ludwick: Buscamos a los asesores que estan activos por entidad
	$st_men = bd_get_active_teachers_by_entity("Masculino",$estado);
	if(!empty($st_men)) {
		foreach($st_men as $id_entity=>$men) {
			$statistic6[$id_entity]->teacher_men += $men->num_asesores;
		}
	}

	// Ludwick: Buscamos a las tutoras que estan activas por entidad
	$tu_women =  bd_get_active_users_by_entity_and_rol($et_rol, "Femenino",$estado);
	if(!empty($tu_women)) {
		foreach($tu_women as $id_entity=>$women) {
			$statistic6[$id_entity]->tutor_women += $women->num_users;
		}
	}

	// Ludwick: Buscamos a los tutores que estan activos por entidad
	$tu_men =  bd_get_active_users_by_entity_and_rol($et_rol, "Masculino",$estado);
	if(!empty($tu_men)) {
		foreach($tu_men as $id_entity=>$men) {
			$statistic6[$id_entity]->tutor_men += $men->num_users;
		}
	}
		
	return $statistic6;
}

/**
 * Ludwick: Estadistica No. 6 : Imprimir en Web la tabla con la informacion de la estadistica
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_active_users_by_gender($estado) {
	$est6 = statistic_get_active_users_by_gender($estado);
	$st_table = "";
	
	$st_table .= "<h2 class='main'>Educandos y Asesores Activos por Entidad y Sexo <br> (Del ".date("d/m/Y", strtotime(('now').'-1 month'))." al ".date("d/m/Y").")</h2>";
	$st_table .= "<table id='reportes-cursos' border='2' align='center' clas='flexible generaltable generalbox' width='90%'>";
	$st_table .= "<tr>
			<th class='header c0'>&nbsp;</th>
    		<th class='header c0' colspan='4' scope='col'>EDUCANDOS</th>
    		<th class='header c0' colspan='4' scope='col'>ASESORES</th>
  			</tr>
			<tr class='r0'>
            <th class='header c0'>Entidad</th>
            <th class='header c0'>Mujeres</th>
            <th class='header c0'>Hombres</th>
            <th class='header c0'>Total</th>
            <th class='header c0'>Porcentaje</th>
            <th class='header c0'>Mujeres</th>
            <th class='header c0'>Hombres</th>
            <th class='header c0'>Total</th>
            <th class='header c0'>Porcentaje</th>
         	</tr>";

	$totales = array();
	$estados = get_records("inea_entidad", "icvepais", 1);
	
	if($estado != 0){
		$estados = get_records_select("inea_entidad", "icvepais = 1 AND icveentfed = ".$estado);
	}else{ 
		$estados = get_records("inea_entidad", "icvepais", 1);
	}

	// Ludwick: Obtener los totales de usuarios por rol
	foreach($estados as $ident=>$estado) {
		$totaleducandos += $est6[$ident]->student_women + $est6[$ident]->student_men;
		$totalasesores += $est6[$ident]->teacher_women + $est6[$ident]->teacher_men;
		$totaltutores += $est6[$ident]->tutor_women + $est6[$ident]->tutor_men;
	}

	foreach($estados as $ident=>$estado) {
		$educandosm = isset($est6[$ident]->student_women)? $est6[$ident]->student_women:0;
		$educandosh = isset($est6[$ident]->student_men)? $est6[$ident]->student_men : 0;
		$educandost = $educandosm + $educandosh;
		$eporcentaje = ($totaleducandos>0)? (($educandost*100)/$totaleducandos) : 0;

		$asesoras = isset($est6[$ident]->teacher_women)? $est6[$ident]->teacher_women : 0;
		$asesores = isset($est6[$ident]->teacher_men)? $est6[$ident]->teacher_men : 0;
		$asesorest = $asesoras + $asesores;
		$aporcentaje = ($totalasesores>0)? (($asesorest*100)/$totalasesores) : 0;

		$tutoras = isset($est6[$ident]->tutor_women)? $est6[$ident]->tutor_women : 0;
		$tutores = isset($est6[$ident]->tutor_men)? $est6[$ident]->tutor_men : 0;
		$tutorest = $tutoras + $tutores;
		$tporcentaje = ($totaltutores>0)? (($tutorest*100)/$totaltutores) : 0;

		$st_table .= "<tr class='r0'>
        			<th class='header c0' align='left'>$estado->cdesentfed</th>
        				<td align='center'>".$educandosm."</td>
       					<td align='center'>".$educandosh."</td>
       					<td align='center'>".$educandost."</td>
        				<td align='center'>".round($eporcentaje)."</td>
        				<td align='center'>".$asesoras."</td>
        				<td align='center'>".$asesores."</td>
        				<td align='center'>".$asesorest."</td>
        				<td align='center'>".round($aporcentaje)."</td>
				</tr>";

		$totales[0] += $educandosm;
		$totales[1] += $educandosh;
		$totales[2] += $educandost;
		$totales[3] += $eporcentaje;
		$totales[4] += $asesoras;
		$totales[5] += $asesores;
		$totales[6] += $asesorest;
		$totales[7] += $aporcentaje;
	}

	$st_table .= "<tr class='r0'>
     			<th class='header c0' >TOTALES </th>";
	foreach($totales as $total) {
		$st_table .= "<th class='header c0' >$total</th>";
	}
	$st_table .= "</tr></table><br>";
	
	return $st_table;
}

/**
 * Ludwick: Estadistica No. 6 : Imprimir en Web la tabla con la informacion de la estadistica (CSV)
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_active_users_by_gender_csv($xmlObject) {
	$est6 = statistic_get_active_users_by_gender();
	$xmlPart = "";
	
	$worksheetName = "Fig. Activas por Entidad y Sexo";
	$headerValues = ",EDUCANDOS,,,,ASESORES,,,,TUTORES";
	$xmlPart .= $xmlObject->AddWorkSheet($worksheetName,$headerValues, 's72');
	
	$DataValues = "Entidad,Mujeres,Hombres,Total,Porcentaje,Mujeres,Hombres,Total,Porcentaje,Mujeres,Hombres,Total,Porcentaje";
	$xmlPart .= $xmlObject->getColumnData($DataValues, 's72');

	$totales = array();
	$estados = get_records("inea_entidad", "icvepais", 1);

	// Ludwick: Obtener los totales de usuarios por rol
	foreach($estados as $ident=>$estado) {
		$totaleducandos += $est6[$ident]->student_women + $est6[$ident]->student_men;
		$totalasesores += $est6[$ident]->teacher_women + $est6[$ident]->teacher_men;
		$totaltutores += $est6[$ident]->tutor_women + $est6[$ident]->tutor_men;
	}

	foreach($estados as $ident=>$estado) {
		$educandosm = isset($est6[$ident]->student_women)? $est6[$ident]->student_women:0;
		$educandosh = isset($est6[$ident]->student_men)? $est6[$ident]->student_men : 0;
		$educandost = $educandosm + $educandosh;
		$eporcentaje = ($totaleducandos>0)? (($educandost*100)/$totaleducandos) : 0;

		$asesoras = isset($est6[$ident]->teacher_women)? $est6[$ident]->teacher_women : 0;
		$asesores = isset($est6[$ident]->teacher_men)? $est6[$ident]->teacher_men : 0;
		$asesorest = $asesoras + $asesores;
		$aporcentaje = ($totalasesores>0)? (($asesorest*100)/$totalasesores) : 0;

		$tutoras = isset($est6[$ident]->tutor_women)? $est6[$ident]->tutor_women : 0;
		$tutores = isset($est6[$ident]->tutor_men)? $est6[$ident]->tutor_men : 0;
		$tutorest = $tutoras + $tutores;
		$tporcentaje = ($totaltutores>0)? (($tutorest*100)/$totaltutores) : 0;
		
		$DataValues = $estado->cdesentfed.",".$educandosm.",".$educandosh.",".$educandost.",".round($eporcentaje).",".$asesoras.",".$asesores.",".$asesorest.",".round($aporcentaje).",".$tutoras.",".$tutores.",".$tutorest.",".round($tporcentaje);
		$xmlPart .= $xmlObject->getColumnData($DataValues, 's42', true);
		
		$totales[0] += $educandosm;
		$totales[1] += $educandosh;
		$totales[2] += $educandost;
		$totales[3] += $eporcentaje;
		$totales[4] += $asesoras;
		$totales[5] += $asesores;
		$totales[6] += $asesorest;
		$totales[7] += $aporcentaje;
		$totales[8] += $tutoras;
		$totales[9] += $tutores;
		$totales[10] += $tutorest;
		$totales[11] += $tporcentaje;
	}

	$DataValues = "TOTALES";
	foreach($totales as $total) {
		$DataValues .= ",".$total;
	}
	$xmlPart .= $xmlObject->getColumnData($DataValues, 's73');
	$xmlPart .= "</Table>";
	$xmlPart .= $xmlObject->GetFooter(); 
	
	return $xmlPart;
}

/**
 * Ludwick: Estadistica No. 7 : Personas Activas por Edad y Rol
 *
 * @deprecated - Funcion personalizada.
 * @return Object $statistic7: Un arreglo con la informacion de la estadistica 7
 * 
 */
function statistic_get_active_users_by_age($estado) {
	$statistic7 = array();
	
	$s_rol = get_student_role(true); // Rol del estudiante
	$t_rol = get_teacher_role(true); // Rol del asesor
	//$et_rol = get_tutor_role(true); // Rol del tutor
	
	
	// Ludwick: Buscamos a las estudiantes que estan activas por rango de edad
	$st_women = bd_get_active_students_by_age_range("Femenino",$estado);
	if(!empty($st_women)) {
		foreach($st_women as $age_range=>$women) {
			$statistic7[$age_range]->student_women += $women->num_educandos;
		}
	}

	// Ludwick: Buscamos a los estudiantes que estan activos por rango de edad
	$st_men =  bd_get_active_students_by_age_range("Masculino",$estado);
	if(!empty($st_men)) {
		foreach($st_men as $age_range=>$men) {
			$statistic7[$age_range]->student_men += $men->num_educandos;
		}
	}
	
	// Ludwick: Buscamos a las asesoras que estan activas por rango de edad
	$te_women = bd_get_active_teachers_by_age_range("Femenino",$estado);
	if(!empty($te_women)) {
		foreach($te_women as $age_range=>$women) {
			$statistic7[$age_range]->teacher_women += $women->num_asesores;
		}
	}

	// Ludwick: Buscamos a los asesores que estan activos por rango de edad
	$te_men =  bd_get_active_teachers_by_age_range("Masculino",$estado);
	if(!empty($te_men)) {
		foreach($te_men as $age_range=>$men) {
			$statistic7[$age_range]->teacher_men += $men->num_asesores;
		}
	}
		
	return $statistic7;
} 

/**
 * Ludwick: Estadistica No. 7 : Imprimir en Web la tabla con la informacion de la estadistica
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_active_users_by_age($estado) {
	$est7 = statistic_get_active_users_by_age($estado);
	$st_table = "";
	
	$st_table .= "<h2 class='main'> Educandos y Asesores por Edad <br> (Del ".date("d/m/Y", strtotime(('now').'-1 month'))." al ".date("d/m/Y").")</h2>";
	$st_table .= "<table id='reportes-cursos' border='2' align='center' clas='flexible generaltable generalbox' width='90%'>";
	$st_table .= "<tr>
			<th class='header c0'>&nbsp;</th>
    		<th class='header c0' colspan='4' scope='col'>EDUCANDOS</th>
    		<th class='header c0' colspan='4' scope='col'>ASESORES</th>
  			</tr>
			<tr class='r0'>
            <th class='header c0'>A帽os</th>
            <th class='header c0'>Mujeres</th>
            <th class='header c0'>Hombres</th>
            <th class='header c0'>Total</th>
            <th class='header c0'>Porcentaje</th>
            <th class='header c0'>Mujeres</th>
            <th class='header c0'>Hombres</th>
            <th class='header c0'>Total</th>
            <th class='header c0'>Porcentaje</th>
         	</tr>";

	$totales = array();

	// Ludwick: Obtener los totales de usuarios por rol
	foreach($est7 as $seccion) {
		$totaleducandos += $seccion->student_women + $seccion->student_men;
		$totalasesores += $seccion->teacher_women + $seccion->teacher_men;
	}

	foreach($est7 as $identificador=>$seccion) {
		$educandosm = isset($seccion->student_women)? $seccion->student_women : 0;
		$educandosh = isset($seccion->student_men)? $seccion->student_men : 0;
		$educandost = $educandosm + $educandosh;
		$eporcentaje = ($totaleducandos>0)? (($educandost*100)/$totaleducandos) : 0;

		$asesoras = isset($seccion->teacher_women)? $seccion->teacher_women : 0;
		$asesores = isset($seccion->teacher_men)? $seccion->teacher_men : 0;
		$asesorest = $asesoras + $asesores;
		$aporcentaje = ($totalasesores>0)? (($asesorest*100)/$totalasesores) : 0;

		$st_table .= "<tr class='r0'>
        			<th class='header c0' align='left'>$identificador</th>
        				<td align='center'>".$educandosm."</td>
       					<td align='center'>".$educandosh."</td>
       					<td align='center'>".$educandost."</td>
        				<td align='center'>".round($eporcentaje)."</td>
        				<td align='center'>".$asesoras."</td>
        				<td align='center'>".$asesores."</td>
        				<td align='center'>".$asesorest."</td>
        				<td align='center'>".round($aporcentaje)."</td>
				</tr>";

		$totales[0] += $educandosm;
		$totales[1] += $educandosh;
		$totales[2] += $educandost;
		$totales[3] += $eporcentaje;
		$totales[4] += $asesoras;
		$totales[5] += $asesores;
		$totales[6] += $asesorest;
		$totales[7] += $aporcentaje;
	}

	$st_table .= "<tr class='r0'>
     			<th class='header c0' >TOTALES </th>";
	foreach($totales as $total) {
		$st_table .= "<th class='header c0' >$total</th>";
	}
	$st_table .= "</tr></table><br>";
	
	return $st_table;
}

/**
 * Ludwick: Estadistica No. 7 : Imprimir en Web la tabla con la informacion de la estadistica (CSV)
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_active_users_by_age_csv($xmlObject) {
	$est7 = statistic_get_active_users_by_age();
	$xmlPart = "";
	
	$worksheetName = "Fig. Activas por Entidad y Edad";
	$headerValues = ",EDUCANDOS,,,,ASESORES,,,";
	$xmlPart .= $xmlObject->AddWorkSheet($worksheetName,$headerValues, 's72');
	
	$DataValues = "A帽os,Mujeres,Hombres,Total,Porcentaje,Mujeres,Hombres,Total,Porcentaje";
	$xmlPart .= $xmlObject->getColumnData($DataValues, 's72');

	$totales = array();

	// Ludwick: Obtener los totales de usuarios por rol
	foreach($est7 as $seccion) {
		$totaleducandos += $seccion->student_women + $seccion->student_men;
		$totalasesores += $seccion->teacher_women + $seccion->teacher_men;
	}

	foreach($est7 as $identificador=>$seccion) {
		$educandosm = isset($seccion->student_women)? $seccion->student_women : 0;
		$educandosh = isset($seccion->student_men)? $seccion->student_men : 0;
		$educandost = $educandosm + $educandosh;
		$eporcentaje = ($totaleducandos>0)? (($educandost*100)/$totaleducandos) : 0;

		$asesoras = isset($seccion->teacher_women)? $seccion->teacher_women : 0;
		$asesores = isset($seccion->teacher_men)? $seccion->teacher_men : 0;
		$asesorest = $asesoras + $asesores;
		$aporcentaje = ($totalasesores>0)? (($asesorest*100)/$totalasesores) : 0;

		$DataValues = $identificador.",".$educandosm.",".$educandosh.",".$educandost.",".round($eporcentaje).",".$asesoras.",".$asesores.",".$asesorest.",".round($aporcentaje);
		$xmlPart .= $xmlObject->getColumnData($DataValues, 's42', true);
		
		$totales[0] += $educandosm;
		$totales[1] += $educandosh;
		$totales[2] += $educandost;
		$totales[3] += $eporcentaje;
		$totales[4] += $asesoras;
		$totales[5] += $asesores;
		$totales[6] += $asesorest;
		$totales[7] += $aporcentaje;
	}

	$DataValues = "TOTALES";
	foreach($totales as $total) {
		$DataValues .= ",".$total;
	}
	$xmlPart .= $xmlObject->getColumnData($DataValues, 's73');
	$xmlPart .= "</Table>";
	$xmlPart .= $xmlObject->GetFooter(); 
	
	return $xmlPart;
}

/**
 * Ludwick: Estadistica No. 7 : Obtiene a educandos activos e incorporaciones y los agrupa
 * de acuerdo a un rango de edad.
 *
 * @deprecated - Funcion personalizada.
 * @param String $gender : Para filtrar por sexo a los educandos
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_active_students_by_age_range($gender="",$estado) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true); // Id del asesor
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
	
	$sql_head = "SELECT grupo_edad, COUNT(*) AS num_educandos
		FROM (
			SELECT id,
			( CASE WHEN edad<=14 THEN '14 y menos'
				WHEN (edad>=15 AND edad<=19)  THEN '15 a 19'
				WHEN (edad>=20 AND edad<=24)  THEN '20 a 24'
				WHEN (edad>=25 AND edad<=29)  THEN '25 a 29'
				WHEN (edad>=30 AND edad<=34)  THEN '30 a 34'
				WHEN (edad>=35 AND edad<=39)  THEN '35 a 39'
				WHEN (edad>=40 AND edad<=44)  THEN '40 a 44'
				WHEN (edad>=45 AND edad<=49)  THEN '45 a 49'
				WHEN (edad>=50 AND edad<=54)  THEN '50 a 54'
				WHEN (edad>=55 AND edad<=59)  THEN '55 a 59'
				WHEN edad>=60 THEN '60 y mas' END ) AS 'grupo_edad' 
			FROM ( ";
	
	$sql_tail =	" ) edades
		WHERE edad IS NOT NULL ) grupo_de_edades
		GROUP BY grupo_edad";
			
	$sql1 = $sql_head."SELECT u.id as id, (YEAR(CURRENT_DATE) - YEAR(str_to_date(u.aim, '%d/%m/%Y'))) - (RIGHT(CURRENT_DATE,5) < RIGHT(str_to_date(u.aim, '%d/%m/%Y'),5)) AS edad
    			
				FROM {$CFG->prefix}user u
				INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
				INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
				INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    			INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
    			INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
    			WHERE u.deleted = 0 
					AND u.username != 'guest'
					AND ra.roleid = ".$s_rol."
					AND gc.courseid IS NOT NULL 
					AND gm.concluido = 0
    				AND gm.acreditado = 0
					AND FROM_UNIXTIME(gm.timeadded) < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
					AND FROM_UNIXTIME(ul.timeaccess) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
					".$condicion."
					 ";
			if(!empty($gender)) {
				$sql1 .= " AND u.yahoo = '$gender' ";
			}
    $sql1 .= "GROUP BY u.id ORDER BY u.institution ".$sql_tail;
	
	$sql2 = $sql_head."SELECT u.id as id, (YEAR(CURRENT_DATE) - YEAR(str_to_date(u.aim, '%d/%m/%Y'))) - (RIGHT(CURRENT_DATE,5) < RIGHT(str_to_date(u.aim, '%d/%m/%Y'),5)) AS edad
    			FROM {$CFG->prefix}user u
				INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
				INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
				INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    			INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
				INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
    			WHERE u.deleted = 0 
					AND u.username != 'guest'
					AND ra.roleid = ".$s_rol."
					AND gc.courseid IS NOT NULL
					AND FROM_UNIXTIME(gm.timeadded) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
					AND FROM_UNIXTIME(ul.timeaccess) <= CURRENT_DATE()
					".$condicion."
					 ";
    		if(!empty($gender)) {
				$sql2 .= " AND u.yahoo = '$gender' ";
			}
    	$sql2 .= "GROUP BY u.id ORDER BY u.institution ".$sql_tail;
	//echo "<br><br>".$sql1."<br><br>".$sql2; 
	//print_object(get_records_sql($sql1));
	//print_object(get_records_sql($sql2));
	
	$arr1 = get_records_sql($sql1);
	$arr2 = get_records_sql($sql2);
	/*print_object($arr1);
	echo "<br>2:";
	print_object($arr2);*/
	
	foreach($arr2 as $key=>$val){
		$arr1[$key]->num_educandos += $val->num_educandos;
	}

	return $arr1;   
	//return get_records_sql($sql);
}

/**
 * Ludwick: Estadistica No. 7 : Obtiene a asesores con educandos activos e incorporaciones
 * y los agrupa de acuerdo a un rango de edad.
 *
 * @deprecated - Funcion personalizada.
 * @param String $gender : Para filtrar por sexo a los asesores
 * @return Object : Un arreglo con los asesores que cumplen con el criterio de busqueda
 * 
 */
function bd_get_active_teachers_by_age_range($gender="",$estado) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true); // Id del asesor
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";

	$sql_head = "SELECT grupo_edad, COUNT(*) AS num_asesores
		FROM (
			SELECT id,
			( CASE WHEN edad<=14 THEN '14 y menos'
				WHEN (edad>=15 AND edad<=19)  THEN '15 a 19'
				WHEN (edad>=20 AND edad<=24)  THEN '20 a 24'
				WHEN (edad>=25 AND edad<=29)  THEN '25 a 29'
				WHEN (edad>=30 AND edad<=34)  THEN '30 a 34'
				WHEN (edad>=35 AND edad<=39)  THEN '35 a 39'
				WHEN (edad>=40 AND edad<=44)  THEN '40 a 44'
				WHEN (edad>=45 AND edad<=49)  THEN '45 a 49'
				WHEN (edad>=50 AND edad<=54)  THEN '50 a 54'
				WHEN (edad>=55 AND edad<=59)  THEN '55 a 59'
				WHEN edad>=60 THEN '60 y mas' END ) AS 'grupo_edad' 
			FROM ( ";
	
	$sql_tail =	" ) edades
		WHERE edad IS NOT NULL ) grupo_de_edades
		GROUP BY grupo_edad";
			
	$sql1 = $sql_head."SELECT asesores.userid AS id, (YEAR(CURRENT_DATE) - YEAR(str_to_date(asesores.aim, '%d/%m/%Y'))) - (RIGHT(CURRENT_DATE,5) < RIGHT(str_to_date(asesores.aim, '%d/%m/%Y'),5)) AS edad
    		FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
			INNER JOIN (SELECT ua.id userid, gma.groupid, gca.courseid, ua.yahoo, ua.institution, ua.aim
				FROM {$CFG->prefix}user ua 
				INNER JOIN {$CFG->prefix}groups_members gma ON (ua.id = gma.userid) 
				INNER JOIN {$CFG->prefix}groups_courses_groups gca ON (gma.groupid = gca.groupid) 
				INNER JOIN {$CFG->prefix}role_assignments raa ON (ua.id=raa.userid) 
				INNER JOIN {$CFG->prefix}context cxa ON (cxa.instanceid = gca.courseid AND cxa.contextlevel = 50 AND cxa.id = raa.contextid) 
				WHERE ua.deleted = 0 AND ua.username != 'guest' AND raa.roleid = ".$t_rol.") asesores ON (asesores.groupid = gm.groupid AND asesores.courseid = gc.courseid)
    		WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND ra.roleid = ".$s_rol."
				AND gc.courseid IS NOT NULL 
				AND gm.concluido = 0
    			AND gm.acreditado = 0
				AND FROM_UNIXTIME(gm.timeadded) < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
				AND FROM_UNIXTIME(ul.timeaccess) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
				".$condicion."
    			 ";
		if(!empty($gender)) {
			$sql1 .= " AND asesores.yahoo = '$gender' ";
		}
    $sql1 .= "GROUP BY asesores.userid ORDER BY asesores.userid ".$sql_tail;
    
    $sql2 = $sql_head."
		SELECT asesores.userid AS id, (YEAR(CURRENT_DATE) - YEAR(str_to_date(asesores.aim, '%d/%m/%Y'))) - (RIGHT(CURRENT_DATE,5) < RIGHT(str_to_date(asesores.aim, '%d/%m/%Y'),5)) AS edad
    		FROM {$CFG->prefix}user u
			INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
			INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
			INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    		INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
			INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
			INNER JOIN (SELECT ua.id userid, gma.groupid, gca.courseid, ua.yahoo, ua.institution, ua.aim
				FROM {$CFG->prefix}user ua 
				INNER JOIN {$CFG->prefix}groups_members gma ON (ua.id = gma.userid) 
				INNER JOIN {$CFG->prefix}groups_courses_groups gca ON (gma.groupid = gca.groupid) 
				INNER JOIN {$CFG->prefix}role_assignments raa ON (ua.id=raa.userid) 
				INNER JOIN {$CFG->prefix}context cxa ON (cxa.instanceid = gca.courseid AND cxa.contextlevel = 50 AND cxa.id = raa.contextid) 
				WHERE ua.deleted = 0 AND ua.username != 'guest' AND raa.roleid = ".$t_rol.") asesores ON (asesores.groupid = gm.groupid AND asesores.courseid = gc.courseid)
    		WHERE u.deleted = 0 
				AND u.username != 'guest'
				AND ra.roleid = ".$s_rol."
				AND gc.courseid IS NOT NULL
				AND FROM_UNIXTIME(gm.timeadded) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
				AND FROM_UNIXTIME(ul.timeaccess) <= CURRENT_DATE()
				".$condicion."
				 ";
		if(!empty($gender)) {
			$sql2 .= " AND asesores.yahoo = '$gender' ";
		}
    $sql2 .= "GROUP BY asesores.userid ORDER BY asesores.userid ".$sql_tail;
  	//echo "<br><br>".$sql1."<br><br>".$sql2; 
	//print_object(get_records_sql($sql1));
	//print_object(get_records_sql($sql2));
	
	$arr1 = get_records_sql($sql1);
	$arr2 = get_records_sql($sql2);
	/*print_object($arr1);
	echo "<br>2:";
	print_object($arr2);*/
	
	foreach($arr2 as $key=>$val){
		$arr1[$key]->num_asesores += $val->num_asesores;
	}

	return $arr1;   
	//return get_records_sql($sql);
}

/**
 * Ludwick: Estadistica No. 8 : Personas Activas por Ocupacion y Rol
 *
 * @deprecated - Funcion personalizada.
 * @return Object $statistic8: Un arreglo con la informacion de la estadistica 8
 * 
 */
function statistic_get_active_users_by_occupation($estado) {
	$statistic8 = array();
	
	$s_rol = get_student_role(true); // Rol del estudiante
	$t_rol = get_teacher_role(true); // Rol del asesor
	//$et_rol = get_tutor_role(true); // Rol del tutor
	
	
	// Ludwick: Buscamos a las estudiantes que estan activas por ocupacion
	$st_women = bd_get_active_students_by_occupation("Femenino",$estado);
	if(!empty($st_women)) {
		foreach($st_women as $occupation=>$women) {
			$statistic8[$occupation]->student_women += $women->num_educandos;
		}
	}

	// Ludwick: Buscamos a los estudiantes que estan activos por ocupacion
	$st_men =  bd_get_active_students_by_occupation("Masculino",$estado);
	if(!empty($st_men)) {
		foreach($st_men as $occupation=>$men) {
			$statistic8[$occupation]->student_men += $men->num_educandos;
		}
	}
	
	// Ludwick: Buscamos a las asesoras que estan activas por ocupacion
	$te_women = bd_get_active_teachers_by_occupation("Femenino",$estado);
	if(!empty($te_women)) {
		foreach($te_women as $occupation=>$women) {
			$statistic8[$occupation]->teacher_women += $women->num_asesores;
		}
	}

	// Ludwick: Buscamos a los asesores que estan activos por ocupacion
	$te_men =  bd_get_active_teachers_by_occupation("Masculino",$estado);
	if(!empty($te_men)) {
		foreach($te_men as $occupation=>$men) {
			$statistic8[$occupation]->teacher_men += $men->num_asesores;
		}
	}
		
	return $statistic8;
}

/**
 * Ludwick: Estadistica No. 8 : Imprimir en Web la tabla con la informacion de la estadistica
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_active_users_by_occupation($estado) {
	$est8 = statistic_get_active_users_by_occupation($estado);
	$st_table = "";
	
	$st_table .= "<h2 class='main'> Educandos y Asesores por Ocupaci贸n <br> (Del ".date("d/m/Y", strtotime(('now').'-1 month'))." al ".date("d/m/Y").")</h2>";
	$st_table .= "<table id='reportes-cursos' border='2' align='center' clas='flexible generaltable generalbox' width='90%'>";
	$st_table .= "<tr>
			<th class='header c0'>&nbsp;</th>
    		<th class='header c0' colspan='4' scope='col'>EDUCANDOS</th>
    		<th class='header c0' colspan='4' scope='col'>ASESORES</th>
  			</tr>
			<tr class='r0'>
            <th class='header c0'>Ocupaci贸n</th>
            <th class='header c0'>Mujeres</th>
            <th class='header c0'>Hombres</th>
            <th class='header c0'>Total</th>
            <th class='header c0'>Porcentaje</th>
            <th class='header c0'>Mujeres</th>
            <th class='header c0'>Hombres</th>
            <th class='header c0'>Total</th>
            <th class='header c0'>Porcentaje</th>
         	</tr>";

	$totales = array();
	$ocupaciones = get_records("inea_ocupaciones", "", "", "cdesocupacion");

	// Ludwick: Obtener los totales de usuarios por rol
	foreach($ocupaciones as $id=>$ocupacion) {
		$totaleducandos += $est8[$id]->student_women + $est8[$id]->student_men;
		$totalasesores += $est8[$id]->teacher_women + $est8[$id]->teacher_men;
	}

	foreach($ocupaciones as $id=>$ocupacion) {
		$educandosm = isset($est8[$id]->student_women)? $est8[$id]->student_women : 0;
		$educandosh = isset($est8[$id]->student_men)? $est8[$id]->student_men : 0;
		$educandost = $educandosm + $educandosh;
		$eporcentaje = ($totaleducandos>0)? (($educandost*100)/$totaleducandos) : 0;

		$asesoras = isset($est8[$id]->teacher_women)? $est8[$id]->teacher_women : 0;
		$asesores = isset($est8[$id]->teacher_men)? $est8[$id]->teacher_men : 0;
		$asesorest = $asesoras + $asesores;
		$aporcentaje = ($totalasesores>0)? (($asesorest*100)/$totalasesores) : 0;

		$st_table .= "<tr class='r0'>
        			<th class='header c0' align='left'>$ocupacion->cdesocupacion</th>
        				<td align='center'>".$educandosm."</td>
       					<td align='center'>".$educandosh."</td>
       					<td align='center'>".$educandost."</td>
        				<td align='center'>".round($eporcentaje)."</td>
        				<td align='center'>".$asesoras."</td>
        				<td align='center'>".$asesores."</td>
        				<td align='center'>".$asesorest."</td>
        				<td align='center'>".round($aporcentaje)."</td>
				</tr>";

		$totales[0] += $educandosm;
		$totales[1] += $educandosh;
		$totales[2] += $educandost;
		$totales[3] += $eporcentaje;
		$totales[4] += $asesoras;
		$totales[5] += $asesores;
		$totales[6] += $asesorest;
		$totales[7] += $aporcentaje;
	}

	$st_table .= "<tr class='r0'>
     			<th class='header c0' >TOTALES </th>";
	foreach($totales as $total) {
		$st_table .= "<th class='header c0' >$total</th>";
	}
	$st_table .= "</tr></table><br>";
	
	return $st_table;
}

/**
 * Ludwick: Estadistica No. 8 : Imprimir en Web la tabla con la informacion de la estadistica (CSV)
 *
 * @deprecated - Funcion personalizada.
 * @return String $st_table: Una cadena con los tags HTML para imprimir la estadistica
 * 
 */
function statistic_print_active_users_by_occupation_csv($xmlObject) {
	$est8 = statistic_get_active_users_by_occupation();
	$xmlPart = "";
	
	$worksheetName = "F. Activas por Ent. y Ocupacion";
	$headerValues = ",EDUCANDOS,,,,ASESORES,,,";
	$xmlPart .= $xmlObject->AddWorkSheet($worksheetName,$headerValues, 's72');
	
	$DataValues = "Ocupaci贸n,Mujeres,Hombres,Total,Porcentaje,Mujeres,Hombres,Total,Porcentaje";
	$xmlPart .= $xmlObject->getColumnData($DataValues, 's73');

	$totales = array();
	$ocupaciones = get_records("inea_ocupaciones", "", "", "cdesocupacion");

	// Ludwick: Obtener los totales de usuarios por rol
	foreach($ocupaciones as $id=>$ocupacion) {
		$totaleducandos += $est8[$id]->student_women + $est8[$id]->student_men;
		$totalasesores += $est8[$id]->teacher_women + $est8[$id]->teacher_men;
	}

	foreach($ocupaciones as $id=>$ocupacion) {
		$educandosm = isset($est8[$id]->student_women)? $est8[$id]->student_women : 0;
		$educandosh = isset($est8[$id]->student_men)? $est8[$id]->student_men : 0;
		$educandost = $educandosm + $educandosh;
		$eporcentaje = ($totaleducandos>0)? (($educandost*100)/$totaleducandos) : 0;

		$asesoras = isset($est8[$id]->teacher_women)? $est8[$id]->teacher_women : 0;
		$asesores = isset($est8[$id]->teacher_men)? $est8[$id]->teacher_men : 0;
		$asesorest = $asesoras + $asesores;
		$aporcentaje = ($totalasesores>0)? (($asesorest*100)/$totalasesores) : 0;

		$DataValues = $ocupacion->cdesocupacion.",".$educandosm.",".$educandosh.",".$educandost.",".round($eporcentaje).",".$asesoras.",".$asesores.",".$asesorest.",".round($aporcentaje);
		$xmlPart .= $xmlObject->getColumnData($DataValues, 's42', true);
		
		$totales[0] += $educandosm;
		$totales[1] += $educandosh;
		$totales[2] += $educandost;
		$totales[3] += $eporcentaje;
		$totales[4] += $asesoras;
		$totales[5] += $asesores;
		$totales[6] += $asesorest;
		$totales[7] += $aporcentaje;
	}

	$DataValues = "TOTALES";
	foreach($totales as $total) {
		$DataValues .= ",".$total;
	}
	$xmlPart .= $xmlObject->getColumnData($DataValues, 's73');
	$xmlPart .= "</Table>";
	$xmlPart .= $xmlObject->GetFooter(); 
	
	return $xmlPart;
}

/**
 * Ludwick: Estadistica No. 8 : Obtiene a educandos activos e incorporaciones y los agrupa
 * de acuerdo a su ocupacion.
 *
 * @deprecated - Funcion personalizada.
 * @param String $gender : Para filtrar por ocupacion a los educandos
 * @return Object : Un arreglo con los educandos que cumplen con el criterio de busqueda
 * 
 */
function bd_get_active_students_by_occupation($gender="",$estado) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true); // Id del asesor
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
	
	$sql1 = "SELECT id_ocupacion, COUNT(nums_in_c) AS num_educandos
			FROM (
				SELECT u.id, u.msn as id_ocupacion, count(u.id) as nums_in_c
    			
				FROM {$CFG->prefix}user u
				INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
				INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
				INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    			INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
				INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
    			WHERE u.deleted = 0 
					AND u.username != 'guest'
					AND u.msn <> 0
					AND ra.roleid = ".$s_rol."
					AND gc.courseid IS NOT NULL 
					AND gm.concluido = 0
    				AND gm.acreditado = 0
					AND FROM_UNIXTIME(gm.timeadded) < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
					AND FROM_UNIXTIME(ul.timeaccess) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
					".$condicion."
					 ";
				
				if(!empty($gender)) {
					$sql1 .= " AND u.yahoo = '$gender' ";
				}
    			$sql1 .= "GROUP BY u.id ORDER BY u.institution) enroled 
				WHERE nums_in_c >= 1  
				GROUP BY id_ocupacion ORDER BY id_ocupacion";
	
	$sql2 = "SELECT id_ocupacion, COUNT(nums_in_c) AS num_educandos
			FROM (
				SELECT u.id, u.msn as id_ocupacion, count(u.id) as nums_in_c
    			
				FROM {$CFG->prefix}user u
				INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
				INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
				INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    			INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
				INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
    			WHERE u.deleted = 0 
					AND u.username != 'guest'
					AND u.msn <> 0
					AND ra.roleid = ".$s_rol."
					AND gc.courseid IS NOT NULL 
					AND FROM_UNIXTIME(gm.timeadded) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
					AND FROM_UNIXTIME(ul.timeaccess) <= CURRENT_DATE()
					".$condicion."
					 ";
    			
				if(!empty($gender)) {
					$sql2 .= " AND u.yahoo = '$gender' ";
				}
    			$sql2 .= "GROUP BY u.id ORDER BY u.institution) incorporaciones 
		WHERE nums_in_c >= 1
		GROUP BY id_ocupacion ORDER BY id_ocupacion";
	//echo "<br><br>".$sql1."<br><br>".$sql2; 
	//print_object(get_records_sql($sql1));
	//print_object(get_records_sql($sql2));
	
	$arr1 = get_records_sql($sql1);
	$arr2 = get_records_sql($sql2);
	//print_object($arr1);
	//echo "<br>2:";
	//print_object($arr2);
	
	foreach($arr2 as $key=>$val){
		$arr1[$key]->num_educandos += $val->num_educandos;
	}
	//print_object($arr1);
	return $arr1;
	
	
	return get_records_sql($select.$from.$where.$grouping);
}

// Asesores por ocupacion
/**
 * Ludwick: Estadistica No. 8 : Obtiene a asesores que tengan educandos activos e incorporaciones
 *  y los agrupa de acuerdo a su ocupacion.
 *
 * @deprecated - Funcion personalizada.
 * @param String $gender : Para filtrar por sexo a los asesores
 * @return Object : Un arreglo con los asesores que cumplen con el criterio de busqueda
 * 
 */
function bd_get_active_teachers_by_occupation($gender="",$estado) {
	global $CFG;
	
	$thisdate = time(); // El dia actual
	if(empty($timerange)) { //Ludwick:130510 -> Rango de tiempo definido
		$timerange = time()-(30 * 24 * 60 * 60); // Hace 30 dias con respecto de la fecha actual (Un mes)
	}
	$s_rol = get_student_role(true); // Id del rol del educando
	$t_rol = get_teacher_role(true); // Id del asesor
	
	if($estado != 0) $condicion = " AND u.institution = ".$estado." ";
	
	$sql1 = "
		SELECT id_ocupacion, COUNT(DISTINCT(asesores.userid)) as num_asesores
    			
		FROM {$CFG->prefix}user u
		INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
		INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
		INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    	INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
		INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
		INNER JOIN (SELECT ua.id userid, gma.groupid, gca.courseid, ua.yahoo, ua.institution, ua.msn as id_ocupacion
			FROM {$CFG->prefix}user ua 
			INNER JOIN {$CFG->prefix}groups_members gma ON (ua.id = gma.userid) 
			INNER JOIN {$CFG->prefix}groups_courses_groups gca ON (gma.groupid = gca.groupid) 
			INNER JOIN {$CFG->prefix}role_assignments raa ON (ua.id=raa.userid) 
			INNER JOIN {$CFG->prefix}context cxa ON (cxa.instanceid = gca.courseid AND cxa.contextlevel = 50 AND cxa.id = raa.contextid) 
			WHERE ua.deleted = 0 AND ua.username != 'guest' AND raa.roleid = ".$t_rol.") asesores ON (asesores.groupid = gm.groupid AND asesores.courseid = gc.courseid)
    	WHERE u.deleted = 0 
			AND u.username != 'guest'
			AND ra.roleid = ".$s_rol."
			AND gc.courseid IS NOT NULL 
			AND gm.concluido = 0
    		AND gm.acreditado = 0
			AND FROM_UNIXTIME(gm.timeadded) < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
			AND FROM_UNIXTIME(ul.timeaccess) BETWEEN DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND CURRENT_DATE()
			".$condicion."
			 ";
	
	if(!empty($gender)) {
		$sql1 .= " AND asesores.yahoo = '$gender' ";
	}
    $sql1 .= "GROUP BY id_ocupacion ORDER BY id_ocupacion";
	
    $sql2 = "
		SELECT id_ocupacion, COUNT(DISTINCT(asesores.userid)) as num_asesores
		
    	FROM {$CFG->prefix}user u
		INNER JOIN {$CFG->prefix}groups_members gm ON (u.id = gm.userid)
		INNER JOIN {$CFG->prefix}groups_courses_groups gc ON (gm.groupid = gc.groupid)
		INNER JOIN {$CFG->prefix}role_assignments ra ON (u.id=ra.userid)
    	INNER JOIN {$CFG->prefix}context cx ON (cx.instanceid = gc.courseid  AND cx.contextlevel = 50 AND cx.id = ra.contextid)
		INNER JOIN {$CFG->prefix}user_lastaccess ul ON (ul.courseid=gc.courseid AND ul.userid=u.id)
		INNER JOIN (SELECT ua.id userid, gma.groupid, gca.courseid, ua.yahoo, ua.institution, ua.msn as id_ocupacion
			FROM {$CFG->prefix}user ua 
			INNER JOIN {$CFG->prefix}groups_members gma ON (ua.id = gma.userid) 
			INNER JOIN {$CFG->prefix}groups_courses_groups gca ON (gma.groupid = gca.groupid) 
			INNER JOIN {$CFG->prefix}role_assignments raa ON (ua.id=raa.userid) 
			INNER JOIN {$CFG->prefix}context cxa ON (cxa.instanceid = gca.courseid AND cxa.contextlevel = 50 AND cxa.id = raa.contextid) 
			WHERE ua.deleted = 0 AND ua.username != 'guest' AND raa.roleid = ".$t_rol.") asesores ON (asesores.groupid = gm.groupid AND asesores.courseid = gc.courseid)
    	WHERE u.deleted = 0 
			AND u.username != 'guest'
			AND ra.roleid = ".$s_rol."
			AND gc.courseid IS NOT NULL 
			AND FROM_UNIXTIME(gm.timeadded) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
			AND FROM_UNIXTIME(ul.timeaccess) <= CURRENT_DATE()
			".$condicion."
			 ";
	
    if(!empty($gender)) {
		$sql2 .= " AND asesores.yahoo = '$gender' ";
	}
	$sql2 .= "GROUP BY id_ocupacion ORDER BY id_ocupacion ";
  	//echo "<br><br>".$sql1."<br><br>".$sql2; 
	//print_object(get_records_sql($sql1));
	//print_object(get_records_sql($sql2));
	
	$arr1 = get_records_sql($sql1);
	$arr2 = get_records_sql($sql2);
	/*print_object($arr1);
	echo "<br>2:";
	print_object($arr2);*/
	
	foreach($arr2 as $key=>$val){
		$arr1[$key]->num_asesores += $val->num_asesores;
	}

	return $arr1;   
	//return get_records_sql($sql);
}

/**
 * Ludwick: Imprimir en Web la tabla con los registros del historial de educandos
 *
 * @deprecated - Funcion personalizada.
 * @param String $filters : Una cadena con los campos a filtrar el historial
 * @return String $st_table: Una cadena con los tags HTML para imprimir la tabla
 * 
 */
function historial_print_regs($filters) {
	//$filters = "courseid=8&icveentfed=29&roleid=5";
	$filtersdb = str_replace("&", " AND ", $filters);
	//echo "<br>Filtro de BD: ".$filtersdb;
	$historial = get_records_select('historial', $filtersdb, 'id,courseid,roleid,clventidad,type', 'id,userid,courseid,roleid,firstaccess,lastaccess,firstlogin,clventidad,clvplaza,clvmunicipio,clvzona,teacherid,groupid,type');
	//print_object($historial);
	//exit;
	$st_table = "";
	
	$st_table .= "<h2 class='main'>Historial de Depuracion <br> (Del ".date("d/m/Y", strtotime(('now').'-1 month'))." al ".date("d/m/Y").")</h2>";
	$st_table .= "<table id='reportes-cursos' border='2' align='center' clas='flexible generaltable generalbox' width='90%'>";
	$st_table .= "
			<tr class='r0'>
            <th class='header c0'>ID</th>
            <th class='header c0'>Usuario</th>
            <th class='header c0'>Curso</th>
            <th class='header c0'>Rol</th>
            <th class='header c0'>Primer acceso</th>
            <th class='header c0'>Ultimo acceso</th>
            <th class='header c0'>Primer Login</th>
            <th class='header c0'>Entidad</th>
            <th class='header c0'>Municipio</th>
            <th class='header c0'>Zona</th>
            <th class='header c0'>Plaza</th>
            <th class='header c0'>Asesor</th>
            <th class='header c0'>Grupo</th>
            <th class='header c0'>Tipo eliminacion</th>
         	</tr>";
	
	$tipo_eliminacion = array("1"=>"Inactividad", "2"=>"Aprobacion", "3"=>"Inactividad asesor");
	foreach($historial as $registro) {
		$username = fullname(get_record('user', 'id', $registro->userid, '', '', '', '', 'id,firstname,lastname'));
		$coursename = get_record('course', 'id', $registro->courseid, '', '', '', '', 'id,shortname,fullname');
		$rolename = get_record('role', 'id', $registro->roleid, '', '', '', '', 'id,name,shortname');
		$estatename = get_record('inea_entidad', 'icvepais', '1', 'icveentfed', $registro->clventidad,'', '', 'id,cdesentfed');
		$teachername = (isset($registro->teacherid))? fullname(get_record('user', 'id', $registro->teacherid, '', '', '', '', 'id,firstname,lastname')) : "";
		$groupname = get_record('groups', 'id', $registro->groupid, '', '', '', '', 'id,name');
		$municipioname = get_record('inea_municipios', 'icvepais', '1', 'icveentfed', $registro->clventidad, 'icvemunicipio', $registro->clvmunicipio, 'id,cdesmunicipio');
		$zonename = get_record('inea_zona', 'icveie', $registro->clventidad, 'icvecz', $registro->clvzona, '', '', 'id,cdescz');
		
		//print_object($zonename);
		$st_table .= "<tr class='r0'>
        				<td>".$registro->id."</td>
       					<td>".$username."</td>
       					<td>".(isset($coursename->shortname)? $coursename->shortname : "")."</td>
        				<td>".(isset($rolename->name)? $rolename->name : "")."</td>
        				<td>".date('d/m/y', $registro->firstaccess)."</td>
        				<td>".date('d/m/y', $registro->lastaccess)."</td>
        				<td>".date('d/m/y', $registro->firstlogin)."</td>
        				<td>".(isset($estatename->cdesentfed)? $estatename->cdesentfed : "")."</td>
        				<td>".(isset($municipioname->cdesmunicipio)? $municipioname->cdesmunicipio : "")."</td>
        				<td>".(isset($zonename->cdescz)? $zonename->cdescz : "")."</td>
        				<td>".$registro->clvplaza."</td>
        				<td>".$teachername."</td>
        				<td>".(isset($groupname->name)? $groupname->name : "")."</td>
        				<td>".$tipo_eliminacion[$registro->type]."</td>
				</tr>";

	}

	/*$st_table .= "<tr class='r0'>
     			<th class='header c0' >TOTALES </th>";
	foreach($totales as $total) {
		$st_table .= "<th class='header c0' >$total</th>";
	}*/
	$st_table .= "</table><br>";
	
	return $st_table;
}
?>
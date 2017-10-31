<?php //Flavio Lozano obtener mensajes

require('../../config.php');

require_login();

$courseid = required_param('cid', PARAM_INT);
$groupids = groups_get_groups_not_in_any_grouping($courseid);
$tutorid = 0;
if ($groupids) {
	$t = get_groups($courseid,$USER->id);
	$group = array_shift($t);
	$userids = groups_get_members($group->id);
	foreach($userids as $userid) {
		if (isteacher($courseid, $userid)) {
			$tutorid = $userid;
			break;
		}
	}
} else { //No hya grupos listamos todos los alumnos
	$context = get_context_instance(CONTEXT_COURSE, $courseid);
	$roleid_asesor = get_field('role','id','shortname',"teacher");
	$tutors =  get_records_sql("SELECT * FROM {$CFG->prefix}role_assignments WHERE contextid = $context->id
                                  AND roleid =".$roleid_asesor);
	//print_object($tutors);
	if (count($tutors) > 0) {
		$tutor = array_shift($tutors);
		$tutorid = $tutor->userid;
		
	}
}
if (empty($tutorid)) {
//	error("No tienes asignado ningÃºn tutor en este curso");
	if (! $course = get_record("course", "id", $courseid)) {  // Objeto del curso
		error("Course is misconfigured");
	}
	
	$asesor_rol->id =4;
	$moderador_rol->id = 11;
	$asesores_delCurso = get_users_from_role_on_context($asesor_rol,get_context_instance(CONTEXT_COURSE, $course->id)); // Obtener todos los usuarios asesores en el curso
	// Ludwick:
	$moderadores_delCurso = get_users_from_role_on_context($moderador_rol,get_context_instance(CONTEXT_COURSE, $course->id)); // Obtener todos los usuarios asesores en el curso
	$primer_moderador = array_shift($moderadores_delCurso); // Obtener el primer moderador
	$moderador = get_record("user", "id", $primer_moderador->userid,'', '', '', '', 'id, firstname, lastname, icq');
	//print_object($moderador);
	$moderador_delCurso[$moderador->id] = "$moderador->firstname $moderador->lastname $moderador->icq"; 
	//print_object($moderador_delCurso);
	//end 
	
	
	foreach ($asesores_delCurso as $asesor){ // Filtrar unicamente estado y id_user de cada asesor
		$los_asesores_objeto = get_user_from_id($asesor->userid);
		$los_asesores[$asesor->userid]['id_estado'] = $los_asesores_objeto->institution;
		$los_asesores[$asesor->userid]['id_usuario'] = $los_asesores_objeto->id;
	}
	
	$grupos_en_elcurso = groups_get_groups($course->id); // Obtiene todos los grupos en el curso;
	//print_object($grupos_en_elcurso);
	function desplegar($course,$inea,$strnewmodules,$titulo_carpeta,$strnewmodule,$cm,$encabezado,$table,$id_estudiante,$eseditor=0){
			print_header("$course->shortname: $inea->name", "$course->fullname",
			"$navigation <a href=index.php?id=$course->id>$strnewmodules</a> -> ".$titulo_carpeta,
			"", "", true,
			navmenu($course, $cm));
			echo "<br />";
		
			echo "<center>".$encabezado."</center>";
		
			echo (!empty($id_estudiante) || $eseditor==1)? '<form method="post">' : '';
			//echo '<form method="post">';
		
			print_table($table);
			//echo '</form>';
			echo (!empty($id_estudiante) || $eseditor==1)? '</form>': '';
	}	
	// Encabezado ->
	//echo "Some........";	
		$origen = get_user_estado($USER->id); // Datos de origen [id estado/id pais/municipio]
		$varmyestado=$origen->institution; // ID del estado
		$varmycountry=$origen->country; // ID del país
		
		$encabezado = "<h4>Asesores</h4>";
		
		$si_no = array ("No","Si"); // Para imprimir el combo de aprobado

		foreach($grupos_en_elcurso as $id_de_cadaGrupo){ // Para filtrar a los asesores que pertenecen al mismo estado que el tutor actual

			$ids_usuarios[$id_de_cadaGrupo] = groups_get_members($id_de_cadaGrupo); // Obtiene la lista de miembros en un grupo
//			print_object($ids_usuarios);
			foreach ($ids_usuarios[$id_de_cadaGrupo] as $posicion=>$valor){
				$los_miembros_objeto = get_user_from_id($valor);
				$los_miembros[$valor]['id_estado'] = $los_miembros_objeto->institution;
				$los_miembros[$valor]['id_usuario'] = $los_miembros_objeto->id;
				$los_miembros[$valor]['username'] = $los_miembros_objeto->username;
				$plaza_objeto = get_plaza($los_miembros_objeto->skype);
				$los_miembros[$valor]['nombre'] = $los_miembros_objeto->firstname.' '.$los_miembros_objeto->lastname.' '.$los_miembros_objeto->icq.' | '.$plaza_objeto->cnomplaza;
			}
			
			$intersecccion = array_intersect_key($los_miembros, $los_asesores); // Hace una filtro de todos miembros de los grupos en el curso con los que son asesores
			if($intersecccion[$los_miembros[$valor]['id_usuario']]['id_estado'] == $varmyestado){ // Nuevo filtro para verificar que pertenecen al mismo estado al que pertenece el tutor
				$asesores_demiestado_enelcurso_nombre[$los_miembros[$valor]['id_usuario']] = $intersecccion[$los_miembros[$valor]['id_usuario']]['nombre'];
				$asesores_demiestado_enelcurso_estado[$los_miembros[$valor]['id_usuario']] = $intersecccion[$los_miembros[$valor]['id_usuario']]['id_estado'];
				$asesores_demiestado_enelcurso_id_usuario[$los_miembros[$valor]['id_usuario']] = $intersecccion[$los_miembros[$valor]['id_usuario']]['id_usuario'];
			}
		}
		
		$table = new object();
		$table->head  = array ("Nombre de Asesores");
		$asesores_demiestado_enelcurso_nombre = 0;
		if(empty($asesores_demiestado_enelcurso_nombre)){ //Ludwick 
			$contactar = $moderador_delCurso;
			$table->head  = array ("Nombre del Moderador");
			$encabezado = "<h4>Moderador</h4>";
			$mensaje_select = 'Seleccionar un moderador...';
		}else{
			$contactar = $asesores_demiestado_enelcurso_nombre;
			$mensaje_select = 'Seleccionar un asesor...';
		}
		$table->align = array ("center");
		//print_object($asesores_demiestado_enelcurso_nombre);
		$varmyestado;
		$varmycountry;

		$menu='<form name="selecasesores_" action="'.$CFG->wwwroot.'/message/discussion.php" method="get">'.choose_from_menu($contactar,'id','',$mensaje_select,'form.submit()','',true,false).'</form>';
		$table->data[] = array($menu);
		desplegar('','','','','','',$encabezado,$table);
		
		//<- Termina encabezado
					
} else {
	redirect("$CFG->wwwroot/message/discussion.php?id=$tutorid",'',0);
}
?>
<?php
require_once('../config.php');
require_once($CFG->libdir . '/weblib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->libdir . '/grouplib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/mod/inea/inealib.php');
//require_once($CFG->dirroot.'/lib/datalib.php');  //print_object(get_categories());

header("Content-Type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8" ?>';

$id_usuario	= optional_param('id_usuario', 0, PARAM_INT);
$id_rol		= optional_param('id_rol', 0, PARAM_INT);
$id_curso	= optional_param('id_curso', 0, PARAM_INT);
$id_grupo	= optional_param('id_grupo', 0, PARAM_INT);
$id_categoria	= optional_param('id_categoria', 0, PARAM_INT);
$accion		= optional_param('accion', '', PARAM_TEXT);

$PAGE->set_url('/login/signup.php', array('id_rol'=>$id_rol));
$PAGE->set_context(context_system::instance());

//$_POST['accion'] = isset($_POST['accion']) ? $_POST['accion'] : $_GET['accion'];
//$_POST['id_categoria'] = $_GET['id_categoria'];
//$_POST['id_usuario'] = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : $_GET['id_usuario'];
//$_POST['id_rol'] = isset($_POST['id_rol']) ? $_POST['id_rol'] : $_GET['id_rol'];
//$_POST['id_curso'] = isset($_POST['id_curso']) ? $_POST['id_curso'] : $_GET['id_curso'];

if(!empty($accion)) {
    //$accion = $_POST['accion'];
	$usuarios_matriculados_antes = $DB->count_records('groups_members'); //LOG
	
	$clave_modelo = 0;
	if($modelo = inea_get_modelo_from_user($id_usuario)) {
		$clave_modelo = $modelo->icvemodesume;
	}
	//print_object($modelo);
	//echo "Modelo: ".$modelo->icvemodesume;
			  
    switch($accion) {
        case 'getcursos':
            if($id_categoria && $id_usuario) {
			// cualquier echo dentro de este if truena el select siguiente
              echo '<select>';
              echo getCursos($id_categoria, $clave_modelo, $id_usuario);
              echo '</select>';
            } 
		break;
        case 'registrarcursos':
            if($id_usuario && $id_rol && $id_curso) {
                if($id_rol == ASESOR){
                    registrarCurso($id_usuario, ASESOR, $id_curso);
                } else {
                    registrarCurso($id_usuario, EDUCANDO, $id_curso, $id_grupo);
                }
                echo "<usuario><id>$id_usuario</id><id_rol>$id_rol</id_rol></usuario>";
            }
            // Guardar en el LOG
            $usuarios_matriculados = $DB->count_records('groups_members');
            $porsentaje_afectado = $usuarios_matriculados*100/$usuarios_matriculados_antes;
            $post = print_r($_POST, true);
            $mensaje = "Se registra en un curso con $usuarios_matriculados_antes y $usuarios_matriculados registros antes y despues de su ejecucion, eliminando un ".($porsentaje_afectado-100)."% (".($usuarios_matriculados_antes-$usuarios_matriculados)."). Con datos en post $post   ";
            $event = \INEA\event\REGISTRO::create(array(
					'objectid' => $mensaje,
					'context' => context_course::instance($id_curso)
			));
			$event->trigger();
			//add_to_log($_POST['id_curso'],'','registrarcursos/LOGIN/JMP','',$mensaje,$_POST['id_curso']);
            if($porsentaje_afectado < 98){
            	enviarMail($mensaje);
            }
            // Fin del LOG
        break;
        case 'getcursosregistrados':
            if($id_usuario && $id_rol) {
                echo getCursosRegistrados($id_usuario, $id_rol);
            }
        break;
        case 'desmatricular':
            if($id_usuario && $id_rol && $id_curso) {
                if($id_curso > 1 && $id_usuario >0 && ($id_rol == ASESOR || $id_rol == EDUCANDO)) {
                    desmatricular($id_usuario, $id_rol, $id_curso);
				}
				
				$nombre_curso = '';
                if($curso = $DB->get_record('course', array('id'=>$id_curso))) {
					$nombre_curso = $curso->fullname;
				}
                echo "<usuario><id>$id_usuario</id><id_rol>$id_rol</id_rol><curso>$nombre_curso</curso></usuario>";
            }
            // Guardar en el LOG
            $usuarios_matriculados = $DB->count_records('groups_members');
            $porsentaje_afectado = $usuarios_matriculados*100/$usuarios_matriculados_antes;
            $post = print_r($_POST,true);
            $mensaje = "Se registra en un curso con $usuarios_matriculados_antes y $usuarios_matriculados registros antes y despues de su ejecucion, eliminando un ".($porsentaje_afectado-100)."% (".($usuarios_matriculados_antes-$usuarios_matriculados)."). Con datos en post $post   ";
            //add_to_log($_POST['id_curso'],'','desmatricular/LOGIN/JMP','',$mensaje,$_POST['id_curso']);
            $event = \INEA\event\REGISTRO::create(array(
					'objectid' => $mensaje,
					'context' => context_course::instance($id_curso)
			));
			$event->trigger();
			if($porsentaje_afectado < 98){
            	enviarMail($mensaje);
            }
           // Fin del LOG
        break;
        case 'getasesores':
            if($id_usuario && $id_curso) {
				$user = $DB->get_record('user', array('id'=>$id_usuario));
                $asesores = asesores($id_curso, $user->skype);
				// Imprimir los asesores
                echo "<select name='asesor'>" ;
                if(!empty($asesores)) {
					foreach($asesores as $asesor) {
						echo "<option value='".$asesor->idgrupo."'>".$asesor->firstname."  ".$asesor->lastname."</option>";
                    }
				}
                echo "<option value='-2'>Estudiar sin asesor</option>";
                echo "</select>";
            }
        break;
    }
}

/**
 * INEA - Obtiene los cursos del usuario
 * @param int $id_categoria
 * @param int $clave_modelo
 * @param int $id_usuario
 * @return Object cursos
 */
function getCursos($id_categoria, $clave_modelo, $id_usuario) {	//RUDY: se añadió parametro modelo. 180414
    global $CFG, $DB;
	
    $depth = -1;
    //$courses = inea_get_courses($id_categoria, 'c.sortorder ASC', 'c.id, c.sortorder, c.visible, c.fullname, c.shortname, c.password, c.summary, c.guest, c.cost, c.currency', $clave_modelo);	
    $courses = inea_get_courses($id_categoria, 'c.sortorder ASC', 'c.id, c.sortorder, c.visible, c.fullname, c.shortname, c.summary', $clave_modelo);	
	//print_object($courses);
    $mycourses = enrol_get_users_courses($id_usuario); // Los cursos en los que esta inscrito el usuario (MACUCO).
	//echo '<option value="'.$CFG->wwwroot.'/course/category.php?id='.$category->id.'" selected="selected">'. format_string($category->name).'</option>';
	$cursos = "";

	if ($courses && !(isset($CFG->max_category_depth) && ($depth >= $CFG->max_category_depth-1))) {
		foreach ($courses as $course) {
			if($course->visible && (!isset($mycourses[$course->id]))) { //&& ($mycourses->id != $course->id)
				$cursos .= '<option  value="'.$course->id.'">'.format_string($course->fullname).'</option>';
			}
		}
	}
	return $cursos;
}

/**
 * INEA - Enrola un usuario a un curso
 * @param int $id_usuario
 * @param int $id_rol
 * @param int $id_curso
 * @param int $id_grupo
 * @return 
 */
function registrarCurso($id_usuario, $id_rol, $id_curso, $id_grupo=null){
    global $DB;
    global $CFG;

	if(empty($id_curso) && empty($id_usuario)) {
		return false;
	}
	
    //$context = get_context_instance(CONTEXT_COURSE, $id_curso);
	$coursecontext = context_course::instance($id_curso);

    if($id_rol == EDUCANDO) { //Si es educando, obtengo el id del grupo del asesor al que se esta inscribiendo.
        if($id_grupo==-2){
            $asesor_estado = inea_get_user_entidad($id_usuario); // Obtenemos el estado del asesor
            $asesor_estado = $asesor_estado->skype;
            $name_g = "Grupo_".$id_curso."_p".$asesor_estado;
            $id_grupo = groups_get_group_by_name($id_curso, $name_g);
            if( !$id_grupo ) {
				// Crear un objeto grupo
				$newgroup = new stdClass();
				$newgroup->courseid = $id_curso;				
                $newgroup->name = "Grupo_".$id_curso."_p".$asesor_estado; // Creamos el nombre del grupo
                $newgroup->description = $asesor_estado->institution; // Le metemos una variable de la entidad al grupo
                $newgroup->descriptionformat = '';
                $id_grupo = groups_create_group($newgroup); // Creamos el grupo
            }
        }else if($id_grupo == null){
            return false;
        }
    } else {//si es asesor creo un nuevo grupo.
        $asesor_estado = inea_get_user_entidad($id_usuario);// Obtenemos el estado del asesor
		// Crear un objeto grupo
		$newgroup = new stdClass();
		$newgroup->courseid = $id_curso;
        $newgroup->name = "Grupo_".$id_curso."_".$id_usuario; // Creamos el nombre del grupo
        $newgroup->description  = $asesor_estado->institution; // Le metemos una variable de la entidad al grupo
		$newgroup->descriptionformat = '';
        $id_grupo = groups_create_group($newgroup); // Creamos el grupo
    }
    
	$group_members = new stdClass();
    $group_members->userid = $id_usuario;	//Asignamos el id de usuario
    $group_members->groupid = $id_grupo; 	//Asignamos el id del grupo
    $group_members->timeadded = time();

    if($id_rol == EDUCANDO) { // si estaba inscrito con otro asesor, lo desmatriculo
		desagrupar($id_curso, $id_usuario);
    }

    if(!$verifica = $DB->insert_record('groups_members', $group_members)) { //Asignamos el usuario al grupo
		$message = 'El usuario no pudo ser asignado al grupo '.$newgroup->name.", se debe asignar manualmente.";
		//print_box("El usuario no pudo ser asignado al grupo ".$objecto_grupo->name.", asignar manualmente");
		echo $OUTPUT->box($message);
		echo $OUTPUT->box_start();
		echo $OUTPUT->box_end();
	}
	
	// role_assign($id_rol, $id_usuario, $id_grupo, $context->id);
	$assignments = array('roleid' => $id_rol, 'userid' => $id_usuario , 'contextid' => $coursecontext->id);
    core_role_external::assign_roles($assignments);
}

/**
 * INEA - Quita a un usuario de un grupo
 * @param int $id_usuario
 * @param int $id_rol
 * @param int $id_curso
 * @return 
 */
function desagrupar($id_curso, $id_usuario) {
	
	if(empty($id_curso) && empty($id_usuario)) {
		return false;
	}
	
	if($grupos_educando = groups_get_user_groups($id_curso, $id_usuario)) {
		$grupos_educando = $grupos_educando[0];
		if(is_array($grupos_educando)) {
			foreach($grupos_educando as $grupo_id) {
				groups_remove_member($grupo_id, $id_usuario);
			}
		} else {
			groups_remove_member($grupos_educando, $id_usuario);
		}
	}
	//$db->Execute('DELETE gm FROM '.$CFG->prefix."groups_members gm, ".$CFG->prefix."groups_courses_groups cg WHERE gm.groupid=cg.groupid AND cg.courseid=".$id_curso." AND gm.userid=".$id_usuario); //Limpio los datos del grupo anterior
	return true;
}

/**
 * INEA - Desmatricular un usuario de un curso
 * @param int $id_usuario
 * @param int $id_rol
 * @param int $id_curso
 * @return 
 */
function desmatricular($id_usuario, $id_rol, $id_curso){
    global $CFG, $DB;
    //$log_eliminados .= " -- ". implode(',', $ids);

	if(empty($id_curso) && empty($id_usuario)) {
		return false;
	}
	
    $user = $DB->get_record('user', array('id'=>$id_usuario, 'id, firstname, lastname, lastaccess, skype as plaza'));
    $coursecontext = context_course::instance($id_curso);

    if($id_rol == EDUCANDO) { //elimino de groups_members y ejecuto role_unassign
		$assignments = array('roleid' => $id_rol, 'userid' => $id_usuario , 'contextid' => $coursecontext->id);
        core_role_external::unassign_roles($assignments);
		//role_unassign($id_rol, $id_usuario, $coursecontext->id);
        desagrupar($id_curso, $id_usuario);
		//$db->Execute('DELETE gm FROM '.$CFG->prefix."groups_members gm, ".$CFG->prefix."groups_courses_groups cg WHERE gm.groupid=cg.groupid AND cg.courseid=".$id_curso." AND gm.userid=".$id_usuario);
    } else if($id_rol == ASESOR) { //Si es asesor, obtengo el grupo al que esta inscrito y si no tiene educandos inscritos elimino el grupo. Si no hay alumnos ejecuto role_unassign
        $id_grupo_inscrito = grupoInscrito($id_usuario, $id_curso);
        //TODO Verificar si no hay alumnos asociados se elimina el grupo, de no ser asi solo se quita de goup_menvers
        $alumnos = alumnos($user->id, $id_curso, $user->plaza);
        $acredit = alumnosAcreditados($id_usuario, $id_curso);
        if(empty($acredit)){
            if(empty($alumnos)){
                groups_delete_group($id_grupo_inscrito);
            }
        }
        if(empty($alumnos)){
            $assignments = array('roleid' => $id_rol, 'userid' => $id_usuario , 'contextid' => $coursecontext->id);
			core_role_external::unassign_roles($assignments);
			//role_unassign($id_rol, $id_usuario, 0, $context->id);
        }
    }
}

/**
 * INEA - Obtener los cursos en los que esta inscrito el usuario
 * @param int $id_usuario
 * @param int $id_rol
 * @return string
 */
function getCursosRegistrados($id_usuario, $id_rol){
    global $CFG, $DB;

	if(empty($id_usuario) && empty($id_rol)) {
		return false;
	}
    
    $user = $DB->get_record('user', array('id'=>$id_usuario), 'id, firstname, lastname, lastaccess, skype as plaza, lastnamephonetic, firstnamephonetic, middlename, alternatename');
    $user->fullname = fullname($user, true);
    //unset($user->firstname);
    //unset($user->lastname);
    //$users[$key] = $user;

    //Macuco
    $courses = inea_get_courses_page('all', 'c.sortorder ASC', 'c.id, c.shortname, c.fullname', $totalcount);
    // Definir si es educando o asesor
	if($id_rol == ASESOR){// si es asesor podra registrarse 5
        $maxcurses = $totalcount;
        $es_educando = false;
    } else {// si es alumno solo podra registrarse en 2
        $maxcurses = 2;
        $es_educando = true;
    }

    $mycourses = enrol_get_users_courses($id_usuario); // Los cursos en los que esta inscrito el usuario (MACUCO).
    // Agregado para saber en que cusos ya ha concluido
    if($es_educando) { 
        $id_groups_members = $DB->get_records_sql('SELECT groupid, acreditado FROM {groups_members} WHERE userid = ?', array($id_usuario));
        $i=0;
        foreach($mycourses as $tmpcourse){
			if($grupos_educando = groups_get_user_groups($tmpcurse->id, $id_usuario)) {
				$tmp_g = $grupos_educando[0];
				//$tmp_g = get_groups($tmpcurse->id, $id_usuario);
				if(!empty($tmp_g)) {
					foreach($tmp_g as $idgrupo){
						$grupo_tmp = groups_get_group($idgrupo);
						$grupos[$tmpcurse->id] = $grupo_tmp;
						$i = $tmpcourse->id;
					}
					$grupos[$i]->acreditado = $id_groups_members[$grupos[$i]->id]->acreditado; //Agrego el campo acreditado por cada uno de los grupos.
				}
			}
		}
	}
		
	$ncursos = 0;
	$i = 0;
	foreach($courses as $acourse) {
		//if($i==$tmp){continue;}  //PARA EL CURSO CERO
		$encurso = false;
		if($es_educando)	{//OBTIENE LA LISTA DE ASESORES SI EL ROL ES 5 o EDUCANDO
			$asesores = asesores($acourse->id, $user->plaza);//Obtener los asesores
			/* verifico los cursos en los que esta inscrito y cursando. */
			//$tmp = get_records_sql("select count(*) nactividades from {$CFG->prefix}inea inea, {$CFG->prefix}inea_answers answers where  inea.id=answers.ineaid and inea.course={$acourse->id} AND userid={$user->id}");
			$tmp = $DB->get_records_sql("SELECT count(*) nactividades FROM {inea_respuestas r, {inea_ejercicios} e WHERE r.userid = ? AND r.ejercicios_id = e.id AND e.courseid = ?", array($user->id, $acourse->id));
            
			foreach($tmp as $actividad) {
				$encurso = $actividad->nactividades;
			}
		}

		if(isset($mycourses[$acourse->id])) {
			$grupoinscrito = grupoInscrito($id_usuario, $acourse->id);
			//$ogrupo = get_groups($acourse->id, $id_usuario);
			//print_object($ogrupo);
			$ogrupo = array();
			$tb_grupo = array();
			if($grupos_educando = groups_get_user_groups($tmpcurse->id, $id_usuario)) {
				$id_grupos = $grupos_educando[0];
				foreach($id_grupos as $idgrupo) {
					$ogrupo[] = groups_get_group($idgrupo);
				}	
			}
			$tb_grupo[$i] = $ogrupo[$grupoinscrito]->name;

			// Para desactivar los cursos
			if(isset($grupos) && isset($grupos[$acourse->id]) && $grupos[$acourse->id]->acreditado) {
				$tb_curso[$i] = "<p align='center'><a href='{$CFG->wwwroot}/course/view.php?id={$acourse->id}'>{$acourse->fullname}</a></p>";
				$tb_elegir[$i] = "<p align='center'>Curso acreditado</p>";
			} else {
				if($encurso) {
					$tb_curso[$i] = "<p align='center'><a href='{$CFG->wwwroot}/course/view.php?id={$acourse->id}'>{$acourse->fullname}</a></p>";
					$tb_elegir[$i] = '<p align="center"><input type="button" value="Desmatricular" disabled="disabled" onclick="alert(\'Ya estas cursando\');"/></p>';
					//$temparray[$i] .= "<input type='checkbox' name='selected".$count."' checked='yes' onclick='my_check(\"selected".$count."\",".$user->id.",".$acourse->id.",false); revisar(this);' $readonly />" ;
					$ncursos++;
				} else {
					if($es_educando) {// Si es educando muestro la lista de asesores
						//if(!empty($asesores)){
						//  $ncursos++;
                        //if((!$encurso) && (!isset( $grupos[$acourse->id]->acreditado ))){
                        //$temparray[$i]  = '<input type="hidden" name="selected'.$count.'" value="'.$user->id.' '.$acourse->id.'" />';
                        //}
                        //$temparray[$i] .= "<input type='checkbox' name='selected".$count."' checked='yes' value='".$user->id." ".$acourse->id." ".true."' onclick='my_check(\"selected".$count."\",".$user->id.",".$acourse->id.",this.checked); revisar(this);' $readonly /><a href='{$CFG->wwwroot}/course/view.php?id={$acourse->id}'>Ir al curso</a>" ;
                        $tb_curso[$i] = "<p><a href='{$CFG->wwwroot}/course/view.php?id={$acourse->id}'>{$acourse->fullname}</a></p>";
                        $tb_elegir[$i] = '<p align="center"><input type="button" value="Desmatricular" onclick="activarDesactivarBoton(this,1);desmatricular('."$id_usuario, $id_rol, $acourse->id".');"/></p>';
                        $ncursos++;
                        // obtengo el asesor al que esta inscrito
						//echo $acourse->id;
                        // Imprimo los asesores
                        /*$temparray[$i] .= "<select name='asesor".$count."' onchange='my_check(\"selected".$count."\",".$user->id.",".$acourse->id.",true);revisar(document.multienrol.selected".$count.");'>"; // checked='yes' onclick='my_check(\"selected".$count."\",".$user->id.",".$acourse->id.",false); revisar(this);' $readonly />" ;
						foreach($asesores as $asesor){
                            $grupoinscrito==$asesor->idgrupo?$selected='selected="selected"':$selected="";//para seleccionar asesor
                            $temparray[$i] .= "<option value='".$asesor->idgrupo."' $selected>".$asesor->firstname."  ".$asesor->lastname."</option>";
                        }
                        $temparray[$i] .= "</select>";
						}else{
							$temparray[$i] .= " No hay asesor";
						}*/
					} else {//No es educando, es asesor
						$ncursos++;
						$alumnos = alumnos($user->id,$acourse->id,$user->plaza);//Obtener los asesores
                        // Para saber si tiene educandos ensu grupo 
						//  echo "JUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUU ".empty($alumnos)." <br/>";
						$tb_curso[$i] = "<p><a href='{$CFG->wwwroot}/course/view.php?id={$acourse->id}'>{$acourse->fullname}</a></p>";
						if(!empty($alumnos)) {
							if(count($alumnos) == 1) {
								$tbl_participantes[$i] = "<p align='center'>Tiene ".count($alumnos)." alumno inscrito</p>";
							} else {
								$tbl_participantes[$i] = "<p align='cente'>Tiene ".count($alumnos)." alumnos inscritos</p>";
							}
							$tb_elegir[$i] = '<p align="center"><input type="button" value="Desmatricular" disabled="disabled" onclick="alert(\'Ya estas cursando\');"/></p>';
						} else {
							$tbl_participantes[$i] = "<p align='center'>No tiene alumnos inscritos</p>";
							/*if((!$encurso) && (!isset( $grupos[$acourse->id]->acreditado ))){
                            $temparray[$i]  = '<input type="hidden" name="selected'.$count.'" value="'.$user->id.' '.$acourse->id.'" />';
							}*/
							//$temparray[$i] .= "<input type='checkbox'  name='selected".$count."' checked='yes' onclick='my_check(\"selected".$count."\",".$user->id.",".$acourse->id.",false); revisar(this);' $readonly /><a href='{$CFG->wwwroot}/course/view.php?id={$acourse->id}'>Ir al curso</a>" ;
							$tb_elegir[$i] = '<p align="center"><input type="button" value="Desmatricular" onclick="activarDesactivarBoton(this,1);desmatricular('."$id_usuario,$id_rol,$acourse->id".');"/></p>';
						}
					}
				}
			}//END ELSE
            
			if($es_educando){ //MACUCO - Obtener el nombre del asesor
				$asesores = asesores($acourse->id, $user->plaza);//Obtener los asesores
				if(empty($asesores)) {
					$tbl_participantes[$i] = "Estudiando sin asesor";
				}
				foreach($asesores as $asesor){
					if($grupoinscrito==$asesor->idgrupo) {
						$tbl_participantes[$i] = $asesor->firstname."  ".$asesor->lastname;
					}
				}
			}
			$i++;
		}//END IF
	}//END FOREACH

	if($ncursos > 1) {
		$mens = "Usted est&aacute; inscrito en los siguientes cursos";
	} else {
		$mens = "Usted est&aacute; inscrito en el siguiente curso";
	}	
	$table = '<div><table width="100%" border="1" cellpadding="5" cellspacing="1" class="generaltable boxaligncenter">'."\n";
	$table .= "<tr><th colspan='4' class='header c0' scope='col'>".$mens."</th></tr>\n";
	$es_educando ? $etiqueta = "Nombre del asesor" : $etiqueta = "Participantes";
	$table .= "<tr><th class='header c0' scope='col'>Curso</th><th class='header c0' scope='col'>Grupo</th><th class='header c0' scope='col'>$etiqueta</th><th class='header c0' scope='col'>Elegir</th></tr>\n";
		
	for($j=0;$j<$i;$j++) {
		$table .= "<tr>\n";
		$table .= "<td>$tb_curso[$j]</td>\n";
		$table .= "<td>$tb_grupo[$j]</td>\n";
		$table .= "<td>$tbl_participantes[$j]</td>\n";
		$table .= "<td>$tb_elegir[$j]</td>\n";
		$table .= "</tr>\n";
	}
	if($i==0) {
		$table ='<div><table width="100%" border="1" cellpadding="5" cellspacing="1" class="generaltable boxaligncenter">'."\n";
		$table .= '<tr align="center"><td colspan="4">Usted no se ha inscrito a&uacute;n a ning&uacute;n curso.</td></tr>';
	}
	$table .= "</table>\n";
	$table .= '<input type="hidden" name="cursando" id="cursando" value="'.$ncursos.'"/></div>';

    return $table;
}

/**
 * INEA - Obtiene a los educandos de un asesor
 * @param int $id_asesor
 * @param int $id_curso
 * @return Object
 */
// DAVE: 07 05 2015 la siguente es la linea original, se cambia porque no muestra a los educandos
//select distinct cg.id as groupid, u.skype as plaza
function alumnos($id_asesor, $id_curso){
    global $CFG, $DB;
	
	if(empty($id_asesor) && empty($id_curso)) {
		return false;
	}
	
    return $DB->get_records_sql("select distinct u.*, grup_plaz_ases.groupid groupid
from {groups_members} gm, {role_assignments} a, {user} u, (
select distinct g.id as groupid, u.skype as plaza
from {groups} g, {groups_members} gm, {role_assignments} a, {user} u
where g.courseid = ?
    AND gm.userid = ?
    AND g.id = gm.groupid
    AND a.userid = gm.userid
    AND a.roleid = ?
    AND u.id = a.userid
) as grup_plaz_ases
where gm.groupid = grup_plaz_ases.groupid
    AND a.userid = gm.userid
    AND a.roleid = ?
    AND u.id = gm.userid
	AND gm.acreditado = 0
    AND u.skype = grup_plaz_ases.plaza ORDER BY u.firstname, u.lastname", array($id_curso, $id_asesor, ASESOR, EDUCANDO));
}

/**
 * INEA - Obtiene a los educandos que han acreditado un curso
 * @param int $id_asesor
 * @param int $id_curso
 * @return Object
 */
function alumnosAcreditados($id_asesor, $id_curso){
    global $CFG, $DB;
	
	if(empty($id_asesor) && empty($id_curso)) {
		return false;
	}
    
	return $DB->get_records_sql("SELECT DISTINCT u.*, grup_plaz_ases.groupid groupid
	FROM {groups_members} gm, {role_assignments} a, {user} u, (
		SELECT distinct g.id as groupid, u.skype as plaza
		FROM {groups} g, {groups_members} gm, {role_assignments} a, {user} u
		WHERE g.courseid = ?
		AND gm.userid = ?
		AND g.id = gm.groupid
		AND a.userid = gm.userid
		AND a.roleid = ?
		AND u.id = a.userid
	) AS grup_plaz_ases
	WHERE gm.groupid = grup_plaz_ases.groupid
    AND a.userid = gm.userid
    AND a.roleid = ?
    AND u.id = gm.userid
    AND gm.acreditado = 1
    AND u.skype = grup_plaz_ases.plaza ORDER BY u.firstname, u.lastname", array($id_curso, $id_asesor, ASESOR, EDUCANDO));
}

/**
 * INEA - Obtiene el ID del grupo si el usuario esta inscrito
 * @param int $id_asesor
 * @param int $id_curso
 * @return Object
 */
function grupoInscrito($id_usuario, $id_curso){
    global $CFG, $DB;
	
	if(empty($id_usuario) && empty($id_curso)) {
		return false;
	}
	
    //$groups = $DB->get_records_sql("SELECT gm.groupid FROM {$CFG->prefix}groups_members gm, {$CFG->prefix}groups_courses_groups cg WHERE gm.groupid=cg.groupid AND cg.courseid=$courseid AND gm.userid=$userid");
    //echo "SELECT gm.groupid FROM {$CFG->prefix}groups_members gm, {$CFG->prefix}groups_courses_groups cg WHERE gm.groupid=cg.groupid AND cg.courseid=$courseid AND gm.userid=$userid <br/>";
    if($grupos_usuario = groups_get_user_groups($id_curso, $id_usuario)) {
		$grupos_usuario = $grupos_usuario[0];
		if(is_array($grupos_usuario)) {
			return $grupos_usuario[0];
		} else {
			return $grupos_usuario;
		}
	}
}

/**
 * INEA - Obtiene a los asesores en un curso
 * @param int $id_asesor
 * @param int $id_curso
 * @return Object
 */
function asesores($id_curso, $id_plaza) {
    global $CFG, $DB;

	if(empty($id_curso) && empty($id_plaza)) {
		return false;
	}
	
    return $DB->get_records_sql("SELECT distinct gm.userid, g.id as idgrupo, u.*
    FROM {groups} g, {groups_members} gm, {role_assignments} a, {user} u
    WHERE courseid = ?
    AND g.id = gm.groupid
    AND a.userid = gm.userid
    AND a.roleid = ?
    AND u.id = gm.userid
    AND u.skype = ? ORDER BY u.firstname, u.lastname", array($id_curso, EDUCANDO, $id_plaza));
}

/**
 * INEA - Envia un correo con un aviso
 * @param string $message
 */
function enviarMail($message){
	global $CFG;
	
	$from = get_admin();
	$user = new stdClass();
	$user->email = "juan.manuel.mp8@gmail.com";
	$user->mailformat = 1;
	$user->firstname = "Juan Manuel";
	$user->lastname = "Muñoz Pérez";
	
	// Enviar email de aviso de desmatriculacion
	email_to_user($user, $from, "IMPORTANTE: Posible desmatriculación amevyt.", $message);
}
?>
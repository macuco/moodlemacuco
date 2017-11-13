<?PHP
    require_once("../config.php");
    require_once("lib.php");

    $group        = optional_param('group', -1, PARAM_INT);                   // Group to show
    $id       = required_param('id');              // course id
    $download = optional_param('download');
    $user     = optional_param('user', -1);
    $action   = optional_param('action', 'grades');
    $cview    = optional_param('cview', -1);

    $id_usuario = $USER->id; // ID del estudiante
    $id_modulo = optional_param('id_modulo', PARAM_INT); // ID del curso

    $rol_actual = obtiene_rol($USER->id,'',$id); // Obtiene el rol del usuario actual en el curso actual de acuerdo al id del modulo
	//echo $rol_actual[1];
    $rol_actual_objeto = $rol_actual[2]; // Obtiene el rol del usuario actual en el curso actual de acuerdo al id del modulo

	
    $id_estado_objeto = get_user_estado($USER->id); // vhackero para ontener el estado al que pertenece el usuario;
    if($id_estado_objeto->institution) $id_estado = $id_estado_objeto->institution; // vhackero para ontener el estado al que pertenece el usuario;

    if (!$course = get_record('course', 'id', $id)) {
        error('No course ID');
    }

    require_login($course->id);
	
    // if the user set new prefs make sure they happen now
    if ($action == 'set_grade_preferences' && $prefs = data_submitted()) {
        if (!confirm_sesskey()) {
            error(get_string('confirmsesskeybad', 'error'));
        }
        grade_set_preferences($course, $prefs);
    }

    $preferences = grade_get_preferences($course->id);

    
    // we want this in its own window
    if ($action == 'stats') {
        grade_stats();
        exit();
    } else if ($action == 'ods') {
        grade_download('ods', $id);
        exit();
    } else if ($action == 'excel') {
        grade_download('xls', $id);
        exit();
    } else if ($action == 'text') {
        grade_download('txt', $id);
        exit();
    }

/// Check to see if groups are being used in this forum
/// and if so, set $currentgroup to reflect the current group

    $groupmode    = groupmode($course);   // Groups are being used
    $currentgroup = get_and_set_current_group($course, $groupmode, $group);

    if (!$currentgroup) {      // To make some other functions work better later
        $currentgroup  = NULL;
    }

    $isseparategroups = ($course->groupmode == SEPARATEGROUPS and $course->groupmodeforce and
                         !has_capability('moodle/site:accessallgroups', $context));

    if ($isseparategroups and (!$currentgroup) ) { 
        print_header("$course->shortname: ".get_string('participants'), $course->fullname,
                     "<a href=\"$CFG->wwwroot/course/view.php?id=$course->id\">$course->shortname</a> -> ".
                     get_string('participants'), "", "", true, "&nbsp;", navmenu($course));
        print_heading(get_string("notingroup", "forum"));
        print_footer($course);
        exit;
    }
	

    //  ----  OBTENER LOS ALUMNOS DEL GRUPO PARA INDICADORES DE AVANCE (MACUCO)
    if ($currentgroup != NULL) {
        $groupmembers = get_group_users($currentgroup);
	
    }else if ($id_estado != NULL && $rol_actual[1] != 1) {	//Para q filtre educandos SOLO a los RE y no al admin
		//echo "Estado: ".$id_estado;
        $groupmembers = get_entidad_users($id_estado);
		
    } else {
        global $CFG;

        $sort = 'u.lastaccess DESC';
        $exceptions = '';

        $except = '';
        // in postgres, you can't have things in sort that aren't in the select, so...
        $extrafield = str_replace('ASC', '', $sort);
        $extrafield = str_replace('DESC', '', $extrafield);
        $extrafield = trim($extrafield);
        if (!empty($extrafield)) {
            $extrafield = ',' . $extrafield;
        }
        $groupmembers = get_records_sql("SELECT DISTINCT u.* $extrafield
                                  FROM {$CFG->prefix}user u,
                                       {$CFG->prefix}groups_members m
                                 WHERE m.userid = u.id
                              ORDER BY $sort");		
    }

    foreach ($groupmembers as $id => $ouser) {	// Descarta usuarios q NO sean educandos
        if (!isstudent($course->id, $id))
            unset($groupmembers[$id]);
    }
    // ---------------------------------------------------------

    $unidades = get_records_select('inea_total_ejercicios','courseid='.$course->id);
    

    $pinta_header = true;
    include("indicadores.html");//Cargar los javascript
    print_header($course->shortname.': '.get_string('grades'), $course->fullname, grade_nav($course, $action),'',$heade_indicadores);


	$origen = get_user_estado($USER->id); // Datos de origen [id estado/id pais/municipio]
//	print_object($origen);
//print_object($USER);
    $mi_estado=$origen->institution; // ID del estado
    $mi_pais=$origen->country; // ID del pais
    
    if(empty($estado)){ // se identifica el estado al que pertenecen y se muestra su plaza
    	$estado = $id_estado;
    } else { // Son usuario que no pueden elegir un estado ni plaza asi que soo veran su plaza
    	if(!empty($estado)) 
    	$estado = $estado;
    }


	//echo "<br>Curso: <b>".utf8_decode($course->fullname)."</b>";
	echo "<br>Curso: <b>".$course->fullname."</b>";
	echo "<br> ".str_replace('_',' ',ucfirst(substr($rol_actual[0], 0, 1)).substr($rol_actual[0], 1)).": <b>".
	get_field_sql("SELECT CONCAT(firstname, ' ', lastname, ' ', icq) FROM {$CFG->prefix}user WHERE id = $id_usuario")."</b>"; // Para imprimir el rol del usuario actual
	echo "<br><br>";
	
    // Should use this variable so that we don't break stuff every time a variable is added or changed.
    $baseurl = 'index.php?contextid='.$context->id.'&amp;roleid='.$roleid.'&amp;estado='.$estado.'&amp;plaza='.$plaza.'&amp;id='.$course->id.'&amp;group='.$currentgroup.'&amp;perpage='.$perpage.'&amp;accesssince='.$accesssince.'&amp;search='.s($search).'&amp;id_modulo='.$id_modulo;

    /// find out current groups mode
    $groupmode = groupmode($course);
	
	if($rol_actual[1] == 8){	// RE
	setup_and_print_groups_vhackero($course, $groupmode, $baseurl, $estado); // RUDY: se subttuye la funcion para que filter los grupos por estado
	}else if($rol_actual[1] == 1){	//admin
	setup_and_print_groups($course, $groupmode, $baseurl); // RUDY: se subttuye la funcion para que NO filter los grupos
	}
    echo '<div class="clearer"></div>';

    grade_preferences_menu($action, $course);

    grade_set_uncategorized();

    if (has_capability('moodle/course:viewcoursegrades', get_context_instance(CONTEXT_COURSE, $course->id))) {
        switch ($action) {
            case "cats":
                grade_set_categories();
                break;
            case "indicadores":
                $pinta_header = false;
         

//print_object($groupmembers);

                include("indicadores.html");
                break;
            case "insert_category":
                grade_insert_category();
                grade_set_categories();
                break;
            case "assign_categories":
                grade_assign_categories();
                grade_set_categories();
                break;
            case "set_grade_weights":
                grade_set_grade_weights();
                grade_display_grade_weights();
                break;
            case "weights":
                grade_display_grade_weights();
                break;
            case "grades":
                if ($preferences->use_advanced == 1) {
                    grade_view_all_grades($user);
                }
                else {
                    // all the grades will be in the 'uncategorized' category
                    grade_view_category_grades($user); // Esta funcion es la que muestra las calificaciones de las evaluaciones de los educandos que ve el asesor
					// RUDY: Las 3 sgtes lineas se agragaron para pintar el avance de acts por educando. Es es case "indicadores" que esta mas arriba
					echo "<br/><br/>";
                	$pinta_header = false;
                	include("indicadores.html");
                }
                break;
            case "vcats":
                grade_view_category_grades($user);
                break;
            case "prefs":
            case "set_grade_preferences":
                grade_display_grade_preferences($course, $preferences);
                break;
            case "letters":
                grade_display_letter_grades();
                break;
            case "set_letter_grades":
                grade_set_letter_grades();
                grade_display_letter_grades();
                break;
            case "delete_category":
                grade_delete_category();
                // re-run set_uncategorized as they may have deleted a category that had items in it 
                grade_set_uncategorized();
                grade_set_categories();
                break;
            case "view_student_grades":
                grade_view_all_grades($user);
                break;
            case "view_student_category_grades":
                grade_view_category_grades($user);
                break;
            default:
                if ($preferences->use_advanced == 1) {
                    grade_view_all_grades($user);
                }
                else {
                    grade_view_category_grades($user);
                }
        } // end switch
        
    } // end if isTeacher
    else {
        if ($preferences->show_weighted || $preferences->show_points || $preferences->show_percent) {

            if ($preferences->use_advanced == 1) { 
                if($action != 'vcats') {
                    grade_view_all_grades($USER->id);
                    $pinta_header = false;

                foreach ($groupmembers as $id => $ouser) {
                    if ($USER->id != $id)
                        unset($groupmembers[$id]);
                }

                echo "<br/><br/>";
                    include("indicadores.html");
                
                }
                else {
                    grade_view_category_grades($USER->id);
                }
            } else {
                grade_view_category_grades($USER->id);
		$pinta_header = false;

                foreach ($groupmembers as $id => $ouser) {
                    if ($USER->id != $id)
                        unset($groupmembers[$id]);
                }

                echo "<br/><br/>";
                    include("indicadores.html");
                echo "<br/><br/>";
					$user_record = get_record('inea_concluidos', 'userid_mol', $USER->id, 'courseid_mol', $course->id);//RUDY: 10/3/14
					//print_object($user_record);
					$user_status_sasa = $user_record->status_sasa;
					switch($user_status_sasa){ //RUDY: 10/3/14
						case '10': 
						  $mensaje_sasa = get_string('messagesasa10'); 
						  break;
						case '11': 
						  $mensaje_sasa = get_string('messagesasa11'); 
						  break;
						case '12': 
						  $mensaje_sasa = get_string('messagesasa12'); 
						  break;
						case '13': 
						  $mensaje_sasa = get_string('messagesasa13'); 
						  break;
						case '14': 
						  $mensaje_sasa = get_string('messagesasa14'); 
						  break;
						case '15': 
						  $mensaje_sasa = get_string('messagesasa15'); 
						  break;
						case '16': 
						  $mensaje_sasa = get_string('messagesasa16'); 
						  break;
						case '17': 
						  $mensaje_sasa = get_string('messagesasa17'); 
						  break;
						case '18': 
						  $mensaje_sasa = get_string('messagesasa18'); 
						  break;
						case '19': 
						  $mensaje_sasa = get_string('messagesasa19'); 
						  break;
						case '20': 
						  $mensaje_sasa = get_string('messagesasa20'); 
						  break;
						case '21': 
						  $mensaje_sasa = get_string('messagesasa21'); 
						  break;
						case '22': 
						  $mensaje_sasa = get_string('messagesasa22'); 
						  break;
						case '23': 
						  $mensaje_sasa = get_string('messagesasa23'); 
						  break;
						case '99': 
						  $mensaje_sasa = get_string('messagesasa99'); 
						  break;
						case 'default': 
						  $mensaje_sasa = get_string('messagesasadefault'); 
						break;
					}
					notify($mensaje_sasa); //RUDY: Mmensaje para el educando al concluir su curso. 10/3/14
            }

        } else {
            error(get_string('gradebookhiddenerror','grades'));
        }
    } // end else (!teacher)

 echo "<br/><br/>";

?>
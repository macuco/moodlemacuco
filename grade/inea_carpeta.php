<?PHP
require_once($CFG->dirroot."/grade/lib.php");
    
    
    
    //MACUCO -- Agregar el nombre del role
    if(file_exists($CFG->dirroot.'/mod/inea/inealib.php')){
        require_once $CFG->dirroot.'/mod/inea/inealib.php';
        
    }
    
    $id_usuario = $USER->id; // ID del estudiante
    $id_modulo = optional_param('id_modulo', 0, PARAM_INT); // ID del curso
/*
    $rol_actual = 5;// obtiene_rol($USER->id,'',$id); // Obtiene el rol del usuario actual en el curso actual de acuerdo al id del modulo
	//echo $rol_actual[1];
    $rol_actual_objeto = $rol_actual[2]; // Obtiene el rol del usuario actual en el curso actual de acuerdo al id del modulo

*/	
    $id_estado_objeto = inea_get_user_estado($USER->id); // vhackero para ontener el estado al que pertenece el usuario;
    //print_object$id($id_estado_objeto);
    if($id_estado_objeto->institution) $id_estado = $id_estado_objeto->institution; // vhackero para ontener el estado al que pertenece el usuario;

    
    
    
    /// basic access checks
//    if (!$course = $DB->get_record('course', array('id' => $id))) {
//        print_error('invalidcourseid');
//    }
   /* 
    $PAGE->set_url(new moodle_url('/grade/carpeta.php', array('id'=>$course->id)));
    
    //require_login($course);
    $context = context_course::instance($course->id);
    
   
    //groups_get_activity_group();
    //groups_get_my_groups();
    $grupos = $grupos = groups_get_all_groups($course->id, $USER->id);
    $grupo = inea_get_user_group($course->id, $USER->id);
    
    //print_object(count($grupos));
    //print_object($course);
    
    
   //exit;
	
    $rol_actual = 1; //TODO Poner el ID del responsable estatal

    //  ----  OBTENER LOS ALUMNOS DEL GRUPO PARA INDICADORES DE AVANCE (MACUCO)
    if (!empty($grupo) && count($grupos)==1 ) {
        $groupmembers = groups_get_members($grupo->id);//groups_get_groups_members($groupsids);
	
    }else if (isset($id_estado) && $rol_actual != 1) {	//Para q filtre educandos SOLO a los RE y no al admin
		//echo "Estado: ".$id_estado;
        $groupmembers = get_entidad_users($id_estado);
		
    } else {
        global $CFG, $DB;

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
        $groupmembers = $DB->get_records_sql("SELECT DISTINCT u.* $extrafield
                                  FROM {$CFG->prefix}user u,
                                       {$CFG->prefix}groups_members m
                                 WHERE m.userid = u.id
                              ORDER BY $sort");		
    }
*/
   
    //exit();
    
    foreach ($groupmembers as $id => $ouser) {	// Descarta usuarios q NO sean educandos
        if (!isstudent($course->id, $id)){
            unset($groupmembers[$id]);
        }else{//Completar el usuario
            $groupmembers[$id] = $DB->get_record('user', array('id'=>$id));
        }
    }
    // ---------------------------------------------------------

    $unidades = $DB->get_records_select('inea_total_ejercicios','courseid='.$course->id);
    
    
    //$PAGE->set_pagelayout('report');
    //echo $OUTPUT->heading(get_string('pluginname', 'gradereport_user') . ' - ' . "JUUUAN");
    
    
    /*$pinta_header = true;
    include("indicadores.php");//Cargar los javascript
    
    $OUTPUT = $PAGE->get_renderer('core');
    $OUTPUT->inea_head_html=$heade_indicadores;
    print_grade_page_head($course->id, 'report', 'user');
    */
    
    $pinta_header = false;
    include("indicadores.php");//Cargar los javascript
    
 
 
?>
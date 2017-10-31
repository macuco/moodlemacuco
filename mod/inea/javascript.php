<?php 
require_once('../../config.php');
if(isset($_GET['imprimir'])){
    echo javascript_curso(2,29);
}
function javascript_curso($cursoid,$ineaid) {
	global $USER, $CFG, $DB;
	
	//$course = get_record("course", "id", $cursoid);
	/* vhackero Preparar el Javascript par amanejo del curso desde un recurso externo >*/
	$url_ret = '';
	$context = context_course::instance($cursoid); //get_context_instance(CONTEXT_COURSE, $cursoid);
	/*if (!$USER->editor[$cursoid]) !isadmin($USER->id) && !iscreator($USER->id) && (isteacher($course->id, $USER->id) || isstudent($course->id, $USER->id))) {
		$url_ret = '?urlret='.$course->urlsae;
		}*/
	if (! $course = $DB->get_record("course", array("id" => $cursoid)) ) {  // Objeto del curso
    		error("Error en el ID del curso");
    }
	
    
	$URL_logout =  "$CFG->wwwroot/login/logout.php$url_ret";

    $formato = false;//Variable para hacer que se muestre el javascript con formato
	$javascript = "";
	//$javascript .= "<script language=\"javascript\">\n";
	//$javascript .=  "<!--\n";
	$javascript .=  " var id_curso=$cursoid;";
	if($formato) $javascript .=  "\n";
	$javascript .=  " var id_usuario=$USER->id;";
	if($formato) $javascript .=  "\n\n";
	$javascript .=  "function Curso_salir(ruta){";
	if($formato) $javascript .=  "\n";
	$javascript .=  "ruta.location.href=\"$URL_logout\";";
	if($formato) $javascript .=  "\n";
	$javascript .=  "}";
	if($formato) $javascript .=  "\n";
	$javascript .=  "function Usuario_miCorreo(ruta){";
	if($formato) $javascript .=  "\n";
	
	if(tieneRol(6,$cursoid)) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";";
	else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/message/index.php?cid=$cursoid\";";
	if($formato) $javascript .=  "\n";
	$javascript .=  "}";
	if($formato) $javascript .=  "\n";
	$javascript .=  "function Curso_correoTutor(ruta){";
	if($formato) $javascript .=  "\n";
	if (tieneRol(3,$cursoid)) {
		if(tieneRol(6)) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";";
		else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/message/index.php?cid=$cursoid\";";
	} else {
	    if(tieneRol(6)) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";";
		//else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/mod/inea/mensaje.php?cid=$cursoid\";\n";
	    else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/message/index.php?cid=$cursoid\";";
	}
	if($formato) $javascript .=  "\n";
	$javascript .=  "}";
	if($formato) $javascript .=  "\n";
	$javascript .=  "function Usuario_verAlumnos(ruta){";
	if($formato) $javascript .=  "\n";
	if(tieneRol(6)) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";";
	else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/user/index.php?contextid=$context->id&roleid=5\";";
	if($formato) $javascript .=  "\n";
	$javascript .=  "}";
	if($formato) $javascript .=  "\n";
	$javascript .=  "function Usuario_verTutor(ruta){";
	if($formato) $javascript .=  "\n";
	if (tieneRol(3)) {
	    if(tieneRol(6)) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";";
		else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/message/index.php?cid=$cursoid\";";
	} else {
	    if(tieneRol(6)) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";";
		else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/mod/inea/mensaje.php?cid=$cursoid\";";
	}//	$javascript .=  "ruta.location.href=\"$CFG->wwwroot/user/index.php?contextid=$context->id&roleid=4\";\n";
	if($formato) $javascript .=  "\n";
	$javascript .=  "}";
	if($formato) $javascript .=  "\n";
	$javascript .=  "function Curso_verCalendario(ruta){";
	if($formato) $javascript .=  "\n";
	if(tieneRol(6)) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";";
	else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/course/view.php?id=$cursoid&seccion=3\";";
	if($formato) $javascript .=  "\n";
	$javascript .=  "}";
	if($formato) $javascript .=  "\n";
	$javascript .=  "function Curso_verChat(ruta){";
	if($formato) $javascript .=  "\n";
	if(tieneRol(6)) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";";
	else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/mod/chat/index.php?id=$cursoid\";";
	if($formato) $javascript .=  "\n";
	$javascript .=  "}";
	if($formato) $javascript .=  "\n";
	$javascript .=  "function Curso_verForos(ruta){";
	if($formato) $javascript .=  "\n";
	$javascript .=  "ruta.location.href=\"$CFG->wwwroot/mod/forum/index.php?id=$cursoid\";";
	if($formato) $javascript .=  "\n";
	$javascript .=  "}";
	if($formato) $javascript .=  "\n";
	$javascript .=  "function Curso_miCarpeta(ruta){";
	if($formato) $javascript .=  "\n";
	if(tieneRol(6)) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";";
	else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/mod/inea/carpeta.php?id_modulo=$ineaid\";";
	if($formato) $javascript .=  "\n";
	$javascript .=  "}";
	if($formato) $javascript .=  "\n";
	$javascript .=  "function Curso_verCalificaciones(ruta){";
	if($formato) $javascript .=  "\n";
	if(tieneRol(6)) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";";
	else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/grade/index.php?id=$cursoid&id_modulo=$ineaid\";";
	if($formato) $javascript .=  "\n";
	$javascript .=  "}";
	if($formato) $javascript .=  "\n";
	$javascript .=  "function Curso_verCarpeta(ruta){";
	if($formato) $javascript .=  "\n";
	if(tieneRol(6)) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";";
	else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/mod/inea/carpeta.php?id_modulo=$ineaid\";";
	if($formato) $javascript .=  "\n";
	$javascript .=  "}\n";

//        $javascript .=  "function wwwroot(){\n";
//	$javascript .=  "return \"$CFG->wwwroot\";\n";
//	$javascript .=  "}\n";

	//$javascript .=  "\n";
	//$javascript .= $str;
	//$javascript .=  "\n";
	//$javascript .=  "//-->\n";
	//$javascript .=  "</script>\n";
//	$javascript .= imprimeActividades_Vhackero($course);
	
	return $javascript;
}

function tieneRol($idRol, $sistemContext = false ){
    global $COURSE, $USER;
    if($sistemContext){
        $cContext = context_system::instance(); 
    }else{
        $cContext = context_course::instance($COURSE->id); // global $COURSE
    }
    
    $currenRole = current(get_user_roles($cContext, $USER->id));
    
    
    if($currenRole){
        return $currenRole->id==$idRol? true : false;
    }
    return false;
}
?>
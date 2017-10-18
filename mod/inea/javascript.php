<?php 
require('../../config.php');
echo javascript_curso(2,29);
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

	/* Vhackero > Preparar las funciones JavaScript */
	$str  = "
// Vhackero - Jorge Polo Contreras Paredes
// Obtiene la ruta de cualquier p�gina dentro de frames anidados.
var currentFrame = 'top'
var x;
var y = new Array();
y[0] = 0;
var level = 0;
var actual = self.name;
var ruta;
function parsetree()
{
	for (i=y[level];i<x.length;i++)
	{
		if (x.frames[i].name == actual){
			ruta = currentFrame + '.frames[' + i + ']';
			//alert('Cuando ruta='+ruta+' y eval(ruta).name='+eval(ruta).name+', actual es igual a '+actual);
			}
		if (x.frames[i].length > 0){
			currentFrame = currentFrame + '.frames[' + i + ']';
			y[level] = i + 1;
			level++;
			y[level] = 0;
			return;
		}
	}
	currentFrame = currentFrame.substring(0,currentFrame.lastIndexOf('.'));

	if (level == 0) currentFrame =='';
	level--;

}

while (currentFrame != ''){
	x = eval(currentFrame);
	parsetree();
}

//alert('ruta es '+ruta+' y eval(ruta).name es '+eval(ruta).name)
//alert(top.id_curso);";
	
	$javascript = "";
	//$javascript .= "<script language=\"javascript\">\n";
	//$javascript .=  "<!--\n";
	$javascript .= "// Imprimir funciones JavaScript para acceder a recursos y actividades Moodle desde los contenidos\n";
    $javascript .= "// @autor Jorge Polo Contreras Paredes - Vhackero\n";
    $javascript .= "// @eMail vhackero@vhackero.zzn.com, vhackero@gmail.com - Vhackero\n";
    $javascript .= "\n";
	$javascript .=  " var id_curso=$cursoid;\n";
	$javascript .=  " var id_usuario=$USER->id;\n";
	$javascript .=  "\n";
	$javascript .=  "function Curso_salir(ruta){\n";
	$javascript .=  "ruta.location.href=\"$URL_logout\";\n";
	$javascript .=  "}\n";
	$javascript .=  "function Usuario_miCorreo(ruta){\n";
	
	/*if(isguest()) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";\n";
	else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/message/index.php?cid=$cursoid\";\n";
	*/
	$javascript .=  "}\n";
	$javascript .=  "function Curso_correoTutor(ruta){\n";
/*	if ($USER->tutor[$cursoid]) {
		if(isguest()) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";\n";
		else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/message/index.php?cid=$cursoid\";\n";
	} else {
		if(isguest()) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";\n";
		else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/mod/inea/mensaje.php?cid=$cursoid\";\n";
	}
*/	$javascript .=  "}\n";
	$javascript .=  "function Usuario_verAlumnos(ruta){\n";
/*	if(isguest()) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";\n";
	else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/user/index.php?contextid=$context->id&roleid=5\";\n";
*/	$javascript .=  "}\n";
	$javascript .=  "function Usuario_verTutor(ruta){\n";
/*	if ($USER->tutor[$cursoid]) {
		if(isguest()) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";\n";
		else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/message/index.php?cid=$cursoid\";\n";
	} else {
		if(isguest()) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";\n";
		else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/mod/inea/mensaje.php?cid=$cursoid\";\n";
	}//	$javascript .=  "ruta.location.href=\"$CFG->wwwroot/user/index.php?contextid=$context->id&roleid=4\";\n";
*/	$javascript .=  "}\n";
	$javascript .=  "function Curso_verCalendario(ruta){\n";
/*	if(isguest()) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";\n";
	else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/course/view.php?id=$cursoid&seccion=3\";\n";
*/	$javascript .=  "}\n";
	$javascript .=  "function Curso_verChat(ruta){\n";
/*	if(isguest()) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";\n";
	else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/mod/chat/index.php?id=$cursoid\";\n";
*/	$javascript .=  "}\n";
	$javascript .=  "function Curso_verForos(ruta){\n";
	$javascript .=  "ruta.location.href=\"$CFG->wwwroot/mod/forum/index.php?id=$cursoid\";\n";
	$javascript .=  "}\n";
	$javascript .=  "function Curso_miCarpeta(ruta){\n";
/*	if(isguest()) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";\n";
	else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/mod/inea/carpeta.php?id_modulo=$ineaid\";\n";
*/	$javascript .=  "}\n";
	$javascript .=  "function Curso_verCalificaciones(ruta){\n";
/*	if(isguest()) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";\n";
	else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/grade/index.php?id=$cursoid&id_modulo=$ineaid\";\n";
*/	$javascript .=  "}\n";
	$javascript .=  "function Curso_verCarpeta(ruta){\n";
/*	if(isguest()) $javascript .=  "ruta.location.href=\"$CFG->wwwroot/cursos/invitado.php\";\n";
	else $javascript .=  "ruta.location.href=\"$CFG->wwwroot/mod/inea/carpeta.php?id_modulo=$ineaid\";\n";
*/	$javascript .=  "}\n";

        $javascript .=  "function wwwroot(){\n";
	$javascript .=  "return \"$CFG->wwwroot\";\n";
	$javascript .=  "}\n";

	$javascript .=  "\n";
	$javascript .= $str;
	$javascript .=  "\n";
	//$javascript .=  "//-->\n";
	//$javascript .=  "</script>\n";
//	$javascript .= imprimeActividades_Vhackero($course);
	
	return $javascript;
}


?>
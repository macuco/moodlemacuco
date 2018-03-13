<?php  // $Id: validar_deposito.php,v 1.0 2009/04/24 08:42:30 Ludwick Exp $
    require_once("../config.php");
    require_once("../course/lib.php");
	require_once($CFG->dirroot.'/auth/email/crear_pdf.php');
	
	//global $USER; // Usuario que hace la resolucion (asesor secundario)
	//print_object($_POST);
    if(isset($_POST["constancia"])) {
    	if(!isset($_POST["user"]) || !$_POST["user"]) {
    		error("Error, no se puede determinar al usuario");
    	}
    	
    	if(!isset($_POST["course"]) || !$_POST["course"]) {
    		error("Error, no se puede determinar el curso");
    	}
		
    	// Ludwick: obtener los datos del egresado
    	$userid = $_POST["user"];
    	if(!$user = get_record("user", "id", $userid)){
			error("No se han podido obtener los datos del usuario");
		}
		
		// Ludwick: obtener los datos del curso del egresado
    	$courseid = $_POST["course"];
    	if (! $course = get_record("course", "id", $courseid)) {
    		error("No se han podido obtener los datos del curso");
   		}
		
		//RUDY: obtener fecha concluido y folio. 150812
		$groups_members = get_record_sql("SELECT mgm.id, mgm.fecha_concluido
									FROM {$CFG->prefix}groups_members mgm INNER JOIN {$CFG->prefix}groups_courses_groups mgcg ON mgm.groupid = mgcg.groupid
									WHERE mgm.userid = $userid AND mgcg.courseid = $courseid");		
    	if(!$groups_members){
			error("No se han podido obtener los datos de conclusion (fecha o folio)");
		}
		
		//RUDY: obtener array de registros de autoevaluaciones. 150812
		$quizes = get_records_sql("SELECT mqg.quiz, mqg.grade, mq.name
									FROM {$CFG->prefix}quiz_grades mqg JOIN {$CFG->prefix}quiz mq ON mqg.quiz = mq.id
									WHERE mqg.userid = $userid AND mq.course = $courseid
									ORDER BY mqg.quiz");

   		$usuario = new object();
   		$usuario->id = $user->id;
   		$usuario->nombre = strtoupper($user->firstname);
   		$usuario->appaterno = strtoupper($user->lastname);
   		$usuario->apmaterno = strtoupper($user->icq);
   		$usuario->nombre_completo = $usuario->nombre." ".$usuario->appaterno." ".$usuario->apmaterno;
   		$usuario->rfe = $user->idnumber;
   		$usuario->curso = $course->fullname;
   		//$usuario->fecha = $groups_members->fecha_concluido;
   		$usuario->folio = $groups_members->id;
		$usuario->quizes = $quizes;
   		
   		$fecha = date("d-m-Y", $groups_members->fecha_concluido);
   		$fragmentos = split("-", $fecha);
   		
   		$meses = array(1=>"Enero", 2=>"Febrero", 3=>"Marzo", 4=>"Abril", 5=>"Mayo", 6=>"Junio", 7=>"Julio", 8=>"Agosto", 9=>"Septiembre", 10=>"Octubre", 11=>"Noviembre", 12=>"Diciembre");
   		$usuario->fecha = $fragmentos[0]." - ".$meses[(int)$fragmentos[1]]." - ".$fragmentos[2];
   		//print_object($usuario);
   		crear_constancia($usuario);
   	} else {
    	error("Error, no se puede determinar su acci&oacute;n");
    }
?>
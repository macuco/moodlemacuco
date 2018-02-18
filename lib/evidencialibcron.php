<?php  // $Id: evidencialib.php,v 1.221.2.45 2014/03/27 14:08:25 rudy Exp $

    require_once("/opt/lampp/htdocs/PuelProduccion/implantacion2a/config.php");
    require_once("/opt/lampp/htdocs/PuelProduccion/implantacion2a/lib/accesslib.php");
    require_once("/opt/lampp/htdocs/PuelProduccion/implantacion2a/lib/locallib.php");
    //require_once("deprecatedlib.php");
	
echo "Comienza proceso: <br>";
	

	for($i = 1; $i <= 32; $i++){
	  
	// Obtiene todos los educandos concluidos despues del 25/3/14
	$records = get_records_sql("SELECT mgm.id, mgm.groupid, mgm.userid, mgcg.courseid, mu.instituto FROM mdl_groups_members mgm INNER JOIN mdl_groups_courses_groups mgcg
	ON mgm.groupid = mgcg.groupid INNER JOIN mdl_user mu ON mgm.userid = mu.id WHERE concluido AND mgm.status_inc = 'S' = 1 AND mu.instituto = $i");
	// Original de Rudy, se modifima por DAVE para evitar que se suba varias veces la evidencia y se convierta en error 21
	// para subir evidencia con modificacion de asesor o alta en sasa cambiar a S en la tabla mgm en status_inc
	// ON mgm.groupid = mgcg.groupid INNER JOIN mdl_user mu ON mgm.userid = mu.id WHERE concluido = 1 AND fecha_concluido >=1395772094 AND mgm.status_inc != 'I' AND mu.instituto = $i");
	

	//print_object($records);
	
		 echo "EVIDENCIA PARA ENTIDAD ".$i.": <br><br>";

	
		  foreach($records as $record){
			  $evidencia = evidencia_sasa($record->userid, $record->courseid);
			  print_object($evidencia);
	  
	  
					  // Ingresa resultados a tablas groups_members e inea_concluidos
						  $mensaje_sasa = '';
						  switch($evidencia[idrespuesta]){ //Linea bien
						  //switch(99){ //Linea de prueba
							  case '10': 
								$mensaje_sasa = get_string('messagesasa10'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido],'idnumber',$USER->idnumber);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido],'idnumber',$USER->idnumber);
								break;
							  case '11': 
								$mensaje_sasa = get_string('messagesasa11'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido],'idnumber',$USER->idnumber);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido],'idnumber',$USER->idnumber);
								break;
							  case '12': 
								$mensaje_sasa = get_string('messagesasa12'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido],'idnumber',$USER->idnumber);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido],'idnumber',$USER->idnumber);
								break;
							  case '13': 
								$mensaje_sasa = get_string('messagesasa13'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido],'idnumber',$USER->idnumber);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido],'idnumber',$USER->idnumber);
								break;
							  case '14': 
								$mensaje_sasa = get_string('messagesasa14'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido]);
								break;
							  case '15': 
								$mensaje_sasa = get_string('messagesasa15'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido]);
								break;
							  case '16': 
								$mensaje_sasa = get_string('messagesasa16'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido]);
								break;
							  case '17': 
								$mensaje_sasa = get_string('messagesasa17'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido]);
								break;
							  case '18': 
								$mensaje_sasa = get_string('messagesasa18'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido]);
								break;
							  case '19': 
								$mensaje_sasa = get_string('messagesasa19'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido]);
								break;
							  case '20': 
								$mensaje_sasa = get_string('messagesasa20'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido]);
								break;
							  case '21': 
								$mensaje_sasa = get_string('messagesasa21'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido]);
								break;
							  case '22': 
								$mensaje_sasa = get_string('messagesasa22'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido]);
								break;
							  case '23': 
								$mensaje_sasa = get_string('messagesasa23'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido]);
								break;
							  case '99': 
								$mensaje_sasa = get_string('messagesasa99'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido]);
								break;
							  case 'default': 
								$mensaje_sasa = get_string('messagesasadefault'); 
								set_field('groups_members','status_sasa', $evidencia[idrespuesta],'id', $evidencia[id_user_concluido]);
								set_field('groups_members','status_inc', $evidencia[ccveestado],'id', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_sasa', $evidencia[idrespuesta],'id_groups_members', $evidencia[id_user_concluido]);
								set_field('inea_concluidos','status_inc', $evidencia[ccveestado],'id_groups_members', $evidencia[id_user_concluido]);
								break;
						  } // llave de cierree del switch
		  } // llave de cierree del foreach
	} // llave de cierree del for
	

?>
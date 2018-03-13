<?php  // $Id: evidencialib.php,v 1.221.2.45 2014/03/27 14:08:25 rudy Exp $

require_once('../config.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->libdir . '/locallib.php');
require_once($CFG->dirroot . '/mod/inea/inealib.php');
	
echo "Comienza proceso: <br>";

for($i = 1; $i <= 32; $i++) { // Por cada id de entidad federativa
  
	// Obtiene todos los educandos concluidos despues del 25/3/14}
	// Original de Rudy, se modifima por DAVE para evitar que se suba varias veces la evidencia y se convierta en error 21
		// para subir evidencia con modificacion de asesor o alta en sasa cambiar a S en la tabla mgm en status_inc
		// ON mgm.groupid = mgcg.groupid INNER JOIN mdl_user mu ON mgm.userid = mu.id WHERE concluido = 1 AND fecha_concluido >=1395772094 AND mgm.status_inc != 'I' AND mu.instituto = $i");
	$sql = "SELECT gm.id, gm.groupid, gm.userid, g.courseid, u.instituto 
		FROM {groups_members} gm 
		INNER JOIN {groups} g ON gm.groupid = g.id 
		INNER JOIN {user} u ON gm.userid = u.id 
		WHERE gm.concluido = 1
			AND gm.status_inc = 'S' 
			AND u.instituto = ?";

	if($concluidos = $DB->get_records_sql($sql, array($i))) {
		//print_object($concluidos);
		echo "EVIDENCIA PARA ENTIDAD ".$i.": <br><br>";
		
		foreach($concluidos as $concluido){
			$evidencia = inea_evidencia_sasa($concluido->userid, $concluido->courseid);
			print_object($evidencia);
			// Ingresa resultados a tablas groups_members e inea_concluidos
			$mensaje_sasa = '';
			$params_conluidos = array('id_groups_members' => $evidencia["id_user_concluido"]);
			
			switch($evidencia["idrespuesta"]){ //Linea bien
				case '10': $mensaje_sasa = get_string('messagesasa10', 'inea'); $params_conluidos = array('id_groups_members' => $evidencia["id_user_concluido"], 'idnumber' => $USER->idnumber); break;
				case '11': $mensaje_sasa = get_string('messagesasa11', 'inea'); $params_conluidos = array('id_groups_members' => $evidencia["id_user_concluido"], 'idnumber' => $USER->idnumber); break;
				case '12': $mensaje_sasa = get_string('messagesasa12', 'inea'); $params_conluidos = array('id_groups_members' => $evidencia["id_user_concluido"], 'idnumber' => $USER->idnumber); break;
				case '13': $mensaje_sasa = get_string('messagesasa13', 'inea'); $params_conluidos = array('id_groups_members' => $evidencia["id_user_concluido"], 'idnumber' => $USER->idnumber); break;
				case '14': $mensaje_sasa = get_string('messagesasa14', 'inea'); break;
				case '15': $mensaje_sasa = get_string('messagesasa15', 'inea'); break;
				case '16': $mensaje_sasa = get_string('messagesasa16', 'inea'); break;
				case '17': $mensaje_sasa = get_string('messagesasa17', 'inea'); break;
				case '18': $mensaje_sasa = get_string('messagesasa18', 'inea'); break;
				case '19': $mensaje_sasa = get_string('messagesasa19', 'inea'); break;
				case '20': $mensaje_sasa = get_string('messagesasa20', 'inea'); break;
				case '21': $mensaje_sasa = get_string('messagesasa21', 'inea'); break;
				case '22': $mensaje_sasa = get_string('messagesasa22', 'inea'); break;
				case '23': $mensaje_sasa = get_string('messagesasa23', 'inea'); break;
				case '99': $mensaje_sasa = get_string('messagesasa99', 'inea'); break;
				case 'default': $mensaje_sasa = get_string('messagesasadefault', 'inea'); break;
			} // llave de cierree del switch
			
			$gm_campos = array(
				'status_sasa' => $evidencia["idrespuesta"],
				'status_inc' => $evidencia["ccveestado"]);
			
			foreach($gm_campos as $campo => $valor) {
				//Acualizamos valores en tabla groups_members
				$DB->set_field('groups_members', $campo, $valor, array('id' => $evidencia["id_user_concluido"]);
					
				//Acualizamos valores en tabla inea_concluidos
				$DB->set_field('inea_concluidos', $campo, $valor, $params_conluidos);
			}
		} // llave de cierree del foreach
	} // if
} // llave de cierree del for
?>
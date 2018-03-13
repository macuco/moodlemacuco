<?php  // $Id: examenlib.php,v 1.221.2.45 2014/03/31 14:08:25 rudy Exp $
require_once('../config.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->libdir . '/locallib.php');
require_once($CFG->dirroot . '/mod/inea/inealib.php');
	
echo "Comienza proceso: <br>";

for($i = 1; $i <= 32; $i++) { // Por cada id de entidad federativa
  
// Obtiene todos los educandos concluidos y registrados como tal en SASA, despues del 25/3/14. NOTA: el primer campo de la consulta debe ser mgm.id  y no grupo ni userid, de lo contrario cuando existe grupo o usuario repetido solo se toma un registro ya que el primer campo siempre es la key del array
// $records = get_records_sql("SELECT mgm.groupid, mgm.userid FROM mdl_groups_members mgm INNER JOIN mdl_user mu ON mgm.userid = mu.id WHERE concluido = 1 AND fecha_concluido >=1395772094 AND (mgm.status_inc = 'I') AND (mgm.status_calif_sasa IS NULL OR mgm.status_calif_sasa = 0 OR mgm.calificacion = 5) AND mu.instituto = $i");

	$sql = "SELECT gm.id, gm.groupid, gm.userid
		FROM {groups_members} gm
		INNER JOIN {user} u ON gm.userid = u.id
		WHERE gm.concluido = 1 
			AND gm.fecha_concluido >= 1395772094 
			AND gm.status_inc = 'I' 
			AND (gm.calificacion IS NULL OR gm.calificacion = 5) 
			AND u.instituto = ?";
			
	if($concluidos = $DB->get_records_sql($sql, array($i))) {
		//print_object($records);
		echo "CALIFICACIONES PARA ENTIDAD: ".$i.": <br><br>";
		
		foreach($concluidos as $concluido) {
			$calificacion = calificacion_sasa_cron($concluido->userid, $concluido->groupid);
			//echo "Usuario (u.id): ".$concluido->userid;
			//echo "Grupo (gm.groupid): ".$concluido->groupid."<br>";
		
			// Ingresa resultados a tablas groups_members e inea_concluidos
			$mensaje_sasa = '';
			switch($calificacion["idrespuesta"]){ //Linea bien
				case '10': $mensaje_sasa = get_string('messagesasa10', 'inea'); break;
				case '11': $mensaje_sasa = get_string('messagesasa11', 'inea'); break;
				case '12': $mensaje_sasa = get_string('messagesasa12', 'inea'); break;
				case '13': $mensaje_sasa = get_string('messagesasa13', 'inea'); break;
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

			if($calificacion["icvesituacion"] != -1) {		  
				$f_aplica = $calificacion["fAplica"];
				$parts_f_aplica = explode('/', $f_aplica);
				$new_f_aplica = "{$parts_f_aplica[2]}-{$parts_f_aplica[1]}-{$parts_f_aplica[0]}";						  
				//echo $new_f_aplica."<br>";
				$f_aplica_int = strtotime($new_f_aplica);
				//echo $f_aplica_int."<br>";

				$gm_campos = array(
					'status_calif_sasa' => $calificacion["icvesituacion"],
					'calificacion' => $calificacion["Calificacion"],
					'fecha_aplicacion' => $new_f_aplica);
				
				foreach($gm_campos as $campo => $valor) {
					//Acualizamos valores en tabla groups_members
					$DB->set_field('groups_members', $campo, $valor, array('id' => $calificacion["id_user_concluido"]);
					
					//Acualizamos valores en tabla inea_concluidos
					$DB->set_field('inea_concluidos', $campo, $valor, array('id_groups_members' => $calificacion["id_user_concluido"]));
				}
				
				//Acualizamos valores en tabla groups_members para marcar concluido
				if($calificacion["Calificacion"] >= 6){
					$DB->set_field('groups_members', 'acreditado', 1, array('id' => $calificacion["id_user_concluido"]));
					$DB->set_field('groups_members', 'fecha_acreditado', $f_aplica_int, array('id' => $calificacion["id_user_concluido"]));
				}
			}
		}// llave de cierree del foreach		  
	}  
} // llave de cierree del for
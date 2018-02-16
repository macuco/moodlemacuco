<?php  // $Id: examenlib.php,v 1.221.2.45 2014/03/31 14:08:25 rudy Exp $
require_once("../config.php");
require_once($CFG->libdir . "/accesslib.php");
require_once($CFG->libdir . "/locallib.php");
	
echo "Comienza proceso";

for($i = 1; $i <= 32; $i++) { // Por cada id de entidad federativa
  
// Obtiene todos los educandos concluidos y registrados como tal en SASA, despues del 25/3/14. NOTA: el primer campo de la consulta debe ser mgm.id  y no grupo ni userid, de lo contrario cuando existe grupo o usuario repetido solo se toma un registro ya que el primer campo siempre es la key del array
// $records = get_records_sql("SELECT mgm.groupid, mgm.userid FROM mdl_groups_members mgm INNER JOIN mdl_user mu ON mgm.userid = mu.id WHERE concluido = 1 AND fecha_concluido >=1395772094 AND (mgm.status_inc = 'I') AND (mgm.status_calif_sasa IS NULL OR mgm.status_calif_sasa = 0 OR mgm.calificacion = 5) AND mu.instituto = $i");

	$sql = "SELECT gm.id, gm.groupid, gm.userid
		FROM {groups_members} gm
		INNER JOIN {user} u ON gm.userid = u.id
		WHERE gm.concluido = 1 
			AND gm.fecha_concluido >= 1395772094 
			AND (gm.status_inc = 'I') 
			AND (gm.calificacion IS NULL OR gm.calificacion = 5) AND u.instituto = ?";
			
	if($concluidos = $DB->get_records_sql($sql, array($i))) {
		//print_object($records);
		echo "CALIFICACIONES PARA ENTIDAD: ".$i.": <br><br>";
		foreach($concluidos as $concluido) {
			$calificacion = calificacion_sasa_cron($concluido->userid, $concluido->groupid);
			//echo "Usuario (u.id): ".$concluido->userid;
			//echo "Grupo (gm.groupid): ".$concluido->groupid."<br>";
		
			// Ingresa resultados a tablas groups_members e inea_concluidos
			$mensaje_sasa = '';
			switch($calificacion[idrespuesta]){ //Linea bien
				case '10': $mensaje_sasa = get_string('messagesasa10'); break;
				case '11': $mensaje_sasa = get_string('messagesasa11'); break;
				case '12': $mensaje_sasa = get_string('messagesasa12'); break;
				case '13': $mensaje_sasa = get_string('messagesasa13'); break;
				case '14': $mensaje_sasa = get_string('messagesasa14'); break;
				case '15': $mensaje_sasa = get_string('messagesasa15'); break;
				case '16': $mensaje_sasa = get_string('messagesasa16'); break;
				case '17': $mensaje_sasa = get_string('messagesasa17'); break;
				case '18': $mensaje_sasa = get_string('messagesasa18'); break;
				case '19': $mensaje_sasa = get_string('messagesasa19'); break;
				case '20': $mensaje_sasa = get_string('messagesasa20'); break;
				case '21': $mensaje_sasa = get_string('messagesasa21'); break;
				case '22': $mensaje_sasa = get_string('messagesasa22'); break;
				case '23': $mensaje_sasa = get_string('messagesasa23'); break;
				case '99': $mensaje_sasa = get_string('messagesasa99'); break;
				case 'default': $mensaje_sasa = get_string('messagesasadefault'); break;
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
					'fecha_aplicacion' => $new_f_aplica,
				)
				
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

function calificacion_sasa_cron($id_user="", $id_group="") {
	global $CFG, $DB;
			
	$qry1 = "SELECT mu.instituto, mu.zona, mu.idnumber AS rfe, mu.id_sasa, mu.icvemodesume, mgm.fecha_concluido, mgm.id, mgm.groupid, mc.idnumber, mc.idnumber_1014 FROM mdl_user mu INNER JOIN mdl_groups_members mgm ON mu.id = mgm.userid INNER JOIN mdl_groups_courses_groups mgcg ON mgm.groupid = mgcg.groupid INNER JOIN mdl_course mc ON mgcg.courseid = mc.id WHERE mu.id = ".$id_user." AND mgcg.groupid = ".$id_group;
	
	//echo $qry1;
	$result_conn = mysql_query($qry1);

    	$row=mysql_fetch_array($result_conn); 
       
		$id_sasa = $row['id_sasa'];
       	//$rfe_e= $row['rfe'];  
       	$entidad= $row['instituto'];
       	//$cz= $row['zona'];
       	$icvemodulo= $row['icvemodesume'] == 10 ? $row['idnumber'] : $row['idnumber_1014'];	// RUDY: Si el modelo es 10 (MOL) entonces toma clave de MOL si no toma clave de 10-14
		//$f_concluido = date('d/m/Y',$row['fecha_concluido']);
		$grupo = $row['groupid'];	
		//$rfc_a = obtener_rfc_asesor_grupo($grupo); // funcion definida en lib/accesslib.php
		$id_user_concluido = $row['id'];	
	

	$qry3 = "SELECT nombre, base, usuario, pass FROM mdl_inea_sasa_conn WHERE instituto = ".$entidad;

	//echo "<br>".$qry3. "<---- consulta sql server  "; 

	$result_conn = mysql_query($qry3);

    	$row=mysql_fetch_array($result_conn); 
       
       	$nombre= $row['nombre'];  
		$base = $row['base'];
		$usuario= $row['usuario'];
		$pass= $row['pass'];
	
    $conectID = mssql_connect("$nombre","$usuario","$pass");
    mssql_select_db("$base");


	$qry2 = "EXEC mv_getCalificacionEducandoModulo ".$id_sasa.",".$icvemodulo; //

	 //echo "<br>".$qry2. "<---- consulta para SQL   "; 
 

   	$result_usu = mssql_query($qry2);

	$calificacion = mssql_fetch_array($result_usu);

	//$estatus = $evidencia['ccveestado'];
	//echo "<br> Estatus: ".$estatus;

	 //echo "<br>".$evidencia . "<---- Evidencia   ";
	 
	
	//RUDY: Adjuntar el campo id de mdl_groups_members a la matriz devuelta, ya q lo necesitamos en mod/quiz/view.php
	$calificacion['id_user_concluido'] = $id_user_concluido;

	 print_object($calificacion);

	mssql_close($conectID);
	
	return $calificacion;

}
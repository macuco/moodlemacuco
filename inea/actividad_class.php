<?php
require_once($CFG->dirroot.'/lib/dmllib.php');
class actividad {

	function guarda($datos,$ineaid, $usuarioid, $path_archivo) {
		$titulo =   $datos['titulo'];
		if (empty($titulo))
		$titulo .= $path_archivo;

		$tbl_resp_ti = date('Y-m-d G:i:s T',$datos['start_time']);

		$arreglo = addslashes(serialize($datos));

		$obj = get_record_select('inea_answers', "ineaid=$ineaid and userid=$usuarioid and url='$path_archivo' and swf IS NULL");

		if ($obj) {
			$tbl_state = $obj->state;
			$actualizar = true;
		} else {
			$obj = new object();
			$obj->ineaid = $ineaid;
			$obj->userid = $usuarioid;
			$obj->url = $path_archivo;
			$obj->title = $titulo;
			$actualizar = false;
		}
		$obj->value = $arreglo;
		$obj->timemodified = time();

		if ($actualizar) {
			$obj->state = 'Corregido';
			$obj->review = 'Sin revisar';
			update_record('inea_answers', $obj);
		} else {
			$obj->state = 'Sin correccion';
			$obj->review = 'Sin revisar';
			insert_record('inea_answers', $obj);
		}

		return "Actividad guardada correctamente!";
	}
	function usuario_ya_contesto_actividad($url,$ineaid, $usuarioid,$eshtml=0)
	{

		//*******************Obtiene ultima respuestas mas recientes**********
		if($eshtml==1)
			$r = get_field('inea_answers', 'value', 'userid', $usuarioid, 'url', $url, 'ineaid', $ineaid, 'swf','null');  
		else
			$r = get_field('inea_answers', 'value', 'userid', $usuarioid, 'url', $url, 'ineaid', $ineaid);  
		//get_field('inea_answers', 'value', 'id', $id);
		return $r;

		/*********************************************************************

		$sql  = "SELECT tbl_resp_arreglo";
		$sql .= " FROM $tbl_resp";
		$sql .= " WHERE tbl_resp_uri = '$tbl_resp_uri'";
		$sql .= " AND tbl_resp_tbl_usu_id = $tbl_resp_tbl_usu_id";
		$sql .= " AND tbl_resp_fecha_registro= '$fecha_max'";

		$result = $db->getOne($sql);

		$db->disconnect();
		return $result;*/

	}

}

?>

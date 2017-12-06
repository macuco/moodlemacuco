<?php
defined('MOODLE_INTERNAL') || die();

require_once('../config.php');
require_once $CFG->dirroot . '/mod/inea/inealib.php';

$id_instituto	= optional_param('id_instituto',1, PARAM_INT);
$todas      	= optional_param('todas',0, PARAM_INT);

header("Content-Type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8" ?>';
$zonas = inea_get_zonas($id_instituto);
echo '<select>';
	if($todas == 1){
		echo '<value>-1</value>';
		echo '<option> - - - Todas - - - </option>';	
	}
	foreach ($zonas as $zona) {
		echo '<value>'.$zona->icvecz.'</value>';
		echo '<option>'.format_string( $zona->cdescz ).'</option>';	
	}
echo '</select>';
exit;
?>
<?php
require_once('JSON.php');
// Future-friendly json_encode
if( !function_exists('json_encode') ) {
    function json_encode($data) {
        $json = new Services_JSON();
        return( $json->encode($data) );
    }
}

// Future-friendly json_decode
if( !function_exists('json_decode') ) {
    function json_decode($data) {
        $json = new Services_JSON();
        return( $json->decode($data) );
    }
}
//$a = json_encode( array('a'=>1, 'b'=>2, 'c'=>'I <3 JSON') );
//echo $a;
//phpinfo();exit;


require_once('../../../config.php');
@header('Content-type: text/html; charset=utf-8');

/**
 * **** Estructura de respuesta  ******
 * RESPONSE : {
 *  error : [true|false],
 *  dataError : String,
 *  contestado : [true|false],
 *  datos : <DATOS>
 * }
 *
 * DATOS: Array<EJERCICIO>
 *
 * EJERCICIO : {
 *  id_ejercicio : String,
 *  tipo : String,
 *  respuestas : Array<DATOSRESPUESTA>
 * }
 *
 * DATOSRESPUESTAS : {
 *   pregunta : int,
 *   datos : <RESPUESTA>,
 *   retroalimentacion : int(% obtenido),
 *   timemodified : date
 *   id : int(id del registro que se obtubo la info)
 * }
 *
 * RESPUESTA : {
 *   name_pregunta*:valor*   //ej: U1-T1-A1-E5__respuesta1__3:"f"
 * }
 *
 */

//TODO falta definir correctamente los errores

$contestado = optional_param('contestado', 0, PARAM_INT); //Parametro opcional para verificar si ya contesto o no algun ejercicio
$courseid = optional_param('courseid', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$id_ejercicio = optional_param('id_ejercicio', '', PARAM_TEXT);
$pregunta = optional_param('pregunta', -1, PARAM_INT);
$tipo = optional_param('tipo', '', PARAM_TEXT);
$rol = optional_param('rol', 5, PARAM_INT);


$response = new stdClass();
$response->error = false;
$response->dataError = "";
$response->contestado = false;

// ---- OBTENGO LA URL DEL ARCHIVO ---------------
$id_archivo = $_SERVER['HTTP_REFERER'];
//$id_archivo = 'http://localhost/mevyt/moodle/pluginfile.php/31/mod_inea/content/2/B2MFM/contenidos/u_1/u1_act1.html';
//inea.php/28,35,177/B2MFM/index2.html
$id_archivo = explode("/pluginfile.php/", $id_archivo); // Separando la ruta del archivo
$id_archivo_curso = count($id_archivo) > 0 ? $id_archivo[1] : $id_archivo[0]; // Obteniendo la parte final de la ruta
//$args = explode("/", $id_archivo_curso); // Separando por diagonal los valores obtenidos
$args = explode('/', ltrim($id_archivo_curso, '/'));
if (count($args) < 3) { // always at least context, component and filearea
    print_error('invalidarguments');
}
$contextid = (int)array_shift($args);
$component = clean_param(array_shift($args), PARAM_COMPONENT);
$filearea  = clean_param(array_shift($args), PARAM_AREA);

list($context, $course, $cm) = get_context_info_array($contextid);

$resource = $DB->get_record('inea', array('id'=>$cm->instance), '*', MUST_EXIST);

array_shift($args); // ignore revision - designed to prevent caching problems only
$fs = get_file_storage();
$relativepath = implode('/', $args);

$relativepath = explode("#", $relativepath);
$relativepath = $relativepath[0];
$relativepath = explode("?", $relativepath);
$relativepath = $relativepath[0];

$fullpath = rtrim("/$context->id/mod_inea/$filearea/0/$relativepath", '/');

$file = $fs->get_file_by_hash(sha1($fullpath));
//$exist = $fs->file_exists_by_hash(sha1($fullpath));
//print_object($exist);exit();

//$tmp = explode(",", $tmp[0]); // Separando por comas los valores
//$id_curso = $tmp[1]; // Obteniendo el id del curso

//--- Quitar los parametros que lleve de mas


// -----------------------------------------------

$dataerror = "";
/* verifico que el archivo exista */
/*$f = $CFG->dataroot . "/$courseid/" . $id_archivo;
if (is_dir($f)) {
    if (file_exists($f . '/index.html')) {
        $f = rtrim($f, '/') . '/index.html';
    } else if (file_exists($f . '/index.htm')) {
        $f = rtrim($f, '/') . '/index.htm';
    } else if (file_exists($f . '/Default.htm')) {
        $f = rtrim($f, '/') . '/Default.htm';
    } else {
        $response->dataError = "El archivo ($f) no existe";
        $response->error = true;
    }
} else if (!file_exists($f)) {
    $response->dataError = "El archivo no existe: ".$f;
    $response->error = true;
}*/
//print_object($fs->get_file_by_hash(sha1($fullpath)));
if( ! $fs->file_exists_by_hash( sha1($fullpath) ) ){
    $response->dataError = "El archivo no existe: ".$f;
    $response->error = true;
}

if ($response->error) {
    echo json_encode($response);
    exit;
}
// ----------------------------------------------
//print_object($relativepath);exit();

if ($USER->id)
    $userid = $USER->id;

if($userid==1){//PARA NO GUARDAR O RECUPERE IINFORMACION EL INVITADO. 07/03/2011
	$response->contestado = false;
         echo json_encode($response);
	exit;
}
global $DB;

if ($contestado) {
		
            $where = ' courseid=' . $courseid ." and url='".$relativepath."'";
            //$where = "userid=" . $userid . ' and courseid=' . $courseid ."' and url='".$id_archivo."'";
            //print_object(function_exists('get_records_select'));
            $ejercicios = $DB->get_records_select('inea_ejercicios', $where );
            //print_object($ejercicios);exit();
            if($ejercicios && $userid){
                $datos = array();
                foreach($ejercicios as $id=>$ejercicio){
                    $respuestasa = $DB->get_records_select('inea_respuestas', "ejercicios_id=".$id." AND userid=".$userid );
                    if($respuestasa){
                        $respuestas = array();
                        foreach($respuestasa as $respuesta){
                            $respuesta->datos;
                            $tmp = json_decode($respuesta->datos);
                            foreach($tmp as $key=>$value){//Para quitar las diagonales que pone de más al guardar los datos
				$tmp->$key = stripslashes($value);
                            }
                            $respuesta->datos = $tmp;
                            $respuestas[] = $respuesta;
                        }
                        $obj = new stdClass();
                        $obj->id_ejercicio = $ejercicio->id_ejercicio;
                        $obj->tipo = $ejercicio->tipo;
                        $obj->respuestas = $respuestas;
                        $datos[] = $obj;
                    }
                }
                $response->contestado = true;
                $response->datos = $datos;
                echo json_encode($response);
            }else{
                $response->contestado = false;
                echo json_encode($response);
            }
            exit;
}


// ---- PARA GUARDAR Y RECUPERAR LOS DATOS DEL EJERCICIO ---------
if ($id_ejercicio && $courseid && $pregunta>-1) {
    $where = ' courseid=' . $courseid . " and id_ejercicio='".$id_ejercicio."' and url='".$relativepath."'";
    $ejercicio = $DB->get_record_select('inea_ejercicios', $where );
   
    //print_object($ejercicio);exit();
    //if ($ejercicio == false) { // RUDY: Linea original, se cambia por la siguiente para evitar nuevos guardados de definiciones de acts. 9/7/14
	if (false) {
        $post = (Object) $_GET;
        $post->userid = $userid;
        $post->url = $relativepath;
        $datos = split("-",$post->id_ejercicio);
        
       /* if(count($datos)<4){
            $response->dataError = "El formato del ejercicio no es correcto: \n".$post->id_ejercicio;
            $response->error = true;
            die(json_encode($response));
        }*/
        
        $unidad = split("U",$datos[0]);
        if(count($unidad)<2){
            $response->dataError = "El formato del ejercicio no es correcto: \n".$post->id_ejercicio;
            $response->error = true;
            die(json_encode($response));
        }
        
        $post->unidad = $unidad[1];
        $id = $DB->insert_record('inea_ejercicios', $post,true);
        $ejercicio = $DB->get_record_select('inea_ejercicios', $where );
        //print_object($post);
    }
    
    $respuestas = $DB->get_record_select('inea_respuestas', "ejercicios_id=".$ejercicio->id." and pregunta=".$pregunta." AND userid=".$userid );
   
    if($respuestas == false){
        $post = (Object) $_GET;
        $post->ejercicios_id = $ejercicio->id;
        $post->datos = json_encode($_POST);
        $post->extra = "";
        $DB->insert_record('inea_respuestas', $post,false);
        echo json_encode($post);
    }else {
            $post = (Object) $_GET;
            
            $respuestas->datos = json_encode($_POST);
            $respuestas->retroalimentacion = $post->retroalimentacion;
            unset ($respuestas->timemodified);
            //print_object($respuestas);exit();
            $DB->update_record('inea_respuestas', $respuestas);
            echo json_encode($respuestas);
    }
    exit;
}
$response->error = true;
$response->dataError = "Servicio no definido: \n".  print_r($object,true);
echo json_encode($response);
?>

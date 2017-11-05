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

$contestado = optional_param('contestado', 0); //Parametro opcional para verificar si ya contesto o no algun ejercicio
$courseid = optional_param('courseid', false);
$userid = optional_param('userid', false);
$id_ejercicio = optional_param('id_ejercicio', false);
$pregunta = optional_param('pregunta', -1);
$tipo = optional_param('tipo', false);
$rol = optional_param('rol', 5);


$response = new object();
$response->error = false;
$response->dataError = "";
$response->contestado = false;

// ---- OBTENGO LA URL DEL ARCHIVO ---------------
$id_archivo = $_SERVER['HTTP_REFERER'];

$id_archivo = explode("/inea.php/", $id_archivo); // Separando la ruta del archivo
$id_archivo_curso = count($id_archivo) > 0 ? $id_archivo[1] : $id_archivo[0]; // Obteniendo la parte final de la ruta
$tmp = explode("/", $id_archivo_curso); // Separando por diagonal los valores obtenidos
for ($i = 1; $i < count($tmp); $i++) // Recorriendo para armar la cadena de id archivo correcta
    $id_archivo_tmp .= $tmp[$i] . "/";
$id_archivo = substr($id_archivo_tmp, 0, strlen($id_archivo_tmp) - 1); // Eliminando el ultimo _ dado por el ciclo
$tmp = explode(",", $tmp[0]); // Separando por comas los valores
$id_curso = $tmp[1]; // Obteniendo el id del curso

$id_archivo = explode("#", $id_archivo);
$id_archivo = $id_archivo[0];
$id_archivo = explode("?", $id_archivo);
$id_archivo = $id_archivo[0];

// -----------------------------------------------

$dataerror = "";
/* verifico que el archivo exista */
$f = $CFG->dataroot . "/$courseid/" . $id_archivo;
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
}

if ($response->error) {
    echo json_encode($response);
    exit;
}
// ----------------------------------------------


if ($USER->id)
    $userid = $USER->id;

if($userid==1){//PARA NO GUARDAR O RECUPERE IINFORMACION EL INVITADO. 07/03/2011
	$response->contestado = false;
         echo json_encode($response);
	exit;
}
if ($contestado) {
		
            $where = ' courseid=' . $courseid ." and url='".$id_archivo."'";
            //$where = "userid=" . $userid . ' and courseid=' . $courseid ."' and url='".$id_archivo."'";
            $ejercicios = get_records_select('inea_ejercicios', $where );
            if($ejercicios && $userid){
                $datos = array();
                foreach($ejercicios as $id=>$ejercicio){
                    $respuestasa = get_records_select('inea_respuestas', "ejercicios_id=".$id." AND userid=".$userid );
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
                        $obj = new object();
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
    $where = ' courseid=' . $courseid . " and id_ejercicio='".$id_ejercicio."' and url='".$id_archivo."'";
    $ejercicio = get_record_select('inea_ejercicios', $where );
    
    if ($ejercicio == false) {
        $post = (Object) $_GET;
        $post->userid = $userid;
        $post->url = $id_archivo;
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
        $id = insert_record('inea_ejercicios', $post,true);
        $ejercicio = get_record_select('inea_ejercicios', $where );
        print_object($post);
    }
    
    $respuestas = get_record_select('inea_respuestas', "ejercicios_id=".$ejercicio->id." and pregunta=".$pregunta." AND userid=".$userid );
    if($respuestas == false){
        $post = (Object) $_GET;
        $post->ejercicios_id = $ejercicio->id;
        $post->datos = mysql_real_escape_string(json_encode($_POST));
        insert_record('inea_respuestas', $post);
        echo json_encode($post);
    }else {
            $post = (Object) $_GET;
            print_object($_POST);
            $respuestas->datos = mysql_real_escape_string(json_encode($_POST));
            $respuestas->retroalimentacion = $post->retroalimentacion;
            unset ($respuestas->timemodified);
            update_record('inea_respuestas', $respuestas);
            echo json_encode($respuestas);
    }
    exit;
}
$response->error = true;
$response->dataError = "Servicio no definido: \n".  print_r($object,true);
echo json_encode($response);
?>

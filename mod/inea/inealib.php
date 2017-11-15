<?php
/**
 * @author macuco Juan Manuel Muñoz Pérez juan.manuel.mp8@gmail.com
 */

/**
 * inealib.php - libreria de funciones INEA
 *
 * Libreria de funciones generales para el funcionamiento de la plataforma INEA.
 * Otras librerias:
 *  - datalib.php     - funciones para acceder a la base de datos
 *
 * @package    inea
 * @subpackage lib
 * @copyright  2017 INEA MEVyt en Línea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * INEA - Obtiene una entidad o estado
 * @param int $id_pais
 * @param int $id_entidad
 * @return Object entidad
 */
function inea_get_instituto($id_instituto) {
    global $DB;
    
    return $DB->get_record('inea_instituto', array('icveie'=>$id_instituto));
}

/**
 * INEA - Obtiene una entidad o estado
 * @param int $id_pais
 * @param int $id_entidad
 * @return Object entidad
 */
function inea_get_entidad($id_pais, $id_entidad) {
    global $DB;
    
    return $DB->get_record('inea_entidad', array('icveentfed'=>$id_entidad, 'icvepais'=>$id_pais), 'id, icvepais, icveentfed, cdesentfed');
}

/**
 * INEA - Obtiene un municipio
 * @param int $id_pais
 * @param int $id_entidad
 * @param int $id_municipio El municipio a obtener.
 * @return Object entidad
 */
function inea_get_municipio($id_pais, $id_entidad, $id_municipio) {
    global $DB;
    
    return $DB->get_record('inea_municipios', array('icveentfed'=>$id_entidad,'icvepais'=>$id_pais, 'icvemunicipio'=>$id_municipio), 'id, icvepais, icveentfed, icvemunicipio, cdesmunicipio');
}

/**
 * INEA - Obtiene una plaza
 * @param int $id_plaza
 * @return Object plaza
 */
function inea_get_plaza($id_plaza) {
global $DB;

return $DB->get_record('inea_plazas', array('id'=>$id_plaza), 'id, icvepais, icveentfed, icvemunicipio, cnomplaza');
}

/**
 * INEA - Obtiene una zona
 * @param int $id_instituto
 * @param int $id_zona
 * @return Object zona
 */
function inea_get_zona($id_instituto, $id_zona) {
    global $DB;
    
    return $DB->get_record('inea_zona', array('icveie'=>$id_instituto, 'icvecz'=>$id_zona));
}

/**
 * INEA - Obtiene el nombre del modelo.
 * @param int $modelo
 * @return
 */
function inea_get_modelo($id_modelo) {
    global $DB;
    
    return $DB->get_record('inea_modelos', array('icvemodesume'=>$modelo), 'cdesmodelo');
}

/**
 * INEA - Obtiene el catalogo de ocupaciones.
 * @return object
 */
function inea_get_ocupaciones() {
    global $DB;
    
    return $DB->get_records_select('inea_ocupaciones', '', null, 'ASC', 'cdesocupacion');
}

/**
 * INEA - Obtiene el catalogo de entidades.
 * @return object
 */
function inea_get_entidades() {
    global $DB;
    
    return $DB->get_records_select('inea_entidad', '', null, '', 'id, icvepais, icveentfed, cdesentfed');
}

/**
 * INEA - Obtiene el catalogo de municipios.
 * @return object
 */
function inea_get_municipios() {
    global $DB;
    
    return $DB->get_records_select('inea_municipios', '', null, '', 'id, icvepais, icveentfed, icvemunicipio, cdesmunicipio');
}

/**
 * INEA - Obtiene el catalogo de plazas.
 * @return object
 */
function inea_get_plazas() {
    global $DB;
    
    return $DB->get_records_select('inea_plazas', '', null, '', 'id, icvepais, icveentfed, icvemunicipio, cnomplaza, ccveplaza');
}

/**
 * INEA - Obtiene el catalogo de zonas.
 * @return object
 */
function inea_get_zonas($id_instituto) {
    global $DB;
    
    return $DB->get_records_select('inea_zona', 'icveie = '.$id_instituto);
}

/**
 * INEA - Obtiene el id del estado, municipio  y pais de un usuario
 * @return object
 */
function inea_get_user_estado($id) {
    global $DB;
    
    return $DB->get_record('user', array('id'=>$id), 'institution, country, city, skype');
}

/**
 * INEA - Obtiene el nombre del grupo dado el ID
 * @return object
 */
function inea_get_nombre_grupo($id) {
    global $DB;
    
    $grupo = $DB->get_record('groups', array('id'=>$id), 'name');
    return $grupo->name;
}

/**
 * INEA - Funcion copiada del archivo admin/export_data/CreteCSV.class.php.
 * @param int $id_grupo
 * @return int
 */
function inea_obtener_rfc_asesor_grupo($id_grupo) { //Vhackero Funcion para obtener el tutor/tutores de un grupo
    $nombre_grupo = inea_get_nombre_grupo($id_grupo);
    $id_asesor = substr($nombre_grupo, strrpos($nombre_grupo, "_")+1);
    
    $asesor = inea_get_user_from_id($id_asesor);
    return (!empty($asesor))? $value = $asesor->idnumber: $value = "";
}

/**
 * INEA - Obtiene los datos de un usuario, si existe, a partir de su RFE.
 * @param String $rfe
 * @return Object USER si el RFE existe registrado.
 */
function inea_get_user_from_rfe($rfe) {
    global $DB;
    
    return $DB->get_record('user',array('idnumber'=>$rfe));
}

/**
 * INEA - Obtiene los datos de un usuario de la tabla INEA (usuario no confirmado), si existe, a partir de su RFE.
 * @param String $rfe
 * @return Object USER si el RFE existe registrado.
 */
function inea_get_inea_user_from_rfe($rfe) {
    global $DB;
    
    return $DB->get_record('inea_user', array('idnumber'=>$rfe));
}

/**
 * INEA - Obtiene los datos de un usuario, si existe, a partir de su id.
 * @param String $id
 * @return Object USER si el id de usuario existe.
 */
function inea_get_user_from_id($id) {
    global $DB;
    
    return $DB->get_record('user', array('id'=>$id));
}

/**
 * INEA - Obtiene una lista de Plazas comunitarias de acuerdo al municipio, estado y pais al que pertenece.
 * @param int $id_pais
 * @param int $id_entidad
 * @param int $id_municipio El municipio a obtener.
 * @return Object USER si el id de usuario existe.
 */
function inea_get_plaza_from_municipio() {
    global $DB;
    
    return $DB->get_records_select('inea_plazas', '', null, '', 'id, idplaza, ccveplaza, cnomplaza, icveie');
}

//--------------------------------------------------------------------------------

/*  ************** Funciones para MEVyt DAS ************** */
/**
 * INEA - Obtiene uno o varios registros de un catalogo SASA. // RUDY nov/2013
 *
 * @deprecated - Funcion personalizada.
 * @param String $table - El nombre de una tabla del catalogo
 * @param String $fields - Los campos a obtener de la tabla
 * @param String $condition - Una condicion para filtrar la consulta
 * @param boolean $all - Una condicion para obtener uno o todos los campos
 * @return array - un arreglo con el/los usuario(s)
 *
 */
function inea_get_record_sasa($entidad="", $rfe="") {
    $query = "SELECT nombre, base, usuario, pass FROM mdl_inea_sasa_conn WHERE instituto = ".$entidad;
    //echo "<br>".$qry3. "<---- consulta sql server  ";
    
    $result_conn = mysql_query($qry3);
    
    $row=mysql_fetch_array($result_conn);
    $nombre= $row['nombre'];
    $base = $row['base'];
    $usuario= $row['usuario'];
    $pass= $row['pass'];
    
    /*echo "<br>".$nombre. "<---- nombre server de sql   ";
     echo "<br>".$base. "<----- base";
     echo "<br>".$usuario. "<---- usuario de sql   ";
     echo "<br>".$pass. "<---- pass de sql   ";*/
    //mysql_close($conectID2);
    
    $conectID = mssql_connect("$nombre","$usuario","$pass");
    mssql_select_db("$base");
    
    $qry2 = "EXEC GetIdEducandoSASA '".$rfe."'";
    // echo "<br>".$qry2. "<---- consulta para SQL   ";
    
    $result_usu = mssql_query($qry2);
    $registro=mssql_fetch_array($result_usu);
    //print_object($registro);
    
    /*$ideducando = $registro['ideducando'];
     $idmodelo = $registro['icvemodelo'];
     $apPaterno = $registro['cpaterno'];
     $apMaterno = $registro['cmaterno'];
     $nombre = $registro['cnombre'];
     $fecha = $registro['cfecha'];
     $icveentfed = $registro['icveentfed'];
     $icvemunicipio = $registro['icvemunicipio'];
     $icvelocalidad = $registro['icvelocalidad'];
     $icvecz = $registro['icvecz'];
     
     echo "Resultado BD SASA <br>";
     echo "Id Educando ".$ideducando;
     echo "<br> ";
     echo "id Modelo ".$idmodelo;
     echo "<br> ";
     echo "Apellido Paterno ".$apPaterno;
     echo "<br> ";
     echo "Apellido Materno ".$apMaterno;
     echo "<br> ";
     echo "Nombre ".$nombre;
     echo "<br> ";
     echo "Fecha ".$fecha;
     echo "<br> ";
     echo "Entidad ".$icveentfed;
     echo "<br> ";
     echo "Municipio ".$icvemunicipio;
     echo "<br> ";
     echo "Localidad ".$icvelocalidad;
     echo "<br> ";
     echo "Zona ".$icvecz;*/
    
    //echo "<br>".$id_ideducando . "<---- resultado obtenido de sql server ";
    
    mssql_close($conectID);
    
    //print_object($registro);
    return $registro;
}

/**
 * INEA - Envia evidencia a SASA. // RUDY dic/2013
 *
 * @deprecated - Funcion personalizada.
 * @param String $table - El nombre de una tabla del catalogo
 * @param String $fields - Los campos a obtener de la tabla
 * @param String $condition - Una condicion para filtrar la consulta
 * @param boolean $all - Una condicion para obtener uno o todos los campos
 * @return array - un arreglo con el/los usuario(s)
 *
 */
function inea_evidencia_sasa($id_user="", $id_curso="") {
    
    $qry1 = "SELECT mu.instituto, mu.zona, mu.idnumber AS rfe, mu.id_sasa, mu.icvemodesume, mgm.fecha_concluido, mgm.id, mgm.groupid, mc.idnumber, mc.idnumber_1014 FROM mdl_user mu INNER JOIN mdl_groups_members mgm ON mu.id = mgm.userid INNER JOIN mdl_groups_courses_groups mgcg ON mgm.groupid = mgcg.groupid INNER JOIN mdl_course mc ON mgcg.courseid = mc.id WHERE mu.id = ".$id_user." AND mgcg.courseid = ".$id_curso;
    //echo $qry1;
    
    $result_conn = mysql_query($qry1);
    $row = mysql_fetch_array($result_conn);
    
    $id_sasa = $row['id_sasa'];
    $rfe_e= $row['rfe'];
    $entidad= $row['instituto'];
    $cz= $row['zona'];
    $icvemodulo= $row['icvemodesume'] == 10 ? $row['idnumber'] : $row['idnumber_1014'];	// RUDY: Si el modelo es 10 (MOL) entonces toma clave de MOL si no toma clave de 10-14
    $f_concluido = date('d/m/Y',$row['fecha_concluido']);
    $grupo = $row['groupid'];
    $rfc_a = obtener_rfc_asesor_grupo($grupo); ///!!!!!!
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
    
    $qry2 = "EXEC mv_SetEvidenciaEducandoModulo ".$id_sasa.",'".$rfe_e."',".$entidad.",".$cz.",".$icvemodulo.",'".$f_concluido."','".$rfc_a."'";
    //$qry2 = "EXEC mv_SetEvidenciaEducandoModulo 460418,'CAMJ980713ND5',8,1,64,'19/03/2014','AEAZ931110LC1'"; //
    
    //echo "<br>".$qry2. "<---- consulta para SQL   ";
    
    $result_usu = mssql_query($qry2);
    $evidencia = mssql_fetch_array($result_usu);
    
    //$estatus = $evidencia['ccveestado'];
    //echo "<br> Estatus: ".$estatus;
    
    //echo "<br>".$evidencia . "<---- Evidencia   ";
    //print_object($evidencia);
    
    //RUDY: Adjuntar el campo id de mdl_groups_members a la matriz devuelta, ya q lo necesitamos en mod/quiz/view.php
    $evidencia['id_user_concluido'] = $id_user_concluido;
    
    mssql_close($conectID);
    
    return $evidencia;
}

/*  ************** Funciones para MEVyt USA ************** */
/**
 * INEA - Obtiene uno o varios registros de un catalogo SASACE.
 *
 * @deprecated - Funcion personalizada.
 * @param String $table - El nombre de una tabla del catalogo
 * @param String $fields - Los campos a obtener de la tabla
 * @param String $condition - Una condicion para filtrar la consulta
 * @param boolean $all - Una condicion para obtener uno o todos los campos
 * @return array - un arreglo con el/los usuario(s)
 *
 */
function inea_get_record_sasace($table="", $fields="*", $condition="", $all=true) {
    global $DB;
    
    if(empty($table))
        return false;
        
        $select = "SELECT $fields ";
        $from = "FROM $table ";
        if(!empty($condition))
            $where = "WHERE $condition ";
            else
                $where = "";
                
                return ($all)? $DB->get_records_sql($select.$from.$where) : get_record_sql($select.$from.$where);
}

/**
 * INEA - Genera el RFE/RFC de un asesor en el catalogo SASACE.
 *
 * @deprecated - Funcion personalizada.
 * @return array $users - un arreglo con los usuarios
 *
 */
function inea_generate_rfe_teacher() {
    global $CFG;
    require_once($CFG->dirroot.'/login/genera_rfe.php');
    
    $teachers = inea_get_record_sasace("asesor", "CLAVEASESOR, NOMBRE, PATERNO, MATERNO, FECHANACIMIENTO", "CLAVEASESOR IS NOT NULL AND NOMBRE <> '' AND PATERNO <> '' AND MATERNO <> '' AND FECHANACIMIENTO <> '' ");
    foreach($teachers as $teacher) {
        $apPaterno 	= utf8_decode($teacher->PATERNO);
        $apMaterno 	= utf8_decode($teacher->MATERNO);
        $nombre 	= utf8_decode($teacher->NOMBRE);
        
        $teacher->RFE = GenerarLlaveRFE($apPaterno, $apMaterno, $nombre, $teacher->FECHANACIMIENTO);
        //echo "<br>".$teacher->CLAVEASESOR." ".$nombre." ".$apPaterno." ".$apMaterno." ".$teacher->FECHANACIMIENTO." ".$teacher->RFE;
        
        //print_object($teacher);
        $sql = "UPDATE asesor
                SET RFE='".$teacher->RFE."'
                WHERE CLAVEASESOR={$teacher->CLAVEASESOR}";
        if(!execute_sql($sql, false)) {
            //notify("El asesor $teacher->NOMBRE $teacher->PATERNO no ha sido actualizado.");
            \core\notification::info("El asesor $teacher->NOMBRE $teacher->PATERNO no ha sido actualizado.");
        }
    }
}

/**
 * INEA - Busca el RFE/RFC de un asesor dado en el catalogo SASACE.
 *
 * @deprecated - Funcion personalizada.
 * @param String $rfe - La cadena de texto con clave rfe a ser buscada en el catalogo
 * @return int $claveasesor - El id del asesor que corresponde al RFE
 *
 */
function inea_search_rfe_teacher($rfe) {
    global $CFG;
    
    $teachers_with_rfe = inea_get_record_sasace("asesor", "CLAVEASESOR, NOMBRE, PATERNO, MATERNO, FECHANACIMIENTO, RFE", "RFE <> '' ");
    $claveasesor = 0;
    foreach($teachers_with_rfe as $teacher) {
        if($teacher->RFE == $rfe) {
            $claveasesor = $teacher->CLAVEASESOR;
            break;
        }
    }
    
    return $claveasesor;
}



/*                 **************************************                */
/**
 * @autor macuco
 */
function add_category_profile(core_user\output\myprofile\tree $tree, $user, $course){
    
    complete_user_inea($user); //Completar el objeto usuario con los datos del inea
    
    $node = new core_user\output\myprofile\node('contact', 'rfe', get_string("rfe","inea"), null, null, $user->rfe);
    $tree->add_node($node);
    
    $node = new core_user\output\myprofile\node('contact', 'sexo', get_string("sexo","inea"), null, null, $user->sexo);
    $tree->add_node($node);
    
    //$node = new core_user\output\myprofile\node('ineadetails', 'prueba', "prueba", null, $url);
    $node = new core_user\output\myprofile\node('contact', 'estado', get_string("estado","inea"), null, null, getEntidadString($user->estado));
    $tree->add_node($node);
    
    $node = new core_user\output\myprofile\node('contact', 'municipio', get_string("municipio","inea"), null, null, getMunicipioString($user->estado, $user->municipio) );
    $tree->add_node($node);
    
    $node = new core_user\output\myprofile\node('contact', 'plaza', get_string("plaza","inea"), null, null, getPlazaString($user->plaza));
    $tree->add_node($node);
    
    $node = new core_user\output\myprofile\node('contact', 'ocupacion', get_string("ocupacion","inea"), null, null, getOcupacionString($user->ocupacion));
    $tree->add_node($node);
    
}

/**
 * Agrega al objeto usuario todos los campos que neceista el inea para trabajar
 * rfe
 * sexo
 * estado
 * municipio
 * plaza
 * ocupacion
 * @param unknown $user
 */
function complete_user_inea($user){
    global $DB;
    $data = $DB->get_record('inea_user', array('user_id'=>$user->id));
    unset($data->id);
    unset($data->user_id);
    if(!empty($data)){
        foreach ($data as $clave=>$valor){
            $user->$clave = $valor;
        }
    }else{
        $user->rfe='';
        $user->sexo='';
        $user->estado='';
        $user->municipio='';
        $user->plaza='';
        $user->ocupacion='';
    }
    
    $user->destado = getEntidadString($user->estado);
    
    $user->dmunicipio = getMunicipioString($user->estado, $user->municipio);
    
    $user->dplaza = getPlazaString($user->plaza);
    
    $user->docupacion = getOcupacionString($user->ocupacion);
}

function complete_user_role($user, $courseid){
    $context = context_course::instance($courseid);
    $obj = array_values(get_user_roles($context, $user->id))[0];
    if(isset($obj->shortname)){
        $user->role = getRolename($obj->roleid);
        $user->roleid = $obj->roleid;
    }
}


function getRolename($roleid){
    switch($roleid){
        case 5:
        return "Educando";
        case 4:
        return "Asesor";
    }
    return "Otro";
}

/**
 * 
 * @param  $id de la entidad
 * @return stdClass
 * 1	idPrimaria
 * 2	icvepais
 * 3	icveentfed
 * 4	cdesentfed
 * 5	ccveentcurp
 * 6	fmodifica
 */
function getEntidad($id){
    global $DB;
    $data = $DB->get_record('inea_entidad', array('icveentfed'=>$id, 'icvepais'=>1));
    return $data;
}

/**
 * 
 * @param $entidad_id
 * @param $municipio_id
 * @return stdClass
 *  1	idPrimaria
 *  2	icvepais
 *  3	icveentfed
 *  4	icvemunicipio
 *  5	cdesmunicipio
 *  6	fmodifica
 */
function getMunicipio($entidad_id, $municipio_id){
    global $DB;
    $data = $DB->get_record('inea_municipios', array('icveentfed'=>$entidad_id, 'icvemunicipio'=>$municipio_id));
    return $data;
}

/**
 * 
 * @param unknown $plaza_id
 * @return stdClass
 * 1	id
 * 2	idplaza
 * 3	ccveplaza
 * 4	icvepais
 * 5	icveie	
 * 6	icveentfed
 * 7	icvecz	
 * 8	icvemunicipio
 * 9	icvelocalidad
 * 10	icvetipoplaza
 * 11	cnomplaza
 */
function getPlaza($plaza_id){
    global $DB;
    $data = $DB->get_record('inea_plazas', array('idplaza'=>$plaza_id));
    return $data;
}

/**
 * 
 * @param unknown $ocupacion_id
 * @return stdClass
 * 	1	icveocupacion
	2	cidenocupacion
	3	cdesocupacion
	4	fmodifica
 */
function getOcupacion($ocupacion_id){
    global $DB;
    $data = $DB->get_record('inea_ocupaciones', array('icveocupacion'=>$ocupacion_id));
    return $data;
}



function getEntidadString($id){
    $entidad = getEntidad($id);
    return isset($entidad->cdesentfed)?$entidad->cdesentfed:'';
}

function getMunicipioString($entidad_id, $municipio_id){
    $municipio = getMunicipio($entidad_id, $municipio_id);
    return isset($municipio->cdesmunicipio)?$municipio->cdesmunicipio:'';
}

function getPlazaString($plaza_id){
    $plaza = getPlaza($plaza_id);
    return isset($plaza->cnomplaza)?$plaza->cnomplaza:'';
}

function getOcupacionString($ocupacion_id){
    $ocupacion = getOcupacion($ocupacion_id);
    return isset($ocupacion->cdesocupacion)?$ocupacion->cdesocupacion:'';
}
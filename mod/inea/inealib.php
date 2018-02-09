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
 * Id rol para educando
 * @var unknown
 */
define('EDUCANDO', 5);
/**
 * Id rol para asesor
 * @var unknown
 */
define('ASESOR', 4);


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
    
    return $DB->get_record('inea_modelos', array('icvemodesume'=>$id_modelo), 'cdesmodelo');
}

/**
 * INEA - Obtiene el catalogo de ocupaciones.
 * @return object
 */
function inea_get_ocupaciones() {
    global $DB;
    
    return $DB->get_records('inea_ocupaciones', null, '', 'cdesocupacion');
}

/**
 * INEA - Obtiene el catalogo de entidades.
 * @return object
 */
function inea_get_entidades() {
    global $DB;
    
    return $DB->get_records('inea_entidad', null, '', 'id, icvepais, icveentfed, cdesentfed');
}

/**
 * INEA - Obtiene el catalogo de municipios.
 * @return object
 */
function inea_get_municipios() {
    global $DB;
    
    return $DB->get_records('inea_municipios', null, '', 'id, icvepais, icveentfed, icvemunicipio, cdesmunicipio');
}

/**
 * INEA - Obtiene el catalogo de plazas.
 * @return object
 */
function inea_get_plazas() {
    global $DB;
    
    return $DB->get_records('inea_plazas', null, '', 'id, icvepais, icveentfed, icvemunicipio, cnomplaza, ccveplaza');
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
 * INEA - Obtiene el id de la entidad, municipio y pais de un usuario
 * @return object
 */
function inea_get_user_entidad($id_usuario) {
    global $DB;
    
    return $DB->get_record('user', array('id'=>$id_usuario), 'institution, country, city, skype');
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
 * INEA - Obtiene el id del asesor segun el nombre del grupo
 * @param int $id_grupo
 * @return int
 */
function inea_get_asesor_grupo($id_grupo) {
	global $CFG;
	
	$id_asesor = null;
	if($nombre_grupo = inea_get_nombre_grupo($id_grupo)) {
		$id_asesor = substr($nombre_grupo, strrpos($nombre_grupo, "_")+1);
	}
	
	return $id_asesor;
}

/**
 * INEA - Funcion copiada del archivo admin/export_data/CreteCSV.class.php.
 * @param int $id_grupo
 * @return int
 */
function inea_get_rfc_asesor_grupo($id_grupo) { //Vhackero Funcion para obtener el tutor/tutores de un grupo
	global $CFG;
	
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
    
    return $DB->get_record('user', array('idnumber'=>$rfe));
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
    
    return $DB->get_records('inea_plazas', null, '', 'id, idplaza, ccveplaza, cnomplaza, icveie');
}

/**
 * INEA - Lista la descripcion y nombre de cada estado.
 * @param int $id_pais
 * @return Array $lista un arreglo con el nombre - descripcion de cada estado.
 */
function inea_list_entidades($id_pais) {
	global $DB;
	
	$entidades = $DB->get_records('inea_entidad', array('icvepais'=>$id_pais), '', 'id, icvepais, icveentfed, cdesentfed');
	$list = array();
	
	foreach ($entidades as $entitidad) {
			//$list[$entityid->icveentfed] = $entityid->cdesentfed;
			$list[$entitidad->icveentfed] = $entitidad->cdesentfed;
	}
	//print_object($list);
	return $list;
}

/**
 * INEA - Obtiene una zona segun la plaza
 *
 * @deprecated - Funcion personalizada.
 * @return Int : Un valor con el id de la zona
 * 
 */
function inea_get_zona_by_plaza($id_plaza = null) {
	global $DB;
	
    if($zona = $DB->get_record('inea_plazas', array('idplaza'=>$id_plaza))){
		return $zona->icvecz;
	} 
	
	return 0;
}

/**
 * INEA - Obtiene el modelo al que pertenece el usuario
 *
 * @param int $userid
 * @return object modelo
 */
function inea_get_modelo_from_user($userid) {
    global $CFG, $DB;

    return $DB->get_record_sql('SELECT icvemodesume FROM {user} WHERE id = ?', array($userid));
}

/**
 * INEA - Regresa la lista de cursos de acuerdo a la categoria
 *
 * @global object
 * @uses CONTEXT_COURSE
 * @param string|int $categoryid id de la categoria o 'all' para devolver todas
 * @param string $sort El cambo y el tipo de orden
 * @param string $fields Los campos a regresar
 * @param string $clave_modelo La clave del modelo al que pertenece
 * @return array Lista de cursos
 */
function inea_get_courses($categoryid="all", $sort="c.sortorder ASC", $fields="c.*", $clave_modelo=0) {

    global $USER, $CFG, $DB;

    $params = array();
	
	if ($categoryid != "all" && is_numeric($categoryid)) {
        $categoryselect = "WHERE c.category = '$categoryid'";
    } else {
        $categoryselect = "";
    }

	//INEA: condicion agregada para el filtrado de cursos para educandos del modelo 10-14.
	if($clave_modelo == 11){	// 10: Modelo MOL, 11: Modelo 10-14
		$modelostatement = "AND c.idnumber_1014 IS NOT NULL"; 
    } else {
        $modelostatement = "";
    }
	
    if (empty($sort)) {
        $sortstatement = "";
    } else {
        $sortstatement = "ORDER BY $sort";
    }

    $visiblecourses = array();

    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
    $params['contextlevel'] = CONTEXT_COURSE;
	
    $sql = "SELECT $fields $ccselect
              FROM {course} c
           $ccjoin
              $categoryselect
			  $modelostatement
              $sortstatement";

    // pull out all course matching the cat
    if ($courses = $DB->get_records_sql($sql, $params)) {

        // loop throught them
        foreach ($courses as $course) {
            context_helper::preload_from_record($course);
            if (isset($course->visible) && $course->visible <= 0) {
                // for hidden courses, require visibility check
                if (has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
                    $visiblecourses [$course->id] = $course;
                }
            } else {
                $visiblecourses [$course->id] = $course;
            }
        }
    }
    return $visiblecourses;
}

/**
 * INEA - Regresa la lista de cursos de acuerdo a la categoria
 *
 * Similar a inea_get_courses, pero permite la paginacion
 *
 * @global object
 * @uses CONTEXT_COURSE
 * @param string|int $categoryid id de la categoria o 'all' para devolver todas
 * @param string $sort El cambo y el tipo de orden
 * @param string $fields Los campos a regresar
 * @param int $totalcount Referencia para el numero de cursos
 * @param string $limitfrom El numero de curso donde se empieza
 * @param string $limitnum El numero de cursos limite
 * @return array Arreglo de cursos
 */
function inea_get_courses_page($categoryid="all", $sort="c.sortorder ASC", $fields="c.*",
                          &$totalcount, $limitfrom="", $limitnum="") {
    global $USER, $CFG, $DB;

    $params = array();

    $categoryselect = "";
    if ($categoryid !== "all" && is_numeric($categoryid)) {
        $categoryselect = "WHERE c.category = :catid";
        $params['catid'] = $categoryid;
    } else {
		if($categoryid == "all") {
			$categoryselect = "";
		} else {
			$categoryselect = "WHERE c.".$categoryid;
		}
    }

    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
    $params['contextlevel'] = CONTEXT_COURSE;

    $totalcount = 0;
    if (!$limitfrom) {
        $limitfrom = 0;
    }
    $visiblecourses = array();

    $sql = "SELECT $fields $ccselect
              FROM {course} c
              $ccjoin
           $categoryselect
          ORDER BY $sort";

    // pull out all course matching the cat
    $rs = $DB->get_recordset_sql($sql, $params);
    // iteration will have to be done inside loop to keep track of the limitfrom and limitnum
    foreach($rs as $course) {
        context_helper::preload_from_record($course);
		$totalcount++;
        if ($totalcount > $limitfrom && (!$limitnum or count($visiblecourses) < $limitnum)) {
            $visiblecourses [$course->id] = $course;
        }
    }
    $rs->close();
    return $visiblecourses;
}
//--------------------------------------------------------------------------------
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
	global $DB;
	
	if(empty($entidad)) {
		return null;
	}
	
	//$conulta = "SELECT nombre, base, usuario, pass FROM mdl_inea_sasa_conn WHERE instituto = ".$entidad;
    //echo "<br>".$qry3. "<---- consulta sql server  ";
	$sasa = $DB->get_record_sql('SELECT nombre, base, usuario, pass FROM {inea_sasa_conn} WHERE instituto = ?', array($entidad));
    
    $servidor = $sasa->nombre;
    $basedatos = $sasa->base;
    $usuario = $sasa->usuario;
    $clave = $sasa->pass;
    
    /*echo "<br>".$servidor. "<---- nombre server de sql   ";
    echo "<br>".$basedatos. "<----- base";
    echo "<br>".$usuario. "<---- usuario de sql   ";
    echo "<br>".$clave. "<---- pass de sql   ";*/
   
	$dsn = "Driver={SQL Server Native Client 10.0};Server=$servidor;Database=$basedatos;";
	
	//realizamos la conexion mediante odbc
	$conexion = odbc_connect("Driver={SQL Server Native Client 10.0};Server=$servidor;Database=$basedatos;", $usuario, $clave);

	if (!$conexion){
		exit("<strong>Ya ocurrido un error tratando de conectarse con el origen de datos.</strong>");
	}	

	$consulta = "EXEC GetIdEducandoSASA '".$rfe."'";

	// generamos la tabla mediante odbc_result_all(); utilizando borde 1
	$resultado = odbc_exec($conexion, $consulta) or die(exit("Error en odbc_exec"));
	
	print_object(odbc_result_all($resultado, "border=1"));
	
	exit;
	
    $conectID = mssql_connect($nombre, $usuario, $pass);
    mssql_select_db($base);
    
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
    
    $node = new core_user\output\myprofile\node('contact', 'rfe', get_string("rfe","inea"), null, null, $user->idnumber);
    $tree->add_node($node);
    
    $node = new core_user\output\myprofile\node('contact', 'sexo', get_string("sexo","inea"), null, null, $user->yahoo);
    $tree->add_node($node);
    
    //$node = new core_user\output\myprofile\node('ineadetails', 'prueba', "prueba", null, $url);
    $node = new core_user\output\myprofile\node('contact', 'estado', get_string("estado","inea"), null, null, getEntidadString($user->institution));
    $tree->add_node($node);
    
    $node = new core_user\output\myprofile\node('contact', 'municipio', get_string("municipio","inea"), null, null, getMunicipioString($user->institution, $user->city) );
    $tree->add_node($node);
    
    $node = new core_user\output\myprofile\node('contact', 'plaza', get_string("plaza","inea"), null, null, getPlazaString($user->skype));
    $tree->add_node($node);
    
    $node = new core_user\output\myprofile\node('contact', 'ocupacion', get_string("ocupacion","inea"), null, null, getOcupacionString($user->msn));
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
    
    $user->destado = getEntidadString($user->institution);
    
    $user->dmunicipio = getMunicipioString($user->institution, $user->city);
    
    $user->dplaza = getPlazaString($user->skype);
    
    $user->docupacion = getOcupacionString($user->msn);
    
}

function complete_user_role($user, $courseid){
    $context = context_course::instance($courseid);
    if(empty(get_user_roles($context, $user->id))){
        return;
    }
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


/**
 * Devuelve el nombre de la entidad
 * @param unknown $id
 * @return string
 */
function getEntidadString($id){
    $entidad = getEntidad($id);
    return isset($entidad->cdesentfed)?$entidad->cdesentfed:'';
}

/**
 * Devuelve el nombre del municipio
 * @param unknown $entidad_id
 * @param unknown $municipio_id
 * @return string
 */
function getMunicipioString($entidad_id, $municipio_id){
    $municipio = getMunicipio($entidad_id, $municipio_id);
    return isset($municipio->cdesmunicipio)?$municipio->cdesmunicipio:'';
}

/**
 * Devuelve el nombre de la plaza
 * @param unknown $plaza_id
 * @return string
 */
function getPlazaString($plaza_id){
    $plaza = getPlaza($plaza_id);
    return isset($plaza->cnomplaza)?$plaza->cnomplaza:'';
}

/**
 * Devuelve el nombre de la ocupacion
 * @param unknown $ocupacion_id
 * @return string
 */
function getOcupacionString($ocupacion_id){
    $ocupacion = getOcupacion($ocupacion_id);
    return isset($ocupacion->cdesocupacion)?$ocupacion->cdesocupacion:'';
}

/**
 * INEA - Obtiene el ID del grupo si el usuario esta inscrito
 * @param int $userid
 * @param int $courseid
 * @return Object
 */
function inea_get_user_group($courseid, $userid){
	global $CFG, $DB;
	
    $grupos = groups_get_all_groups($courseid, $userid);
    if(!empty($grupos)){
        return array_values($grupos)[0];
    }
    return [];
}

//RUDY: integre siguiente funcion partiendo de la anterior. 300712
function inea_get_entidad_users($id_estado, $sort='u.lastaccess DESC', $exceptions='',
    $fields='u.*') {
        global $CFG, $DB;
        if (!empty($exceptions)) {
            $except = ' AND u.id NOT IN ('. $exceptions .') ';
        } else {
            $except = '';
        }
        // in postgres, you can't have things in sort that aren't in the select, so...
        $extrafield = str_replace('ASC','',$sort);
        $extrafield = str_replace('DESC','',$extrafield);
        $extrafield = trim($extrafield);
        if (!empty($extrafield)) {
            $extrafield = ','.$extrafield;
        }
        return $DB->get_records_sql("SELECT DISTINCT $fields $extrafield
                              FROM {$CFG->prefix}user u
                             WHERE u.institution = '$id_estado' $except
                          ORDER BY $sort");
}

function isstudent($courseid, $userid){
    $cContext = context_course::instance($courseid); // global $COURSE
    $roles = get_user_roles($cContext,$userid);
    foreach($roles as $id=>$rol){
        
        if($rol->roleid == EDUCANDO){
            return true;
        }
    }
    return false;
}

/**
 * Funcion para obtener el avance de las actividades INEA de una unidad de un curso de un usario
 * @param $userid
 * @param $courseid
 * @param x$unidadid
 */
function obtener_avance_unidad($userid, $courseid, $unidadid){
    global $DB;
    $unidades = $DB->get_records_select('inea_total_ejercicios','courseid='.$courseid.' and unidad='.$unidadid);
    $unidad = array_pop($unidades);
    
    $ejercicios = $DB->get_records_select('inea_ejercicios','courseid='.$courseid.' AND unidad='.$unidadid,array(),'','id');
    $ejercicios = array_keys($ejercicios);
    $ejercicios = implode(",",$ejercicios);
    
    $respuestas = $DB->get_records_select('inea_respuestas','userid='.$userid.' AND ejercicios_id in('.$ejercicios.') group by ejercicios_id');
    $contestadas = empty($respuestas)?0:count($respuestas);
    
    $total = 100*$unidad->nactividades/$unidad->porcentaje;
    $avance = round(($contestadas*100/$total)*100)/100;
    
    return $avance;
    
}

/**
 * Funcion que personaliza los campos de registro de los usuarios
 * Powerful function that is used by edit and editadvanced to add common form elements/rules/etc.
 *
 * @param moodleform $mform
 * @param array $editoroptions
 * @param array $filemanageroptions
 * @param stdClass $user
 */
function inea_useredit_shared_definition(&$mform, $editoroptions, $filemanageroptions, $user) {
    global $CFG, $USER, $DB;
    
    if ($user->id > 0) {
        useredit_load_preferences($user, false);
    }
    
    $strrequired = get_string('required');
    $stringman = get_string_manager();
    // Add the necessary names.
    /*foreach (useredit_get_required_name_fields() as $fullname) {
        $mform->addElement('text', $fullname,  get_string($fullname),  'maxlength="100" size="30"');
        if ($stringman->string_exists('missing'.$fullname, 'core')) {
            $strmissingfield = get_string('missing'.$fullname, 'core');
        } else {
            $strmissingfield = $strrequired;
        }
        $mform->addRule($fullname, $strmissingfield, 'required', null, 'client');
        $mform->setType($fullname, PARAM_NOTAGS);
    }*/
    
    $campo = 'firstname';
    $mform->addElement('text', $campo,  get_string($campo),  'maxlength="100" size="30"');
    $mform->addRule($campo, get_string($campo), 'required', null, 'client');
    $mform->setType($campo, PARAM_NOTAGS);
    
    $campo = 'lastname';
    $mform->addElement('text', $campo,  "Apellido paterno",  'maxlength="100" size="30"');
    $mform->addRule($campo, get_string($campo), 'required', null, 'client');
    $mform->setType($campo, PARAM_NOTAGS);
    
    $campo = 'icq';
    $mform->addElement('text', $campo,  "Apellido materno",  'maxlength="100" size="30"');
    //$mform->addRule($campo, get_string($campo), 'required', null, 'client');
    $mform->setType($campo, PARAM_NOTAGS);

    $mform->addElement('html', html_writer::empty_tag('hr'));
    
    $choices = Array("Masculino"=>"Masculino", "Femenino"=>"Femenino");
    //print_object();
    $choices = array('' => get_string('sexo','inea') . '...') + $choices;
    $mform->addElement('select', 'yahoo', get_string('sexo','inea'), $choices);
    $mform->addRule('yahoo', get_string('nosexo','inea'), 'required', null, 'server');
    
    $url = "";
    $element = &$mform->addElement('date_selector', 'aim', utf8_encode(get_string('fechanacimiento','inea')),array ('startyear'=> 1900,'stopyear'=> 2009,'zona horaria'=> 99,'applydst'=> true , 'opcional' => true), 'onchange="generaRFE(document.getElementById(\'id_lastname\').value, document.getElementById(\'id_icq\').value, document.getElementById(\'id_firstname\').value, this.form,\''.$url.'\');"');
    $mform->addRule('aim', utf8_encode(get_string('nofechanacimiento','inea')), 'required', null, 'client');
    
    $modificando = true;
    $user->aim = "02/12/2017";
    if($modificando){
        $t = explode('/',$user->aim);
        $script =  '<script type="text/javascript">
addEvent(window,"load",ventanaBienvenida,false);
var day = '.$t[0].';var month = '.$t[1].';var year = '.$t[2].';
function ventanaBienvenida()
{
    selects = document.getElementsByTagName("select");
    for(i=0;i<selects.length;i++){
        select = selects[i];
        if(select.getAttribute("name").indexOf("aim")>=0){
            if(select.getAttribute("name").indexOf("day")>=0)
                select.value=day;
            if(select.getAttribute("name").indexOf("month")>=0)
                select.value=month;
            if(select.getAttribute("name").indexOf("year")>=0)
                select.value=year;
        }
    }
}
    
function addEvent(elemento,nomevento,funcion,captura)
{
  if (elemento.attachEvent)
  {
    elemento.attachEvent("on"+nomevento,funcion);
    return true;
  }
  else
    if (elemento.addEventListener)
    {
      elemento.addEventListener(nomevento,funcion,captura);
      return true;
    }
    else
      return false;
}
</script>';
        //$mform->addElement('html',$script);
    }
    
    
    $mform->addElement('text', 'idnumber', get_string('rfe', "inea"), ' size="20" readonly="readonly"');
    $mform->setType('idnumber', PARAM_TEXT);
    $mform->addRule('idnumber', 'No se ha especificado el RFE.', 'required', null, 'client');
    $mform->addRule('idnumber', 'Datos del
	 incorrectos', 'rfc', null, 'client');
    
    
    
    $enabledusernamefields = useredit_get_enabled_name_fields();
    // Add the enabled additional name fields.
    foreach ($enabledusernamefields as $addname) {
        $mform->addElement('text', $addname,  get_string($addname), 'maxlength="100" size="30"');
        $mform->setType($addname, PARAM_NOTAGS);
    }
    
    // Do not show email field if change confirmation is pending.
    if ($user->id > 0 and !empty($CFG->emailchangeconfirmation) and !empty($user->preference_newemail)) {
        $notice = get_string('emailchangepending', 'auth', $user);
        $notice .= '<br /><a href="edit.php?cancelemailchange=1&amp;id='.$user->id.'">'
            . get_string('emailchangecancel', 'auth') . '</a>';
            $mform->addElement('static', 'emailpending', get_string('email'), $notice);
    } else {
        $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="30"');
        $mform->addRule('email', $strrequired, 'required', null, 'client');
        $mform->setType('email', PARAM_RAW_TRIMMED);
    }
    
    $choices = array();
    $choices['0'] = get_string('emaildisplayno');
    $choices['1'] = get_string('emaildisplayyes');
    $choices['2'] = get_string('emaildisplaycourse');
    //$mform->addElement('select', 'maildisplay', get_string('emaildisplay'), $choices);
    $mform->addElement('hidden', 'maildisplay', get_string('emaildisplay'), $choices);
    $mform->setDefault('maildisplay', core_user::get_property_default('maildisplay'));
    $mform->setType('maildisplay', PARAM_INT);
    
    $mform->addElement('text', 'city', get_string('city'), 'maxlength="120" size="21"');
    $mform->setType('city', PARAM_TEXT);
    if (!empty($CFG->defaultcity)) {
        $mform->setDefault('city', $CFG->defaultcity);
    }
    
    $choices = get_string_manager()->get_list_of_countries();
    $choices = array('' => get_string('selectacountry') . '...') + $choices;
    $mform->addElement('select', 'country', get_string('selectacountry'), $choices);
    if (!empty($CFG->country)) {
        $mform->setDefault('country', core_user::get_property_default('country'));
    }
    
    if (isset($CFG->forcetimezone) and $CFG->forcetimezone != 99) {
        $choices = core_date::get_list_of_timezones($CFG->forcetimezone);
        $mform->addElement('static', 'forcedtimezone', get_string('timezone'), $choices[$CFG->forcetimezone]);
        $mform->addElement('hidden', 'timezone');
        $mform->setType('timezone', core_user::get_property_type('timezone'));
    } else {
        $choices = core_date::get_list_of_timezones($user->timezone, true);
        $mform->addElement('select', 'timezone', get_string('timezone'), $choices);
    }
    
    if (!empty($CFG->allowuserthemes)) {
        $choices = array();
        $choices[''] = get_string('default');
        $themes = get_list_of_themes();
        foreach ($themes as $key => $theme) {
            if (empty($theme->hidefromselector)) {
                $choices[$key] = get_string('pluginname', 'theme_'.$theme->name);
            }
        }
        $mform->addElement('select', 'theme', get_string('preferredtheme'), $choices);
    }
    
    $mform->addElement('editor', 'description_editor', get_string('userdescription'), null, $editoroptions);
    $mform->setType('description_editor', PARAM_CLEANHTML);
    $mform->addHelpButton('description_editor', 'userdescription');
    
    if (empty($USER->newadminuser)) {
        $mform->addElement('header', 'moodle_picture', get_string('pictureofuser'));
        $mform->setExpanded('moodle_picture', true);
        
        if (!empty($CFG->enablegravatar)) {
            $mform->addElement('html', html_writer::tag('p', get_string('gravatarenabled')));
        }
        
        $mform->addElement('static', 'currentpicture', get_string('currentpicture'));
        
        $mform->addElement('checkbox', 'deletepicture', get_string('delete'));
        $mform->setDefault('deletepicture', 0);
        
        $mform->addElement('filemanager', 'imagefile', get_string('newpicture'), '', $filemanageroptions);
        $mform->addHelpButton('imagefile', 'newpicture');
        
        $mform->addElement('text', 'imagealt', get_string('imagealt'), 'maxlength="100" size="30"');
        $mform->setType('imagealt', PARAM_TEXT);
        
    }
    
    // Display user name fields that are not currenlty enabled here if there are any.
    $disabledusernamefields = useredit_get_disabled_name_fields($enabledusernamefields);
    if (count($disabledusernamefields) > 0) {
        $mform->addElement('header', 'moodle_additional_names', get_string('additionalnames'));
        foreach ($disabledusernamefields as $allname) {
            $mform->addElement('text', $allname, get_string($allname), 'maxlength="100" size="30"');
            $mform->setType($allname, PARAM_NOTAGS);
        }
    }
    
    if (core_tag_tag::is_enabled('core', 'user') and empty($USER->newadminuser)) {
        $mform->addElement('header', 'moodle_interests', get_string('interests'));
        $mform->addElement('tags', 'interests', get_string('interestslist'),
            array('itemtype' => 'user', 'component' => 'core'));
        $mform->addHelpButton('interests', 'interestslist');
    }
    
    // Moodle optional fields.
    $mform->addElement('header', 'moodle_optional', get_string('optional', 'form'));
    
    $mform->addElement('text', 'url', get_string('webpage'), 'maxlength="255" size="50"');
    $mform->setType('url', core_user::get_property_type('url'));
    
    /*$mform->addElement('text', 'icq', get_string('icqnumber'), 'maxlength="15" size="25"');
    $mform->setType('icq', core_user::get_property_type('icq'));
    $mform->setForceLtr('icq');
    
    $mform->addElement('text', 'skype', get_string('skypeid'), 'maxlength="50" size="25"');
    $mform->setType('skype', core_user::get_property_type('skype'));
    $mform->setForceLtr('skype');
    
    $mform->addElement('text', 'aim', get_string('aimid'), 'maxlength="50" size="25"');
    $mform->setType('aim', core_user::get_property_type('aim'));
    $mform->setForceLtr('aim');
    
   /* $mform->addElement('text', 'yahoo', get_string('yahooid'), 'maxlength="50" size="25"');
    $mform->setType('yahoo', core_user::get_property_type('yahoo'));
    $mform->setForceLtr('yahoo');
    */
    $mform->addElement('text', 'msn', get_string('msnid'), 'maxlength="50" size="25"');
    $mform->setType('msn', core_user::get_property_type('msn'));
    $mform->setForceLtr('msn');
    
    //$mform->addElement('text', 'idnumber', get_string('idnumber'), 'maxlength="255" size="25"');
    //$mform->setType('idnumber', core_user::get_property_type('idnumber'));
    
    $mform->addElement('text', 'institution', get_string('institution'), 'maxlength="255" size="25"');
    $mform->setType('institution', core_user::get_property_type('institution'));
    
    $mform->addElement('text', 'department', get_string('department'), 'maxlength="255" size="25"');
    $mform->setType('department', core_user::get_property_type('department'));
    
    $mform->addElement('text', 'phone1', get_string('phone1'), 'maxlength="20" size="25"');
    $mform->setType('phone1', core_user::get_property_type('phone1'));
    $mform->setForceLtr('phone1');
    
    $mform->addElement('text', 'phone2', get_string('phone2'), 'maxlength="20" size="25"');
    $mform->setType('phone2', core_user::get_property_type('phone2'));
    $mform->setForceLtr('phone2');
    
    $mform->addElement('text', 'address', get_string('address'), 'maxlength="255" size="25"');
    $mform->setType('address', core_user::get_property_type('address'));
}

/**
 * INEA - Enrola a un usuario a un curso
 *
 * @param int $userid El usuario a enrolar
 * @param int $courseid El curso en el cual se va a enrolar el usuario
 * @param int $roleid El rol que va a tener dentro del curso
 * @return bool
 */
function inea_enrol_user($userid, $courseid, $roleid) {
	global $DB;
	
    $user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id);
    
	if(!$roleid) {
		return false;
	}
	
	if (!is_enrolled($context, $user)) {
		// We need manual enrol type
        $enrol = enrol_get_plugin('manual');
        if ($enrol === null) {
            return false;
        }
        $instances = enrol_get_instances($course->id, true);
        $manualinstance = null;
        foreach ($instances as $instance) {
            if ($instance->enrol == 'manual') {
                $manualinstance = $instance;
                break;
            }
        }
		
        if ($manualinstance === null) {
			return false;
        } 
		
		$enrol->enrol_user($manualinstance, $userid, $roleid);
    } else {
		return false;
	}
	
    return true;
}

/**
 * INEA - Desenrola a un usuario de un curso
 *
 * @param int $userid El usuario a desenrolar
 * @param int $courseid El curso en el cual se va a desenrolar el usuario
 * @return bool
 */
function inea_unenrol_user($userid, $courseid) {
	global $DB;
	
    $user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id);
	
	if (is_enrolled($context, $user)) {
		// We need manual enrol type
        $enrol = enrol_get_plugin('manual');
        if ($enrol === null) {
            return false;
        }
        $instances = enrol_get_instances($course->id, true);
        $manualinstance = null;
        foreach ($instances as $instance) {
			if($enrol->allow_unenrol($instance) && $instance->enrol == 'manual') {
                $manualinstance = $instance;
                break;
            }
        }
        
		if ($manualinstance === null) {
			return false;
		}
        
		$enrol->unenrol_user($instance, $userid);
    } else {
		return false;
	}
	
    return true;
}

/**
 * Imprime datos en consola web
 *
 * @param String $data Cualquier cadena de datos
 *
 */
function debug_to_console( $data ) {
    $output = $data;
	
	if (is_object($output)) {
		$output = var_dump((array)$output);
	}
    
	if (is_array($output)) {
        $output = implode(',', $output);
	}

    echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}
?>
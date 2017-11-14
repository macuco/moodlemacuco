<?php
/**
 * @author macuco Juan Manuel Muñoz Pérez juan.manuel.mp8@gmail.com
 */
defined('MOODLE_INTERNAL') || die;

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
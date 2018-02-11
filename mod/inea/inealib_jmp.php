<?php


//MACUCO
function get_all_ocupaciones() {
    global $DB;
    return $DB->get_records_select('inea_ocupaciones', '', null,'cdesocupacion ASC');
    
}

//MACUCO
function get_all_entities() {
    global $DB;
    return $DB->get_records_select('inea_entidad', '', null,'','id, icvepais, icveentfed, cdesentfed');
    
}
//MACUCO
function get_all_municipios() {
    global $DB;
    return $DB->get_records_select('inea_municipios','',null,'','id, icvepais, icveentfed, icvemunicipio, cdesmunicipio');
    
}
//MACUCO
function get_all_plazas() {
    global $DB;
    return $DB->get_records_select('inea_plazas','',null, '','id, icvepais, icveentfed, icvemunicipio, cnomplaza, ccveplaza');
}

//MACUCO
function get_all_zonas($id_instituto) {
    global $DB;
    return $DB->get_records_select('inea_zona',empty($id_instituto)?'':'icveie='.$id_instituto,array('icveie' => $id_instituto));
}
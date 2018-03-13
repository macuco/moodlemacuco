<?php

require_once '../../grade/report/user/externallib.php';

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

//MACUCO
function get_municipio($id_estado, $id_municipio ) {
    global $DB;
    return $DB->get_record_select('inea_municipios','icvemunicipio= ? AND icveentfed = ? ',array($id_municipio, $id_estado),'id, icvepais, icveentfed, icvemunicipio, cdesmunicipio');
    
}

function get_calificaciones($courseid, $userid, $groupid = 0){
    global $CFG, $USER;
    
    list($params, $course, $context, $user, $groupid) = check_report_access($courseid, $userid, $groupid);
    $userid   = $params['userid'];
    
    // We pass userid because it can be still 0.
    list($gradeitems, $warnings) = get_report_data($course, $context, $user, $userid, $groupid, false);
    
    foreach ($gradeitems as $gradeitem) {
        if (isset($gradeitem['feedback']) and isset($gradeitem['feedbackformat'])) {
            list($gradeitem['feedback'], $gradeitem['feedbackformat']) =
            external_format_text($gradeitem['feedback'], $gradeitem['feedbackformat'], $context->id);
        }
    }
    
    //$result = array();
    //$result['usergrades'] = $gradeitems;
    //$result['warnings'] = $warnings;
    return $gradeitems;
}

function check_report_access($courseid, $userid, $groupid = 0) {
    global $USER;
    
    $params = array(
        'courseid' => $courseid,
        'userid' => $userid,
        'groupid' => $groupid,
    );
    // Function get_course internally throws an exception if the course doesn't exist.
    $course = get_course($courseid);
    
    $context = context_course::instance($courseid);
    //self::validate_context($context);
    
    // Specific capabilities.
    //require_capability('gradereport/user:view', $context);
    
    $user = null;
    
    if (empty($userid)) {
        //require_capability('moodle/grade:viewall', $context);
    } else {
        $user = core_user::get_user($userid, '*', MUST_EXIST);
        core_user::require_active_user($user);
        // Check if we can view the user group (if any).
        // When userid == 0, we are retrieving all the users, we'll check then if a groupid is required.
        if (!groups_user_groups_visible($course, $user->id)) {
            throw new moodle_exception('notingroup');
        }
    }
    
    $access = true;
    
    if (!empty($groupid)) {
        // Determine is the group is visible to user.
        if (!groups_group_visible($groupid, $course)) {
            throw new moodle_exception('notingroup');
        }
    } else {
        // Check to see if groups are being used here.
        if ($groupmode = groups_get_course_groupmode($course)) {
            $groupid = groups_get_course_group($course);
            // Determine is the group is visible to user (this is particullary for the group 0).
            if (!groups_group_visible($groupid, $course)) {
                throw new moodle_exception('notingroup');
            }
        } else {
            $groupid = 0;
        }
    }
    
    return array($params, $course, $context, $user, $groupid);
}



function get_report_data($course, $context, $user, $userid, $groupid, $tabledata = true) {
    global $CFG;
    
    $warnings = array();
    // Require files here to save some memory in case validation fails.
    require_once($CFG->dirroot . '/group/lib.php');
    require_once($CFG->libdir  . '/gradelib.php');
    require_once($CFG->dirroot . '/grade/lib.php');
    require_once($CFG->dirroot . '/grade/report/user/lib.php');
    
    // Force regrade to update items marked as 'needupdate'.
    grade_regrade_final_grades($course->id);
    
    $gpr = new grade_plugin_return(
        array(
            'type' => 'report',
            'plugin' => 'user',
            'courseid' => $course->id,
            'userid' => $userid)
        );
    
    $reportdata = array();
    
    // Just one user.
    if ($user) {
        $report = new grade_report_user($course->id, $gpr, $context, $userid);
        $report->fill_table();
        
        $gradeuserdata = array(
            'courseid'      => $course->id,
            'userid'        => $user->id,
            'userfullname'  => fullname($user),
            'maxdepth'      => $report->maxdepth,
        );
        if ($tabledata) {
            $gradeuserdata['tabledata'] = $report->tabledata;
        } else {
            $gradeuserdata['gradeitems'] = $report->gradeitemsdata;
        }
        $reportdata[] = $gradeuserdata;
    } else {
        $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
        $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
        $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $context);
        
        $gui = new graded_users_iterator($course, null, $groupid);
        $gui->require_active_enrolment($showonlyactiveenrol);
        $gui->init();
        
        while ($userdata = $gui->next_user()) {
            $currentuser = $userdata->user;
            $report = new grade_report_user($course->id, $gpr, $context, $currentuser->id);
            $report->fill_table();
            
            $gradeuserdata = array(
                'courseid'      => $course->id,
                'userid'        => $currentuser->id,
                'userfullname'  => fullname($currentuser),
                'maxdepth'      => $report->maxdepth,
            );
            if ($tabledata) {
                $gradeuserdata['tabledata'] = $report->tabledata;
            } else {
                $gradeuserdata['gradeitems'] = $report->gradeitemsdata;
            }
            $reportdata[] = $gradeuserdata;
        }
        $gui->close();
    }
    return array($reportdata, $warnings);
}

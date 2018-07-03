<?php

/**
 * Cambia el numero de secciones de un curso
 *
 * @package    local
 * @author macuco juan.manuel.mp8@gmail.com
 * @since Moodle 2.7
 */

defined('MOODLE_INTERNAL') || die;
//echo $CFG->dirroot.'/course/lib.php';
require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/course/lib.php");
require_once("$CFG->dirroot/course/format/lib.php");
//require_once($CFG->dirroot.'/course/lib.php');
/**
 * core grades functions
 */
class local_sections extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.7
     */
    public static function change_numsections_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id of course'),
                'increase' => new external_value(PARAM_INT, 'Incrementar o decrementar el numero de secciones')
            )
        );
    	
    }

    /**
     * Esta funcion fue hecha para agregar o quitar secciones en un curso
     *
     * @param  int $courseid        Course id
     * @param  int increase      Indica si incrementa o decrementa las secciones
     * @since Moodle 2.7
     */
    public static function change_numsections($courseid, $increase) {
        global $CFG, $USER, $DB;
        $params = self::validate_parameters(self::change_numsections_parameters(),
            array('courseid' => $courseid, 'increase' => $increase));

        

        
        $coursecontext = context_course::instance($params['courseid']);

        try {
            self::validate_context($coursecontext);
        } catch (Exception $e) {
            $exceptionparam = new stdClass();
            $exceptionparam->message = $e->getMessage();
            $exceptionparam->courseid = $params['courseid'];
            throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);
        }

        require_capability('moodle/course:update', $coursecontext);
        

	
		$course = $DB->get_record('course', array('id' => $params['courseid']), '*', MUST_EXIST);
		$courseformatoptions = course_get_format($course)->get_format_options();

    	
        $access = false;
        if (has_capability('moodle/course:update', $coursecontext)) {
            // Can view all user's grades in this course.
            $access = true;

        } 
        
        if (!$access) {
            throw new moodle_exception('nopermissiontoviewgrades', 'error');
        }
        
        

		if (isset($courseformatoptions['numsections'])) {
		    if ($increase) {
		        // Add an additional section.
		        $courseformatoptions['numsections']++;
		    } else {
		        // Remove a section.
		        $courseformatoptions['numsections']--;
		    }
		
		    // Don't go less than 0, intentionally redirect silently (for the case of
		    // double clicks).
		    if ($courseformatoptions['numsections'] >= 0) {
		        update_course((object)array('id' => $course->id,
		            'numsections' => $courseformatoptions['numsections']));
		        return 1;
		    }
		}
        
        return 0;
    }


    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.7
     */
    public static function change_numsections_returns() {
        return new external_value(
            PARAM_INT,
            'Un valor como  0  => OK, 1 => FAILED'
        );
    }
    
    
    
    
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.7
     */
    public static function get_section_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id of course'),
                'sectionid' => new external_value(PARAM_INT, 'Id de la seccion a obtener',VALUE_DEFAULT,null)
            )
        );
    	
    }

    /**
     * Esta funcion fue hecha para agregar o quitar secciones en un curso
     *
     * @param  int $courseid        Course id
     * @param  int increase      Indica si incrementa o decrementa las secciones
     * @since Moodle 2.7
     */
    public static function get_section($courseid, $sectionid) {
        global $CFG, $USER, $DB;
        $params = self::validate_parameters(self::get_section_parameters(),
            array('courseid' => $courseid, 'sectionid' => $sectionid));

        

        
        $coursecontext = context_course::instance($params['courseid']);

        try {
            self::validate_context($coursecontext);
        } catch (Exception $e) {
            $exceptionparam = new stdClass();
            $exceptionparam->message = $e->getMessage();
            $exceptionparam->courseid = $params['courseid'];
            throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);
        }

        require_capability('moodle/course:update', $coursecontext);
        
        $course = $DB->get_record('course', array('id' => $params['courseid']), '*', MUST_EXIST);

        $secciones = array();
         
        if($params['sectionid']){
        	$section = $DB->get_record('course_sections', array('id' => $params['sectionid']), '*', MUST_EXIST);
        	$sectionnum = $section->section;


        	$sectioninfo = get_fast_modinfo($course)->get_section_info($sectionnum);
        	 
        	$array1 = convert_to_array($sectioninfo);
        	//-----------Quitar estos datos para no acomplejar la estructura de regreso
        	unset($array1['conditionscompletion']);
        	unset($array1['conditionsgrade']);
        	unset($array1['conditionsfield']);
        	//------------------
        	$secciones[]=(object)$array1;//Convertir a objero la salida y agregarlo a la lista

        }else{
        	$modinfo = get_fast_modinfo($course);
        	$sectioninfo = $modinfo->get_section_info_all();
        	foreach ($sectioninfo as $thissection) {
        		$array1 = convert_to_array($thissection);
        		//-----------Quitar estos datos para no acomplejar la estructura de regreso
        		unset($array1['conditionscompletion']);
        		unset($array1['conditionsgrade']);
        		unset($array1['conditionsfield']);
        		//------------------
        		$secciones[]=(object)$array1;//Convertir a objero la salida y agregarlo a la lista
		    }
    	}
    	return $secciones;
    }


    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.7
     */
    public static function get_section_returns() {
    	/*
    	 * [id] => 1
    [section] => 0
    [name] => 
    [visible] => 1
    [summary] => 
    [summaryformat] => 1
    [availability] => 
    [available] => 1
    [availableinfo] => 
    [uservisible] => 1
    [sequence] => 3
    [course] => 2
    	 */
        return
        new external_multiple_structure( 
        new external_single_structure(
                        array(
                            //'activityid' => new external_value(
                            //    PARAM_ALPHANUM, 'The ID of the activity or "course" for the course grade item'),
                            'id'  => new external_value(PARAM_INT, 'Id de la seccion'),
                        	'section'  => new external_value(PARAM_INT, 'Id del curso al que pertenece la seccion'),
                            'course'  => new external_value(PARAM_INT, 'Id del curso al que pertenece la seccion'),
                            'name' => new external_value(PARAM_TEXT, 'El nombre de la seccion'),
                            'summary' => new external_value(PARAM_RAW, 'HTML del resumen de la seccion'),
                            'summaryformat' => new external_value(PARAM_INT, 'Formato del sumario'),
                            'availability' => new external_value(PARAM_ALPHANUM, 'Disponibilidad'),
                        	//'available' => new external_value(PARAM_INT, 'Disponible'),
                        	//'availableinfo' => new external_value(PARAM_ALPHANUM, 'Info de disponibilidad'),
                        	'visible' => new external_value(PARAM_INT, 'Si esta o no visible para los usuarios'),
                        	'sequence' => new external_value(PARAM_TEXT, 'Secuencia dentro del curso'),
                        )
                    )
                    );
    }
    
    
    
    
    
    
    
    
    
	/**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.7
     */
    public static function update_sections_parameters() {
        return new external_function_parameters(
        	array(
                'sections' => new external_multiple_structure( 
        		new external_single_structure(
                        array(
                            //'activityid' => new external_value(
                            //    PARAM_ALPHANUM, 'The ID of the activity or "course" for the course grade item'),
                            'id'  => new external_value(PARAM_INT, 'Id de la seccion'),
                            'course'  => new external_value(PARAM_INT, 'Id del curso al que pertenece la seccion'),
                            'name' => new external_value(PARAM_TEXT, 'El nombre de la seccion'),
                            'summary' => new external_value(PARAM_RAW, 'HTML del resumen de la seccion',VALUE_DEFAULT,
                                    '', NULL_NOT_ALLOWED),
                            'summaryformat' => new external_value(PARAM_INT, 'Formato del sumario',VALUE_DEFAULT,1,NULL_NOT_ALLOWED),
                            'availability' => new external_value(PARAM_ALPHANUM, 'Disponibilidad',VALUE_OPTIONAL,
                                    '', NULL_NOT_ALLOWED),
                        	'usedefaultname' => new external_value(PARAM_INT, 'Utilizar el valor por default para el nombre de la seccion',VALUE_OPTIONAL,
                                    '', NULL_NOT_ALLOWED),
                        	//'availableinfo' => new external_value(PARAM_ALPHANUM, 'Info de disponibilidad'),
                        	'visible' => new external_value(PARAM_INT, 'Si esta o no visible para los usuarios',VALUE_DEFAULT,1,NULL_NOT_ALLOWED),
                        	'sequence' => new external_value(PARAM_TEXT, 'Secuencia dentro del curso',VALUE_OPTIONAL,
                                    '', NULL_NOT_ALLOWED),
                        )
                    )
                    )
                )
         );
    	
    }

    /**
     * Esta funcion fue hecha para agregar o quitar secciones en un curso
     *
     * @param  int $courseid        Course id
     * @param  int increase      Indica si incrementa o decrementa las secciones
     * @since Moodle 2.7
     */
    public static function update_sections($sections) {
        global $CFG, $USER, $DB;
        $params = self::validate_parameters(self::update_sections_parameters(),
            array('sections' => $sections));
        
        
		foreach ($params['sections'] as $index => $section){
        	$data = ((object)$section);
			//print_r($data->course);exit;
	       	$coursecontext = context_course::instance($data->course);
	       	
	       	$course = course_get_format($data->course)->get_course();
	       	course_create_sections_if_missing($course, range(0, $course->numsections));
	       	 
	       	
	        $sectioninfo = get_fast_modinfo($data->course)->get_section_info($data->id);
	        $data->id = $sectioninfo->id;
	        
	        $section = $DB->get_record('course_sections', array('id' => $sectioninfo->id), '*', MUST_EXIST);
			$sectionnum = $section->section;
	
	        try {
	            self::validate_context($coursecontext);
	        } catch (Exception $e) {
	            $exceptionparam = new stdClass();
	            $exceptionparam->message = $e->getMessage();
	            $exceptionparam->courseid = $params['courseid'];
	            throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);
	        }
	
	        require_capability('moodle/course:update', $coursecontext);
	        
	        $course = $DB->get_record('course', array('id' => $data->course), '*', MUST_EXIST);
	
	 		if (!empty($CFG->enableavailability)) {
		        // Renamed field.
		        $data->availability = $data->availabilityconditionsjson;
		        unset($data->availabilityconditionsjson);
		        if ($data->availability === '') {
		            $data->availability = null;
		        }
		    }
		    $DB->update_record('course_sections', $data);
		    rebuild_course_cache($course->id, true);

		    // ---------- Dejar visible o no la sección ---------------
			 if (has_capability('moodle/course:sectionvisibility', $coursecontext)) {
	            if (!$data->visible) {
	                set_section_visible($course->id, $hide, '0');
	            }else if ($data->visible) {
	                set_section_visible($course->id, $show, '1');
	            }
	        }
        	// ---------------------------------------------------------
		    
		    
		    if (isset($data->section)) {
		        // Usually edit form does not change relative section number but just in case.
		        $sectionnum = $data->section;
		    }
		    course_get_format($course->id)->update_section_format_options($data);
		
		    // Set section info, as this might not be present in form_data.
		    if (!isset($data->section))  {
		        $data->section = $sectionnum;
		    }
		    // Trigger an event for course section update.
		    $event = \core\event\course_section_updated::create(
		            array(
		                'objectid' => $data->id,
		                'courseid' => $course->id,
		                'context' => $coursecontext,
		                'other' => array('sectionnum' => $data->section)
		            )
		        );
		    $event->trigger();
		    
		    
		}
    	return null;
    }


    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.7
     */
    public static function update_sections_returns() {
    	return null;
    }
    
    
    
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.7
     */
    public static function update_sections_sequence_parameters() {
    	return new external_function_parameters(
    			array(
    					'sections' => new external_multiple_structure(
    							new external_single_structure(
    									array(
    											//'activityid' => new external_value(
    											//    PARAM_ALPHANUM, 'The ID of the activity or "course" for the course grade item'),
    											'id'  => new external_value(PARAM_INT, 'Id de la seccion'),
    									'course'  => new external_value(PARAM_INT, 'Id del curso al que pertenece la seccion'),
    									'name' => new external_value(PARAM_TEXT, 'El nombre de la seccion'),
    									'summary' => new external_value(PARAM_RAW, 'HTML del resumen de la seccion',VALUE_DEFAULT,
    											'', NULL_NOT_ALLOWED),
    									'summaryformat' => new external_value(PARAM_INT, 'Formato del sumario',VALUE_DEFAULT,1,NULL_NOT_ALLOWED),
    									'availability' => new external_value(PARAM_ALPHANUM, 'Disponibilidad',VALUE_OPTIONAL,
    											'', NULL_NOT_ALLOWED),
    									'usedefaultname' => new external_value(PARAM_INT, 'Utilizar el valor por default para el nombre de la seccion',VALUE_OPTIONAL,
    											'', NULL_NOT_ALLOWED),
    									//'availableinfo' => new external_value(PARAM_ALPHANUM, 'Info de disponibilidad'),
    									'visible' => new external_value(PARAM_INT, 'Si esta o no visible para los usuarios',VALUE_DEFAULT,1,NULL_NOT_ALLOWED),
    									'sequence' => new external_value(PARAM_TEXT, 'Secuencia dentro del curso',VALUE_OPTIONAL,
    											'', NULL_NOT_ALLOWED),
    									)
    							)
    			)
    			)
    			);
    			 
    }
    
    /**
     * Esta funcion fue hecha para agregar o quitar secciones en un curso
     *
     * @param  int $courseid        Course id
     * @param  int increase      Indica si incrementa o decrementa las secciones
     * @since Moodle 2.7
     */
    public static function update_sections_sequence($sections) {
    	global $CFG, $USER, $DB;
    	$params = self::validate_parameters(self::update_sections_sequence_parameters(),
    			array('sections' => $sections));
    	foreach ($params['sections'] as $index => $section){
    		$data = ((object)$section);
    		//print_r($data->course);exit;
    		$coursecontext = context_course::instance($data->course);
    		
    		// --- aqui va ---
    		$sectioninfo = get_fast_modinfo($data->course)->get_section_info($data->id);
    		 
    		$initialdata = convert_to_array($sectioninfo);
    		
    		//-- aqiu va
    		//$data->section = $data->id;
    		
    		$section = $DB->get_record('course_sections', array('id' => $initialdata['id']), '*', MUST_EXIST);
    		$section->name = $data->name;
    		$section->summary = $data->summary;
    		$section->visible = 1;
    		//print_object($section);exit;
    		$data = $section;
    		//$sectionnum = $section->section;
    
    		try {
    			self::validate_context($coursecontext);
    		} catch (Exception $e) {
    			$exceptionparam = new stdClass();
    			$exceptionparam->message = $e->getMessage();
    			$exceptionparam->courseid = $params['courseid'];
    			throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);
    		}
    
    		require_capability('moodle/course:update', $coursecontext);
    		 //print_object($data);exit;
    		$course = $DB->get_record('course', array('id' => $data->course), '*', MUST_EXIST);
    
    		if (!empty($CFG->enableavailability)) {
    			// Renamed field.
    			$data->availability = $data->availabilityconditionsjson;
    			unset($data->availabilityconditionsjson);
    			if ($data->availability === '') {
    				$data->availability = null;
    			}
    		}
    		$DB->update_record('course_sections', $data);
    		rebuild_course_cache($course->id, true);
    
    		// ---------- Dejar visible o no la sección ---------------
    		if (has_capability('moodle/course:sectionvisibility', $coursecontext)) {
    			if (!$data->visible) {
    				set_section_visible($course->id, $hide, '0');
    			}else if ($data->visible) {
    				set_section_visible($course->id, $show, '1');
    			}
    		}
    		// ---------------------------------------------------------
    
    
    		if (isset($data->section)) {
    			// Usually edit form does not change relative section number but just in case.
    			$sectionnum = $data->section;
    		}
    		course_get_format($course->id)->update_section_format_options($data);
    
    		// Set section info, as this might not be present in form_data.
    		if (!isset($data->section))  {
    			$data->section = $sectionnum;
    		}
    		// Trigger an event for course section update.
    		$event = \core\event\course_section_updated::create(
    				array(
    						'objectid' => $data->id,
    						'courseid' => $course->id,
    						'context' => $coursecontext,
    						'other' => array('sectionnum' => $data->section)
    				)
    				);
    		$event->trigger();
    
    
    	}
    	return true;
    }
    
    
    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.7
     */
    public static function update_sections_sequence_returns() {
    	return new external_value(PARAM_BOOL, 'True si fueron actualizadas correctamente');
    }
    
    
    
	
    
    
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.7
     */
    public static function delete_sections_parameters() {
    	return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'sectionid' => new external_value ( PARAM_INT, 'Id de la seccion a obtener' ) 
		) );
    }
    
    /**
     * Esta funcion fue hecha para agregar o quitar secciones en un curso
     *
     * @param  int $courseid        Course id
     * @param  int increase      Indica si incrementa o decrementa las secciones
     * @since Moodle 2.7
     */
    public static function delete_sections($courseid,$sectionid) {
    	global $CFG, $USER, $DB;
    	$params = self::validate_parameters(self::delete_sections_parameters(),
    			array('courseid' => $courseid, 'sectionid' => $sectionid));
    	
    	
    	
    	$id = $params['sectionid'];
    	
    	//$PAGE->set_url('/course/editsection.php', array('id'=>$id, 'sr'=> $sectionreturn));
    	
    	$section = $DB->get_record('course_sections', array('id' => $id), '*', MUST_EXIST);
    	if($section->course!=$params['courseid']){//Los datos de la seccion no coinciden con los del curso
    		$exceptionparam = new stdClass();
    		$exceptionparam->message = "Los identificadores de la sección no corresponden a los identificadores del curso";
    		$exceptionparam->courseid = $params['courseid'];
    		$exceptionparam->sectionid = $params['sectionid'];
    		throw new moodle_exception('errorcoursesectionnotvalid' , 'webservice', '', $exceptionparam);
    	}
    	$course = $DB->get_record('course', array('id' => $section->course), '*', MUST_EXIST);
    	$sectionnum = $section->section;
    	
    	//require_login($course);
    	$context = context_course::instance($course->id);
    	require_capability('moodle/course:update', $context);
    	
    	// Get section_info object with all availability options.
    	$sectioninfo = get_fast_modinfo($course)->get_section_info($sectionnum);
    	
    	// Deleting the section.
    	//if ($deletesection) {
    		//$cancelurl = course_get_url($course, $sectioninfo, array('sr' => $sectionreturn));
    		if (course_can_delete_section($course, $sectioninfo)) {
    			course_delete_section($course, $sectioninfo, true);
    		} else {
    			$exceptionparam = new stdClass();
    			$exceptionparam->message = get_string('nopermissions')." - ".get_string('deletesection');
    			$exceptionparam->courseid = $params['courseid'];
    			$exceptionparam->sectionid = $params['sectionid'];
    			throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);
    			
    		}
    	//}
    	
    	return null;
    }
    
    
    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.7
     */
    public static function delete_sections_returns() {
    	return null;
    }
    
    
    
}

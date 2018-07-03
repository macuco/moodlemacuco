<?php

/**
 * Clase que registra los servicios relacionados a los cursos
 *
 * @package    local
 * @author macuco juan.manuel.mp8@gmail.com
 * @since Moodle 2.7
 */
defined ( 'MOODLE_INTERNAL' ) || die ();
// echo $CFG->dirroot.'/course/lib.php';
require_once ("$CFG->libdir/externallib.php");
 require_once($CFG->dirroot.'/course/lib.php');
/**
 * core grades functions
 */
class local_courses extends external_api {
	
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 * @since Moodle 2.3
	 */
	public static function get_courses_without_idnumber_parameters() {
		return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id of course',VALUE_DEFAULT,null)
            )
        );
	}
	
	/**
	 * Get courses
	 *
	 * @param array $options
	 *        	It contains an array (list of ids)
	 * @return array
	 * @since Moodle 2.2
	 */
	public static function get_courses_without_idnumber() {
		global $CFG, $DB;
		require_once ($CFG->dirroot . "/course/lib.php");
		
		// validate parameter
		/*$params = self::validate_parameters ( self::get_courses_parameters (), array (
				'options' => $options 
		) );*/
		
		// retrieve courses
		//if (! array_key_exists ( 'ids', $params ['options'] ) or empty ( $params ['options'] ['ids'] )) {
			$courses = $DB->get_records ( 'course',array("idnumber"=>'') );
		//} else {
			//$courses = $DB->get_records_list ( 'course', 'id', $params ['options'] ['ids'] );
		//}
		
		// create return value
		$coursesinfo = array ();
		foreach ( $courses as $course ) {
			
			// now security checks
			$context = context_course::instance ( $course->id, IGNORE_MISSING );
			$courseformatoptions = course_get_format ( $course )->get_format_options ();
			try {
				self::validate_context ( $context );
			} catch ( Exception $e ) {
				$exceptionparam = new stdClass ();
				$exceptionparam->message = $e->getMessage ();
				$exceptionparam->courseid = $course->id;
				throw new moodle_exception ( 'errorcoursecontextnotvalid', 'webservice', '', $exceptionparam );
			}
			require_capability ( 'moodle/course:view', $context );
			
			$courseinfo = array ();
			$courseinfo ['id'] = $course->id;
			$courseinfo ['fullname'] = $course->fullname;
			$courseinfo ['shortname'] = $course->shortname;
			$courseinfo ['categoryid'] = $course->category;
			list ( $courseinfo ['summary'], $courseinfo ['summaryformat'] ) = external_format_text ( $course->summary, $course->summaryformat, $context->id, 'course', 'summary', 0 );
			$courseinfo ['format'] = $course->format;
			$courseinfo ['startdate'] = $course->startdate;
			if (array_key_exists ( 'numsections', $courseformatoptions )) {
				// For backward-compartibility
				$courseinfo ['numsections'] = $courseformatoptions ['numsections'];
			}
			
			// some field should be returned only if the user has update permission
			$courseadmin = has_capability ( 'moodle/course:update', $context );
			if ($courseadmin) {
				$courseinfo ['categorysortorder'] = $course->sortorder;
				$courseinfo ['idnumber'] = $course->idnumber;
				$courseinfo ['showgrades'] = $course->showgrades;
				$courseinfo ['showreports'] = $course->showreports;
				$courseinfo ['newsitems'] = $course->newsitems;
				$courseinfo ['visible'] = $course->visible;
				$courseinfo ['maxbytes'] = $course->maxbytes;
				if (array_key_exists ( 'hiddensections', $courseformatoptions )) {
					// For backward-compartibility
					$courseinfo ['hiddensections'] = $courseformatoptions ['hiddensections'];
				}
				$courseinfo ['groupmode'] = $course->groupmode;
				$courseinfo ['groupmodeforce'] = $course->groupmodeforce;
				$courseinfo ['defaultgroupingid'] = $course->defaultgroupingid;
				$courseinfo ['lang'] = $course->lang;
				$courseinfo ['timecreated'] = $course->timecreated;
				$courseinfo ['timemodified'] = $course->timemodified;
				$courseinfo ['forcetheme'] = $course->theme;
				$courseinfo ['enablecompletion'] = $course->enablecompletion;
				$courseinfo ['completionnotify'] = $course->completionnotify;
				$courseinfo ['courseformatoptions'] = array ();
				foreach ( $courseformatoptions as $key => $value ) {
					$courseinfo ['courseformatoptions'] [] = array (
							'name' => $key,
							'value' => $value 
					);
				}
			} 
			
			if ($courseadmin or $course->visible or has_capability ( 'moodle/course:viewhiddencourses', $context )) {
				$coursesinfo [] = $courseinfo;
			}
		}
		
		return $coursesinfo;
	}
	
	/**
	 * Returns description of method result value
	 *
	 * @return external_description
	 * @since Moodle 2.2
	 */
	public static function get_courses_without_idnumber_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'id' => new external_value ( PARAM_INT, 'course id' ),
				'shortname' => new external_value ( PARAM_TEXT, 'course short name' ),
				'categoryid' => new external_value ( PARAM_INT, 'category id' ),
				'categorysortorder' => new external_value ( PARAM_INT, 'sort order into the category', VALUE_OPTIONAL ),
				'fullname' => new external_value ( PARAM_TEXT, 'full name' ),
				'idnumber' => new external_value ( PARAM_RAW, 'id number', VALUE_OPTIONAL ),
				'summary' => new external_value ( PARAM_RAW, 'summary' ),
				'summaryformat' => new external_format_value ( 'summary' ),
				'format' => new external_value ( PARAM_PLUGIN, 'course format: weeks, topics, social, site,..' ),
				'showgrades' => new external_value ( PARAM_INT, '1 if grades are shown, otherwise 0', VALUE_OPTIONAL ),
				'newsitems' => new external_value ( PARAM_INT, 'number of recent items appearing on the course page', VALUE_OPTIONAL ),
				'startdate' => new external_value ( PARAM_INT, 'timestamp when the course start' ),
				'numsections' => new external_value ( PARAM_INT, '(deprecated, use courseformatoptions) number of weeks/topics', VALUE_OPTIONAL ),
				'maxbytes' => new external_value ( PARAM_INT, 'largest size of file that can be uploaded into the course', VALUE_OPTIONAL ),
				'showreports' => new external_value ( PARAM_INT, 'are activity report shown (yes = 1, no =0)', VALUE_OPTIONAL ),
				'visible' => new external_value ( PARAM_INT, '1: available to student, 0:not available', VALUE_OPTIONAL ),
				'hiddensections' => new external_value ( PARAM_INT, '(deprecated, use courseformatoptions) How the hidden sections in the course are displayed to students', VALUE_OPTIONAL ),
				'groupmode' => new external_value ( PARAM_INT, 'no group, separate, visible', VALUE_OPTIONAL ),
				'groupmodeforce' => new external_value ( PARAM_INT, '1: yes, 0: no', VALUE_OPTIONAL ),
				'defaultgroupingid' => new external_value ( PARAM_INT, 'default grouping id', VALUE_OPTIONAL ),
				'timecreated' => new external_value ( PARAM_INT, 'timestamp when the course have been created', VALUE_OPTIONAL ),
				'timemodified' => new external_value ( PARAM_INT, 'timestamp when the course have been modified', VALUE_OPTIONAL ),
				'enablecompletion' => new external_value ( PARAM_INT, 'Enabled, control via completion and activity settings. Disbaled,
                                        not shown in activity settings.', VALUE_OPTIONAL ),
				'completionnotify' => new external_value ( PARAM_INT, '1: yes 0: no', VALUE_OPTIONAL ),
				'lang' => new external_value ( PARAM_SAFEDIR, 'forced course language', VALUE_OPTIONAL ),
				'forcetheme' => new external_value ( PARAM_PLUGIN, 'name of the force theme', VALUE_OPTIONAL ),
				'courseformatoptions' => new external_multiple_structure ( new external_single_structure ( array (
						'name' => new external_value ( PARAM_ALPHANUMEXT, 'course format option name' ),
						'value' => new external_value ( PARAM_RAW, 'course format option value' ) 
				) ), 'additional options for particular course format', VALUE_OPTIONAL ) 
		), 'course' ) );
	}
	
	
	
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 * @since Moodle 2.2
	 */
	public static function create_course_notvisible_parameters() {
		$courseconfig = get_config('moodlecourse'); //needed for many default values
		return new external_function_parameters(
				array(
						'course' => new external_multiple_structure(
								new external_single_structure(
										array(
												'fullname' => new external_value(PARAM_TEXT, 'full name'),
												'shortname' => new external_value(PARAM_TEXT, 'course short name'),
												'categoryid' => new external_value(PARAM_INT, 'category id'),
												'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
												'summary' => new external_value(PARAM_RAW, 'summary', VALUE_OPTIONAL),
												'summaryformat' => new external_format_value('summary', VALUE_DEFAULT),
												'format' => new external_value(PARAM_PLUGIN,
														'course format: weeks, topics, social, site,..',
														VALUE_DEFAULT, $courseconfig->format),
												'showgrades' => new external_value(PARAM_INT,
														'1 if grades are shown, otherwise 0', VALUE_DEFAULT,
														$courseconfig->showgrades),
												'newsitems' => new external_value(PARAM_INT,
														'number of recent items appearing on the course page',
														VALUE_DEFAULT, $courseconfig->newsitems),
												'startdate' => new external_value(PARAM_INT,
														'timestamp when the course start', VALUE_OPTIONAL),
												'numsections' => new external_value(PARAM_INT,
														'(deprecated, use courseformatoptions) number of weeks/topics',
														VALUE_OPTIONAL),
												'maxbytes' => new external_value(PARAM_INT,
														'largest size of file that can be uploaded into the course',
														VALUE_DEFAULT, $courseconfig->maxbytes),
												'showreports' => new external_value(PARAM_INT,
														'are activity report shown (yes = 1, no =0)', VALUE_DEFAULT,
														$courseconfig->showreports),
												'visible' => new external_value(PARAM_INT,
														'1: available to student, 0:not available', VALUE_OPTIONAL),
												'hiddensections' => new external_value(PARAM_INT,
														'(deprecated, use courseformatoptions) How the hidden sections in the course are displayed to students',
														VALUE_OPTIONAL),
												'groupmode' => new external_value(PARAM_INT, 'no group, separate, visible',
														VALUE_DEFAULT, $courseconfig->groupmode),
												'groupmodeforce' => new external_value(PARAM_INT, '1: yes, 0: no',
														VALUE_DEFAULT, $courseconfig->groupmodeforce),
												'defaultgroupingid' => new external_value(PARAM_INT, 'default grouping id',
														VALUE_DEFAULT, 0),
												'enablecompletion' => new external_value(PARAM_INT,
														'Enabled, control via completion and activity settings. Disabled,
                                        not shown in activity settings.',
														VALUE_OPTIONAL),
												'completionnotify' => new external_value(PARAM_INT,
														'1: yes 0: no', VALUE_OPTIONAL),
												'lang' => new external_value(PARAM_SAFEDIR,
														'forced course language', VALUE_OPTIONAL),
												'forcetheme' => new external_value(PARAM_PLUGIN,
														'name of the force theme', VALUE_OPTIONAL),
												'courseformatoptions' => new external_multiple_structure(
														new external_single_structure(
																array('name' => new external_value(PARAM_ALPHANUMEXT, 'course format option name'),
																		'value' => new external_value(PARAM_RAW, 'course format option value')
																)),
														'additional options for particular course format', VALUE_OPTIONAL),
										)
										), 'courses to create'
								)
				)
				);
		
	}
	
	/**
	 * Create  courses
	 *
	 * @param array $courses
	 * @return array courses (id and shortname only)
	 * @since Moodle 2.2
	 */
	public static function create_course_notvisible($courses) {
		global $CFG, $DB;
		require_once($CFG->dirroot . "/course/lib.php");
		require_once($CFG->libdir . '/completionlib.php');
		require_once($CFG->dirroot . "/servicios_web/forum/externallib.php");
	
		//print_r($_POST);
		//print_r($_GET);
		//exit;
		//$params = self::validate_parameters(self::create_courses_parameters(),
		//		array('course' => $course));
		
		$availablethemes = core_component::get_plugin_list('theme');
		$availablelangs = get_string_manager()->get_list_of_translations();
	
		$transaction = $DB->start_delegated_transaction();
		//print_r($course);
		
		//$course = $params['courses'][0];
		
		foreach ($courses as $course) {
	
			if(isset($course['numsections']) && $course['numsections']<=0 ){
				unset($course['numsections']);
			}
			
			// Ensure the current user is allowed to run this function
			$context = context_coursecat::instance($course['categoryid'], IGNORE_MISSING);
			try {
				self::validate_context($context);
			} catch (Exception $e) {
				$exceptionparam = new stdClass();
				$exceptionparam->message = $e->getMessage();
				$exceptionparam->catid = $course['categoryid'];
				throw new moodle_exception('errorcatcontextnotvalid', 'webservice', '', $exceptionparam);
			}
			require_capability('moodle/course:create', $context);
	
			// Make sure lang is valid
			if (array_key_exists('lang', $course) and empty($availablelangs[$course['lang']])) {
				throw new moodle_exception('errorinvalidparam', 'webservice', '', 'lang');
			}
	
			// Make sure theme is valid
			if (array_key_exists('forcetheme', $course)) {
				if (!empty($CFG->allowcoursethemes)) {
					if (empty($availablethemes[$course['forcetheme']])) {
						throw new moodle_exception('errorinvalidparam', 'webservice', '', 'forcetheme');
					} else {
						$course['theme'] = $course['forcetheme'];
					}
				}
			}
	
			//force visibility if ws user doesn't have the permission to set it
			$category = $DB->get_record('course_categories', array('id' => $course['categoryid']));
			if (!has_capability('moodle/course:visibility', $context)) {
				$course['visible'] = $category->visible;
			}
			$course['visible'] = 0; //Forsar que sea invisible
	
			//set default value for completion
			$courseconfig = get_config('moodlecourse');
			if (completion_info::is_enabled_for_site()) {
				if (!array_key_exists('enablecompletion', $course)) {
					$course['enablecompletion'] = $courseconfig->enablecompletion;
				}
			} else {
				$course['enablecompletion'] = 0;
			}
	
			$course['category'] = $course['categoryid'];
	
			// Summary format.
			$course['summaryformat'] = external_validate_format($course['summaryformat']);
	
			if (!empty($course['courseformatoptions'])) {
				foreach ($course['courseformatoptions'] as $option) {
					$course[$option['name']] = $option['value'];
				}
			}
	
			//Note: create_course() core function check shortname, idnumber, category
			$course['id'] = create_course((object) $course)->id;
	
			$resultcourses[] = array('id' => $course['id'], 'shortname' => $course['shortname']);
		}
		local_forum_external::default_forum($course['id']);
		local_courses::crearURL($course['id']);
		local_courses::crearArchivo($course['id']);
		
		$transaction->allow_commit();
	
		
		return $course['id'];
	}
	
	
	public static function crearURL($courseid){
		global $CFG, $DB;
		require_once("$CFG->dirroot/course/modlib.php");
		
		$course = $courseid;
		$section = 0;
		$add = "url";
		
		$course = $DB->get_record('course', array('id'=>$course), '*', MUST_EXIST);
		course_create_sections_if_missing($course, range(0, $course->numsections));
		list($module, $context, $cw) = can_add_moduleinfo($course, $add, $section);
		
		
		$data = new stdClass();
		$data->section          = $section;  // The section number itself - relative!!! (section column in course_sections)
		$data->visible          = $cw->visible;
		$data->course           = $course->id;
		$data->module           = $module->id;
		$data->modulename       = $module->name;
		$data->groupmode        = $course->groupmode;
		$data->groupingid       = $course->defaultgroupingid;
		$data->id               = '';
		$data->instance         = '';
		$data->coursemodule     = '';
		$data->add              = $add;
		$data->return           = 0; //must be false if this is an add, go back to course view on cancel
		$data->sr               = 0;
		$data->name				= 'ENCUESTA';
		$data->externalurl		= 'http://www.google.com';
		$data->introeditor 		= Array('text' => 'Esta es mi descripcion','format'=>1);
		$data->mform_isexpanded_id_content = 1;
		$data->display = 6;
		$data->popupwidth = 620;
		$data->popupheight = 450;
		$data->printintro = 1;
		
		
		$modmoodleform = "$CFG->dirroot/mod/$module->name/mod_form.php";
		if (file_exists($modmoodleform)) {
			require_once($modmoodleform);
		} else {
			print_error('noformdesc');
		}
		
		$fromform = add_moduleinfo($data, $course);
		return $formform;
	}
	
	public static function crearArchivo($courseId){
		global $CFG, $DB;
		require_once("$CFG->dirroot/course/modlib.php");
		
		$course = $courseId;
		$section = 0;
		$add = "resource";
		
		$course = $DB->get_record('course', array('id'=>$course), '*', MUST_EXIST);
		course_create_sections_if_missing($course, range(0, $course->numsections));
		list($module, $context, $cw) = can_add_moduleinfo($course, $add, $section);
		
		
		
		$data = new stdClass();
		$data->section          = $section;  // The section number itself - relative!!! (section column in course_sections)
		$data->visible          = $cw->visible;
		$data->course           = $course->id;
		$data->module           = $module->id;
		$data->modulename       = $module->name;
		$data->groupmode        = $course->groupmode;
		$data->groupingid       = $course->defaultgroupingid;
		$data->id               = '';
		$data->instance         = '';
		$data->coursemodule     = '';
		$data->add              = $add;
		$data->return           = 0; //must be false if this is an add, go back to course view on cancel
		$data->sr               = 0;
		$data->name				= 'GUÍA DE USO';
		//$data->externalurl		= 'http://www.google.com';
		$data->introeditor 		= Array('text' => 'Esta es mi descripcion','format'=>1);
		$data->mform_isexpanded_id_content = 1;
		$data->files = 654984610;
		$data->display = 6;
		$data->popupwidth = 620;
		$data->popupheight = 450;
		$data->printintro = 1;
		
		
		$modmoodleform = "$CFG->dirroot/mod/$module->name/mod_form.php";
		if (file_exists($modmoodleform)) {
			require_once($modmoodleform);
		} else {
			print_error('noformdesc');
		}
		
		$fromform = add_moduleinfo($data, $course);
		return $fromform;
	}
	
	/**
	 * Returns description of method result value
	 *
	 * @return external_description
	 * @since Moodle 2.2
	 */
	public static function create_course_notvisible_returns() {
		return new external_value(PARAM_INT, 'course id');
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	public static function courses_create_tags_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'course id'),
						'tag' => new external_multiple_structure(
								new external_value(PARAM_TEXT, 'tag name')
								)
						
				)
			);
	}

	public static function courses_create_tags($courseid, $tags) {
		global $CFG, $USER, $DB;
		require_once($CFG->dirroot.'/tag/locallib.php');
		
		$params = self::validate_parameters(self::courses_create_tags_parameters(),
				array('courseid' => $courseid, 'tag' => $tags));
		
		//foreach ($params['tag'] as $index => $){
		//$tags = $tag;
		//$USER = get_admin();
		//Check for a valid array of tags
		if(empty($tags) && count($tags)<=0) {
			return false;
		}
		
		$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
		
		if(!$course) {
			return false;
		}
		
		if(!$context = context_course::instance($course->id)) {
			return false;
		}
		
		//Check for string values in tags
		$validtags = array();
		foreach($tags as $tag) {
			if(is_string($tag))
				$validtags[] = $tag;
		}
		
		//Get all existing tags from course
		$coursetags = tag_get_tags('course', $course->id);
		foreach($coursetags as $course_tag){
			//Delete current tags from course
			tag_set_delete('course', $course->id, $course_tag->name);
		}
		
		//Insert new tags into course
		tag_set('course', $course->id, $tags, 'core', $context->id);
		
		//tag_set_delete('course', $course->id, 'tlecuitl');
		//tag_set_add('course', $course->id, 'tlecuitl', 'core', $context->id);
		
		//return tag_get_tags('course', $course->id);
		return true;
	}
	
	
	public static function courses_create_tags_returns() {
		return new external_value(PARAM_INT, 'Si fue creado (1) o no fue creado (0)');
	}
	
	
	
	
	
	
	
	
	
	
	
	
	public static function courses_duplicate_course_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'course id'),
						'data' => new external_value(PARAM_INT, 'Indica si se realiza la copia con datos (SI = 1) o sin datos (NO = 0)',
														VALUE_DEFAULT, 0),
						'visible' => new external_value(PARAM_INT, 'Indica si la copia será visible (SI = 1) o no visible (NO = 0)',
								VALUE_DEFAULT, 0),
				)
		);
	}
	
	public static function courses_duplicate_course($courseid, $data, $visible) {
		global $CFG, $USER, $DB;
		require_once($CFG->dirroot . '/course/externallib.php');

		$params = self::validate_parameters(self::courses_duplicate_course_parameters(),
				array('courseid' => $courseid, 'data' => $data, 'visible' => $visible));
		
		// The CLONEing options (these are the defaults).
		if($data) {//Si el curso va con datos de usuarios
			$options = array(
					array ('name' => 'activities', 'value' => 1),
					array ('name' => 'blocks', 'value' => 1),
					array ('name' => 'filters', 'value' => 1),
					array ('name' => 'users', 'value' => 1),
					array ('name' => 'role_assignments', 'value' => 1),
					array ('name' => 'comments', 'value' => 1),
					array ('name' => 'userscompletion', 'value' => 1),
					array ('name' => 'logs', 'value' => 1),
					array ('name' => 'grade_histories', 'value' => 1),
			);
		}else{ // El curso van sin datos de usuarios
			$options = array(
					array ('name' => 'activities', 'value' => 1),
					array ('name' => 'blocks', 'value' => 1),
					array ('name' => 'filters', 'value' => 1),
					array ('name' => 'users', 'value' => 0),
					array ('name' => 'role_assignments', 'value' => 0),
					array ('name' => 'comments', 'value' => 0),
					array ('name' => 'userscompletion', 'value' => 0),
					array ('name' => 'logs', 'value' => 0),
					array ('name' => 'grade_histories', 'value' => 0),
			);
		}
		
		// To simplify the skeleton code, let's run the whole thing as an
		// admin. You probably *don't* want to do this in production code.
		$USER = get_admin();
		
		//Get course info
		if(!$origincourse = $DB->get_record('course', array('id' => $courseid))) {
			return false;
		}
		//print_object($origincourse);
		// Get category ID from the original course
		$newcategoryid = $origincourse->category;
		// Rename the new course
		$newfullname = $origincourse->fullname."_copy".time();
		$newshortname = $origincourse->shortname."_c".time();
		
		//echo "El ID del curso es: ".$courseid."<br>";
		//echo "El nombre del curso es: ".$newfullname."<br>";
		//echo "La clave del curso es: ".$newshortname."<br>";
		//echo "La categoria del curso es: ".$newcategoryid."<br>";
		//echo "La visibilidad del curso es: ".$visible."<br>";
		//echo "Los parametros del curso son: <br>";
		//print_object($options);
		//exit;
		
		try {
			$newcourse = core_course_external::duplicate_course($courseid, $newfullname, $newshortname, $newcategoryid, $visible, $options);
		} catch (exception $e) {
			// Some debugging information to see what went wrong
			print_object($e);
			//var_dump($e);
		}
		
		//echo 'Nuevo curso duplica"'. $newcourse['shortname'] . '" and id "' . $newcourse['id'] . "\"\n";
		return $newcourse['id'];
	}
	
	
	public static function courses_duplicate_course_returns() {
		return new external_value(PARAM_INT, 'ID del nuevo curso clonado');
	}
	
	
	
	public static function courses_backup_course_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'course id'),
						'data' => new external_value(PARAM_INT, 'Indica si se realiza el respaldo con datos (SI = 1) o sin datos (NO = 0)',
								VALUE_DEFAULT, 0)
				)
				);
	}
	
	public static function courses_backup_course($courseid, $data) {
		global $CFG, $USER, $DB;
		require_once($CFG->dirroot . '/course/externallib.php');
	
		$params = self::validate_parameters(self::courses_backup_course_parameters(),
				array('courseid' => $courseid, 'data' => $data));
	
		// The CLONEing options (these are the defaults).
		if($data) {//Si el curso va con datos de usuarios
			$options = array(
					array ('name' => 'activities', 'value' => 1),
					array ('name' => 'blocks', 'value' => 1),
					array ('name' => 'filters', 'value' => 1),
					array ('name' => 'users', 'value' => 1),
					array ('name' => 'role_assignments', 'value' => 1),
					array ('name' => 'comments', 'value' => 1),
					array ('name' => 'userscompletion', 'value' => 1),
					array ('name' => 'logs', 'value' => 1),
					array ('name' => 'grade_histories', 'value' => 1),
			);
		}else{ // El curso van sin datos de usuarios
			$options = array(
					array ('name' => 'activities', 'value' => 1),
					array ('name' => 'blocks', 'value' => 1),
					array ('name' => 'filters', 'value' => 1),
					array ('name' => 'users', 'value' => 0),
					array ('name' => 'role_assignments', 'value' => 0),
					array ('name' => 'comments', 'value' => 0),
					array ('name' => 'userscompletion', 'value' => 0),
					array ('name' => 'logs', 'value' => 0),
					array ('name' => 'grade_histories', 'value' => 0),
			);
		}
	
		return 'https://fs01n1.sendspace.com/dl/f3c4fe57fd1052d75f3598339abb489b/59644fef010da9ca/d596a1/respaldo-moodle2-course-12-ec-26-20170710-2223-nu.mbz';
	}
	
	
	public static function courses_backup_course_returns() {
		return new external_value(PARAM_TEXT, 'URL para descargar el archivo de respaldo');
	}
	
	
	
	
	
	public static function courses_avance_oas_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'course id'),
						'userid' => new external_value(PARAM_INT, 'user id'),
				)
				);
	}
	
	public static function courses_avance_oas($courseid, $userid) {
		global $CFG, $USER, $DB;
	
		$params = self::validate_parameters(self::courses_avance_oas_parameters(),
				array('courseid' => $courseid, 'userid' => $userid ));
	
		
			
		$modinfo = get_fast_modinfo($courseid);
		$course = course_get_format($courseid)->get_course();
		$numSections = 0;
		/*--- CALCULAR LAS CALIFICACIONES DE CADA OBJETO DE APRENDISAJE ---------*/
		$calificacionTrofeo = 0;
		$numeroMedallas = 0;
		$calificacionMedalla = array();
		foreach ($modinfo->get_section_info_all() as $section => $thissection) {
			// ID de actividades deseccion: $modinfo->sections[$thissection->section]
			$showsection = $thissection->uservisible ||
			($thissection->visible && !$thissection->available &&
					!empty($thissection->availableinfo));
			if ($thissection->uservisible) {
				if ($section > $course->numsections || $section==0) {
					continue;
				}
			}
			$numSections++;
		
			if(!isset($modinfo->sections[$thissection->section])){
				continue;
			}
		
		
			$totalCalificacion = 0;
			$objetos = 0;
		
			foreach ($modinfo->sections[$thissection->section] as $modnumber) {
				$mod = $modinfo->cms[$modnumber];
				include_once($CFG->dirroot.'/mod/'.$mod->modname.'/locallib.php');
				if(strpos($mod->modname, 'scorm') !== false){
					if (! $cm = get_coursemodule_from_id($mod->modname, $mod->id, 0, true)) {
						print_error('invalidcoursemodule');
					}
					if (! $scorm = $DB->get_record($mod->modname, array("id" => $cm->instance))) {
						print_error('invalidcoursemodule');
					}
		
					if($mod->modname=='scormsisi'){
						$calculatedgrade = scormsisi_grade_user($scorm, $userid);
					}else{
						$calculatedgrade = scorm_grade_user($scorm, $userid);
					}
		
					if ($scorm->grademethod !== GRADESCOES && !empty($scorm->maxgrade)) {
						$calculatedgrade = $calculatedgrade / $scorm->maxgrade;
						//$calculatedgrade = number_format($calculatedgrade * 100, 0) .'%';
					}
		
					$totalCalificacion += ($calculatedgrade*100);
					$objetos++;
				}
			}
			$numeroMedallas ++;
			if($objetos>0){
				//print_object($totalCalificacion/$objetos);
				$calificacionMedalla[] = ($totalCalificacion/$objetos);
				$calificacionTrofeo += ($totalCalificacion/$objetos);
			}else{
				$calificacionMedalla[] = 0;
			}
		}
		
		//$calificacionTrofeo = 0;
		if($numeroMedallas>0){
			$calificacionTrofeo = round($calificacionTrofeo/$numeroMedallas);
		}
		
		$tiposMedallas = array();
		$medallasTerminadas = 0;
		foreach($calificacionMedalla as $i=>$cal){
			$tiposMedallas[] = $cal>=80?'activa':'inactiva';
			$medallasTerminadas += $cal>=80?1:0;
		
		}
		
		//$this->calificacionMedallas = $calificacionMedalla;
		
		$avanceMedallas = 0;
		if($numeroMedallas>0){
			$avanceMedallas = round($medallasTerminadas * 100 / $numeroMedallas);
		}
			
		$respuesta = array();
		$respuesta['trofeo'] = $calificacionTrofeo;
		$respuesta['medallas'] = $calificacionMedalla;
		$respuesta['avanceMedallas'] = $avanceMedallas;
		$respuesta['tiposMedallas'] = $tiposMedallas;
		return $respuesta['avanceMedallas'];
	}
	
	
	public static function courses_avance_oas_returns() {
		return new external_value(PARAM_INT, 'El avance de las medallas (El valor de la barra de las medallas)');
	}
	
	
		
	
	
}
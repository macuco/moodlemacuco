<?php

/**
 * Clase que registra los servicios relacionados a los cursos
 *
 * @package    local
 * @author macuco juan.manuel.mp8@gmail.com
 * @since Moodle 2.7
 */

//echo "JUAN";
//require_once ("../../config.php");

//create_chat::create_chat();
defined ( 'MOODLE_INTERNAL' ) || die ();
// echo $CFG->dirroot.'/course/lib.php';
require_once ("$CFG->libdir/externallib.php");
 require_once($CFG->dirroot.'/course/lib.php');
 
/**
 * core grades functions
 */
class local_mods extends external_api {
	
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 * @since Moodle 2.3
	 */
	public static function create_forum_parameters() {
		return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id of course'),
            	'section' => new external_value(PARAM_INT, 'number of section'),
            	'name' => new external_value(PARAM_TEXT, 'Name of the forum'),
            	'description' => new external_value(PARAM_TEXT, 'Description of the forum')
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
	public static function create_forum($courseid, $section, $name, $description) {
		
		
		global $CFG, $DB;
		require_once ($CFG->dirroot . "/course/lib.php");
		
		// validate parameter
		$params = self::validate_parameters ( self::create_forum_parameters (), array (
				'courseid' => $courseid, 'section'=>$section, 'name'=>$name, 'description'=>$description 
		) );
		
		
		
		//require_once("../../config.php");
		require_once($CFG->dirroot."/course/lib.php");
		require_once($CFG->libdir.'/filelib.php');
		require_once($CFG->libdir.'/gradelib.php');
		require_once($CFG->libdir.'/completionlib.php');
		require_once($CFG->libdir.'/plagiarismlib.php');
		require_once($CFG->dirroot . '/course/modlib.php');
		
		$add    = 'forum';
		
		if (!empty($add)) {
		
			
			$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
			
			
			list($module, $context, $cw) = can_add_moduleinfo($course, $add, $section);
		
			$cm = null;
		
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
			$data->name				= $name;
			$data->introeditor 		= Array('text' => $description);
			$data->type				= 'general';
			$data->cmidnumber		= '';
		
			if (plugin_supports('mod', $data->modulename, FEATURE_MOD_INTRO, true)) {
				$draftid_editor = file_get_submitted_draft_itemid('introeditor');
				file_prepare_draft_area($draftid_editor, null, null, null, null, array('subdirs'=>true));
				$data->introeditor = array('text'=>$description, 'format'=>FORMAT_HTML, 'itemid'=>$draftid_editor); // TODO: add better default
			}
		
			if (plugin_supports('mod', $data->modulename, FEATURE_ADVANCED_GRADING, false)
					and has_capability('moodle/grade:managegradingforms', $context)) {
						require_once($CFG->dirroot.'/grade/grading/lib.php');
		
						$data->_advancedgradingdata['methods'] = grading_manager::available_methods();
						$areas = grading_manager::available_areas('mod_'.$module->name);
		
						foreach ($areas as $areaname => $areatitle) {
							$data->_advancedgradingdata['areas'][$areaname] = array(
									'title'  => $areatitle,
									'method' => '',
							);
							$formfield = 'advancedgradingmethod_'.$areaname;
							$data->{$formfield} = '';
						}
					}
		
					if (!empty($type)) { //TODO: hopefully will be removed in 2.0
						$data->type = $type;
					}
		
					$sectionname = get_section_name($course, $cw);
					$fullmodulename = get_string('modulename', $module->name);
		
					if ($data->section && $course->format != 'site') {
						$heading = new stdClass();
						$heading->what = $fullmodulename;
						$heading->to   = $sectionname;
						$pageheading = get_string('addinganewto', 'moodle', $heading);
					} else {
						$pageheading = get_string('addinganew', 'moodle', $fullmodulename);
					}
					$navbaraddition = $pageheading;
		
		}
		
		$fromform = $data;
		
			// Convert the grade pass value - we may be using a language which uses commas,
			// rather than decimal points, in numbers. These need to be converted so that
			// they can be added to the DB.
			
			if (isset($fromform->gradepass)) {
				$fromform->gradepass = unformat_float($fromform->gradepass);
			}
				$fromform = add_moduleinfo($fromform, $course);
			
		
		return $fromform->coursemodule;
	}
	
	/**
	 * Returns description of method result value
	 *
	 * @return external_description
	 * @since Moodle 2.2
	 */
	public static function create_forum_returns() {
		return new external_value ( PARAM_INT, 'forumid' );
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * 
	 * @return external_function_parameters
	 */
	public static function delete_mod_parameters() {
		return new external_function_parameters ( array (
				'modid' => new external_value ( PARAM_INT, 'id del modulo a eliminar' ) 
		) );
	}
	
	/**
	 * Elimina cualquier mod (Forum, SCORM, CHAT, ETC)
	 * @param unknown $modid
	 */
	public static function delete_mod($modid) {
		global $CFG, $DB;
		require_once ($CFG->dirroot . "/course/lib.php");
		
		// validate parameter
		$params = self::validate_parameters ( self::delete_mod_parameters (), array (
				'modid' => $modid 
		) );
		
		$cm = get_coursemodule_from_id ( '', $modid, 0, true, MUST_EXIST );
		$course = $DB->get_record ( 'course', array (
				'id' => $cm->course 
		), '*', MUST_EXIST );
		
		$modcontext = context_module::instance ( $cm->id );
		require_capability ( 'moodle/course:manageactivities', $modcontext );
		
		// Delete the module.
		course_delete_module ( $cm->id );
	}
	
	/**
	 * 
	 * @return NULL
	 */
	public static function delete_mod_returns() {
		return null;
	}
	
	
	
	
	
	
	/**
	 *
	 * @return external_function_parameters
	 */
	public static function delete_discussion_parameters() {
		return new external_function_parameters ( array (
				'discussionid' => new external_value ( PARAM_INT, 'id del tema de discusión' )
		) );
	}
	
	/**
	 * Elimina cualquier mod (Forum, SCORM, CHAT, ETC)
	 * @param unknown $modid
	 */
	public static function delete_discussion($discussionid) {
		global $CFG, $DB;
		
		require_once ($CFG->dirroot . "/course/lib.php");
		require_once ($CFG->dirroot . "/mod/forum/lib.php");
	
		// validate parameter
		$params = self::validate_parameters ( self::delete_discussion_parameters (), array (
				'discussionid' => $discussionid
		) );
	
		$discussion = $DB->get_record('forum_discussions', array('id' => $discussionid), '*', MUST_EXIST);
	
		
		$delete = $discussion->firstpost;
		
		if (! $post = forum_get_post_full($delete)) {
			print_error('invalidpostid', 'forum','',$delete);
		}
		if (! $discussion = $DB->get_record("forum_discussions", array("id" => $post->discussion))) {
			print_error('notpartofdiscussion', 'forum');
		}
		if (! $forum = $DB->get_record("forum", array("id" => $discussion->forum))) {
			print_error('invalidforumid', 'forum');
		}
		if (!$cm = get_coursemodule_from_instance("forum", $forum->id, $forum->course)) {
			print_error('invalidcoursemodule');
		}
		if (!$course = $DB->get_record('course', array('id' => $forum->course))) {
			print_error('invalidcourseid');
		}
		
		
		$modcontext = context_module::instance($cm->id);
		if (! (($post->userid == $USER->id && has_capability ( 'mod/forum:deleteownpost', $modcontext )) || has_capability ( 'mod/forum:deleteanypost', $modcontext ))) {
			print_error ( 'cannotdeletepost', 'forum' );
		}
		
		$replycount = forum_count_replies ( $post );
			
			// check user capability to delete post.
		$timepassed = time () - $post->created;
		if (($timepassed > $CFG->maxeditingtime) && ! has_capability ( 'mod/forum:deleteanypost', $modcontext )) {
			print_error ( "cannotdeletepost", "forum", forum_go_back_to ( new moodle_url ( "/mod/forum/discuss.php", array (
					'd' => $post->discussion 
			) ) ) );
		}
		
		if ($post->totalscore) {
			notice ( get_string ( 'couldnotdeleteratings', 'rating' ), forum_go_back_to ( new moodle_url ( "/mod/forum/discuss.php", array (
					'd' => $post->discussion 
			) ) ) );
		} else if ($replycount && ! has_capability ( 'mod/forum:deleteanypost', $modcontext )) {
			print_error ( "couldnotdeletereplies", "forum", forum_go_back_to ( new moodle_url ( "/mod/forum/discuss.php", array (
					'd' => $post->discussion 
			) ) ) );
		} else {
			if (! $post->parent) { // post is a discussion topic as well, so delete discussion
				if ($forum->type == 'single') {
					notice ( "Sorry, but you are not allowed to delete that discussion!", forum_go_back_to ( new moodle_url ( "/mod/forum/discuss.php", array (
							'd' => $post->discussion 
					) ) ) );
				}
				forum_delete_discussion ( $discussion, false, $course, $cm, $forum );
				
				$params = array (
						'objectid' => $discussion->id,
						'context' => $modcontext,
						'other' => array (
								'forumid' => $forum->id 
						) 
				);
				
				$event = \mod_forum\event\discussion_deleted::create ( $params );
				$event->add_record_snapshot ( 'forum_discussions', $discussion );
				$event->trigger ();
				
				
			} else if (forum_delete_post ( $post, has_capability ( 'mod/forum:deleteanypost', $modcontext ), $course, $cm, $forum )) {
				
				if ($forum->type == 'single') {
					// Single discussion forums are an exception. We show
					// the forum itself since it only has one discussion
					// thread.
					$discussionurl = new moodle_url ( "/mod/forum/view.php", array (
							'f' => $forum->id 
					) );
				} else {
					$discussionurl = new moodle_url ( "/mod/forum/discuss.php", array (
							'd' => $discussion->id 
					) );
				}
				
				return true;
			} else {
				print_error ( 'errorwhiledelete', 'forum' );
			}
		}
		return true;
	}
	
	/**
	 *
	 * @return NULL
	 */
	public static function delete_discussion_returns() {
		return new external_value ( PARAM_INT, 'eliminado' );;
	}
	
	
	
	
	
	
	
	
	
	
	public static function create_scorm_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'id of course'),
						'section' => new external_value(PARAM_INT, 'number of section'),
						'name' => new external_value(PARAM_TEXT, 'Name of the forum'),
						'description' => new external_value(PARAM_TEXT, 'Description of the forum')
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
	public static function create_scorm($courseid, $section, $name, $description) {
	
	
		global $CFG, $DB;
		require_once ($CFG->dirroot . "/course/lib.php");
	
		// validate parameter
		$params = self::validate_parameters ( self::create_scorm_parameters (), array (
				'courseid' => $courseid, 'section'=>$section, 'name'=>$name, 'description'=>$description
		) );
	
	
	
		//require_once("../../config.php");
		require_once($CFG->dirroot."/course/lib.php");
		require_once($CFG->libdir.'/filelib.php');
		require_once($CFG->libdir.'/gradelib.php');
		require_once($CFG->libdir.'/completionlib.php');
		require_once($CFG->libdir.'/plagiarismlib.php');
		require_once($CFG->dirroot . '/course/modlib.php');
	
		$add    = 'scormsisi';
	
		if (!empty($add)) {
	
				
			$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
				
			try{
			list($module, $context, $cw) = can_add_moduleinfo($course, $add, $section);
			}catch(Exception $e){
				$add = 'scorm';
				try{
					list($module, $context, $cw) = can_add_moduleinfo($course, $add, $section);
				}catch (Exception $e){
					throw $e;
				}
			}
	
			$cm = null;
	
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
			$data->name				= $name;
			$data->introeditor 		= Array('text' => $description, 'format'=>1);
			$data->type				= 'general';
			$data->cmidnumber		= '';
			$data->showdescription  = 1;
			$data->width			= 100;
			$data->height			= 500;
			$data->packagefile		= 266820094; //Este es el id del archivo
			//$data->packagefile		= 426104907;//Este es el id del archivo local
	
			
		
			
			
			if (plugin_supports('mod', $data->modulename, FEATURE_MOD_INTRO, true)) {
				$draftid_editor = file_get_submitted_draft_itemid('introeditor');
				file_prepare_draft_area($draftid_editor, null, null, null, null, array('subdirs'=>true));
				$data->introeditor = array('text'=>$description, 'format'=>FORMAT_HTML, 'itemid'=>$draftid_editor); // TODO: add better default
			}
			
	
			if (plugin_supports('mod', $data->modulename, FEATURE_ADVANCED_GRADING, false)
					and has_capability('moodle/grade:managegradingforms', $context)) {
						require_once($CFG->dirroot.'/grade/grading/lib.php');
	
						$data->_advancedgradingdata['methods'] = grading_manager::available_methods();
						$areas = grading_manager::available_areas('mod_'.$module->name);
	
						foreach ($areas as $areaname => $areatitle) {
							$data->_advancedgradingdata['areas'][$areaname] = array(
									'title'  => $areatitle,
									'method' => '',
							);
							$formfield = 'advancedgradingmethod_'.$areaname;
							$data->{$formfield} = '';
						}
					}
	
					if (!empty($type)) { //TODO: hopefully will be removed in 2.0
						$data->type = $type;
					}
	
					$sectionname = get_section_name($course, $cw);
					$fullmodulename = get_string('modulename', $module->name);
	
					if ($data->section && $course->format != 'site') {
						$heading = new stdClass();
						$heading->what = $fullmodulename;
						$heading->to   = $sectionname;
						$pageheading = get_string('addinganewto', 'moodle', $heading);
					} else {
						$pageheading = get_string('addinganew', 'moodle', $fullmodulename);
					}
					$navbaraddition = $pageheading;
	
		}
	
		// --------- VERIFICAR QUE EXISTA EL ARCHIVO --------------
		$fs = get_file_storage();
		//$fs->delete_area_files($context->id, 'mod_scorm', 'package');
		file_save_draft_area_files($scorm->packagefile, 0, 'mod_'.$add, 'package',
				0, array('subdirs' => 0, 'maxfiles' => 1));
		// Get filename of zip that was uploaded.
		$files = $fs->get_area_files($context->id, 'mod_'.$add, 'package', 0, '', false);
		$file = reset($files);
		if($file==null){
			//throw new Exception('No existe un paquete ZIP para poder ser creado el SCORM.');
		//	unset($data->packagefile);
		}
		//-------- FIN VERIFICAR QUE EXISTA EL ARCHIVO --------------
		
		$fromform = $data;
	
		// Convert the grade pass value - we may be using a language which uses commas,
		// rather than decimal points, in numbers. These need to be converted so that
		// they can be added to the DB.
			
		if (isset($fromform->gradepass)) {
			$fromform->gradepass = unformat_float($fromform->gradepass);
		}
		
		$fromform = add_moduleinfo($fromform, $course);
		//print_object($fromform);exit;
	
		return $fromform->coursemodule;
	}
	
	/**
	 * Returns description of method result value
	 *
	 * @return external_description
	 * @since Moodle 2.2
	 */
	public static function create_scorm_returns() {
		return new external_value ( PARAM_INT, 'scormid' );
	}
	
	
	
	
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 * @since Moodle 3.0
	 */
	public static function add_discussion_news_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'Course ID'),
						'subject' => new external_value(PARAM_TEXT, 'New Discussion subject'),
						'message' => new external_value(PARAM_RAW, 'New Discussion message (only html format allowed)'),
						'groupid' => new external_value(PARAM_INT, 'The group, default to -1', VALUE_DEFAULT, -1),
						'options' => new external_multiple_structure (
								new external_single_structure(
										array(
												'name' => new external_value(PARAM_ALPHANUM,
														'The allowed keys (value format) are:
                                        discussionsubscribe (bool); subscribe to the discussion?, default to true
                            '),
												'value' => new external_value(PARAM_RAW, 'The value of the option,
                                                            This param is validated in the external function.'
														)
										)
										), 'Options', VALUE_DEFAULT, array())
				)
				);
	}
	
	/**
	 * Add a new discussion into an existing forum.
	 *
	 * @param int $courseid the course instance id
	 * @param string $subject new discussion subject
	 * @param string $message new discussion message (only html format allowed)
	 * @param int $groupid the user course group
	 * @param array $options optional settings
	 * @return array of warnings and the new discussion id
	 * @since Moodle 3.0
	 * @throws moodle_exception
	 */
	public static function add_discussion_news($courseid, $subject, $message, $groupid = -1, $options = array()) {
		global $DB, $CFG;
		require_once($CFG->dirroot . "/mod/forum/lib.php");
	
		$params = self::validate_parameters(self::add_discussion_news_parameters(),
				array(
						'courseid' => $courseid,
						'subject' => $subject,
						'message' => $message,
						'groupid' => $groupid,
						'options' => $options
				));
	
		// ---- Obtener el foro de novedades
		$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
		$context = context_course::instance($courseid);
		if(!empty($context)) {
			$news_forum = forum_get_course_forum($context->instanceid, "news"); // Obtener el foro de novedades
			$params['forumid'] = $news_forum->id;
		}else{
			throw new moodle_exception('errorinvalidparam', 'webservice', '', $courseid);
		}
	
		// Validate options.
		$options = array(
				'discussionsubscribe' => true
		);
		foreach ($params['options'] as $option) {
			$name = trim($option['name']);
			switch ($name) {
				case 'discussionsubscribe':
					$value = clean_param($option['value'], PARAM_BOOL);
					break;
				default:
					throw new moodle_exception('errorinvalidparam', 'webservice', '', $name);
			}
			$options[$name] = $value;
		}
	
		$warnings = array();
	
		// Request and permission validation.
		$forum = $DB->get_record('forum', array('id' => $params['forumid']), '*', MUST_EXIST);
		list($course, $cm) = get_course_and_cm_from_instance($forum, 'forum');
	
		$context = context_module::instance($cm->id);
		self::validate_context($context);
	
		// Normalize group.
		if (!groups_get_activity_groupmode($cm)) {
			// Groups not supported, force to -1.
			$groupid = -1;
		} else {
			// Check if we receive the default or and empty value for groupid,
			// in this case, get the group for the user in the activity.
			if ($groupid === -1 or empty($params['groupid'])) {
				$groupid = groups_get_activity_group($cm);
			} else {
				// Here we rely in the group passed, forum_user_can_post_discussion will validate the group.
				$groupid = $params['groupid'];
			}
		}
	
		if (!forum_user_can_post_discussion($forum, $groupid, -1, $cm, $context)) {
			throw new moodle_exception('cannotcreatediscussion', 'forum');
		}
	
		$thresholdwarning = forum_check_throttling($forum, $cm);
		forum_check_blocking_threshold($thresholdwarning);
	
		// Create the discussion.
		$discussion = new stdClass();
		$discussion->course = $course->id;
		$discussion->forum = $forum->id;
		$discussion->message = $params['message'];
		$discussion->messageformat = FORMAT_HTML;   // Force formatting for now.
		$discussion->messagetrust = trusttext_trusted($context);
		$discussion->itemid = 0;
		$discussion->groupid = $groupid;
		$discussion->mailnow = 0;
		$discussion->subject = $params['subject'];
		$discussion->name = $discussion->subject;
		$discussion->timestart = 0;
		$discussion->timeend = 0;
	
		if ($discussionid = forum_add_discussion($discussion)) {
	
			$discussion->id = $discussionid;
	
			// Trigger events and completion.
	
			$params = array(
					'context' => $context,
					'objectid' => $discussion->id,
					'other' => array(
							'forumid' => $forum->id,
					)
			);
			$event = \mod_forum\event\discussion_created::create($params);
			$event->add_record_snapshot('forum_discussions', $discussion);
			$event->trigger();
	
			$completion = new completion_info($course);
			if ($completion->is_enabled($cm) &&
					($forum->completiondiscussions || $forum->completionposts)) {
						$completion->update_state($cm, COMPLETION_COMPLETE);
					}
	
					$settings = new stdClass();
					$settings->discussionsubscribe = $options['discussionsubscribe'];
					forum_post_subscription($settings, $forum, $discussion);
		} else {
			throw new moodle_exception('couldnotadd', 'forum');
		}
	
		$result = array();
		$result['discussionid'] = $discussionid;
		$result['warnings'] = $warnings;
		return $result;
	}
	
	/**
	 * Returns description of method result value
	 *
	 * @return external_description
	 * @since Moodle 3.0
	 */
	public static function add_discussion_news_returns() {
		return new external_single_structure(
				array(
						'discussionid' => new external_value(PARAM_INT, 'New Discussion ID'),
						'warnings' => new external_warnings()
				)
				);
	}
	
	
	
	
}


//echo "JUAN";
//local_mods::create_chat();

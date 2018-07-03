<?php


require_once ($CFG->libdir . "/accesslib.php");
class local_role extends external_api {
	
	/**
	 * Returns description of method parameters
	 * 
	 * @return external_function_parameters
	 */
	public static function is_user_enrolled_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'userid' => new external_value ( PARAM_INT, 'id of user to check' ) 
		) );
	}
	
	/**
	 * Check is a user is enrolled in a context course
	 *
	 * @param int $courseid
	 *        	Course ID to check if a user has been enrolled in this course
	 * @param int $userid
	 *        	User ID to check if this user has been enrolled
	 *        	
	 * @return bool TRUE if user is enrolled or FALSE if not
	 */
	public static function is_user_enrolled($courseid, $userid) {
		global $DB;
		
		// validate parameter
		$params = self::validate_parameters ( self::is_user_enrolled_parameters (), array (
				'courseid' => $courseid, 'userid' => $userid
		) );
		
		//$USER = get_admin ();
		
		// Check if variables exist
		if (empty ( $courseid ) || empty ( $userid )) {
			return false;
		}
		
		// Check if user exist in database
		if (! $user = $DB->get_record ( 'user', array (
				'id' => $userid 
		) )) {
			return false;
		}
		
		// Check for valid context course
		try {
			$context = context_course::instance ( $courseid );
		} catch ( exception $e ) {
			// Some debugging information to see what went wrong
			return false;
		}
		
		//print_object(is_enrolled ( $context, $user->id, '', true ));exit;
		// Is user enrolled in context course?
		return is_enrolled ( $context, $user->id, '', true );
	}
	
	/**
	 * Returns description of method result value
	 * 
	 * @return external_description
	 */
	public static function is_user_enrolled_returns() {
		return new external_value ( PARAM_BOOL, 'A vlue 1 to enroled, 0 not enroled' );
	}
}

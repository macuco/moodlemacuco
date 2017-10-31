<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This script delegates file serving to individual plugins
 *
 * @package    core
 * @subpackage file
 * @copyright  2008 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Disable moodle specific debug messages and any errors in output.
define('NO_DEBUG_DISPLAY', true);

require_once('../../config.php');
require_once('../../lib/filelib.php');

$relativepath = get_file_argument();
$forcedownload = optional_param('forcedownload', 0, PARAM_BOOL);
$preview = optional_param('preview', null, PARAM_ALPHANUM);
// Offline means download the file from the repository and serve it, even if it was an external link.
// The repository may have to export the file to an offline format.
$offline = optional_param('offline', 0, PARAM_BOOL);
$embed = optional_param('embed', 0, PARAM_BOOL);
file_pluginfile_inea($relativepath, $forcedownload, $preview, $offline, $embed);

/**
 * This function delegates file serving to individual plugins
 *
 * @param string $relativepath
 * @param bool $forcedownload
 * @param null|string $preview the preview mode, defaults to serving the original file
 * @param boolean $offline If offline is requested - don't serve a redirect to an external file, return a file suitable for viewing
 *                         offline (e.g. mobile app).
 * @param bool $embed Whether this file will be served embed into an iframe.
 * @todo MDL-31088 file serving improments
 */
function file_pluginfile_inea($relativepath, $forcedownload, $preview = null, $offline = false, $embed = false) {
    global $DB, $CFG, $USER;
    // relative path must start with '/'
    if (!$relativepath) {
        print_error('invalidargorconf');
    } else if ($relativepath[0] != '/') {
        print_error('pathdoesnotstartslash');
    }
    
    // extract relative path components
    $args = explode('/', ltrim($relativepath, '/'));
    
    if (count($args) < 3) { // always at least context, component and filearea
        print_error('invalidarguments');
    }
    
    $contextid = (int)array_shift($args);
    $component = clean_param(array_shift($args), PARAM_COMPONENT);
    $filearea  = clean_param(array_shift($args), PARAM_AREA);
    
    list($context, $course, $cm) = get_context_info_array($contextid);
    
    $fs = get_file_storage();
    
    $sendfileoptions = ['preview' => $preview, 'offline' => $offline, 'embed' => $embed];
    // ========================================================================================================================
    if (strpos($component, 'mod_') === 0) {
        $modname = substr($component, 4);
        if (!file_exists("$CFG->dirroot/mod/$modname/lib.php")) {
            send_file_not_found();
        }
        require_once("$CFG->dirroot/mod/$modname/lib.php");
        
        if ($context->contextlevel == CONTEXT_MODULE) {
            if ($cm->modname !== $modname) {
                // somebody tries to gain illegal access, cm type must match the component!
                send_file_not_found();
            }
        }
        
        if ($filearea === 'intro') {
            if (!plugin_supports('mod', $modname, FEATURE_MOD_INTRO, true)) {
                send_file_not_found();
            }
            
            // Require login to the course first (without login to the module).
            require_course_login($course, true);
            
            // Now check if module is available OR it is restricted but the intro is shown on the course page.
            $cminfo = cm_info::create($cm);
            if (!$cminfo->uservisible) {
                if (!$cm->showdescription || !$cminfo->is_visible_on_course_page()) {
                    // Module intro is not visible on the course page and module is not available, show access error.
                    require_course_login($course, true, $cminfo);
                }
            }
            
            // all users may access it
            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, 'mod_'.$modname, 'intro', 0, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }
            
            // finally send the file
            send_stored_file($file, null, 0, false, $sendfileoptions);
        }
        
        $filefunction = $component.'_pluginfile';
        $filefunctionold = $modname.'_pluginfile';
        
        if (function_exists($filefunction)) {
            // if the function exists, it must send the file and terminate. Whatever it returns leads to "not found"
            $filefunction($course, $cm, $context, $filearea, $args, $forcedownload, $sendfileoptions);
        } else if (function_exists($filefunctionold)) {
            // if the function exists, it must send the file and terminate. Whatever it returns leads to "not found"
            $filefunctionold($course, $cm, $context, $filearea, $args, $forcedownload, $sendfileoptions);
        }
        
        send_file_not_found();
        //exit();
        // ========================================================================================================================
    }
    
}


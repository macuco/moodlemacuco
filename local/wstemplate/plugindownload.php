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
 * This script delegates backups file serving
 *
 * @package    local
 * @subpackage file
 * @copyright  2017 Eddy Roman 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('../../lib/filelib.php');

$relativepath = get_file_argument();
$forcedownload = optional_param('forcedownload', true, PARAM_BOOL);
$preview = optional_param('preview', null, PARAM_ALPHANUM);

file_plugindownload($relativepath, $forcedownload, $preview);

/**
 * This function delegates backups file serving
 *
 * @param string $relativepath
 * @param bool $forcedownload
 * @param null|string $preview the preview mode, defaults to serving the original file
 * @todo MDL-31088 file serving improments
 */
function file_plugindownload($relativepath, $forcedownload = true, $preview = null) {
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
    //print_object($component); exit;
    $fs = get_file_storage();
   
    // ========================================================================================================================
   if ($component === 'user') {
        if ($filearea === 'backup' and $context->contextlevel == CONTEXT_USER) {

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, 'user', 'backup', 0, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close(); // Unlock session during file serving.
            send_stored_file($file, 0, 0, true, array('preview' => $preview)); // must force download - security!

        } else {
            send_file_not_found();
        }

    // ========================================================================================================================
    } else if ($component === 'backup') {
        if ($filearea === 'course' and $context->contextlevel == CONTEXT_COURSE) {

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, 'backup', 'course', 0, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close(); // Unlock session during file serving.
            send_stored_file($file, 0, 0, $forcedownload, array('preview' => $preview));

        } else {
            send_file_not_found();
        }

    // ========================================================================================================================
    } else {
        // try to serve general plugin file in arbitrary context
        $dir = core_component::get_component_directory($component);
        if (!file_exists("$dir/lib.php")) {
            send_file_not_found();
        }
        include_once("$dir/lib.php");

        $filefunction = $component.'_pluginfile';
        if (function_exists($filefunction)) {
            // if the function exists, it must send the file and terminate. Whatever it returns leads to "not found"
            $filefunction($course, $cm, $context, $filearea, $args, $forcedownload, array('preview' => $preview));
        }

        send_file_not_found();
    }    
}

?>
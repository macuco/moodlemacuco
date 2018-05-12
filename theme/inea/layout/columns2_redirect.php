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
 * A two column layout for the inea theme.
 *
 * @package   theme_inea
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');

if (isloggedin()) {
    //---- Verificación si tiene rol en el contexto y redireccionar al mod inea si existe -----
    $modinfo = get_fast_modinfo($COURSE->id);
    $cContext = context_course::instance($COURSE->id); // global $COURSE
    $currenRole = current(get_user_roles($cContext, $USER->id));
    
    //print_object(get_user_roles($cContext, $USER->id));exit;
    if($currenRole){
        $viewContentCourse = $currenRole->roleid==5||$currenRole->roleid==4? true : false;
        if($viewContentCourse && $ineas = $modinfo->get_instances_of('inea')){
            print_object($ineas);
            
            $inea = reset($ineas);
            echo $CFG->wwwroot .'/mod/inea/view.php?id='.$inea->id.'&redirect=0';
            print_object(reset($ineas));exit;
            redirect($CFG->wwwroot .'/mod/inea/view.php?id='.$inea->id.'&redirect=0');
            array_values($modinfo->get_instances_of('inea'))[0]->id;
            //TODO REDIRECT
        }
    }
    
    // -----------------------------------------------------------------------------------------
    
    
    
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}
$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu)
];

$templatecontext['flatnavigation'] = $PAGE->flatnav;
echo $OUTPUT->render_from_template('theme_inea/columns2', $templatecontext);


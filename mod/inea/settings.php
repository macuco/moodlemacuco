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
 * INEA module admin settings and defaults
 *
 * @package    mod_inea
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
                                                           RESOURCELIB_DISPLAY_EMBED,
                                                           RESOURCELIB_DISPLAY_FRAME,
                                                           RESOURCELIB_DISPLAY_DOWNLOAD,
                                                           RESOURCELIB_DISPLAY_OPEN,
                                                           RESOURCELIB_DISPLAY_NEW,
                                                           RESOURCELIB_DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_AUTO,
                                   RESOURCELIB_DISPLAY_EMBED,
                                   RESOURCELIB_DISPLAY_DOWNLOAD,
                                   RESOURCELIB_DISPLAY_OPEN,
                                   RESOURCELIB_DISPLAY_POPUP,
                                  );

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configtext('inea/framesize',
        get_string('framesize', 'inea'), get_string('configframesize', 'inea'), 130, PARAM_INT));
    $settings->add(new admin_setting_configmultiselect('inea/displayoptions',
        get_string('displayoptions', 'inea'), get_string('configdisplayoptions', 'inea'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('ineamodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('inea/printintro',
        get_string('printintro', 'inea'), get_string('printintroexplain', 'inea'), 1));
    $settings->add(new admin_setting_configselect('inea/display',
        get_string('displayselect', 'inea'), get_string('displayselectexplain', 'inea'), RESOURCELIB_DISPLAY_AUTO,
        $displayoptions));
    $settings->add(new admin_setting_configcheckbox('inea/showsize',
        get_string('showsize', 'inea'), get_string('showsize_desc', 'inea'), 0));
    $settings->add(new admin_setting_configcheckbox('inea/showtype',
        get_string('showtype', 'inea'), get_string('showtype_desc', 'inea'), 0));
    $settings->add(new admin_setting_configcheckbox('inea/showdate',
        get_string('showdate', 'inea'), get_string('showdate_desc', 'inea'), 0));
    $settings->add(new admin_setting_configtext('inea/popupwidth',
        get_string('popupwidth', 'inea'), get_string('popupwidthexplain', 'inea'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('inea/popupheight',
        get_string('popupheight', 'inea'), get_string('popupheightexplain', 'inea'), 450, PARAM_INT, 7));
    $options = array('0' => get_string('none'), '1' => get_string('allfiles'), '2' => get_string('htmlfilesonly'));
    $settings->add(new admin_setting_configselect('inea/filterfiles',
        get_string('filterfiles', 'inea'), get_string('filterfilesexplain', 'inea'), 0, $options));
}

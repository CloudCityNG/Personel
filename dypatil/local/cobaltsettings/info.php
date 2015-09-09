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
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage  providing  cobalt settings
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/cobaltsettings/lib.php');
global $CFG, $DB, $USER;
$systemcontext =context_system::instance();
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/cobaltsettings/info.php');
$PAGE->navbar->add(get_string('global_settings', 'local_cobaltsettings'), new moodle_url('/local/cobaltsettings/index.php'));
$PAGE->navbar->add(get_string('info', 'local_cobaltsettings'));
echo $OUTPUT->header();
/* ---global settings heading--- */
echo $OUTPUT->heading(get_string('cobalt_settings', 'local_cobaltsettings'));
$hier1 = new hierarchy();
/* ---instance of school settings form--- */
/* ---adding tabs using cobalt_settings_tabs function--- */
$global_ob = global_settings::getInstance();
$currenttab = 'info';
$global_ob->globalsettings_tabs($currenttab, 'gpasettings');
/* ---description of the cobalt level settings table --- */
echo $OUTPUT->box(get_string('helpinfo', 'local_cobaltsettings'));
$content = get_string('info_help', 'local_cobaltsettings');
echo '<div class="help_cont">' . $content . '<div>';
echo $OUTPUT->footer();
?>





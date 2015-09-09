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
 * @subpackage Academiccalendar
 * @copyright  2012 Naveen <naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE;
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/academiccalendar/create_event_form.php');
require_once($CFG->dirroot . '/local/academiccalendar/lib.php');
$hierarchy = new hierarchy();
$acalendar = academiccalendar :: get_instatnce();
$systemcontext = context_system::instance();
$PAGE->set_url('/local/academiccalendar/index.php');
$PAGE->set_pagelayout('admin');
require_login();
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('pluginname', 'local_academiccalendar'));
$PAGE->set_heading(get_string('pluginname', 'local_academiccalendar'));
$PAGE->navbar->add(get_string('pluginname', 'local_academiccalendar'), new moodle_url('/local/academiccalendar/index.php'));
$PAGE->navbar->add(get_string('info', 'local_academiccalendar'));
$PAGE->requires->css('/local/academiccalendar/css/style.css');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_academiccalendar'));
$currenttab = 'info';
require('tabs.php');
/* ---Moodle 2.2 and onwards--- */
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpinfo', 'local_academiccalendar'));
}
$content = get_string('info_help', 'local_academiccalendar');
$school = get_string('schoolid', 'local_collegestructure');
$program = get_string('program', 'local_programs');
$semester = get_string('semester', 'local_semesters');
$content = preg_replace("/school/i", $school, $content);
$content = preg_replace("/semester/i", $semester, $content);
echo '<div class="help_cont">' . $content . '<div>';
echo $OUTPUT->footer();
?>
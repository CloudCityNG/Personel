
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
 * Edit a tool provided in a course
 *
 * @package    local
 * @subpackage scheduleexam
 * @copyright  2013 pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/graduation/lib.php');
require_once($CFG->dirroot . '/local/graduation/graduation_form.php');

require_once($CFG->dirroot . '/local/lib.php');

require_login();
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/local/graduation/in.php');

echo $OUTPUT->header();

//$x = local_graduation_cron(); exit;

$gradesubobj = new graduation_form();
$gradesubobj->display();



echo $OUTPUT->footer();

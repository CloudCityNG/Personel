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
 * Gradeletter Plugin - to define the gradeletter and the point for a given range of Marks for a course
 *
 * @package    local
 * @subpackage pramod
 * @copyright  2013 Pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
global $CFG, $USER;

$systemcontext =context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

if (!has_capability('local/gradeletter:manage', $systemcontext)) {
    print_error('You dont have permissions');
}

$PAGE->set_url('/local/gradeletter/info.php');

//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_gradeletter'));
$PAGE->navbar->add(get_string('info', 'local_gradeletter'));

echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading(get_string('pluginname', 'local_gradeletter'));
$currenttab = 'info';
//adding tabs
require('tabs.php');

// Moodle 2.2 and onwards
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpinfo', 'local_gradeletter'));
}

echo $OUTPUT->footer();

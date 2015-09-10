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
 * @subpackage users
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/users/lib.php');
$id = optional_param('id', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$sort = optional_param('sort', 'name', PARAM_ALPHANUM);
$dir = optional_param('dir', 'ASC', PARAM_ALPHA);
$perpage = 10;

global $CFG, $USER;
$myuser = users::getInstance();
$hierarchy = new hierarchy();
$systemcontext = context_system::instance();
require_login();

if (!has_capability('local/collegestructure:manage', $systemcontext)) {
    print_error('You dont have permissions');
}

//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/users/info.php');
$PAGE->set_title(get_string('users') . ': ' . get_string('info', 'local_users'));
//Header and the navigation bar
$PAGE->set_heading(get_string('browseusers', 'local_users'));
$PAGE->navbar->add(get_string('manageusers', 'local_users'), new moodle_url('/local/users/index.php', array('id' => $id)));
$PAGE->navbar->add(get_string('info', 'local_users'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageusers', 'local_users'));
//Heading of the page
$currenttab = 'info';
$myuser->createtabview($currenttab);

if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpinfodes', 'local_users'));
}
$content = get_string('info_help', 'local_users');
echo '<div class="help_cont">' . $content . '<div>';
echo $OUTPUT->footer();

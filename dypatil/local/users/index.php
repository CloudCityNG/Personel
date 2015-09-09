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
$page = optional_param('page', 0, PARAM_INT);
$sort = optional_param('sort', 'name', PARAM_ALPHANUM);
$dir = optional_param('dir', 'ASC', PARAM_ALPHA);
$perpage = 10;

global $CFG, $USER;
$myuser = users::getInstance();
$hierarchy = new hierarchy();
$systemcontext = context_system::instance();
require_login();

if (!has_capability('local/collegestructure:manage', $systemcontext) && !has_capability('local/users:view', $systemcontext)) {
    print_error('You dont have permissions');
}

//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);


$PAGE->set_url('/local/users/index.php');
$PAGE->set_title(get_string('users') . ': ' . get_string('browseusers', 'local_users'));
//Header and the navigation bar
$PAGE->set_heading(get_string('browseusers', 'local_users'));
$PAGE->navbar->add(get_string('manageusers', 'local_users'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageusers', 'local_users'));
//Description for the page

$currenttab = 'browse';
$myuser->createtabview($currenttab);
echo $OUTPUT->box(get_string('browseuserspage', 'local_users'));
$filters = new filtering();

$context = context_system::instance();
$extracolumns = get_extra_user_fields($context);
$columns = array_merge(array('firstname', 'lastname'), $extracolumns, array('school', 'role', 'lastaccess'));

foreach ($columns as $column) {
    if ($column == 'school') {
        $string[$column] = get_string('schoolid', 'local_collegestructure');
    } else {
        $string[$column] = get_user_field_name($column);
    }

    if ($sort != $column) {
        $columnicon = "";
        if ($column == "lastaccess") {
            $columndir = "DESC";
        } else {
            $columndir = "ASC";
        }
    } else {
        $columndir = $dir == "ASC" ? "DESC" : "ASC";
        if ($column == "lastaccess") {
            $columnicon = ($dir == "ASC") ? "sort_desc" : "sort_asc";
        } else {
            $columnicon = ($dir == "ASC") ? "sort_asc" : "sort_desc";
        }
        $columnicon = "<img class='iconsort' src=\"" . $OUTPUT->pix_url('t/' . $columnicon) . "\" alt=\"\" />";
    }
    if (!($column == 'school' || $column == 'role'))
        $$column = "<a href=\"index.php?sort=$column&amp;dir=$columndir\">" . $string[$column] . "</a>$columnicon";
    else
        $$column = $string[$column];
}

$override = new stdClass();
$override->firstname = 'firstname';
$override->lastname = 'lastname';
$fullnamelanguage = get_string('fullnamedisplay', '', $override);
if (($CFG->fullnamedisplay == 'firstname lastname') or ( $CFG->fullnamedisplay == 'firstname') or ( $CFG->fullnamedisplay == 'language' and $fullnamelanguage == 'firstname lastname' )) {
    $fullnamedisplay = "$firstname / $lastname";
    if ($sort == "name") { // If sort has already been set to something else then ignore.
        $sort = "firstname";
    }
} else { // ($CFG->fullnamedisplay == 'language' and $fullnamelanguage == 'lastname firstname').
    $fullnamedisplay = "$lastname / $firstname";
    if ($sort == "name") { // This should give the desired sorting based on fullnamedisplay.
        $sort = "lastname";
    }
}
list($extrasql, $params) = $filters->get_sql_filter();


$users = $myuser->get_users_listing($sort, $dir, $page * $perpage, $perpage, $extrasql, $params, $context);

$usercount = $myuser->get_usercount();
$usersearchcount = $myuser->get_usercount($extrasql, $params);

if ($extrasql !== '') {
    echo $OUTPUT->heading("$usersearchcount / $usercount " . get_string('users'));
    $usercount = $usersearchcount;
} else {
    echo $OUTPUT->heading("$usercount " . get_string('users'));
}

$baseurl = new moodle_url('/local/users/index.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage));
echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);

if (!$users) {
    $match = array();
    echo $OUTPUT->heading(get_string('nousersfound'));

    $table = new html_table();
    $data = array();
} else {
    $table = new html_table();
    $table->id = "usertable";
    $table->head[] = $fullnamedisplay;
    foreach ($extracolumns as $field) {
        $table->head[] = ${$field};
    }
    $table->head[] = $school;
    $table->head[] = $role;
    $table->head[] = $lastaccess;
    $table->head[] = get_string('action');
    $data = array();
    foreach ($users as $user) {
        $line = array();
        $line[] = html_writer::tag('a', fullname($user, true), array('href' => '' . $CFG->wwwroot . '/local/users/profile.php?id=' . $user->id . ''));
        $line[] = $user->email;
        $line[] = $myuser->get_schoolnames($user);
        $role = $myuser->get_rolename($user->id);
        if ($role) {
            $line[] = ucwords($role->shortname);
        } else if (is_siteadmin($user->id)) {
            $line[] = 'Manager';
        } else {
            $line[] = 'Not assigned';
        }

        $line[] = ($user->lastaccess) ? format_time(time() - $user->lastaccess) : get_string('never');
        if ($user->suspended) {
            foreach ($line as $k => $v) {
                $line[$k] = html_writer::tag('span', $v, array('class' => 'usersuspended'));
            }
        }
        if (has_capability('local/users:manage', $systemcontext))
            $line[] = $myuser->get_different_actions('users', 'user', $user->id, !$user->suspended);
        $data[] = $line;
    }
}
$filters->display_add();
$filters->display_active();

$table->id = "usertable";
$table->size = array('20%', '20%', '15%', '15%', '20%', '10%');
$table->align = array('left', 'left', 'left', 'left', 'left', 'left');
$table->width = '100%';
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
echo $OUTPUT->heading(html_writer::tag('a', get_string('addnewuser', 'local_users'), array('href' => '' . $CFG->wwwroot . '/local/users/user.php')));
echo $OUTPUT->footer();

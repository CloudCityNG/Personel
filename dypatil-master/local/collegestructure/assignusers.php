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
 *
 * @package    Collegestucture
 * @subpackage assign registrarusers
 * @copyright  2013 Niranjan {niranjan@eabyas.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
$page = optional_param('page', 0, PARAM_INT);
/* ---which page to show--- */
$moveto = optional_param('moveto', 0, PARAM_INT);
$sesskey = optional_param('sesskey', '', PARAM_RAW);
$schoolids = optional_param('id', 0, PARAM_INT);

$defaultperpage = 20;
$perpage = optional_param('perpage', $defaultperpage, PARAM_INT);
/* ---how many per page--- */
global $DB;
/* ---first level of checking--- */
require_login();
$systemcontext = context_system::instance();
/* ---second level of checking--- */
if (!has_capability('local/collegestructure:manage', $systemcontext)) {
    $returnurl = new moodle_url('/local/error.php');

    redirect($returnurl);
}
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('assignregistrar_title', 'local_collegestructure'));
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manageschools', 'local_collegestructure'), "/local/collegestructure/index.php", get_string('viewusers', 'local_collegestructure'));
$PAGE->navbar->add(get_string('assignusers', 'local_collegestructure'));
$PAGE->set_url(new moodle_url('/local/collegestructure/assignusers.php', array('id' => $schoolids)));
$PAGE->set_pagelayout('admin');
echo $OUTPUT->header();

$returnurl = new moodle_url('/local/collegestructure/assignusers.php', array('id' => $schoolids));
$hierarche = new hierarchy();
$school = new school();
/* ---Adding the users to the school --- */
/* ---when Registrar select the users to the school and submits to assign users--- */
if (!empty($moveto) and $data = data_submitted()) {

    if (!$destschool = $DB->get_record('local_school', array('id' => $data->moveto))) {
        print_error('cannotfindschool', '', '', $data->moveto);
    }
    /*
     * ###Bugreport #189-Assign Regsitrar
     * @author Naveen Kumar<naveen@eabyas.in>
     * (Resolved) Removed success message from the loop, So that it won't redirect
     */
    $currenturl = "{$CFG->wwwroot}/local/collegestructure/assignusers.php";
    if (empty($data)) {
        $hierarche->set_confirmation(get_string('pleaseselectschool', 'local_collegestructure'), $currenturl);
    }
    $users = array();

    foreach ($data as $key => $value) {
        if (preg_match('/^c\d+$/', $key)) {
            $userid = substr($key, 1);
            array_push($users, $userid);
        }
    }

    $school->add_users($users, $data->moveto);
}
/* ---end of the user assigning to schools--- */

/* ---Prepare the standard URL params for this page. We'll need them later--- */
$urlparams = array('id' => $schoolids);
if ($page) {
    $urlparams['page'] = $page;
}
if ($perpage) {
    $urlparams['perpage'] = $perpage;
}


echo $OUTPUT->heading(get_string('manageschools', 'local_collegestructure'));
$currenttab = 'assignregistrar';
$school->print_collegetabs($currenttab, $id = NULL);

// error handling instead of showing empty table
/*$schoollist = $DB->get_records('local_school');
if (empty($schoollist)) {
    print_cobalterror('schoolnotcreated', 'local_collegestructure', $CFG->wwwroot . '/local/collegestructure/school.php');
}*/
//$hierarche->get_school_items();
echo $OUTPUT->box(get_string('asignregistrartabdes', 'local_collegestructure'));
if (is_siteadmin())
    $sql = "SELECT * FROM {local_school}";
else
    $sql = " SELECT distinct(s.id),s.* FROM {local_school} s  where id in(select schoolid from {local_school_permissions} where userid={$USER->id}) ORDER BY s.sortorder  ";
$schoolusers = $DB->get_records_sql($sql);

$data = array();

if ($schoolusers) {
    foreach ($schoolusers as $schooluser) {
        $line = array();
        $reg = array();
        $line[] = $schooluser->fullname;
        $reg[] = $hierarche->get_allregisters($schooluser->id);
        $line[] = $reg[0];
        $data[] = $line;
    }
} else {
    $line = array();
    $line[] = "No user is assigned till now";
    $data[] = $line;
}
$table = new html_table();
if ($schoolusers) {
    $table->head = array(
        get_string('schoolname', 'local_collegestructure'), get_string('username', 'local_collegestructure'));
}

$table->size = array('40%', '40%', '20%');
$table->align = array('left', 'left', 'left', 'center');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);

echo '<br/><br/>';
echo $OUTPUT->box(get_string('assignregistrartxt', 'local_collegestructure'));


$hierarchy = new hierarchy();
$users = $hierarchy->get_manager();
$PAGE->requires->js('/local/collegestructure/js/school.js');
if (!$users) {
    echo $OUTPUT->heading(get_string("nousersyet", 'local_collegestructure'));
} else {
    echo '<form id="movemodules" action="assignusers.php" method="post"><div>';
    echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
    echo '<table border="0" cellspacing="2" width="50%" cellpadding="4" class="generalbox boxaligncenter"><tr>';
    echo '<th class="header" align="left">' . get_string('username', 'local_collegestructure') . '</th>';
    echo '<th class="header" scope="col" align="center">' . get_string('select') . '</th>';
    echo '</tr>';

    $count = 0;
    $abletomoveusers = false;
    /* ---for now--- */

    $baseurl = new moodle_url('/local/collegestructure/assignusers.php', $urlparams + array('sesskey' => sesskey()));
    foreach ($users as $id => $auser) {
        $count++;
        echo '<tr>';
        $checkexist = $DB->get_records('local_school_permissions', array('schoolid' => $schoolids, 'userid' => $id));
        if ($checkexist)
            $startclass = 'class="dimmed"  disabled="disabled"';
        else
            $startclass = 'class="courseassign"';
        echo '<td align="left"><a ' . $startclass . ' href="' . $CFG->wwwroot . '/user/view.php?id=' . $id . '">' . format_string($auser) . '&nbsp;</a></td>';
        echo '<td align="center">';
        echo '<input type="checkbox" name="c' . $id . '" ' . $startclass . ' />';
        echo '</td>';
        echo "</tr>";
    }

    $items = $hierarchy->get_school_items(true);
    $parents = $hierarchy->get_school_parent($items);
    echo '<tr><td colspan="3" align="center">';
    $sdclass = 'class="dimmed"  disabled="disabled"';
    echo html_writer::select($parents, 'moveto', $parents, null, array('id' => 'movetoid'));
    echo '<input type="submit" id="movetoid"  value="Assign Registrars" />';
    echo '</td></tr>';
    echo '</table>';
    echo '</div></form>';
    echo '<br />';
}
echo $OUTPUT->footer();

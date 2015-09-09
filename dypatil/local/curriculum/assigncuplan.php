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
 * @package    local
 * @subpackage local_curriculum
 * @copyright  2013 niranjan  {niranjan@eabyas.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');

require_once($CFG->dirroot . '/local/lib.php');
$PAGE->requires->js('/local/curriculum/js/filter.js');

$id = optional_param('id', 0, PARAM_INT); // Category id
$proid = optional_param('proid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT); // which page to show
$moveto = optional_param('moveto', 0, PARAM_INT);
//$sesskey = optional_param('sesskey', '', PARAM_RAW);
$curriculumids = optional_param('curriculumid', 0, PARAM_INT);
;
$defaultperpage = 20;
$perpage = optional_param('perpage', $defaultperpage, PARAM_INT); // how many per page

global $DB;

require_login();
$systemcontext = context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('managecurriculum', 'local_curriculum'), "/local/curriculum/index.php", get_string('viewcurriculum', 'local_curriculum'));
$PAGE->navbar->add(get_string('assigncuplan', 'local_curriculum'));
$PAGE->set_url(new moodle_url('/local/curriculum/assignmodules.php', array('id' => $id)));
require_login();
$PAGE->set_pagelayout('admin');


echo $OUTPUT->header();
$currenttab = "assigncuplan";
$curriculum = new curricula();
$curriculum->print_curriculumtabs($currenttab);
//$PAGE->set_category_by_id($id);
$returnurl = new moodle_url('/local/curriculum/assignmodules.php', array('id' => $id));

// Process any Module assigning Process actions.
// assign a specified module to a new semester
if (!empty($moveto) and $data = data_submitted()) {

    if (!$destcategory = $DB->get_record('local_curriculum', array('id' => $data->moveto))) {
        print_error('cannotfindcurriculum', '', '', $data->moveto);
    }

    $modules = array();
    foreach ($data as $key => $value) {
        if (preg_match('/^c\d+$/', $key)) {
            $moduleid = substr($key, 1);
            array_push($modules, $moduleid);
        }
    }
    add_modules_to_curriculum($modules, $data->moveto);
}


// Prepare the standard URL params for this page. We'll need them later.
$urlparams = array('id' => $id);
if ($page) {
    $urlparams['page'] = $page;
}
if ($perpage) {
    $urlparams['perpage'] = $perpage;
}



echo $OUTPUT->heading(get_string('assigncuplan', 'local_curriculum'));

//$sql="SELECT * FROM {local_curriculum} lc JOIN {local_curriculum_modules} cm ON lc.id=cm.curriculumid JOIN {local_school} s ON lc.schoolid=s.id ";
$tools = $DB->get_records('local_curriculum_modules', array('curriculumid' => $curriculumids));

$data = array();
// Begin output

if ($tools) {
    foreach ($tools as $tool) {
        $line = array();
        //  $line[] = $tool->id;
        $modulename = array();
        $modulename = get_modulename($tool->moduleid);
        $linkcss = $modulename[2] ? ' ' : ' class="dimmed"  ';
        $line[] = '<a title="Assign Course" ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/modules/view.php?id=' . $tool->moduleid . '&sesskey=' . sesskey() . '">' . $modulename[1] . '</a>';
        $buttons = array();
        $buttons[] = html_writer::link(new moodle_url('/local/curriculum/curriculum.php', array('id' => $curriculumids, 'moduleid' => $tool->moduleid, 'proid' => $proid, 'unassign' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('unassign', 'local_curriculum'), 'title' => get_string('unassign', 'local_curriculum'), 'class' => 'iconsmall')));
        $line[] = implode(' ', $buttons);

        $data[] = $line;
    }
} else {
    $line = array();
    $line[] = get_string('no_modules', 'local_curriculum');
    $data[] = $line;
}
$table = new html_table();
if ($tools) {
    $table->head = array(
        get_string('modulename', 'local_curriculum'), get_string('unassign', 'local_curriculum'));
}

$table->size = array('20%', '20%', '10%', '10%', '10%', '10%');
$table->id = "cooktable";
$table->align = array('left', 'left', 'left', 'center');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);

//$semestername=get_semesterf($curriculumids);

echo $OUTPUT->box(get_string('listmodules', 'local_curriculum'));

$displaylist = array();
$notused = array();
//$displaylist=get_programlist();
// Print out all the modules.
$sql = "SELECT * FROM {local_module} where programid={$proid} AND visible=1 ";
$modules = $DB->get_records_sql($sql);
$numcourses = count($modules);

// We can consider that we are using pagination when the total count of courses is different than the one returned.
//$pagingmode = $totalcount != $numcourses;

if (!$modules) {
    // There is no course to display.
    if (empty($subcategorieswereshown)) {

        echo $OUTPUT->heading(get_string("nomodulessyet", 'local_curriculum'));
    }
} else {
    // The conditions above have failed, we display a basic list of courses with paging/editing options.
    //  echo $OUTPUT->paging_bar($totalcount, $page, $perpage, "/local/semesters/assignmodules.php?moduleid='.$moduleids.'&id=$category->id&perpage=$perpage");

    echo '<form id="movemodules" action="assignmodules.php?curriculumid=' . $curriculumids . '&proid=' . $proid . '" method="post"><div>';
    echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
    echo '<table border="0" cellspacing="2" width="70%" cellpadding="4" class="generalbox boxaligncenter"><tr>';
    echo '<th class="header" scope="col">' . get_string('modules', 'local_curriculum') . '</th>';
    echo '<th class="header" scope="col">' . get_string('select') . '</th>';

    echo '</tr>';

    $count = 0;
    $abletomovecourses = false;  // for now

    $baseurl = new moodle_url('/local/curriculum/assignmodules.php', $urlparams + array('sesskey' => sesskey()));
    foreach ($modules as $amodule) {
        $count++;
        echo '<tr>';
        $modulename = $amodule->fullname;
        $checkexist = $DB->get_records('local_curriculum_modules', array('curriculumid' => $curriculumids, 'moduleid' => $amodule->id));
        if ($checkexist)
            $startclass = 'class="dimmed"  disabled="disabled"';
        else
            $startclass = 'class="moduleassign"';
        echo '<td align="center"><a ' . $startclass . ' href="local/modules/view.php?id=' . $amodule->id . '">' . format_string($modulename) . '</a></td>';
        echo '<td align="center">';


        echo '<input type="checkbox" name="c' . $amodule->id . '" ' . $startclass . ' />';
        echo '</td>';
        echo "</tr>";
    }

    $notused = array();
    $curriculumid = $curriculum->get_curriculumf($curriculumids);
    echo '<tr><td colspan="3" align="right">';
    echo '<input type="submit" id="movetoid" class = "click autosubmit" value="Assign Modules" />';

    //  echo html_writer::select($curriculumid, 'moveto', $curriculumid, null, array('id'=>'movetoid', 'class' => 'autosubmit'));
    $PAGE->requires->yui_module('moodle-core-formautosubmit', 'M.core.init_formautosubmit', array(array('selectid' => 'movetoid', 'nothing' => $curriculumid))
    );

    echo '<input type="hidden" name="moveto" value="' . $curriculumids . '" />';
    echo '<input type="hidden" name="id" value="' . $curriculumids . '" />';
    echo '</td></tr>';


    echo '</table>';
    echo '</div></form>';
    echo '<br />';
}



echo $OUTPUT->footer();

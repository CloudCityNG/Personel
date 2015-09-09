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
$id = optional_param('id', -1, PARAM_INT);
$day = optional_param('df', false, PARAM_BOOL);
$week = optional_param('wf', false, PARAM_BOOL);
$month = optional_param('mf', false, PARAM_BOOL);
$year = optional_param('yf', false, PARAM_BOOL);
$hierarchy = new hierarchy();
$acalendar = academiccalendar :: get_instatnce();
$systemcontext =  context_system::instance();
$PAGE->set_url('/local/academiccalendar/index.php');
$PAGE->set_pagelayout('admin');
require_login();
if (isguestuser()) {
    print_error('noguest');
}
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('pluginname', 'local_academiccalendar'));
$PAGE->navbar->add(get_string('pluginname', 'local_academiccalendar'), new moodle_url('/local/academiccalendar/index.php'));
$PAGE->navbar->add(get_string('vieweventsnav', 'local_academiccalendar'));
$PAGE->requires->css('/local/academiccalendar/css/style.css');
require_once($CFG->dirroot . '/local/scheduleexam/lib.php');
$exams = new schedule_exam();
echo $OUTPUT->header();
$sql = $acalendar->event_whereclause($day, $week, $month, $year);
$systemcontext =  context_system::instance();
$usercontext = context_user::instance($USER->id);

$context =context_user::instance($USER->id);
if (has_capability('local/clclasses:enrollclass', $context) && !is_siteadmin()) {
    echo $OUTPUT->heading(get_string('myacademics', 'local_courseregistration'));
} else {
    echo $OUTPUT->heading(get_string('pluginname', 'local_academiccalendar'));
}
if (is_siteadmin($USER)) {
    $admin_school = $DB->get_records('local_school');
    if (empty($admin_school))
        throw new schoolnotfound_exception();
}

if (!is_siteadmin($USER)) {
    if (has_capability('local/collegestructure:manage', $systemcontext)) {
        $assigned_schools = $hierarchy->get_assignedschools();

        foreach ($assigned_schools as $assigned_school) {
            if ($assigned_school->id != null) {
                $aschools[] = $assigned_school->id;
            }
        }
        $schoollist = implode(',', $aschools);
        empty($schoollist) ? $schoollist = 0 : $schoollist;
        empty($sql) ? $sql .=' ((eventlevel=1) OR
                                     (schoolid in (' . $schoollist . ')))' : $sql .=' AND ((eventlevel=1) OR
                                     (schoolid in (' . $schoollist . ')))';
    } elseif (has_capability('local/clclasses:enrollclass', $usercontext)) {

        $userdetails = $DB->get_records('local_userdata', array('userid' => $USER->id));
        $usersems = $DB->get_records('local_user_semester', array('userid' => $USER->id));
        if (empty($userdetails))
            throw new notassignedschool_exception();
        foreach ($userdetails as $userdetail) {
            $schoollist[] = $userdetail->schoolid;
            $programlist[] = $userdetail->programid;
        }
        foreach ($usersems as $usersem) {
            $usems[] = $usersem->semesterid;
        }
        !empty($usems) ? $sems_string = implode(',', $usems) : $sems_string = 0;
        !empty($schoollist) ? $schoollist_string = implode(',', $schoollist) : $schoollist_string = 0;
        !empty($programlist) ? $programlist_string = implode(',', $programlist) : $programlist_string = 0;
        empty($sql) ? $sql .='((eventlevel=1 and eventtypeid !=1) OR
                                     (eventlevel=2 and schoolid in (' . $schoollist_string . ')) OR
                                     (
                                     eventlevel=3 and schoolid in (' . $schoollist_string . ') AND
                                     programid in (' . $programlist_string . ')
                                     ) OR  (eventlevel=4 and schoolid in(' . $schoollist_string . ') and semesterid in (' . $sems_string . ')))' : $sql .='  AND
                                     ((eventlevel=1) OR
                                     (eventlevel=2 and schoolid in (' . $schoollist_string . ')) OR
                                     (
                                     eventlevel=3 and schoolid in (' . $schoollist_string . ') AND
                                     programid in (' . $programlist_string . ')
                                     ) OR  (eventlevel=4 and schoolid in(' . $schoollist_string . ') and semesterid in (' . $sems_string . ')))';
    } else if (has_capability('local/clclasses:submitgrades', $systemcontext)) {
        $userdetails = $DB->get_records('local_scheduleclass', array('instructorid' => $USER->id));
        if (empty($userdetails))
            $userdetails = $DB->get_records('local_school_permissions', array('userid' => $USER->id));
        foreach ($userdetails as $userdetail) {
            if ($userdetail->schoolid != null) {

                $schoollist[] = $userdetail->schoolid;
            }
        }
        if (empty($schoollist))
            throw new notassignedschool_exception();
        $schoollist_string = implode(',', $schoollist);
        empty($sql) ? $sql .='((eventlevel=1 and eventtypeid !=1) OR
                                     (eventlevel=2 and schoolid in (' . $schoollist_string . ')) OR
                                     (
                                     eventlevel=3 and schoolid in (' . $schoollist_string . ') 
                                     ))' : $sql .='  AND
                                     ((eventlevel=1) OR
                                     (eventlevel=2 and schoolid in (' . $schoollist_string . ')) OR
                                     (
                                     eventlevel=3 and schoolid in (' . $schoollist_string . ') 
                                     ))';
    }
}

//$sql .= 'ORDER BY startdate DESC';
$eactivities = $DB->get_records_select('local_event_activities', $sql, null, 'startdate DESC');
$data = array();
$capabilities_array = array('local/academiccalendar:manage', 'local/academiccalendar:delete', 'local/academiccalendar:update', 'local/academiccalendar:visible', 'local/collegestructure:manage');
foreach ($eactivities as $eactivity) {
    $line = array();
    $event_inst = $DB->get_record('local_event_types', array('id' => $eactivity->eventtypeid));
    $eventname = $event_inst->eventtypename;
    $school_inst = $DB->get_record('local_school', array('id' => $eactivity->schoolid));
    !empty($school_inst) ? $schoolname = $school_inst->fullname : NULL;
    $program_inst = $DB->get_record('local_program', array('id' => $eactivity->programid));
    !empty($program_inst) ? $programname = $program_inst->fullname : NULL;
    $semester_inst = $DB->get_record('local_semester', array('id' => $eactivity->semesterid));
    !empty($semester_inst) ? $semestername = $semester_inst->fullname : NULL;
    $eactivity->startdate = date('d M Y', $eactivity->startdate);
    $startdate = $eactivity->startdate;
    $eactivity->enddate > 0 ? $eactivity->enddate = date('d M Y', $eactivity->enddate) : $eactivity->enddate = '';
    $enddate = $eactivity->enddate;

    $linkcss = $eactivity->publish ? ' ' : 'class="dimmed" ';
    $line[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/academiccalendar/viewevent.php?id=' . $eactivity->id . '">' . format_string($eactivity->eventtitle) . '</a>';
    $line[] = $eventname;
    $eventls = array('1' => 'Global',
        '2' => 'Organization',
        '3' => 'Program',
        '4' => 'Course Offering');
    $eventinfo = '';

    !empty($eactivity->schoolid) ? $eventinfo .='<span><strong>' . get_string('schoolid', 'local_collegestructure') . ':</strong> ' . $schoolname . '</span><br />' : null;
    !empty($eactivity->programid) ? $eventinfo .='<span><strong>' . get_string('program', 'local_programs') . ' :</strong> ' . $programname . '</span><br />' : null;
    !empty($eactivity->semesterid) ? $eventinfo .='<span><strong>' . get_string('semester', 'local_semesters') . ' :</strong> ' . $semestername . '</span><br />' : null;
    $eventinfo .= '<span><strong>' . get_string('eventlevel', 'local_academiccalendar') . ':</strong> ' . $eventls[$eactivity->eventlevel] . '</span><br />';
    $line[] = $eventinfo;
    $date = $startdate;
    !empty($enddate) ? $date .= ' - ' . $enddate : null;
    $line[] = $date;
    $line[] = $startdate;
    $line[] = $enddate;
    !empty($eactivity->schoolid) ? $line[] = $schoolname : $line[] = null;
    !empty($eactivity->programid) ? $line[] = $programname : $line[] = null;
    !empty($eactivity->semesterid) ? $line[] = $semestername : $line[] = null;
    $line[] = !empty($eactivity->eventlevel) ? $acalendar->eventls[$eactivity->eventlevel] : null;


    if (is_siteadmin($USER) || has_any_capability($capabilities_array, $systemcontext)) {
        $buttons = $hierarchy->get_actions('academiccalendar', 'edit_event', $eactivity->id, $eactivity->publish);
        $line[] = $buttons;
    }
    $data[] = $line;
}

if (is_siteadmin($USER) || has_capability('local/collegestructure:manage', $systemcontext)) {
    $currenttab = 'view';
    require('tabs.php');
}
/* ---Moodle 2.2 and onwards--- */
if (!$acalendar->is_student()) {
    if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
        echo $OUTPUT->box(get_string('vieweventdescription', 'local_academiccalendar'));
    }
}
if ($acalendar->is_student()) {
    $currenttab = "academiccalendar";
    $exams->studentside_tabs($currenttab);
}
/* ---View Part starts--- */
$PAGE->requires->js('/local/academiccalendar/acfilter.js');
echo '<div id="datatable">';
echo '<div class="advfilter">';
echo $acalendar->render_duration_filters();
echo '</div>';
echo "<div id='filter-box' >";
echo '<div class="filterarea">';
echo '</div>';

echo '</div></div>';
if (!is_siteadmin()) {
    $help = $OUTPUT->help_icon('downloadbutton', 'local_academiccalendar');
    echo "<div id = 'downloadbutton'><input type='submit' name='allvalues' class='mine' value='' style='opacity:0.325;'/>$help</div>";
}
$table = new html_table();
$table->id = "cooktable";
$table->head = array(get_string('eventtitle', 'local_academiccalendar'),
    get_string('activitytype', 'local_academiccalendar'),
    get_string('eventinfo', 'local_academiccalendar'),
    get_string('date', 'local_academiccalendar'),
    get_string('startdate', 'local_academiccalendar'),
    get_string('enddate', 'local_academiccalendar'),
    get_string('schoolid', 'local_collegestructure'),
    get_string('program', 'local_programs'),
    get_string('semester', 'local_semesters'),
    get_string('eventllevel', 'local_academiccalendar')
);

if (is_siteadmin($USER) || has_any_capability($capabilities_array, $systemcontext)) {
    $table->head[] = get_string('action');
}
$table->width = '100%';
$table->align = array('left', 'left', 'left', 'left', 'left');
$table->size = array('20%', '17%', '30%', '23%', '10%');
if (empty($data)) {
    $table->data = 'No Events';
} else {
    $table->data = $data;
}


echo html_writer::table($table);
if (!isset($data)) {
    ?>
    <script>
        $(document).ready(function () {
            $('#downloadbutton').css({opacity: 0});
        });
    </script>
    <?php

}
echo '<div id="contents"></div>';
echo $OUTPUT->footer();
?>

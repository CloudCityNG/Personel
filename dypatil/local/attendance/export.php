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
 * Export attendance sessions
 *
 * @package   mod_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/export_form.php');
require_once(dirname(__FILE__).'/renderables.php');
require_once(dirname(__FILE__).'/renderhelpers.php');

$id             = required_param('id', PARAM_INT);

$att            = $DB->get_record('local_attendance', array('id' => $id), '*', MUST_EXIST);
$classinfo  = $DB->get_record('local_clclasses',array('id'=>$att->classid));
//$sacademicyearinfo   = $DB->get_record('local_academicyear', array('id' =>$sessioninfo ->academicyearid ));
$PAGE->set_context(context_system::instance());

require_login();
$att = new local_attendance($att,$classinfo,null , $PAGE->context);

$att->perm->require_export_capability();

$PAGE->set_url($att->url_export());
$PAGE->set_pagelayout('admin');
//$PAGE->set_title($course->shortname. ": ".$att->name);
//$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
//$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'attendance'));
$PAGE->navbar->add(get_string('export', 'local_attendance'));


$formparams = array( 'attendanceid'=>$att->id, 'modcontext' => $PAGE->context,'attendanceobject'=>$att,'classid'=>$att->classid);
$mform = new local_attendance_export_form($att->url_export(), $formparams);

if ($mform->is_submitted()) {
    $formdata = $mform->get_data();

    $pageparams = new local_att_page_with_filter_controls();
    $pageparams->init($classinfo);
    $pageparams->page = 0;
    $pageparams->group = $formdata->group;
    $pageparams->set_current_sesstype($formdata->group ? $formdata->group : local_att_page_with_filter_controls::SESSTYPE_ALL);
    if (isset($formdata->includeallsessions)) {
        if (isset($formdata->includenottaken)) {
            $pageparams->view = LOCAL_ATT_VIEW_ALL;
        } else {
            $pageparams->view = LOCAL_ATT_VIEW_ALLPAST;
            $pageparams->curdate = time();
        }
        $pageparams->init_start_end_date();
    } else {
        $pageparams->startdate = $formdata->sessionstartdate;
        $pageparams->enddate = $formdata->sessionenddate;
    }
    $att->pageparams = $pageparams;

    $reportdata = new local_attendance_report_data($att);
    if ($reportdata->users) {
        $filename = clean_filename($classinfo->shortname.'_Attendances_'.userdate(time(), '%Y%m%d-%H%M'));

        $group = $formdata->group ? $reportdata->groups[$formdata->group] : 0;
        $data = new stdClass;
        $data->tabhead = array();
        $data->classname = $att->classinfo->fullname;
    //    $data->group = $group ? $group->name : get_string('allparticipants');

        if (isset($formdata->ident['id'])) {
            $data->tabhead[] = get_string('studentid', 'local_attendance');
        }
        if (isset($formdata->ident['uname'])) {
            $data->tabhead[] = get_string('username');
        }
        
        $optional = array('idnumber', 'institution', 'department');
        foreach ($optional as $opt) {
            if (isset($formdata->ident[$opt])) {
                $data->tabhead[] = get_string($opt);
            }
        }
        
        $data->tabhead[] = get_string('lastname');
        $data->tabhead[] = get_string('firstname');
        //$groupmode = groups_get_activity_groupmode($cm, $course);
        //if (!empty($groupmode)) {
        //    $data->tabhead[] = get_string('groups');
        //}

        if (count($reportdata->sessions) > 0) {
            foreach ($reportdata->sessions as $sess) {
                $text = userdate($sess->sessdate, get_string('strftimedmyhm', 'local_attendance'));
                $text .= ' ';
                $text .= get_string('commonsession', 'local_attendance');
                $data->tabhead[] = $text;
                if (isset($formdata->includeremarks)) {
                    $data->tabhead[] = get_string('remark', 'local_attendance', $text);
                }
            }
        } else {
            print_error('sessionsnotfound', 'local_attendance', $att->url_manage());
        }
        if ($reportdata->gradable) {
            $data->tabhead[] = get_string('grade');
        }

        $i = 0;
        $data->table = array();
       
        foreach ($reportdata->users as $user) {
            if (isset($formdata->ident['id'])) {
                $data->table[$i][] = $user->id;
            }
            if (isset($formdata->ident['uname'])) {
                $data->table[$i][] = $user->username;
            }
            
            $optional_row = array('idnumber', 'institution', 'department');
            foreach ($optional_row as $opt) {
                if (isset($formdata->ident[$opt])) {
                    $data->table[$i][] = $user->$opt;
                }
            }
            
            $data->table[$i][] = $user->lastname;
            $data->table[$i][] = $user->firstname;
            //if (!empty($groupmode)) {
            //    $grouptext = '';
            //    $groupsraw = groups_get_all_groups($course->id, $user->id, 0, 'g.name');
            //    $groups = array();
            //    foreach ($groupsraw as $group) {
            //        $groups[] = $group->name;;
            //    }
            //    $data->table[$i][] = implode(', ', $groups);
            //}
            $cellsgenerator = new local_user_sessions_cells_text_generator($reportdata, $user);
            $data->table[$i] = array_merge($data->table[$i], $cellsgenerator->get_cells(isset($formdata->includeremarks)));
            if ($reportdata->gradable) {
                $data->table[$i][] = $reportdata->grades[$user->id].' / '.$reportdata->maxgrades[$user->id];
            }
            $i++;
        }

        if ($formdata->format === 'text') {
            exporttocsv($data, $filename);
        } else {
            exporttotableed($data, $filename, $formdata->format);
        }
        exit;
    } else {
        print_error('studentsnotfound', 'attendance', $att->url_manage());
    }
}

$output = $PAGE->get_renderer('local_attendance');
$tabs = new local_attendance_tabs($att, local_attendance_tabs::TAB_EXPORT);
echo $output->header();
echo $output->heading(get_string('attendanceforclass', 'local_attendance', $classinfo ));
echo $output->render($tabs);

$mform->display();

echo $OUTPUT->footer();


function exporttotableed($data, $filename, $format) {
    global $CFG;

    if ($format === 'excel') {
        require_once("$CFG->libdir/excellib.class.php");
        $filename .= ".xls";
        $workbook = new MoodleExcelWorkbook("-");
    } else {
        require_once("$CFG->libdir/odslib.class.php");
        $filename .= ".ods";
        $workbook = new MoodleODSWorkbook("-");
    }
    // Sending HTTP headers.
    $workbook->send($filename);
    // Creating the first worksheet.
    $myxls = $workbook->add_worksheet('Attendances');
    // Format types.
    $formatbc = $workbook->add_format();
    $formatbc->set_bold(1);

    $myxls->write(0, 0, get_string('class','local_clclasses'), $formatbc);
    $myxls->write(0, 1, $data->classname);
   // $myxls->write(1, 0, get_string('group'), $formatbc);
   // $myxls->write(1, 1, $data->group);

    $i = 3;
    $j = 0;
    foreach ($data->tabhead as $cell) {
        $myxls->write($i, $j++, $cell, $formatbc);
    }
    $i++;
    $j = 0;
    foreach ($data->table as $row) {
        foreach ($row as $cell) {
            $myxls->write($i, $j++, $cell);
        }
        $i++;
        $j = 0;
    }
    $workbook->close();
}

function exporttocsv($data, $filename) {

    $filename .= ".txt";

    header("Content-Type: application/download\n");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Expires: 0");
    header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");

    echo get_string('course')."\t".$data->classname."\n";
   // echo get_string('group')."\t".$data->group."\n\n";

    echo implode("\t", $data->tabhead)."\n";
    foreach ($data->table as $row) {
        echo implode("\t", $row)."\n";
    }
}

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
 * deletes a template
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package evaluation
 */

require_once("../../config.php");
require_once("lib.php");
require_once('delete_template_form.php');
require_once($CFG->libdir.'/tablelib.php');
require_once $CFG->dirroot.'/local/lib.php';

$current_tab = 'templates';
$hierarchy = new hierarchy();
$id = required_param('id', PARAM_INT);
$classid = required_param('clid',PARAM_INT);
$canceldelete = optional_param('canceldelete', false, PARAM_INT);
$shoulddelete = optional_param('shoulddelete', false, PARAM_INT);
$deletetempl = optional_param('deletetempl', false, PARAM_INT);

$PAGE->set_pagelayout('admin');
$context = context_system::instance();
require_login();
$PAGE->set_context($context);
$url = new moodle_url('/local/evaluations/delete_template.php', array('id'=>$id,'clid'=>$classid));
if ($canceldelete !== false) {
    $url->param('canceldelete', $canceldelete);
}
if ($shoulddelete !== false) {
    $url->param('shoulddelete', $shoulddelete);
}
if ($deletetempl !== false) {
    $url->param('deletetempl', $deletetempl);
}
$PAGE->set_url($url);

if (($formdata = data_submitted()) AND !confirm_sesskey()) {
    print_error('invalidsesskey');
}

if ($canceldelete == 1) {
    $editurl = new moodle_url('/local/evaluations/edit.php', array('id'=>$id,'clid'=>$classid, 'do_show'=>'templates'));
    redirect($editurl->out(false));
}

//if (! $cm = get_coursemodule_from_id('evaluation', $id)) {
//    print_error('invalidcoursemodule');
//}

//if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
//    print_error('coursemisconf');
//}

if (! $evaluation = $DB->get_record("local_evaluation", array("id"=>$id))) {
    print_error('invalidcoursemodule');
}

//$context = context_module::instance($cm->id);

//require_login($course, true, $cm);

//require_capability('mod/evaluation:deletetemplate', $context);

$mform = new evaluation_delete_template_form();
$newformdata = array('id'=>$id,
                    'deletetempl'=>$deletetempl,
                    'clid'=>$classid,
                    'confirmdelete'=>'1');

$mform->set_data($newformdata);
$formdata = $mform->get_data();

$deleteurl = new moodle_url('/local/evaluations/delete_template.php', array('id'=>$id,'clid'=>$classid));

if ($mform->is_cancelled()) {
    redirect($deleteurl->out(false));
}

if (isset($formdata->confirmdelete) AND $formdata->confirmdelete == 1) {
    if (!$template = $DB->get_record("evaluation_template", array("id"=>$deletetempl))) {
        print_error('error');
    }

   // if ($template->ispublic) {
    //    $systemcontext = get_system_context();
    //    require_capability('mod/evaluation:createpublictemplate', $systemcontext);
    //    require_capability('mod/evaluation:deletetemplate', $systemcontext);
   // }

    evaluation_delete_template($template);
    $message = get_string('templatedelsuccess','local_evaluations');
     $style = array('style'=>'notifysuccess');
 $hierarchy->set_confirmation($message,$deleteurl->out(false),$style);
    //redirect($deleteurl->out(false));
}

/// Print the page header
$strevaluations = get_string("modulenameplural", "local_evaluations");
$strevaluation  = get_string("modulename", "local_evaluations");
$strdeleteevaluation = get_string('delete_template', 'local_evaluations');

//$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(format_string($evaluation->name));
echo $OUTPUT->header();



/// Print the main part of the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
echo $OUTPUT->heading($strdeleteevaluation);
if ($shoulddelete == 1) {
 echo $OUTPUT->box_start('generalbox errorboxcontent boxaligncenter boxwidthnormal');
    echo print_string('confirmdeletetemplate', 'local_evaluations');
   echo '<div id="templatedel">';
    $mform->display();
    echo '</div>';
    echo $OUTPUT->box_end();
} else {
     // edited by hema
     // Bug id #151
     /// print the tabs
     require('tabs.php');
    //first we get the own templates
    $templates = evaluation_get_template_list($classid, 'own');
    if (!is_array($templates)) {
        echo $OUTPUT->box(get_string('no_templates_available_yet', 'local_evaluations'),
                         'generalbox boxaligncenter');
    } else {
        echo $OUTPUT->heading(get_string('class','local_clclasses'), 3);
        echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthnormal');
        $tablecolumns = array('template', 'action');
        $tableheaders = array(get_string('template', 'local_evaluations'), get_string('actiontemplate', 'local_evaluations'));
        $tablecourse = new flexible_table('evaluation_template_course_table');

        $tablecourse->define_columns($tablecolumns);
        $tablecourse->define_headers($tableheaders);
        $tablecourse->define_baseurl($deleteurl);
        $tablecourse->column_style('action', 'width', '10%');

        $tablecourse->sortable(false);
        $tablecourse->set_attribute('width', '100%');
        $tablecourse->set_attribute('class', 'generaltable');
        $tablecourse->setup();

        foreach ($templates as $template) {
            $data = array();
            $data[] = $template->name;
            $url = new moodle_url($deleteurl, array(
                                            'id'=>$id,
                                            'clid'=>$classid,
                                            'deletetempl'=>$template->id,
                                            'shoulddelete'=>1,
                                            ));

            $data[] = $OUTPUT->single_button($url, $strdeleteevaluation, 'post');
            $tablecourse->add_data($data);
        }
        $tablecourse->finish_output();
        echo $OUTPUT->box_end();
    }
    //now we get the public templates if it is permitted
    $systemcontext = get_system_context();
//    if (has_capability('mod/evaluation:createpublictemplate', $systemcontext) AND
  //      has_capability('mod/evaluation:deletetemplate', $systemcontext)) {
        $templates = evaluation_get_template_list($classid, 'public');
        if (!is_array($templates)) {
            echo $OUTPUT->box(get_string('no_templates_available_yet', 'local_evaluations'),
                              'generalbox boxaligncenter');
        } else {
            echo $OUTPUT->heading(get_string('public', 'local_evaluations'), 3);
            echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthnormal');
            $tablecolumns = array('template', 'action');
            $tableheaders = array(get_string('template', 'local_evaluations'), '');
            $tablepublic = new flexible_table('evaluation_template_public_table');

            $tablepublic->define_columns($tablecolumns);
            $tablepublic->define_headers($tableheaders);
            $tablepublic->define_baseurl($deleteurl);
            $tablepublic->column_style('action', 'width', '10%');

            $tablepublic->sortable(false);
            $tablepublic->set_attribute('width', '100%');
            $tablepublic->set_attribute('class', 'generaltable');
            $tablepublic->setup();

            foreach ($templates as $template) {
                $data = array();
                $data[] = $template->name;
                $url = new moodle_url($deleteurl, array(
                                                'id'=>$id,
                                                'clid'=>$classid,
                                                'deletetempl'=>$template->id,
                                                'shoulddelete'=>1,
                                                ));

                $data[] = $OUTPUT->single_button($url, $strdeleteevaluation, 'post');
                $tablepublic->add_data($data);
            }
            $tablepublic->finish_output();
            echo $OUTPUT->box_end();
        }
   // }

    echo $OUTPUT->box_start('boxaligncenter boxwidthnormal');
    $url = new moodle_url($deleteurl, array(
                                    'id'=>$id,
                                    'clid'=>$classid,
                                    'canceldelete'=>1,
                                    ));

    echo '<div style="text-align:center;">'.$OUTPUT->single_button($url, get_string('back'), 'post',array('id'=>'test')).'</div>';
    echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();


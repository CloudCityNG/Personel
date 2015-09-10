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
 * @subpackage Evaluations
 * @copyright  2012 Naveen <naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG,$DB,$PAGE;
require_once($CFG->dirroot.'/local/lib.php');
require_once($CFG->dirroot.'/local/evaluations/create_form.php');
$id = optional_param('id',-1,PARAM_INT);
$classid = required_param('clid',PARAM_INT);
$delete    = optional_param('delete', 0, PARAM_BOOL);
$confirm   = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$hierarchy = new hierarchy();
$PAGE->set_url('/local/evaluations/create_evaluation.php',array('clid'=>$classid));
$PAGE->set_pagelayout('admin');
$systemcontext = context_system::instance();
require_login();
$PAGE->set_context($systemcontext);
require_capability('local/evaluations:addinstance', context_system::instance());
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('pluginname','local_evaluations'));

if ($id > 0) {
    if (! ($evaluation = $DB->get_record('local_evaluation', array('id'=>$id)))) {
        print_error('invalidtoolid', 'local_academiccalendar');
    }
} 
 else {
    $evaluation = new stdClass();
    $evaluation->id = -1;
}
$returnurl = new moodle_url('/local/evaluations/index.php');

if ($delete ) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
      $DB->delete_records('local_evaluation',array('id'=>$id));
       //redirect($returnurl);
        $message = get_string('evaldeleted','local_evaluations');
        $style = array('style'=>'notifysuccess');
       $hierarchy->set_confirmation($message,$returnurl,$style);
    }
    $strheading = get_string('deleteevaluation', 'local_evaluations');
	$PAGE->navbar->add(get_string('viewevaluations','local_evaluations'));
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/evaluations/create_evaluation.php', array('id'=>$id, 'clid'=>$classid,'delete'=>1, 'confirm'=>1, 'sesskey'=>sesskey()));
    $message = get_string('delconfirm', 'local_evaluations');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}
if ($id > 0)
$PAGE->navbar->add(get_string('create','local_evaluations'));
else
$PAGE->navbar->add(get_string('updevl','local_evaluations'));
//Hide and show for event activity
if ($visible >=0 and $id and confirm_sesskey()) {
    $result = $DB->set_field('local_evaluation', 'publish_stats', $visible, array('id' => $id));
    $data->evaluation = $DB->get_field('local_evaluation','name',array('id'=>$id));
	$data->visible = $DB->get_field('local_evaluation','publish_stats',array('id'=>$id));
	if ($data->visible == 1) {
		$data->visible = 'Activated';
	} else {
		$data->visible = 'Inactivated';
	}
	if ($result) {
	$message = get_string('success', 'local_evaluations', $data);
	 $style = array('style'=>'notifysuccess');
	}else {
	$message = get_string('failure', 'local_evaluations', $data);
        $style = array('style'=>'notifyproblem');
	}
	$hierarchy->set_confirmation($message,$returnurl,$style);
}

//Creating object for form
$editoroptions = array('id'=>$id,'classid'=>$classid);
$eval_form = new evaluation_create_form($PAGE->url,$editoroptions);
if($id > 0 ){
  $evaltype = array ( '1' => get_string('evoltype2','local_evaluations'),
                                  '2' => get_string('evoltype3','local_evaluations'),
                                 '3'=>get_string('evoltype4','local_evaluations'));
    $evaluation->description = array('text' => $evaluation->description,'format' => FORMAT_HTML);
   $evtype =  $DB->get_field('local_evaluation','evaluationtype',array('id'=>$id));
    $evaluation->evaluationtype =$evaltype[$evtype];
}
$eval_form->set_data($evaluation);
if ($eval_form->is_cancelled()) {
    redirect($returnurl);
} else if($data = $eval_form->get_data()){
  if($evaluation->id > 0 ){
$data->classid = $classid;
!empty($data->evaluatedinstructor) ? $data->evaluatedinstructor = implode(',',$data->evaluatedinstructor) : null;
$data->description = $data->description['text'];
$DB->update_record('local_evaluation',$data);
 $url = new moodle_url('/local/evaluations/view.php',array('id'=>$data->id,'clid'=>$classid));
 $message = get_string('evalupdated','local_evaluations');
 $style = array('style'=>'notifysuccess');
 $hierarchy->set_confirmation($message,$returnurl,$style);
  }else {
$data->classid = $classid;
$data->publish_stats=1;
!empty($data->evaluatedinstructor) ? $data->evaluatedinstructor = implode(',',$data->evaluatedinstructor) : null;
$data->description = $data->description['text'];
$eid = $DB->insert_record('local_evaluation',$data);
 $returnurl = new moodle_url('/local/evaluations/view.php',array('id'=>$eid,'clid'=>$classid));
  $message = get_string('evalcreated','local_evaluations');
 $style = array('style'=>'notifysuccess');
 $hierarchy->set_confirmation($message,$returnurl,$style);
}
}

echo $OUTPUT->header();
//if($id > 0){
       $current_tab = 'editevaluation';
        require('tabs.php');
//}
if ($id > 0)
echo $OUTPUT->box(get_string('updateevls','local_evaluations'));
else
$conf = new object();
$conf->fullname=$DB->get_field('local_clclasses','fullname',array('id'=>$classid));
echo $OUTPUT->box(get_string('createevls','local_evaluations',$conf));
$eval_form->display();
echo $OUTPUT->footer();
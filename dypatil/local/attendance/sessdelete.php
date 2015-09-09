<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG,$DB,$PAGE,$USER;
require_once($CFG->dirroot.'/local/attendance/lib.php');
require_once($CFG->dirroot.'/local/lib.php');
require_once($CFG->dirroot.'/local/attendance/add_form.php');
$id = optional_param('id', -1, PARAM_INT);
$classid = optional_param('classid', -1, PARAM_INT); 
$delete    = optional_param('delete', 0, PARAM_BOOL);
$confirm   = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$hide = optional_param('hide', 0, PARAM_INT);
$show = optional_param('show', 0, PARAM_INT);
$hierarchy = new hierarchy();
$conf = new object();
$systemcontext = context_system::instance();
if ($id > 0) {
   
	if (! ($tool = $DB->get_record('local_attendance_sessions', array('id'=>$id)))) 
	{
        print_error('invalidtoolid', 'local_attendance');
        }
	else {
	$tool->description = array('text' => $tool->description,'format' => FORMAT_HTML);
	}
    
} 
 else {
         $tool = new stdClass();
         $tool->id = -1;
      } 

$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
if (!has_capability('local/clclasses:manage', $systemcontext)) {
    print_error('You dont have permissions');
}

$PAGE->set_heading(get_string('pluginname','local_attendance'));
$PAGE->set_url('/local/attendance/sessdelete.php', array('id' => $id));
$returnurl = new moodle_url('/local/attendance/manage.php', array('classid'=>$classid));
$strheading = get_string('manage','local_attendance');

// If the $delete variable is set, then delete the record from the table
if ($delete ) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
	
	  
       $deletesess=local_delete_attendance_sess($id);
	   if($deletesess) {
	   $message = get_string('deletesesssuccess', 'local_attendance');
	   $style = array('style'=>'notifysuccess');
	   }
	   else {
	   $message = get_string('deletesessfailure', 'local_attendance');
	   $style = array('style'=>'notifyproblem');
	   }
	   $hierarchy->set_confirmation($message,$returnurl,$style);
    }
	$strheading = get_string('deleteattendance', 'local_attendance');

    $PAGE->set_title($strheading);
	echo $OUTPUT->header();
	echo $OUTPUT->heading(get_string('deleteattsess','local_attendance'));
	
    $depend=local_check_attendance_session($id);
	if($depend > 0) {
	    echo '<div align="center">';
		$message = get_string('sesscheck', 'local_attendance');
        echo $OUTPUT->box($message);
        echo $OUTPUT->continue_button($returnurl);
        echo '</div>';
	}
	else {
	$yesurl = new moodle_url('/local/attendance/sessdelete.php', array('id'=>$id, 'delete'=>1,'classid'=>$classid,
                                                                       'confirm'=>1, 'sesskey'=>sesskey()));
    $message = get_string('deletesess', 'local_attendance');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
	}
    echo $OUTPUT->footer();
    die;
}


$heading = get_string('editsess','local_attendance');
$boxcontent=get_string('editsessdescription','local_attendance');
$PAGE->set_heading(get_string('pluginname','local_attendance'));
$PAGE->set_title($strheading);
$editform = new local_attendance_edit_form(null,array('id'=>$id,'classid'=>$classid));
$editform->set_data($tool);
if ($editform->is_cancelled()) {
    redirect($returnurl);
} 
else if ($data = $editform->get_data()) {
	$data->description = $data->description['text'];
	$data->sessdate = $data->sessiondate;
        $data->duration = $data->durtime['hours']*HOURSECS + $formdata->durtime['minutes']*MINSECS;
	$DB->update_record('local_attendance_sessions', $data);

		$message = get_string('sessupdatesuccess', 'local_attendance');
		$style = array('style'=>'notifysuccess');
$returnurl = new moodle_url('/local/attendance/manage.php', array('classid'=>$data->classid));
$hierarchy->set_confirmation($message,$returnurl,$style);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editsess', 'local_attendance'));
$editform->display();
echo $OUTPUT->footer();
?>

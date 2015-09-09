<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG,$DB,$PAGE,$USER;
require_once($CFG->dirroot.'/local/attendance/lib.php');
require_once($CFG->dirroot.'/local/lib.php');
require_once($CFG->dirroot.'/local/attendance/mod_form.php');
$id = optional_param('id', -1, PARAM_INT);
$classid = optional_param('classid', -1, PARAM_INT); 
$delete    = optional_param('delete', 0, PARAM_BOOL);
$confirm   = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$hide = optional_param('hide', 0, PARAM_INT);
$show = optional_param('show', 0, PARAM_INT);
$hierarchy = new hierarchy();
$conf = new object();
$systemcontext =context_system::instance();
if ($id > 0) {
   
	if (! ($tool = $DB->get_record('local_attendance', array('id'=>$id)))) 
	{
        print_error('invalidtoolid', 'local_attendance');
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
$PAGE->set_url('/local/attendance/delete.php', array('id' => $id));
$returnurl = new moodle_url('/local/attendance/modedit.php', array('id' => $id,'classid'=>$classid));
$strheading = get_string('manage','local_attendance');

// If the $delete variable is set, then delete the record from the table
if ($delete ) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
	$conf->attendance = $DB->get_field('local_attendance','name', array('id'=>$id));
	  
       $deletebuilding=local_delete_attendance($id);
	   if($deletebuilding) {
	   $message = get_string('deletesuccess', 'local_attendance',$conf);
	   $style = array('style'=>'notifysuccess');
	   }
	   else {
	   $message = get_string('deletefailure', 'local_attendance',$conf);
	   $style = array('style'=>'notifyproblem');
	   }
	   $hierarchy->set_confirmation($message,$returnurl,$style);
    }
	$strheading = get_string('deleteattendance', 'local_attendance');

    $PAGE->set_title($strheading);
	echo $OUTPUT->header();
	echo $OUTPUT->heading(get_string('deleteatt','local_attendance'));
	
    $depend=local_check_attendance($id);
	if($depend > 0) {
	    echo '<div align="center">';
		$message = get_string('attendancecheck', 'local_attendance');
        echo $OUTPUT->box($message);
        echo $OUTPUT->continue_button($returnurl);
        echo '</div>';
	}
	else {
	$yesurl = new moodle_url('/local/attendance/delete.php', array('id'=>$id, 'delete'=>1,'classid'=>$classid,
                                                                       'confirm'=>1, 'sesskey'=>sesskey()));
    $message = get_string('deleteattendance', 'local_attendance');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
	}
    echo $OUTPUT->footer();
    die;
}

//Make the buildings to active or inactive
if((!empty($hide) or !empty($show)) && $id && confirm_sesskey()){
    $record=new stdClass();
    $data = $DB->get_record('local_attendance', array('id'=>$id));
    $record->name=$data->name;
    if (!empty($hide)) {
        $disabled = 0;
        $record->set='inactivated';
        $message = get_string('attinactivesuccess', 'local_attendance', $record);
    } else {
         $disabled = 1;
         $record->set='activated';
         $message = get_string('attactivesuccess', 'local_attendance', $record);
    }
    $DB->set_field('local_attendance', 'visible', $disabled, array('id' => $id));
    $options = array('style'=>'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $options);
	
   
}
$heading = ($id > 0) ? get_string('editbuilding','local_attendance') :
get_string('createbuilding', 'local_classroomresources') ;
$boxcontent=($id > 0) ? get_string('editdescription','local_attendance'):
get_string('buildingdescription', 'local_classroomresources');
$PAGE->set_heading(get_string('pluginname','local_attendance'));
$PAGE->set_title($strheading);
$editform = new local_attendance_form(null,array('id'=>$id,'classid'=>$classid));
$editform->set_data($tool);
if ($editform->is_cancelled()) {
    redirect($returnurl);
} 
else if ($data = $editform->get_data()) {
    $conf->name = $data->name;
    $value=$DB->update_record('local_attendance', $data);
		if($value) {
		$message = get_string('updatesuccess', 'local_attendance',$conf);
		$style = array('style'=>'notifysuccess');
		}
		else {
		$message = get_string('updatefailure', 'local_attendance',$conf);
		$style = array('style'=>'notifyproblem');
		}
$hierarchy->set_confirmation($message,$returnurl,$style);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_attendance'));
$editform->display();
echo $OUTPUT->footer();
?>

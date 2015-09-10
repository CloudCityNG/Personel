<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG,$DB,$PAGE,$USER;
require_once($CFG->dirroot.'/local/attendance/lib.php');
require_once($CFG->dirroot.'/local/lib.php');
require_once($CFG->dirroot.'/local/attendance/mod_form.php');
$id    = optional_param('id',0, PARAM_INT);
$delete    = optional_param('delete', 0, PARAM_BOOL);
$confirm   = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$hide = optional_param('hide', 0, PARAM_INT);
$show = optional_param('show', 0, PARAM_INT);
require_login();
$hierarchy = new hierarchy();
$systemcontext = context_system::instance();
if(!has_capability('local/clclasses:manage', $systemcontext) && !is_siteadmin()) {
   $returnurl = new moodle_url('/local/error.php');
   redirect($returnurl); 
}
if ($id > 0) {
   if (! ($tool = $DB->get_record('local_time_settings', array('id'=>$id)))) {
        print_error('invalidtoolid', 'local_attendance');
    }
    else {
   $tool->starttime['starthours']= $tool->starthours;
$tool->starttime['startminutes']=$tool->startminutes;
$tool->endtime['endhours']=$tool->endhours;
$tool->endtime['endminutes']=$tool->endminutes; 
    }
  
}
else {
   $tool = new stdClass();
   $tool->id = -1;
}
$PAGE->set_url('/local/attendance/timesetting.php');
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($SITE->fullname);
$returnurl = new moodle_url('/local/attendance/timesetting.php'); 
if ($delete ) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
	
	  
      $delete=$DB->delete_records('local_time_settings', array('id'=>$id));
      $message = get_string('deletetimesuccess', 'local_attendance');
      $style = array('style'=>'notifysuccess');
      $hierarchy->set_confirmation($message,$returnurl,$style);
    }
    $strheading = get_string('manage','local_attendance');
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('deletetime','local_attendance'));
    $yesurl = new moodle_url('/local/attendance/timesetting.php', array('id'=>$id, 'delete'=>1,
                                                           'confirm'=>1, 'sesskey'=>sesskey()));
    $message = get_string('deletetimedesc', 'local_attendance');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

$PAGE->navbar->add(get_string('timesetting', 'local_attendance'));
echo $OUTPUT->header();
$records=$DB->get_records_sql('SELECT t.*,s.fullname FROM {local_time_settings} t,{local_school} s WHERE t.schoolid=s.id');
$class=new stdClass();

echo $OUTPUT->heading(get_string('timesetting', 'local_attendance'));



if(empty($records) || $id > 0) {
   $mform = new local_schooltime_form();
   $mform->set_data($tool);
   $mform->display();
   if ($mform->is_cancelled()) {
   $returnurl = new moodle_url('/local/attendance/timesetting.php');
   redirect($returnurl);
   } 
   else if ($data = $mform->get_data()) {
   
  //print_r($data);
if($data->id < 0) {
$data->starthours=$data->starttime['starthours'];
$data->startminutes=$data->starttime['startminutes'];
$data->endhours=$data->endtime['endhours'];
$data->endminutes=$data->endtime['endminutes'];
//print_r($data);
$DB->insert_record('local_time_settings',$data);
}
else {
$data->starthours=$data->starttime['starthours'];
$data->startminutes=$data->starttime['startminutes'];
$data->endhours=$data->endtime['endhours'];
$data->endminutes=$data->endtime['endminutes'];
$DB->update_record('local_time_settings',$data);
}
   $returnurl = new moodle_url('/local/attendance/timesetting.php');
   redirect($returnurl);
   }
}

else {
   //echo "Show attendance record";
   foreach($records as $record) {
  $data = array();
  $buttons = array();
    
    $result = array();
    $result[]=$record->fullname;
    $result[]='('.$record->starthours.':'.$record->startminutes.')'.'-'.'('.$record->endhours.':'.$record->endminutes.')';
    $buttons[] = html_writer::link(new moodle_url('/local/attendance/timesetting.php', array('id'=>$record->id,'delete'=>1, 'sesskey'=>sesskey())), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'),'title'=>get_string('delete'), 'alt'=>get_string('delete'), 'class'=>'iconsmall')));
    $buttons[] =  html_writer::link(new moodle_url('/local/attendance/timesetting.php', array('id'=>$record->id,'sesskey'=>sesskey())), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'), 'title'=>get_string('edit'),'alt'=>get_string('edit'), 'class'=>'iconsmall')));
    
    
    $result[] = implode(' ', $buttons);
    $data[] =$result;
   }
    $table = new html_table();

$table->head  = array(
get_string('school','local_attendance'),
get_string('timings','local_attendance'),
get_string('action','local_attendance')
);
$table->size  = array('20%','20%','20%');
$table->align = array('left','left','left');
$table->width = '99%';
$table->data  = $data;
echo html_writer::table($table);
}
echo $OUTPUT->footer();

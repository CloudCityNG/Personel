<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER, $OUTPUT;

require_once($CFG->dirroot . '/local/lib.php');
$systemcontext = context_system::instance();

$PAGE->set_url('/local/timetable/index.php');
$PAGE->set_pagelayout('base');
$context = context_user::instance($USER->id);
//if (!has_capability('local/timetable:manage', $systemcontext) && !has_capability('local/clclasses:enrollclass', $context)) {
//    print_error('You dont have permissions');
//}


$PAGE->set_context($systemcontext);
$PAGE->requires->css('/local/timetable/cupertino/jquery-ui.min.css');
$PAGE->requires->css('/local/timetable/style/fullcalendar.css');
//$PAGE->requires->css('/local/timetable/style/fullcalendar.print.css');
$PAGE->set_heading(get_string('pluginname', 'local_timetable'));
$PAGE->set_title(get_string('pluginname', 'local_timetable'));
//$PAGE->navbar->add(get_string('pluginname', 'local_timetable'), new moodle_url('/local/timetable/index.php'));
$PAGE->navbar->add(get_string('calendar_navbar', 'local_timetable'));
echo $OUTPUT->header();



echo $OUTPUT->heading(get_string('pluginname', 'local_timetable'));
$hierarchy = new hierarchy();

//$session_ob = manage_session::getInstance();
// -----------incase when school not created yet...it throws schoolnotfound exception
$schoollist = $DB->get_records('local_school', array('visible' => 1));
//if (empty($schoollist))
//    print_error('school not found');

$currenttab = 'view';
//$session_ob->session_tabs($currenttab);
//$des = new cobaltk12_description('timetable', 'view');
//$des->descriptionform_manage();

if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    // echo $OUTPUT->box(get_string('pluginname', 'local_timetable'));
}

echo '<p>Date: <input type="text" id="datepicker"></p>';
echo '<p id="loading"><img src="' . $CFG->wwwroot . '/local/timetable/pix/loadinga.gif"></img></p>';
echo "<div id='calendar'></div>";
echo '<div id="eventContent" title="Event Details" style="display:none;">
     <p id="eventInfo"></p>   
  
    
</div>';


$PAGE->requires->js('/local/timetable/js/moment.min.js');
$PAGE->requires->js('/local/timetable/js/fullcalendar.min.js');
$PAGE->requires->js('/local/timetable/js/jquery-ui1.js');
$PAGE->requires->js('/local/timetable/custom.js');



echo $OUTPUT->footer();
?>
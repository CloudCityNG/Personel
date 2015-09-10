<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/onlinepayment/paytax_form.php');
require_once($CFG->dirroot . '/local/onlinepayment/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

global $DB;
$id = required_param('id', PARAM_INT);
$type = required_param('type', PARAM_RAW);
$delete = optional_param('delete', 0, PARAM_INT);

$hierarchy = new hierarchy();
$tax = tax::getInstance();
$context = context_system::instance();
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($context);
$PAGE->requires->css('/local/onlinepayment/css/style.css');
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
if ($delete) {
    $DB->delete_records('local_costdiscounts', array('id' => $delete));
    redirect('viewcost.php?id=' . $id . '&type=' . $type);
}
if ($type == 'mooc') {
    if (!$course = $DB->get_record('course', array('id' => $id))) {
        print_error('invalid module id', 'local_onlinepayment');
    }
    $PAGE->set_title($course->shortname . ': ' . $course->fullname);
    $PAGE->set_heading($course->fullname);
}
if ($type == 'class') {
    if (!$class = $DB->get_record('local_clclasses', array('id' => $id))) {
        print_error('invalid class id', 'local_onlinepayment');
    }
    $module = $DB->get_record('local_cobaltcourses', array('id' => $class->cobaltcourseid));
    $PAGE->set_title($class->shortname . ': ' . $class->fullname);
    $PAGE->set_heading($class->fullname);
}
$PAGE->navbar->add(get_string('modcostsettings', 'local_onlinepayment'), new moodle_url('/local/onlinepayment/modcost.php'));
$PAGE->navbar->add(get_string('viewdetails', 'local_onlinepayment'));
$PAGE->set_url('/local/onlinepayment/viewcost.php', array('id' => $id));

//display the page
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('viewdetails', 'local_onlinepayment'));

$output = html_writer::start_tag('div', array('class' => 'class_viewcost'));

if ($class) {
    $classCost = $DB->get_record('local_classcost', array('classid' => $class->id));
} else if ($course) {
    $courseCost = $DB->get_record('local_classcost', array('courseid' => $course->id));
}
$cost = html_writer::tag('a', 'Add Price', array('href' => $CFG->wwwroot . '/local/onlinepayment/addcost.php?' . $type . 'id=' . $id));
$price = false;
if ($classCost || $courseCost) {
    if ($classCost) {
        $price = true;
        if ($classCost->classcost != 0)
            $cost = '&pound; ' . $classCost->classcost; else if ($classCost->credithourcost != 0)
            $cost = '&pound; ' . ($module->credithours * $classCost->credithourcost);
    } else if ($courseCost) {
        $price = true;
        $cost = '&pound; ' . $courseCost->coursecost;
    }
}
$output .= html_writer::tag('p', get_string('price', 'local_onlinepayment') . ': <font size="5" color="green">' . $cost . '</font>', array('style' => 'text-align:right;'));
$output .= '<table cellpadding="3">';
if ($class) {
    //$output .= get_string('class', 'local_clclasses')
    $output .= html_writer::tag('tr', '<td>' . get_string('class', 'local_clclasses') . '</td><td class="td-right">: ' . $class->fullname . '</td>', array());
    $output .= html_writer::tag('tr', '<td>' . get_string('course', 'local_cobaltcourses') . '</td><td>: ' . $module->fullname . '</td>', array());
    if ($class->onlinecourseid) {
        $mooc_course = $DB->get_record('course', array('id' => $class->onlinecourseid));
        $output .= html_writer::tag('tr', '<td>' . get_string('mooc', 'local_onlinepayment') . '</td><td>: ' . $mooc_course->fullname . '</td>', array());
    }
    $output .= html_writer::tag('tr', '<td>' . get_string('credithours', 'local_cobaltcourses') . '</td><td>: ' . $module->credithours . '</td>', array());
    if ($classCost && $classCost->credithourcost != 0) {
        $output .= html_writer::tag('tr', '<td>' . get_string('credithourcost', 'local_onlinepayment') . '</td><td>: &pound' . $classCost->credithourcost . '</td>', array());
    }
}
if ($course) {
    $output .= html_writer::tag('tr', '<td>' . get_string('mooc', 'local_onlinepayment') . '</td><td>: ' . $course->fullname . '</td>', array());
}
$output .= '</table>';

if ($classCost) {
    $discounts = $DB->get_records('local_costdiscounts', array('costid' => $classCost->id));
} else if ($courseCost) {
    $discounts = $DB->get_records('local_costdiscounts', array('costid' => $courseCost->id));
}
$output .= '<br/>';
$output .= html_writer::tag('p', get_string('discounts', 'local_onlinepayment') . ': ', array('style' => 'font-size: 20px;'));
if ($discounts) {
    $table = '<table cellpadding="3" class="second-table">';
    foreach ($discounts as $discount) {
        $table .= '<tr>';
        $table .= '<td><font color="gray">' . get_string('discount', 'local_onlinepayment') . ':</font> ' . $discount->discount . '%' . '</td>';
        $table .= '<td><font color="gray">' . get_string('discountcode', 'local_onlinepayment') . ':</font> "' . $discount->discountcode . '"' . '</td>';
        $table .= '<td><font color="gray">' . get_string('applicablefrom', 'local_onlinepayment') . ':</font> "' . date('d M, Y', $discount->startdate) . ' - ' . date('d M, Y', $discount->enddate) . '"' . '</td>';
        $table .= '<td>' . html_writer::link(new moodle_url('/local/onlinepayment/viewcost.php', array('id' => $id, 'type' => $type, 'delete' => $discount->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall'))) . '</td>';
        $table .= '</tr>';
    }
    $table .= '</table>';
    $output .= html_writer::tag('div', $table, array('style' => 'margin-left: 3%;'));
} else {
    $output .= get_string('nodiscounts', 'local_onlinepayment');
}
$link = html_writer::link(new moodle_url('/local/onlinepayment/addcost.php', array($type . 'id' => $id)), get_string('addnewdiscount', 'local_onlinepayment'));
if ($price)
    $output .= html_writer::tag('h4', $link, array());
$output .= html_writer::end_tag('div'); //.class_viewcost



echo $output;
echo $OUTPUT->footer();

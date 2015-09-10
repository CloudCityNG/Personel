<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER, $OUTPUT;
require_once($CFG->dirroot . '/local/rate_course/lib.php');
$PAGE->requires->css('/local/rate_course/css/style.css');
$id = optional_param('id', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_url('/local/rate_course/review.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('pluginname', 'local_rate_course'));
$PAGE->navbar->add(get_string('pluginname', 'local_rate_course'), new moodle_url('/local/rate_course/review.php?id=' . $id . ''));
$PAGE->navbar->add(get_string('review', 'local_rate_course'));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('review', 'local_rate_course'));
$conf = new object();
$conf->fullname = $DB->get_field('course', 'fullname', array('id' => $id));
$numstars = get_rating($id);
$conf->rating = '<img src="' . $CFG->wwwroot . '/local/rate_course/pix/star' . $numstars . '.png">';
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('reviewdescription', 'local_rate_course', $conf));
}
$reviews = get_listof_reviews($id);
if (empty($reviews)) {
    echo get_string('noreview', 'local_rate_course');
} else {
    $data = array();
    foreach ($reviews as $review) {
        echo '<hr class="line">';
        $user = $DB->get_record('user', array('id' => $review->userid));
        echo html_writer::start_tag('div', array('class' => 'reviewimage'));
        echo html_writer::tag('p', $OUTPUT->user_picture($user, array('courseid' => SITEID, 'size' => 50)));
        echo html_writer::tag('p', $user->firstname . ' ' . $user->lastname);
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', array('class' => 'reviewdesc'));
        echo html_writer::tag('p', $review->dateofadded);
        echo '<p class="rating"><img src="' . $CFG->wwwroot . '/local/rate_course/pix/star' . ($review->rating * 2) . '.png"></p>';
        echo html_writer::tag('p', $review->review);
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', array('class' => 'clear'));
        echo html_writer::end_tag('div');
    }
}
echo $OUTPUT->footer();
?>
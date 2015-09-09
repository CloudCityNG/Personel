<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/onlinepayment/paytax_form.php');
require_once($CFG->dirroot . '/local/onlinepayment/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
global $DB;
$id = optional_param('id', -1, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$hierarchy = new hierarchy();
$tax = tax::getInstance();
$systemcontext = context_system::instance();
$returnurl = new moodle_url('/local/onlinepayment/index.php', array('id' => $id));
if ($id > 0) {
    //get the records from the table to edit
    if (!$record = $DB->get_record('local_tax_rate', array('id' => $id))) {
        print_error('invalidtaxrate', 'local_onlinepayment');
    }
    $record->description = array('text' => $record->description, 'format' => FORMAT_HTML);
    $record->type = $DB->get_field('local_tax_type', 'display_name', array('id' => $record->typeid));
    if ($check_for_order = $tax->check_for_date_change($record)) {

        $message = get_string('dontchangetaxrate', 'local_onlinepayment', $record);
        $options = array('style' => 'notifyproblem');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
} else {
    // To create a new Tax Type
    $record = new stdClass();
    $record->id = -1;
}
//If the loggedin user have the required capability allow the page
//if (!has_capability('local/payment:createtax', $systemcontext)) {
//  print_error('You dont have permissions');
//}
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_title(get_string('pluginname', 'local_onlinepayment'));
$PAGE->set_heading(get_string('pluginname', 'local_onlinepayment'));
$PAGE->set_url('/local/onlinepayment/taxrate.php', array('id' => $id));


if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $data = $DB->get_record('local_tax_rate', array('id' => $id));
        $message = get_string('deleteratesuccess', 'local_onlinepayment', $data);
        $options = array('style' => 'notifysuccess');
        $DB->delete_records('local_tax_rate', array('id' => $id));
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    $strheading = get_string('deletetaxrate', 'local_onlinepayment');
    $PAGE->navbar->add(get_string('pluginname', 'local_onlinepayment'), new moodle_url('/local/onlinepayment/index.php', array('id' => $id)));
    $PAGE->navbar->add($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'local_onlinepayment'));
    $yesurl = new moodle_url('/local/onlinepayment/taxrate.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('delrateconfirm', 'local_onlinepayment');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
$params = array('id' => $id, 'editoroptions' => $editoroptions);
$taxrate = new taxrate_form(null, $params);
$taxrate->set_data($record);
if ($taxrate->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $taxrate->get_data()) {
    $data->description = $data->description['text'];
    if ($data->id > 0) {
        $DB->update_record('local_tax_rate', $data);
        $message = get_string('updateratesuccess', 'local_onlinepayment', $data);
    } else {
        $data->timecreated = time();
        $DB->insert_record('local_tax_rate', $data);
        $message = get_string('createratesuccess', 'local_onlinepayment', $data);
    }
    $options = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $options);
}

//display the page
echo $OUTPUT->header();
// Tab view
$tax->createtabview('settings');
$tax->get_inner_headings('index');
//echo $OUTPUT->single_button(new moodle_url('/local/onlinepayment/paytax.php'), get_string('createtaxtype', 'local_onlinepayment'));
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('createtaxratepage', 'local_onlinepayment'));
}
$create = html_writer::tag('a', get_string('createtaxtype', 'local_onlinepayment'), array('href' => $CFG->wwwroot . '/local/onlinepayment/paytax.php', 'style' => 'float:right;'));
echo '<h4>' . $create . '</h4><br/>';
// Display the form
$taxrate->display();
echo $OUTPUT->footer();

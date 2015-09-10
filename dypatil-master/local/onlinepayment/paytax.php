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
$returnurl = new moodle_url('/local/onlinepayment/paytax.php');
if ($id > 0) {
    //get the records from the table to edit
    if (!$record = $DB->get_record('local_tax_type', array('id' => $id))) {
        print_error('invalidtaxtype', 'local_onlinepayment');
    }
    $record->description = array('text' => $record->description, 'format' => FORMAT_HTML);
    if ($DB->record_exists('local_tax_rate', array('typeid' => $id))) {
        $message = get_string('dontchangetaxtype', 'local_onlinepayment', $record);
        $options = array('style' => 'notifyproblem');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
} else {
    // To create a new Tax Type
    $record = new stdClass();
    $record->id = -1;
}
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
//If the loggedin user have the required capability allow the page
if (!has_capability('local/payment:createtax', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_title(get_string('pluginname', 'local_onlinepayment'));
$PAGE->set_heading(get_string('pluginname', 'local_onlinepayment'));
$PAGE->set_url('/local/onlinepayment/paytax.php', array('id' => $id));


if ($delete) {
    $PAGE->url->param('delete', 1);
    $data = $DB->get_record('local_tax_type', array('id' => $id));
    if ($confirm and confirm_sesskey()) {
        $message = get_string('deletetypesuccess', 'local_onlinepayment', $data);
        $options = array('style' => 'notifysuccess');
        $DB->delete_records('local_tax_type', array('id' => $id));
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    $strheading = get_string('deletetaxtype', 'local_onlinepayment');
    $PAGE->navbar->add(get_string('pluginname', 'local_onlinepayment'), new moodle_url('/local/onlinepayment/index.php', array('id' => $id)));
    $PAGE->navbar->add($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'local_onlinepayment'));
    $yesurl = new moodle_url('/local/onlinepayment/paytax.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('deltypeconfirm', 'local_onlinepayment');
    if ($DB->record_exists('local_tax_rate', array('typeid' => $id))) {
        echo $message = get_string('ratecreateddontdelete', 'local_onlinepayment', $data);
        echo $OUTPUT->continue_button($returnurl);
    } else {
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}

$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
$params = array('id' => $id, 'editoroptions' => $editoroptions);
$taxtype = new paytax_form(null, $params);
$taxtype->set_data($record);
if ($taxtype->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $taxtype->get_data()) {
    $data->description = $data->description['text'];
    if ($data->id > 0) {
        $DB->update_record('local_tax_type', $data);
        $message = get_string('updatetypesuccess', 'local_onlinepayment', $data);
    } else {
        $data->timecreated = time();
        $DB->insert_record('local_tax_type', $data);
        $message = get_string('createtypesuccess', 'local_onlinepayment', $data);
    }
    $options = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $options);
}

//display the page
echo $OUTPUT->header();
//echo $OUTPUT->heading(get_string('pluginname', 'local_onlinepayment'));
// Tab view
$tax->createtabview('settings');
$tax->get_inner_headings('index');
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('createtaxtypepage', 'local_onlinepayment'));
}
// Display the form
$taxtype->display();

$create = html_writer::tag('a', get_string('createtaxrate', 'local_onlinepayment'), array('href' => $CFG->wwwroot . '/local/onlinepayment/taxrate.php', 'style' => 'float:right;'));
echo '<h4>' . $create . '</h4><br/>';
$types = $DB->get_records('local_tax_type');
$data = array();
foreach ($types as $type) {
    $line = array();
    $line[] = $type->name;
    $line[] = $type->display_name;
    $line[] = ($type->description) ? $type->description : '-';
    $buttons = array();
    $buttons[] = html_writer::link(new moodle_url('/local/onlinepayment/paytax.php', array('id' => $type->id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
    $buttons[] = html_writer::link(new moodle_url('/local/onlinepayment/paytax.php', array('id' => $type->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
    $line[] = implode(' ', $buttons);
    $data[] = $line;
}
$PAGE->requires->js('/local/onlinepayment/js/taxtype.js');
if (!empty($data)) {
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
}
if (empty($data)) {
    echo get_string('notypescreatedyet', 'local_onlinepayment');
}
$table = new html_table();
$table->id = "taxtypetable";
$table->head = array(get_string('taxname', 'local_onlinepayment'), get_string('displaytaxname', 'local_onlinepayment'), get_string('typedescription', 'local_onlinepayment'), get_string('action'));
$table->size = array('25%', '25%', '40%', '10%');
$table->align = array('left', 'left', 'left', 'center');
$table->width = '100%';
$table->data = $data;
if (!empty($data))
    echo html_writer::table($table);
echo $OUTPUT->footer();

<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $PAGE, $CFG;
require_once($CFG->dirroot.'/local/coursesbulkupload/courses_form.php');
$PAGE->set_url('/local/coursesbulkupload/index.php');
$importid = optional_param('importid', '', PARAM_INT);
require_once($CFG->libdir.'/csvlib.class.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Courses Bulkupload");
$returnurl = new moodle_url('/admin/tool/uploaduser/index.php');
//require_login();

// array of all valid fields for validation
$STD_FIELDS = array('fullname', 'shortname', 'schoolname', 'description');
$PRF_FIELDS = array();
if (empty($importid)) {
    $mform = new uploadcourses_form5();
    //if ($mform->is_cancelled())
    //    redirect($returnurl);
    if ($formdata = $mform->get_data()) {
        $importid = csv_import_reader::get_new_iid('uploadcourse');
        $cir = new csv_import_reader($importid, 'uploadcourse');
        $content = $mform->get_file_content('coursefile');
        $readcount = $cir->load_csv_content($content, $form1data->encoding, $form1data->delimiter_name);
        unset($content);
        if ($readcount === false) {
            print_error('csvfileerror', 'tool_uploadcourse', $returnurl, $cir->get_error());
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl, $cir->get_error());
        }
        // test if columns ok(to validate the csv file content)
        $filecolumns = uu_validate_department_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
        // continue to form2
    }else {
        echo $OUTPUT->header();
        //echo $OUTPUT->heading(get_string('uploadcourses', 'local_coursesbulkupload'));
        echo $OUTPUT->heading_with_help(get_string('uploadcourses', 'local_coursesbulkupload'), 'uploadcourses', 'tool_uploadcourse');
        $mform->display();
        echo $OUTPUT->footer();  
    }
} else {
    $cir = new csv_import_reader($importid, 'uploaddepartment');
}
// Data to set in the form.
$data = array('importid' => $importid, 'previewrows' => $previewrows);
if (!empty($form1data)) {
    // Get options from the first form to pass it onto the second.
    foreach ($form1data->options as $key => $value) {
        $data["options[$key]"] = $value;
    }
}

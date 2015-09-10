<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/prefix/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/prefix/newentity_form.php');
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);

//checking the id is greater than one...if its fetching content from table...used in edit purpose
if ($id > 0) {
    if (!($tool = $DB->get_record('local_create_entity', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_create_entity');
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}

$PAGE->set_url('/local/prefix/entity.php', array('id' => $id));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
//-------successful message returnurl --------
$sreturnurl = new moodle_url('/local/prefix/entity.php', array('id' => $id));
$currenturl = "{$CFG->wwwroot}/local/prefix/entity.php";

//this is the return url ---------------------
$returnurl = new moodle_url('/local/prefix/entity.php');
$strheading = get_string('ename', 'local_prefix');
$PAGE->navbar->add(get_string('prefix_suffix', 'local_prefix'), new moodle_url('/local/prefix/index.php'));
$PAGE->navbar->add(get_string('create_entity', 'local_prefix'));
$PAGE->set_title($strheading);

// calling prefix_Suffix class instance.....
$prefix = prefix_suffix::getInstance();
$hier = new hierarchy();

/* Start of delete the Entity ----------------- */
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $res = $prefix->delete_entity($id, $currenturl);
        if ($res == false) {
            $confirm_msg = get_string('usedinprefix', 'local_prefix');
            $hier->set_confirmation($confirm_msg, $currenturl);
        }
    }
    $strheading = get_string('delete_entity', 'local_prefix');
    $PAGE->navbar->add($strheading);

//$PAGE->set_title($strheading);

    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();
    $currenttab = 'create_entity';
//$prefix->prefix_tabs($currenttab);
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/prefix/entity.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('delconfirm_entity', 'local_prefix');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}
/* End of delete the Entity ------------------------ */

//-----code used to hide and show--------------------------------------------
if ($visible != -1 and $id and confirm_sesskey()) {
    $res11 = $DB->set_field('local_create_entity', 'visible', $visible, array('id' => $id));
    redirect($returnurl);
}
//-------end of code hide and show-------------------------------------------
//-------------creating instance of entity form-------------
$en = new newentity_form();
//-------setting tool array value to entity form used for editing purpose--------------
$en->set_data($tool);
if ($en->is_cancelled()) {
    redirect($returnurl);
}

if ($data = $en->get_data()) {
    if ($data->id > 0) {
// Update code
        $result = $DB->update_record('local_create_entity', $data);
        $prefix->success_error_msg($result, 'success_up_entity', 'error_up_entity', $currenturl);
    } else {
        //-------------adding code(insert entity)
        //----------checking case sensitive------------------------------
        $entity = strtolower($data->entity_name);
        $entitylists = $DB->get_records('local_create_entity');
        foreach ($entitylists as $ent) {
            if ($entity == strtolower($entitylists[$ent->id]->entity_name)) {
                $confirm_msg = get_string('already', 'local_prefix');
                $hier->set_confirmation($confirm_msg, $currenturl);
            }
        }
        // print_object($entitylists);        
        $re = $DB->insert_record('local_create_entity', $data);
        $prefix->success_error_msg($re, 'success_add_entity', 'error_add_entity', $currenturl);
    }
}
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('prefixs', 'local_prefix'));

// adding tab code----------------------------------
$currenttab = 'create_entity';
$prefix = prefix_suffix::getInstance();
$prefix->prefix_tabs($currenttab);
echo $OUTPUT->box(get_string('createentitytabdes', 'local_prefix'));
try {

//echo $OUTPUT->heading(get_string('entityheading', 'local_prefix'));
    $en->display();

    function printtable($entity_list) {
        global $OUTPUT, $DB, $PAGE, $CFG;
        $PAGE->requires->js('/local/prefix/entity_test.js');
        $data = array();
        foreach ($entity_list as $el) {
            // print_object($el);
            //echo $el->entity_name;
            $line = array();
            $line[] = $el->entity_name;
            $buttons = array();
            $hier = new hierarchy();
            if ($el->id <= 4) {

                $buttons = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('default_delete', 'local_prefix'), 'alt' => get_string('delete'), 'class' => 'iconsmall'));
                $buttons .= html_writer::link(new moodle_url('/local/prefix/entity.php', array('id' => $el->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
                $buttons .= html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('default_inactive', 'local_prefix'), 'alt' => get_string('hide'), 'class' => 'iconsmall'));
            } else {
                $buttons = html_writer::link(new moodle_url('/local/prefix/entity.php', array('id' => $el->id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
                $buttons .= html_writer::link(new moodle_url('/local/prefix/entity.php', array('id' => $el->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
                if ($el->visible > 0) {
                    $buttons .= html_writer::link(new moodle_url('/local/prefix/entity.php', array('id' => $el->id, 'visible' => !$el->visible, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('inactive'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
                } else {
                    $buttons .= html_writer::link(new moodle_url('/local/prefix/entity.php', array('id' => $el->id, 'visible' => !$el->visible, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
                }
            }

            $line[] = $buttons;
            $data[] = $line;
        }
        echo "<div id='filter-box' >";
        echo '<div class="filterarea"></div></div>';
        //View Part starts
        //start the table
        $table = new html_table();
        $table->id = 'entitytable';
        $table->head = array(
            get_string('e_name', 'local_prefix'),
            get_string('editop', 'local_examtype'));
        $table->size = array('15%', '15%', '15%', '15%');
        $table->align = array('left', 'left', 'left', 'center');
        $table->width = '49%';
        $table->data = $data;
        echo html_writer::table($table);
    }

//end of function
    $entity_list = $DB->get_records('local_create_entity');
    if (empty($entity_list)) {
        $e = get_string('no_records', 'local_prefix');
        throw new Exception($e);
    }
    printtable($entity_list);
} //end of try block
catch (Exception $e) {
    echo $e->getMessage();
}

echo $OUTPUT->footer();
?>
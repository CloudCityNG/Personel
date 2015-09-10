<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/clclasses/lib.php');

class classtype_view {

    public $schoollist;
    public $classtype_schools = array();

    public function __construct($schoollist) {
        global $CFG, $DB, $USER;
        $this->schoollist = $schoollist;
        foreach ($this->schoollist as $school)
            $schools[] = $school->id;
        $schoolids = implode(',', $schools);
        $records = $DB->get_records_sql("select * from {local_class_scheduletype} where schoolid in ($schoolids) group by schoolid");
        $this->classtype_schools = $records;
    }

    public function timetable_school_classtypes_view() {
        global $DB, $CFG, $OUTPUT, $USER, $PAGE;
        $PAGE->requires->js('/local/timetable/js/tmember_toggle.js');
        $systemcontext = context_system::instance();

        $j = 0;
        foreach ($this->classtype_schools as $list) {
            //  echo $list->teammanagerid;
            $line = array();
            $schoolinfo = $DB->get_record('local_school', array('id' => $list->schoolid, 'visible' => 1));
            if ($j > 0)
                $displaynone = "display:none";
            else
                $displaynone = "";

            $firstrow = "<ul id='settiming_firstrow'><li>" . get_string('school_time', 'local_timetable') . '<b>' . $schoolinfo->fullname . html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/switch'), 'class' => 'iconsmall', 'onclick' => 'teammember_list(' . $list->id . ')', 'id' => 'tm_switchbutton'), array('style' => 'cursor:pointer')) . "</b></li></ul>";

            $toggle = "<div id = 'dialog$list->id' class = 'tmem_toggle dialog1' style = '$displaynone;clear:both; '>";

            $toggle .= $this->toggle_classtypeview($list->schoolid);
            $toggle .="</div>";

            $cell1 = new html_table_cell();
            $cell1->attributes['class'] = 'tmcell';
            $cell1->text = $firstrow . $toggle;
            $line[] = $cell1;
            $data[] = $line;
            $j++;
        }

        $table = new html_table();
        //if (has_capability('local/costcenter:manage', $systemcontext))
        $table->head = array(get_string('schoolclasstypes', 'local_timetable'));
        $table->id = "cltype_view";
        $table->size = array('100%');
        $table->align = array('left', 'left', 'left');
        $table->width = '99%';
        $table->data = $data;


        $output = html_writer::table($table);
        echo html_writer::script("
                $(document).ready(function() {
                $('#timetable_view').dataTable({
                'iDisplayLength': 5,
                'fnDrawCallback': function(oSettings) {
                if(oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
                $('#cltype_view'+'_paginate').hide();
                $('#cltype_view'+'_length').hide();
                }
                },
                'aLengthMenu': [ [5, 10, 25, 50, -1], [5, 10, 25, 50, 'All']],
                'searching': false,
                'aaSorting': [],
        } );
                } );
                ");


        return $output;
    }

    public function toggle_classtypeview($schoolid) {
        global $DB, $CFG, $OUTPUT, $USER, $PAGE;
        $i = 0;

        $records = $DB->get_records('local_class_scheduletype', array('schoolid' => $schoolid));
        foreach ($records as $record) {
            $line = array();
            $line[] = $record->classtype;
            $line[] = $this->to_get_action_buttons($record->id, $record);
            $row = new html_table_row();
            $row->cells = $line;
            //  $row->id = $list->id;
            $data[] = $row;
            $i++;
        }


        $table = new html_table();
        $table->head = array(get_string('classtype_tablehead', 'local_timetable'),
            get_string('action', 'local_timetable'));
        $table->id = "classtype_toggleview";
        $table->class = 'tmember';
        $table->size = array('60%', '40%');
        $table->align = array('left', 'left');
        $table->width = '80%';
        $table->data = $data;

        $output = html_writer::table($table);
        return $output;
    }

// end of function

    public function to_get_action_buttons($rowid, $list) {
        global $DB, $CFG, $OUTPUT, $USER, $PAGE;
        $systemcontext = context_system::instance();
        //   displaying crud operation button
        $delete_cap = array('local/timetable:manage', 'local/timetable:delete');
        if (has_any_capability($delete_cap, $systemcontext)) {
            $options[] = array('link' => new moodle_url('/local/timetable/classtype.php', array('id' => $rowid, 'delete' => 1, 'sesskey' => sesskey())), 'string' => html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
        }

        $update_cap = array('local/timetable:manage', 'local/timetable:update');
        if (has_any_capability($update_cap, $systemcontext)) {
            $options[] = array('link' => new moodle_url('/local/timetable/classtype.php', array('id' => $rowid, 'sesskey' => sesskey())), 'string' => html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
        }

        $visible_cap = array('local/timetable:manage', 'local/timetable:visible');
        if (has_any_capability($visible_cap, $systemcontext)) {

            if ($list->visible > 0) {
                $options[] = array('link' => new moodle_url('/local/timetable/classtype.php', array('id' => $list->id, 'visible' => 0, 'confirm' => 1, 'sesskey' => sesskey())), 'string' => html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('inactive'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
            } else {
                $options[] = array('link' => new moodle_url('/local/timetable/classtype.php', array('id' => $list->id, 'visible' => 1, 'confirm' => 1, 'sesskey' => sesskey())), 'string' => html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
            }
        }

        $menulist = '';
        foreach ($options as $types) {
            $menulist .= html_writer::link($types['link'], $types['string']);
        }

        return $menulist;
    }

}

// end of class
?>
<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * prints an analysed excel-spreadsheet of the evaluation
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package evaluation
 */
require_once("../../config.php");
require_once("lib.php");
require_once("$CFG->libdir/excellib.class.php");

evaluation_load_evaluation_items();

$id = required_param('id', PARAM_INT);  //the POST dominated the GET
$classid = required_param('clid', PARAM_INT);
$classfilter = optional_param('classfilter', '0', PARAM_INT);

$url = new moodle_url('/local/evaluations/analysis_to_excel.php', array('id' => $id, 'clid' => $classid));
//if ($classfilter !== '0') {
//    $url->param('classfilter', $classfilter);
//}
$PAGE->set_url($url);

$formdata = data_submitted();

//if (! $cm = get_classmodule_from_id('local_evaluations', $id)) {
//    print_error('invalidclassmodule');
//}
//if (! $class = $DB->get_record("class", array("id"=>$cm->class))) {
//    print_error('classmisconf');
//}

if (!$evaluation = $DB->get_record("local_evaluation", array("id" => $id))) {
    print_error('invalidevaluationid');
}

//$context = context_module::instance($cm->id);
//
//require_login($class, true, $cm);
//
//require_capability('mod/evaluation:viewreports', $context);
//buffering any output
//this prevents some output before the excel-header will be send
ob_start();
$fstring = new stdClass();
$fstring->bold = get_string('bold', 'local_evaluations');
$fstring->page = get_string('page', 'local_evaluations');
$fstring->of = get_string('of', 'local_evaluations');
$fstring->modulenameplural = get_string('modulenameplural', 'local_evaluations');
$fstring->questions = get_string('questions', 'local_evaluations');
$fstring->itemlabel = get_string('item_label', 'local_evaluations');
$fstring->question = get_string('question', 'local_evaluations');
$fstring->responses = get_string('responses', 'local_evaluations');
$fstring->idnumber = get_string('idnumber');
$fstring->username = get_string('username');
$fstring->fullname = get_string('fullnameuser');
$fstring->classid = get_string('classid', 'local_evaluations');
$fstring->class = get_string('class');
$fstring->anonymous_user = get_string('anonymous_user', 'local_evaluations');
ob_end_clean();

//get the questions (item-names)
$params = array('evaluation' => $evaluation->id, 'hasvalue' => 1);
if (!$items = $DB->get_records('evaluation_item', $params, 'position')) {
    print_error('no_items_available_yet', 'local_evaluations', $CFG->wwwroot . '/local/evaluations/view.php?id=' . $id . '&clid=' . $classid . '');
    exit;
}

$filename = "evaluation.xlsx";

//$mygroupid = groups_get_activity_group($cm);
// Creating a workbook
$workbook = new MoodleExcelWorkbook('-');
$workbook->send($filename);

//creating the needed formats
$xls_formats = new stdClass();
$xls_formats->head1 = $workbook->add_format(array(
    'bold' => 1,
    'size' => 12));

$xls_formats->head2 = $workbook->add_format(array(
    'align' => 'left',
    'bold' => 1,
    'bottum' => 2));

$xls_formats->default = $workbook->add_format(array(
    'align' => 'left',
    'v_align' => 'top'));

$xls_formats->value_bold = $workbook->add_format(array(
    'align' => 'left',
    'bold' => 1,
    'v_align' => 'top'));

$xls_formats->procent = $workbook->add_format(array(
    'align' => 'left',
    'bold' => 1,
    'v_align' => 'top',
    'num_format' => '#,##0.00%'));

// Creating the worksheets
$sheetname = clean_param($evaluation->name, PARAM_ALPHANUM);
error_reporting(0);
$worksheet1 = $workbook->add_worksheet(substr($sheetname, 0, 31));
$worksheet2 = $workbook->add_worksheet('detailed');
error_reporting($CFG->debug);
$worksheet1->hide_gridlines();
$worksheet1->set_column(0, 0, 10);
$worksheet1->set_column(1, 1, 30);
$worksheet1->set_column(2, 20, 15);

//writing the table header
$row_offset1 = 0;
$worksheet1->write_string($row_offset1, 0, userdate(time()), $xls_formats->head1);

////////////////////////////////////////////////////////////////////////
//print the analysed sheet
////////////////////////////////////////////////////////////////////////
//get the completeds
//$completedscount = evaluation_get_completeds_group_count($evaluation, $mygroupid, $classfilter);
//if ($completedscount > 0) {
//    //write the count of completeds
//    $row_offset1++;
//    $worksheet1->write_string($row_offset1,
//                              0,
//                              $fstring->modulenameplural.': '.strval($completedscount),
//                              $xls_formats->head1);
//}

if (is_array($items)) {
    $row_offset1++;
    $worksheet1->write_string($row_offset1, 0, $fstring->questions . ': ' . strval(count($items)), $xls_formats->head1);
}

$row_offset1 += 2;
$worksheet1->write_string($row_offset1, 0, $fstring->itemlabel, $xls_formats->head1);
$worksheet1->write_string($row_offset1, 1, $fstring->question, $xls_formats->head1);
$worksheet1->write_string($row_offset1, 2, $fstring->responses, $xls_formats->head1);
$row_offset1++;

if (empty($items)) {
    $items = array();
}
foreach ($items as $item) {
    //get the class of item-typ
    $itemobj = evaluation_get_item_class($item->typ);
    $row_offset1 = $itemobj->excelprint_item($worksheet1, $row_offset1, $xls_formats, $item, $mygroupid, $classfilter);
}

////////////////////////////////////////////////////////////////////////
//print the detailed sheet
////////////////////////////////////////////////////////////////////////
//get the completeds

$completeds = evaluation_get_completeds_group($evaluation, $mygroupid, $classfilter);
//important: for each completed you have to print each item, even if it is not filled out!!!
//therefor for each completed we have to iterate over all items of the evaluation
//this is done by evaluation_excelprint_detailed_items

$row_offset2 = 0;
//first we print the table-header
$row_offset2 = evaluation_excelprint_detailed_head($worksheet2, $xls_formats, $items, $row_offset2);


if (is_array($completeds)) {
    foreach ($completeds as $completed) {
        $row_offset2 = evaluation_excelprint_detailed_items($worksheet2, $xls_formats, $completed, $items, $row_offset2);
    }
}


$workbook->close();
exit;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
//functions
////////////////////////////////////////////////////////////////////////////////


function evaluation_excelprint_detailed_head(&$worksheet, $xls_formats, $items, $row_offset) {
    global $fstring, $evaluation;

    if (!$items) {
        return;
    }
    $col_offset = 0;

    $worksheet->write_string($row_offset + 1, $col_offset, $fstring->idnumber, $xls_formats->head2);
    $col_offset++;

    $worksheet->write_string($row_offset + 1, $col_offset, $fstring->username, $xls_formats->head2);
    $col_offset++;

    $worksheet->write_string($row_offset + 1, $col_offset, $fstring->fullname, $xls_formats->head2);
    $col_offset++;

    foreach ($items as $item) {
        $worksheet->write_string($row_offset, $col_offset, $item->name, $xls_formats->head2);
        $worksheet->write_string($row_offset + 1, $col_offset, $item->label, $xls_formats->head2);
        $col_offset++;
    }

    $worksheet->write_string($row_offset + 1, $col_offset, $fstring->classid, $xls_formats->head2);
    $col_offset++;

    $worksheet->write_string($row_offset + 1, $col_offset, $fstring->class, $xls_formats->head2);
    $col_offset++;

    return $row_offset + 2;
}

function evaluation_excelprint_detailed_items(&$worksheet, $xls_formats, $completed, $items, $row_offset) {
    global $DB, $fstring;

    if (!$items) {
        return;
    }
    $col_offset = 0;
    $classid = 0;

    $evaluation = $DB->get_record('local_evaluation', array('id' => $completed->evaluation));
    //get the username
    //anonymous users are separated automatically because the userid in the completed is "0"
    if ($user = $DB->get_record('user', array('id' => $completed->userid))) {
        if ($completed->anonymous_response == EVALUATION_ANONYMOUS_NO) {
            $worksheet->write_string($row_offset, $col_offset, $user->idnumber, $xls_formats->head2);
            $col_offset++;
            $userfullname = fullname($user);
            $worksheet->write_string($row_offset, $col_offset, $user->username, $xls_formats->head2);
            $col_offset++;
        } else {
            $userfullname = $fstring->anonymous_user;
            $worksheet->write_string($row_offset, $col_offset, '-', $xls_formats->head2);
            $col_offset++;
            $worksheet->write_string($row_offset, $col_offset, '-', $xls_formats->head2);
            $col_offset++;
        }
    } else {
        $userfullname = $fstring->anonymous_user;
        $worksheet->write_string($row_offset, $col_offset, '-', $xls_formats->head2);
        $col_offset++;
        $worksheet->write_string($row_offset, $col_offset, '-', $xls_formats->head2);
        $col_offset++;
    }

    $worksheet->write_string($row_offset, $col_offset, $userfullname, $xls_formats->head2);

    $col_offset++;
    foreach ($items as $item) {
        $params = array('item' => $item->id, 'completed' => $completed->id);
        $value = $DB->get_record('evaluation_value', $params);

        $itemobj = evaluation_get_item_class($item->typ);
        $printval = $itemobj->get_printval($item, $value);
        $printval = trim($printval);

        if (is_numeric($printval)) {
            $worksheet->write_number($row_offset, $col_offset, $printval, $xls_formats->default);
        } else if ($printval != '') {
            $worksheet->write_string($row_offset, $col_offset, $printval, $xls_formats->default);
        }
        $printval = '';
        $col_offset++;
        $classid = isset($value->class_id) ? $value->class_id : 0;
        if ($classid == 0) {
            $classid = $evaluation->class;
        }
    }
    $worksheet->write_number($row_offset, $col_offset, $classid, $xls_formats->default);
    $col_offset++;
    //if (isset($classid) AND $class = $DB->get_record('class', array('id' => $classid))) {
    //    $classcontext = context_class::instance($classid);
    //    $shortname = format_string($class->shortname, true, array('context' => $classcontext));
    //    $worksheet->write_string($row_offset, $col_offset, $shortname, $xls_formats->default);
    //}
    return $row_offset + 1;
}

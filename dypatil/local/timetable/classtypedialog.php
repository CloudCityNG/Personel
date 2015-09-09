<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
$schoolid = optional_param('schoolid', 0, PARAM_INT);
$cltypename = optional_param('cltypename', '', PARAM_TEXT);
$userinputclasstypename = optional_param('userinputctname', '', PARAM_TEXT);
$userinputschoolid = optional_param('userinputsid', 0, PARAM_INT);
if ($schoolid) {
    require_once($CFG->dirroot . '/local/timetable/scheduleclass_form.php');
    ?>
    <form id="cltypedialog" name="cltypedialog" >
        <table width="100%" border="0" cellspacing="0" cellpadding="5">
            <tr>
                <td width="30%"><label for="name">Class Type<span style="color:red">*</span></label></td>
                <td width="70%"><input type="text"  name="classtype" id="classtype" style="height:25px !important"/></td>
                <td width="15%">&nbsp;</td>
            </tr> 
            <tr>
                <td valign="top">&nbsp;</td>
                <td colspan="2"><input type="button" name="button" id="button" value="Submit" onclick="classtypevalidation()"/>
                </td>
            </tr>
        </table>
        <?php
        echo '<input type="hidden" value="' . $schoolid . '" id="schoolid">';
        ?>
        <p id="error" style="color:red;"></p>
        <p>There are required fields in this form marked <span style="color:red">*</span><p>
    </form>

    <?php
} else if (!empty($cltypename)) {
    $compare_scale_clause = $DB->sql_compare_text('classtype') . ' = ' . $DB->sql_compare_text(':cltype');
    $shortname_exist = $DB->get_records_sql("select * from {local_class_scheduletype} where  $compare_scale_clause", array('cltype' => $cltypename));

    if ($shortname_exist)
        echo "exist";
}
else {
    if ($userinputclasstypename && $userinputschoolid) {

        $temp = new stdclass();
        $temp->schoolid = $userinputschoolid;
        $temp->classtype = $userinputclasstypename;
        $temp->visible = 1;
        $temp->usermodified = $USER->id;
        $temp->timecreated = time();
        $temp->timemodified = time();
        $insertedrowid = $DB->insert_record('local_class_scheduletype', $temp);
        echo $insertedrowid;
    }
}
?>
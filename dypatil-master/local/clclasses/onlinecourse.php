<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/clclasses/createclass_form.php');
global $USER, $DB, $CFG;
$schoid = $_REQUEST['schoid'];
$deptid = $_REQUEST['deptid'];
?>
<form id="courseform" name="courseform" >
    <table width="100%" border="0" cellspacing="0" cellpadding="5">
        <tr>
            <td width="30%"><label for="name">Course Name<span style="color:red">*</span></label></td>
            <td width="70%"><input type="text"  name="fullnames" id="fullname" style="height:25px !important"/></td>
            <td width="15%">&nbsp;</td>
        </tr>
        <tr>
            <td><label for="email">Course ID<span style="color:red">*</span></label></td>
            <td><input type="text" name="shortnames" id="shortname" style="height:25px !important"/></td>
            <td>&nbsp;</td>
        </tr>
        <!--<tr>
          <td><label for="subject">Credit Hours<span style="color:red">*</span></label></td>
          <td width="70%"><input type="text"  name="credithours" id="credithour" style="height:25px !important"/></td>
          <td width="15%">&nbsp;</td>
        </tr>-->


        <tr>
            <td valign="top">&nbsp;</td>
            <td colspan="2"><input type="button" name="button" id="button" value="Submit" onclick="onlinevalidation()"/>
            </td>
        </tr>
    </table>
    <?php
    echo '<input type="hidden" value="' . $schoid . '" id="sid">';
    echo '<input type="hidden" value="' . $deptid . '" id="did">';
    ?>
    <p id="error" style="color:red;"></p>
    <p>There are required fields in this form marked <span style="color:red">*</span>.<p>
</form>
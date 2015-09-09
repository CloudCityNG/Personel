<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/user/selector/lib.php');
require_once(dirname(__FILE__) . '/classlib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/roles/lib.php');
require_once($CFG->dirroot . '/local/clclasses/lib.php');

$id = required_param('id', PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/clclasses/classenrol.php');
$currentcss = '/local/clclasses/css/style.css';
$PAGE->requires->css($currentcss);
//get the admin layout
$PAGE->set_pagelayout('admin');
$PAGE->set_heading(get_string('pluginname', 'local_clclasses'));
$PAGE->set_title(get_string('pluginname', 'local_clclasses'));
require_login();

$semclass = new schoolclasses();

if (!has_capability('local/clclasses:enrolluser', $systemcontext)) {
    print_error('You dont have permissions');
}
$returnurl = $CFG->wwwroot . '/local/clclasses/classenrol.php?id=' . $id;

echo $OUTPUT->header();

if (!$class = $DB->get_record('local_clclasses', array('id' => $id))) {
    print_error('invalid classid');
}
echo $OUTPUT->heading(get_string('class_enroll', 'local_clclasses', $class->fullname));

$classmembersselector = new class_members_selector('removeselect', array('classid' => $id));
$potentialmembersselector = new class_non_members_selector('addselect', array('classid' => $id));


$currenttab = "enroll";
$semclass->print_scheduletabs($currenttab, 0, 1, 0);
/* Description comes here */
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('enrol_user_desc', 'local_clclasses'));
}
echo get_string('classlimitmessage', 'local_clclasses', $class->classlimit);

if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {

    // get lie list of selected users
    $userstoadd = $potentialmembersselector->get_selected_users();

    //Make sure class limit does not exceeds
    $enrolled = $DB->count_records('local_user_clclasses', array('classid' => $class->id, 'registrarapproval' => 1));
    $tobeenrolled = sizeof($userstoadd);
    $availableseats = $class->classlimit - $enrolled;
    if ($tobeenrolled > $availableseats) {
        $message = get_string('classlimitexceded', 'local_clclasses', $availableseats);
        if ($availableseats == 0) {
            $message = get_string('classlimitfilled', 'local_clclasses', $availableseats);
        }
        echo $message;
    } else {
        if (!empty($userstoadd)) {

            // Display the enrolment status selected users
            $unsetusers = check_enrolment_status($class, $userstoadd);
            foreach ($userstoadd as $user) {
                foreach ($unsetusers as $key => $value) {
                    unset($userstoadd[$key]);
                }
            }
            foreach ($userstoadd as $user) {
                // Enrol the users to the class
                if (!class_enrol_user($id, $user->id, $classlist)) {
                    print_error('erroraddremoveuser', 'group', $returnurl);
                }
                $classmembersselector->invalidate_selected_users();
                $potentialmembersselector->invalidate_selected_users();
            }
        }
    }
}

if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoremove = $classmembersselector->get_selected_users();
    if (!empty($userstoremove)) {
        foreach ($userstoremove as $user) {
            if (!class_unenrol_user($id, $user->id)) {
                print_error('erroraddremoveuser', 'group', $returnurl);
            }
            $classmembersselector->invalidate_selected_users();
            $potentialmembersselector->invalidate_selected_users();
        }
    }
}
$today = date('Y-m-d');
$activesem = $DB->get_record_select('local_semester', 'id = ' . $class->semesterid . ' AND "' . $today . '" < FROM_UNIXTIME(enddate,  "%Y-%m-%d")');

if (!$activesem) {
    echo $message = get_string('noactivesemester', 'local_clclasses');
}
?>

<div id="addmembersform">
    <form id="assignform" method="post" action="<?php echo $CFG->wwwroot; ?>/local/clclasses/classenrol.php?id=<?php echo $id; ?>">
        <div>
            <input type="hidden" name="sesskey" value="<?php p(sesskey()); ?>" />

            <table class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">
                <tr>
                    <td id='existingcell'>
                        <p>
                            <label for="removeselect"><?php echo 'Class Members'; ?></label>
                        </p>
                        <?php $classmembersselector->display(); ?>
                    </td>
                    <td id='buttonscell'>
                        <p class="arrow_button">
                            <?php
                            if ($activesem) {
                                ?>
                                <input name="add" id="add" type="submit" value="<?php echo $OUTPUT->larrow() . '&nbsp;Enrol'; ?>" title="<?php print_string('add'); ?>" /><br />
                                <input name="remove" id="remove" type="submit" value="<?php echo 'Unenrol &nbsp;' . $OUTPUT->rarrow(); ?>" title="<?php print_string('remove'); ?>" />
                            <?php } ?>
                        </p>
                    </td>

                    <td id='potentialcell'>
                        <p>
                            <label for="addselect"><?php print_string('potusers', 'local_clclasses'); ?></label>
                        </p>
                        <?php $potentialmembersselector->display(); ?>
                    </td>
                </tr>
                <tr><td colspan="3" id='backcell'>
                        <input type="submit" name="cancel" value="<?php echo "Back to Class"; ?>" />
                    </td></tr>
            </table>
        </div>
    </form>
</div>

<?php
$PAGE->requires->js_init_call('M.core_role.init_add_assign_page');
echo $OUTPUT->footer();

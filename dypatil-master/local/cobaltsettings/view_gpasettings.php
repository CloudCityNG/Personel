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
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage  providing  global settings
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/cobaltsettings/lib.php');
$systemcontext = context_system::instance();
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');

/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);

require_login();

$PAGE->set_url('/local/cobaltsettings/view_gpasettings.php');
if (!has_capability('local/cobaltsettings:view', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->navbar->add(get_string('global_settings', 'local_cobaltsettings'), new moodle_url('/local/cobaltsettings/school_settings.php'));
$PAGE->navbar->add(get_string('view_gpa', 'local_cobaltsettings'));

echo $OUTPUT->header();

/* ---cobalt settings heading--- */
echo $OUTPUT->heading(get_string('gpa/cgp_settings', 'local_cobaltsettings'));

try {
    /* ---adding tabs using global_settings_tabs function--- */
    $currenttab = 'view_gpa';
    $global_ob = global_settings::getInstance();
    $global_ob->globalsettings_tabs($currenttab, 'gpasettings');

    /* ---description of the  table--- */
    echo $OUTPUT->box(get_string('view_sem_heading', 'local_cobaltsettings'));

    /* ---checking if login user is registrar or admin--- */
    $school_id = $global_ob->check_loginuser_registrar_admin(true);

    $sql = "select distinct schoolid from {$CFG->prefix}local_cobalt_gpasettings where schoolid in ($school_id)";
    $gpa_list = $DB->get_records_sql($sql);
    $data = array();
    foreach ($gpa_list as $gpa) {
        $line = array();
        $schoolname = $DB->get_record('local_school', array('id' => $gpa->schoolid));
        $line[] = $schoolname->fullname;
        $cate_types = $DB->get_records('local_cobalt_gpasettings', array('schoolid' => $gpa->schoolid));

        $c_type = '';
        $gpa = '';
        $cgpa = '';
        $probationgpa = '';
        $dismissalgpa = '';
        $buttons = '';
        foreach ($cate_types as $ct) {
            $typesnames = $DB->get_record('local_cobalt_subentities', array('id' => $ct->sub_entityid));
            $c_type.='<div>' . $typesnames->name . '</div>';
            $gpa.='<div>' . round($ct->gpa, 2) . '</div>';
            $cgpa.='<div>' . round($ct->cgpa, 2) . '</div>';
            $probationgpa.='<div>' . round($ct->probationgpa, 2) . '</div>';
            $dismissalgpa.='<div>' . round($ct->dismissalgpa, 2) . '</div>';
            $buttons.='<div>' . html_writer::link(new moodle_url('/local/cobaltsettings/gpa_settings.php', array('id' => $ct->id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
            $buttons.=html_writer::link(new moodle_url('/local/cobaltsettings/gpa_settings.php', array('id' => $ct->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall'))) . '</div>';
        }
        $line[] = $c_type;
        $line[] = $gpa;
        $line[] = $cgpa;
        $line[] = $probationgpa;
        $line[] = $dismissalgpa;
        if (has_capability('local/cobaltsettings:manage', $systemcontext))
            $line[] = $buttons;
        $data[] = $line;
    }
    $PAGE->requires->js('/local/cobaltsettings/js/pagi.js');
    $table = new html_table();
    $table->id = "setting";
    $table->head = array(
        get_string('schoolid', 'local_collegestructure'),
        get_string('category_types', 'local_cobaltsettings'),
        get_string('semgpa', 'local_semesters'),
        get_string('semcgpa', 'local_semesters'),
        get_string('probationgpa', 'local_cobaltsettings'),
        get_string('dismissalgpa', 'local_cobaltsettings'));
    if (has_capability('local/cobaltsettings:manage', $systemcontext))
        $table->head[] = get_string('action', 'local_cobaltsettings');
    $table->size = array('15%', '15%', '10%', '10%', '10%', '10%', '10%');
    $table->align = array('left', 'left', 'left', 'center');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
} /* ---end of try block--- */ catch (Exception $e) {
    echo $e->getMessage();
}

echo $OUTPUT->footer();
?>





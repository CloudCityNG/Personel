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
$PAGE->set_url('/local/cobaltsettings/view_categorylevel.php');

if (!has_capability('local/cobaltsettings:view', $systemcontext)) {
    print_error('You dont have permissions');
}

$PAGE->navbar->add(get_string('global_settings', 'local_cobaltsettings'), new moodle_url('/local/cobaltsettings/index.php'));
$PAGE->navbar->add(get_string('view_entitylevel', 'local_cobaltsettings'));
echo $OUTPUT->header();
/* ---global settings heading--- */
echo $OUTPUT->heading(get_string('cobalt_entitysettings', 'local_cobaltsettings'));
$global_ob = global_settings::getInstance();
$school_id = $global_ob->check_loginuser_registrar_admin(true);
try {
    /* ---adding tabs using global_settings_tabs function--- */
    $currenttab = 'view_entitylevel';
  
    $global_ob->globalsettings_tabs($currenttab, 'entitysettings');

    /* ---description of the  table--- */
    echo $OUTPUT->box(get_string('view_scl', 'local_cobaltsettings'));
    /* ---checking if login user is registrar or admin--- */
    

    $categories = $DB->get_records_sql('select e.id, e.name from {local_cobalt_entitylevels} as el
                                  join {local_cobalt_entity} as e on e.id=el.entityid 
                                  group by entityid');
    $data = array();
    foreach ($categories as $cate) {
        $line = array();
        $cat_list = '<div><b>' . $cate->name . '</b></div>';
        $c_types = $DB->get_records('local_cobalt_subentities', array('entityid' => $cate->id));
        foreach ($c_types as $ct) {
            $cat_list.='<div style="margin-left:20px; font-size:12px;">' . $ct->name . '</div>';
        }
        $line[] = $cat_list;
        $sql = "SELECT * From {$CFG->prefix}local_cobalt_entitylevels  where entityid=$cate->id and  schoolid in ($school_id)";
        $school_used = $DB->get_records_sql($sql);
        if (!empty($school_used)) {
            $school_list = '';
            $levels = '';
            $buttons = '';
            foreach ($school_used as $school) {
                $school_name = $DB->get_record('local_school', array('id' => $school->schoolid));
                $school_list.='<div style="min-width:30px; min-height:19px;" >' . $school_name->fullname . '</div>';
                if ($school->level == "SL")
                    $levels.='<div style="min-width:30px; min-height:19px;">' . get_string('schoolid', 'local_collegestructure') . ' ' . get_string('level', 'local_cobaltsettings') . '</div>';
                elseif ($school->level == "PL")
                    $levels.='<div style="min-width:30px; min-height:19px;">' . get_string('program', 'local_programs') . ' ' . get_string('level', 'local_cobaltsettings') . '</div>';
                else
                    $levels.='<div style="min-width:30px; min-height:19px;" >' . get_string('curriculum', 'local_curriculum') . '  ' . get_string('level', 'local_cobaltsettings') . '</div>';
                $buttons.='<div style="min-height:40px;" >' . html_writer::link(new moodle_url('/local/cobaltsettings/category_level.php', array('id' => $school->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
            }
            $line[] = $school_list;
            $line[] = $levels;
            if (has_capability('local/cobaltsettings:manage', $systemcontext))
                $line[] = $buttons;
        }
        else {
            $line[] = get_string('no_cat', 'local_cobaltsettings');
            $line[] = get_string('leveel_school', 'local_cobaltsettings');
            $line[] = get_string('no_action', 'local_cobaltsettings');
        }
        $data[] = $line;
    }
    /* ---end of main foreach--- */

    $PAGE->requires->js('/local/cobaltsettings/js/pagi.js');
    $table = new html_table();
    $table->id = "setting";
    $table->head = array(
        get_string('entityandsub', 'local_cobaltsettings'),
        get_string('schoolid', 'local_collegestructure'),
        get_string('level', 'local_cobaltsettings'));

    if (has_capability('local/cobaltsettings:manage', $systemcontext))
        $table->head[] = get_string('edit', 'local_cobaltsettings');

    $table->size = array('10%', '20%', '10%', '10%');
    $table->align = array('left', 'left', 'left', 'center');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
}
/* ---end of try block--- */ catch (Exception $e) {
    echo $e->getMessage();
}

echo $OUTPUT->footer();
?>





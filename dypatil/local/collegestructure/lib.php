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
 * General plugin functions.
 *
 * @package    local
 * @subpackage College Structure
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;

use moodle\local\collegestructure as collegestructure;

require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/message/lib.php');
/*
 * Function to add school into the database
 */

class school {
    /**
     * school_add_intance is for adding a school
     * @param-$school is the values that is given
     * 
     */

    public function school_add_instance($school) {
        global $DB, $CFG, $USER;
        
        $hierarchy = new hierarchy();

        if ($school->parentid == 0) {
            $school->depth = 1;
            $school->path = '';
        } else {
            /* ---parent item must exist--- */
            $parent = $DB->get_record('local_school', array('id' => $school->parentid));
            $school->depth = $parent->depth + 1;
            $school->path = $parent->path;
        }
        /* ---get next child item that need to provide--- */
        if (!$sortorder = $hierarchy->get_next_child_sortthread($school->parentid, 'local_school')) {
            return false;
        }

        $school->sortorder = $sortorder;
        $schools = $DB->insert_record('local_school', $school);
 
        if(!is_siteadmin()){
            $this->add_users(array($USER->id), $schools);
        }
        $DB->set_field('local_school', 'path', $school->path . '/' . $schools, array('id' => $schools));
        $currenturl = "{$CFG->wwwroot}/local/collegestructure/index.php";
        $conf = new object();
        $conf->school = $school->fullname;
        $message = get_string('createsuccess', 'local_collegestructure', $conf);
        $hierarchy->set_confirmation($message, $currenturl, array('style' => 'notifysuccess'));
    }

    /**
     * @method school_update_instance
     * @param int $schoolid Schoolid
     * @param object $newschool school data
     * @retun Updates the school
     * 
     */
    public function school_update_instance($schoolid, $newschool) {
        global $DB, $CFG;
        $hierarchy = new hierarchy();
        $oldschool = $DB->get_record('local_school', array('id' => $schoolid));
        $currenturl = "{$CFG->wwwroot}/local/collegestructure/index.php";
        /* ---check if the parentid is the same as that of new parentid--- */
        if ($newschool->parentid != $oldschool->parentid) {
            $newparentid = $newschool->parentid;
            $newschool->parentid = $oldschool->parentid;
        }
        $now = date("d-m-Y");
        $now = strtotime($now);
        $newschool->timemodified = $now;

        $DB->update_record('local_school', $newschool);

        if (isset($newparentid)) {
            $updatedschool = $DB->get_record('local_school', array('id' => $schoolid));
            $newparentid = isset($newparentid) ? $newparentid : 0;
            /* ---if the new parentid is different then update--- */
            $this->update_school($updatedschool, $newparentid, 'local_school');
        }
        $updatedschool = $DB->get_record('local_school', array('id' => $schoolid));

        $conf = new object();
        $conf->school = $newschool->fullname;
        $message = get_string('updatesuccess', 'local_collegestructure', $conf);
        $hierarchy->set_confirmation($message, $currenturl, array('style' => 'notifysuccess'));
    }

    /**
     * @method update_school
     * @param object $school 
     * @param object $newparentid school data
     * @retun Updates the school
     * 
     */
    public function update_school($school, $newparentid, $plugin) {
        global $DB, $CFG;

        $hierarche = new hierarchy ();
        if (!is_object($school)) {
            return false;
        }

        if ($school->parentid == 0) {
            /* ---create a 'fake' old parent item for items at the top level--- */
            $oldparent = new stdClass();
            $oldparent->id = 0;
            $oldparent->path = '';
            $oldparent->depth = 0;
        } else {
            $oldparent = $DB->get_record($plugin, array('id' => $school->parentid));
        }

        if ($newparentid == 0) {
            $newparent = new stdClass();
            $newparent->id = 0;
            $newparent->path = '';
            $newparent->depth = 0;
        } else {
            $newparent = $DB->get_record($plugin, array('id' => $newparentid));

            if ($this->subschool_of($newparent, $school->id) || empty($newparent)) {
                return false;
            }
        }

        if (!$newsortorder = $hierarche->get_next_child_sortthread($newparentid, $plugin)) {
            return false;
        }
        $oldsortorder = $school->sortorder;

        /* ---update the sortorder for the all items--- */
        $this->update_sortorder($oldsortorder, $newsortorder, $plugin);
        /* ---update the depth of the item and its descendants--- */
        $depthdiff = ($newparent->depth + 1) - $school->depth;
        /* ---update the depth--- */
        $params = array('depthdiff' => $depthdiff,
            'path' => $school->path,
            'pathb' => "$school->path/%");

        $sql = "UPDATE $CFG->prefix$plugin
            SET depth = depth + :depthdiff
            WHERE (path = :path OR
            " . $DB->sql_like('path', ':pathb') . ")";
        $DB->execute($sql, $params);
        $length_sql = $DB->sql_length("'$oldparent->path'");
        $substr_sql = $DB->sql_substr('path', "{$length_sql} + 1");
        $updatepath = $DB->sql_concat("'{$newparent->path}'", $substr_sql);

        $params = array(
            'path' => $school->path,
            'pathb' => "$school->path/%");

        $sql = "UPDATE $CFG->prefix$plugin
            SET path = $updatepath
            WHERE (path = :path OR
            " . $DB->sql_like('path', ':pathb') . ")";
        $DB->execute($sql, $params);
        $todb = new stdClass();
        $todb->id = $school->id;
        $todb->parentid = $newparentid;
        $DB->update_record($plugin, $todb);

        return true;
    }

    public function update_sortorder($oldsortorder, $newsortorder, $plugin) {
        global $DB, $CFG;

        $length_sql = $DB->sql_length("'$oldsortorder'");
        $substr_sql = $DB->sql_substr('sortorder', "$length_sql + 1");
        $sortorder = $DB->sql_concat(":newsortorder", $substr_sql);
        $params = array(
            'newsortorder' => $newsortorder,
            'oldsortorder' => $oldsortorder,
            'oldsortordermatch' => "{$oldsortorder}%"
        );
        $sql = "UPDATE $CFG->prefix$plugin
            SET sortorder = $sortorder
            WHERE (sortorder = :oldsortorder OR
            " . $DB->sql_like('sortorder', ':oldsortordermatch') . ')';

        return $DB->execute($sql, $params);
    }

    public function subschool_of($school, $ids) {
        if (!isset($school->path)) {
            return false;
        }
        $ids = (is_array($ids)) ? $ids : array($ids);
        $parents = explode('/', substr($school->path, 1));

        foreach ($parents as $parent) {
            if (in_array($parent, $ids)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @method get_childitems
     * @todo get the child items of the school 
     * @param int $id school id     
     * @retun array of objects - childschool list
     * 
     */
    public function get_childitems($id) {
        global $CFG, $DB;
        $sql = "SELECT path,id from {local_school} where id={$id}";
        $path = $DB->get_field_sql($sql);
        if ($path) {
            /* ---the WHERE clause must be like this to avoid /1% matching /10--- */
            $sql = "SELECT id, fullname, parentid, path
                    FROM {local_school}
                    WHERE path = '{$path}' OR path LIKE '{$path}/%'
                    ORDER BY path";
            return $DB->get_records_sql($sql);
        }
    }

    /**
     * @method school_delete_instance
     * @todo  Delete the school
     * @param int $id school id     
     */
    public function school_delete_instance($id) {
        global $DB, $CFG;
        $hierarchy = new hierarchy ();
        $delete_schools = $this->get_childitems($id);
        $DB->delete_records('local_school', array('id' => $id));
        $DB->delete_records('local_school_permissions ', array('schoolid' => $id));
        $currenturl = "{$CFG->wwwroot}/local/collegestructure/index.php";
        $hierarchy->set_confirmation(get_string('deletesuccess', 'local_collegestructure'), $currenturl, array('style' => 'notifysuccess'));
    }

    /**
     * @method add_users
     * @todo add_users is the function to add the registrar to a school
     * @param array $userids  user array
     * @param int $schoolid  Schoolid
     */
    public function add_users($userids, $schoolid) {
        global $CFG, $DB, $OUTPUT, $USER;

        $hierarchy = new hierarchy ();
        $currenturl = "{$CFG->wwwroot}/local/collegestructure/assignusers.php";
        if (empty($userids)) {
            /* ---nothing to do--- */
            return;
        }

        $userids = array_reverse($userids);
        foreach ($userids as $userid) {
            $registrar = new stdClass();
            $registrar->userid = $userid;
            $registrar->schoolid = $schoolid;
            $getroleid = $hierarchy->get_registrar_roleid();
            foreach ($getroleid as $getid) {
                $context = context_user::instance($userid);
                $systemcontext = context_system::instance();

                if (has_capability('local/clclasses:approveclclasses', $context)) {
                    $roleid = $getid->id;
                } elseif (has_capability('local/collegestructure:manage', $systemcontext)) {
                    $roleid = $getid->id;
                } elseif (has_capability('local/clclasses:submitgrades', $systemcontext)) {
                    $roleid = $getid->id;
                } elseif (!has_capability('local/clclasses:submitgrades', $systemcontext)) {
                    $roleid = $getid->id;
                } elseif (!has_capability('local/clclasses:approveclclasses', $context)) {
                    $roleid = $getid->id;
                } else {
                    $roleid = $getid->id;
                }
            }
            $registrar->roleid = $roleid;
            $now = date("d-m-Y");
            $registrar->timecreated = strtotime($now);
            $registrar->usermodified = $USER->id;
            $school = $DB->get_record('local_school', array('id' => $schoolid));
            $checkexist = $DB->get_record('local_school_permissions', array('userid' => $userid, 'schoolid' => $schoolid));
            if ($checkexist) {
                $hierarchy->set_confirmation(get_string('alreadyassigned', 'local_collegestructure', array('school' => $school->fullname)), $currenturl);
            } else {
                $ll = $DB->insert_record('local_school_permissions', $registrar);
            }
            if ($ll) {
                /* ---start of vijaya--- */
                $conf = new object();
                $conf->username = $DB->get_field('user', 'username', array('id' => $userid));
                $conf->schoolname = $DB->get_field('local_school', 'fullname', array('id' => $schoolid));
                $message = get_string('msg_add_reg_schl', 'local_collegestructure', $conf);

                $userfrom = $DB->get_record('user', array('id' => $USER->id));
                $userto = $DB->get_record('user', array('id' => $userid));
                $message_post_message = message_post_message($userfrom, $userto, $message, FORMAT_HTML);
                /* ---end of vijaya--- */
                //   $hierarchy->set_confirmation(get_string('assignedsuccess', 'local_collegestructure'), $currenturl,array('style' => 'notifysuccess'));
            }
        }

        if ($ll) {
            $hierarchy->set_confirmation(get_string('assignedsuccess', 'local_collegestructure'), $currenturl, array('style' => 'notifysuccess'));
        } else {
            $hierarchy->set_confirmation(get_string('assignedfailed', 'local_collegestructure'), $currenturl, array('style' => 'notifyproblem'));
        }
    }

    /**
     * @method unassign_users_instance
     * @todo To unassign the registrar to a school
     * @param int $id School id
     * @param int $userid User ID
     */
    public function unassign_users_instance($id, $userid) {
        global $DB, $CFG, $USER;

        $hierarchy = new hierarchy ();
        $currenturl = "{$CFG->wwwroot}/local/collegestructure/assignusers.php";
        /* ---start of vijaya--- */
        $conf = new object();
        $conf->username = $DB->get_field('user', 'username', array('id' => $userid));
        $conf->schoolname = $DB->get_field('local_school', 'fullname', array('id' => $id));
        /* ---end of vijaya--- */
        $delete = $DB->delete_records('local_school_permissions', array('schoolid' => $id, 'userid' => $userid));
        if ($delete) {
            /* ---start of vijaya--- */
            $message = get_string('msg_del_reg_schl', 'local_collegestructure', $conf);
            $userfrom = $DB->get_record('user', array('id' => $USER->id));
            $userto = $DB->get_record('user', array('id' => $userid));
            $message_post_message = message_post_message($userfrom, $userto, $message, FORMAT_HTML);
            /* ---end of vijaya--- */
            $hierarchy->set_confirmation(get_string('unassignedsuccess', 'local_collegestructure'), $currenturl, array('style' => 'notifysuccess'));
        } else {
            $hierarchy->set_confirmation(get_string('problemunassignedsuccess', 'local_collegestructure'), $currenturl, array('style' => 'notifyproblem'));
        }
    }

    /**
     * @method display_hierarchy_item
     * @todo To display the all school items
     * @param object $record is school  
     * @param boolean $indicate_depth  depth for the school item
     */
    public function display_hierarchy_item($record, $indicate_depth = true) {
        global $OUTPUT;

        $itemdepth = ($indicate_depth) ? 'depth' . min(10, $record->depth) : 'depth1';
        // @todo get based on item type or better still, don't use inline styles :-(
        $itemicon = $OUTPUT->pix_url('/i/item');
        $cssclass = !$record->visible ? 'dimmed' : '';
        $out = html_writer::start_tag('span', array('class' => 'hierarchyitem ' . $itemdepth, 'style' => 'background-image: url("' . $itemicon . '")'));

        $out .= $OUTPUT->action_link(new moodle_url('view.php', array('id' => $record->id)), format_string($record->fullname), null, array('class' => $cssclass));
        if ($record->type == 1)
        //  $out .=" - (Department)";
            if ($record->type == 2)
                $out .="-(Organization)";
        //   $out .= html_writer::end_tag('span');
        return $out;
    }

    /**
     * @method treeview
     * @todo To add action buttons
     */
    public function treeview() {
        global $DB, $CFG, $OUTPUT, $USER;
        $systemcontext =context_system::instance();

        if (is_siteadmin()) {
            $sql = "SELECT distinct(s.id),s.* FROM {local_school} s ORDER BY s.sortorder";
        } else {
            $sql = "SELECT distinct(s.id),s.* FROM {local_school} s  where id in(select schoolid from {local_school_permissions} sp where sp.schoolid=s.id AND sp.userid={$USER->id}) ORDER BY s.sortorder ";
        }
        $tools = $DB->get_records_sql($sql);
        $data = array();
        foreach ($tools as $tool) {
            $line = array();
            $linkcss = $tool->visible ? ' ' : 'class="dimmed" ';
            $showdepth = 1;
            $line[] = $this->display_hierarchy_item($tool, $showdepth);
            $sql = " SELECT distinct(s.id),s.* FROM {local_school} s  where s.id={$tool->id} AND id in(select schoolid from {local_school_permissions} where schoolid={$tool->id} AND userid={$USER->id}) ORDER BY s.sortorder  ";
            $checkpermissions = $DB->get_records_sql($sql);
            $buttons = array();
            $delete_cap = array('local/collegestructure:manage', 'local/collegestructure:delete');
            if (has_any_capability($delete_cap, $systemcontext)) {
                $buttons[] = html_writer::link(new moodle_url('/local/collegestructure/school.php', array('id' => $tool->id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
            }
            $update_cap = array('local/collegestructure:manage', 'local/collegestructure:update');
            if (has_any_capability($update_cap, $systemcontext)) {
                $buttons[] = html_writer::link(new moodle_url('/local/collegestructure/school.php', array('id' => $tool->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
            }
            $visible_cap = array('local/collegestructure:manage', 'local/collegestructure:visible');
            if (has_any_capability($visible_cap, $systemcontext)) {
                if ($tool->visible) {
                    $buttons[] = html_writer::link(new moodle_url('/local/collegestructure/school.php', array('id' => $tool->id, 'hide' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('inactive'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
                } else {
                    $buttons[] = html_writer::link(new moodle_url('/local/collegestructure/school.php', array('id' => $tool->id, 'show' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
                }
            }
            if ($checkpermissions || is_siteadmin()) {
                $line[] = implode(' ', $buttons);
            } else {
                $line[] = "";
            }
            $data[] = $line;
        }
        $table = new html_table();
        $table->head = array(get_string('schoolname', 'local_collegestructure'), get_string('saction', 'local_collegestructure'));
        $table->size = array('50%', '30%', '20%');
        $table->align = array('left', 'left', 'center');
        $table->width = '99%';
        $table->data = $data;
        $table->id = 'hierarchy-index';
        echo html_writer::table($table);
    }

    /**
     * @method collegegrid
     * @todo To display -Grid view of college structure will all the schools,programs and departments
     * @param int $page page number  
     * @param int $perpage
     */
    public function collegegrid($page, $flist, $perpage) {
        global $DB, $CFG, $USER, $OUTPUT;
        /* ---Get the records from the database--- */
        $hierarchy = new hierarchy();
        $i = 1;
        $sql = "SELECT * FROM {local_school} ORDER BY sortorder";
        $schoolgrid = $DB->get_records_sql($sql);
        $totalcount = count($schoolgrid);
        foreach ($schoolgrid as $school) {
            $j = 1;
            $k = 1;

            $sql = " SELECT distinct(s.id),s.* FROM {local_school} s  where 
                        (s.id={$school->id} AND id in(select schoolid from {local_school_permissions} 
                         where schoolid={$school->id} AND userid={$USER->id})) 
                         ORDER BY s.sortorder  ";
            $checkpermissions = $DB->get_records_sql($sql);
            $creatprogram = '<a title="Add Program" href="' . $CFG->wwwroot . '/local/programs/program.php?id=-1&scid=' . $school->id . '&sesskey=' . sesskey() . '">Add Program</a>';
            $creatdepartment = '<a title="Add Course Library" href="' . $CFG->wwwroot . '/local/departments/departments.php?id=-1&scid=' . $school->id . '&sesskey=' . sesskey() . '">Add Course Library</a>';

            /* ---start of the first division--- */
            $linkcss = $school->visible ? ' ' : 'class="dimmed" ';
            $showdepth = 1;
            
            if ($checkpermissions || is_siteadmin()){
            $str = '<div style="border:0px solid red" id="hierarchy-index">';
            /* ---printing the school and it actions--- */

            $str .='<div >
                        <div style="padding:5px 0px 5px 3px;font-size:14px;border:0px solid #000;background:#CAE1FC">
                            <a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/collegestructure/view.php?id=' . $school->id . '">' . $this->display_hierarchy_item($school, $showdepth) . '</a>';
            $str .='<span style="width:47%;float:right">';
            if ($checkpermissions || is_siteadmin())
                $str .= $hierarchy->get_actions('collegestructure', 'school', $school->id, $school->visible);
            $str .='</span></div>  ';
            /* ---printing the manage program label--- */
            $str .='<div style="border-width:0px 0px 0px 0px;border-style:solid;border-color:#DDD">
                        <div style="border-width:0px 0px 1px 0px;border-style:solid;border-color:#DDD;height:29px;padding:4px 3px 4px 36px;font-weight:bold;font-size:16px;">Programs
                              <span style="width:50%;float:right"> ';
            if ($checkpermissions || is_siteadmin())
                $str .= $creatprogram;
            $str .='</span> </div>';
            /* ---get the program records which are assigned to particular school--- */
            $programlists = $DB->get_records('local_program', array('schoolid' => $school->id));
            /* ---if programs are listed them show them all--- */
            if ($programlists) {
                $programname = array();
                foreach ($programlists as $programlist) {
                    /* ---print the programs and it actions--- */
                    $linkcss = $programlist->visible ? ' ' : 'class="dimmed" ';
                    $str .='<div style="border-width:0px 0px 0px 0px;border-style:solid;border-color:#DDD">
                              <div style="padding:8px 0px 8px 50px;border:0px solid #888000">' . $j . '.&nbsp;<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/programs/view.php?id=' . $programlist->id . '&scid=' . $school->id . '">' . $programlist->fullname . '</a>';
                    $str .='<span style="width:50%;float:right;border:0px solid #000">';
                    if ($checkpermissions || is_siteadmin())
                        $str .=$hierarchy->get_actions('programs', 'program', $programlist->id, $programlist->visible, 'NULL', $school->id);
                    $str .='</span> </div> </div> ';
                    $j++;
                }
            }

            $str .='</div>';
            /* ---end of the program list--- */
            /* ---start of the department management--- */
            /* ---course/category.php?id=15&categoryedit=on&sesskey=B1DqJnEYxM--- */
            $str .='<div style="border-width:0px 0px 0px 0px;border-style:solid;border-color:#DDD">
                     <div style="border-width:0px 0px 0px 0px;border-style:solid;border-color:#DDD;height:33px;padding:3px 3px 3px 36px;font-weight:bold;font-size:16px;">Course Library
                         <span style="width:50%;float:right"> ';
            if ($checkpermissions || is_siteadmin())
                $str .= $creatdepartment;
            $str .='</span>
                     </div>';
            /* ---get the list of all the department assigned to the school--- */
            $departmentlists = $DB->get_records('local_department', array('schoolid' => $school->id));
            /* ---if departments are listed show them all--- */
            if ($departmentlists) {
                $departmentname = array();
                foreach ($departmentlists as $departmentlist) {
                    /* ---print the department and it actions--- */
                    $linkcss = $departmentlist->visible ? ' ' : ' class="dimmed"  ';
                    $str .='<div style="border-width:0px 0px 0px 0px;border-style:solid;border-color:#DDD">
                         <div style="padding:8px 0px 8px 50px;">' . $k . '.&nbsp;<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/departments/viewdept.php?id=' . $departmentlist->id . '&scid=' . $school->id . '&sesskey=B1DqJnEYxM">' . $departmentlist->fullname . '</a>';
                    $str .='<span style="width:50%;float:right">';
                    if ($checkpermissions || is_siteadmin())
                        $str .=$hierarchy->get_actions('departments', 'departments', $departmentlist->id, $departmentlist->visible, 'NULL', $school->id);
                    $str .='</span></div> </div> ';
                    $k++;
                }
            }
            $str .='</div>';
            /* ---niranjan take care of this div--- */
            $str .='</div>';
            $str .='</div>';
            $i++;
            echo $str;
            }
        }
    }

    /* ---End of the Grid view function--- */


    /**
     * @method print_collegetabs
     * @todo To print tabs for schools
     * @param string $currenttab, current tab name
     * @param int $id ,used to change the tab name
     */
    public function print_collegetabs($currenttab, $id) {
        $systemcontext = context_system::instance();

        global $OUTPUT;
        $toprow = array();
        if ($id < 0 || empty($id)) {
            $create_cap = array('local/collegestructure:manage', 'local/collegestructure:create');
            if (has_any_capability($create_cap, $systemcontext))
                $toprow[] = new tabobject('create', new moodle_url('/local/collegestructure/school.php'), get_string('create', 'local_collegestructure'));
        }
        else {
            $update_cap = array('local/collegestructure:manage', 'local/collegestructure:update');
            if (has_any_capability($update_cap, $systemcontext))
                $toprow[] = new tabobject('edit', new moodle_url('/local/collegestructure/school.php'), get_string('editschool', 'local_collegestructure'));
        }
        $toprow[] = new tabobject('view', new moodle_url('/local/collegestructure/index.php'), get_string('view', 'local_collegestructure'));

        $assignregistrar_cap = array('local/collegestructure:manage', 'local/collegestructure:assignregistrar');
        if (has_any_capability($assignregistrar_cap, $systemcontext)) {
            $toprow[] = new tabobject('assignregistrar', new moodle_url('/local/collegestructure/assignusers.php'), get_string('assignregistrar', 'local_collegestructure'));
        }

        if (has_capability('local/collegestructure:manage', $systemcontext)) {
            $toprow[] = new tabobject('info', new moodle_url('/local/collegestructure/info.php'), get_string('info', 'local_collegestructure'));
        }
        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    function cobalt_get_theme_list() {
        global $CFG, $DB;
        $themelist = array();
        $themelist['colms'] = 'MonoGraphic';
        $themelist['slp'] = 'slp';
        
        //Bug Id #369 solved by vinod
        $themes = get_list_of_themes();
        foreach($themelist as $key=>$list){
            if(!array_key_exists($key,$themes)){
                unset($themelist[$key]);
            }
        }
        $themelist = array(NULL=>'---Select---')+$themelist;
        return $themelist;
    }

}

/**
 * @method list_from_courses
 * @todo To print list of cobaltcourses of a class
 * @param int $courseid, course id
 */
function list_from_courses($courseid) {
    global $DB, $CFG;
    $course = $DB->get_record('local_cobaltcourses', array('id' => $courseid));
    $clclasses = $DB->get_records('local_clclasses', array('cobaltcourseid' => $course->id));
    $class_info = empty($clclasses) ? ' <span style="float: right;color:#FA5D08;">(No ' . get_string('pluginname', 'local_clclasses') . ' Available)</span>' : '';
    echo '<p class="menu_head menu_course"><b>' . get_string('course', 'local_cobaltcourses') . ': </b>' . $course->fullname . $class_info . '</p>';
    echo '<div class="menu_body">';
    echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
    foreach ($clclasses as $class) {
        $visible = $class->visible ? '<span class="visible" style="float: right;"> Active &nbsp;</span>' : '<span style="float: right;color:#FA440D;"> Inactive &nbsp;</span>';
        $offered_in_semester = $DB->get_record('local_semester', array('id' => $class->semesterid));
        $type = $class->online == 1 ? 'Online' : 'Offline';
        echo '<p class="menu_head menu_class"><b>' . get_string('class', 'local_clclasses') . ': </b>' . $class->fullname . ' (Offers in ' . $offered_in_semester->fullname . ') - ' . $type . $visible . '</p>';
    }
    echo "</div>"; //.firstpane
    echo "</div>"; //.menu_body
}

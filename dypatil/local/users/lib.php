<?php

require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/message/lib.php');
require_once($CFG->dirroot . '/user/filters/text.php');
require_once($CFG->dirroot . '/user/filters/date.php');
require_once($CFG->dirroot . '/user/filters/select.php');
require_once($CFG->dirroot . '/user/filters/globalrole.php');
require_once($CFG->dirroot . '/local/users/school.php');
require_once($CFG->dirroot . '/local/users/globalrole.php');
require_once($CFG->dirroot . '/user/filters/user_filter_forms.php');

class users {

    private static $_users;
    private $dbHandle;

    private function __construct() {
        
    }

    public static function getInstance() {
        if (!self::$_users) {
            self::$_users = new users();
        }
        return self::$_users;
    }

    /**
     * @method systemroles_custom
     * @todo For system level roles select menu
     * @return array System level roles select menu
     * */
    function systemroles_custom() {
        global $DB, $USER;

        $sql = "SELECT r.* FROM {role} r, {role_context_levels} rc WHERE r.id = rc.roleid AND rc.contextlevel = " . CONTEXT_SYSTEM . "";
        if (!is_siteadmin()) {
            $sql .= " AND r.id <> 1";
        }
        $sql .= " ORDER BY r.sortorder ASC";
        $roles = $DB->get_records_sql($sql);
        $out = array();
        $out[null] = "Select role";
        foreach ($roles as $role) {
            $out[$role->id] = ucwords($role->shortname);
        }
        return $out;
    }

    /**
     * @method insert_newuser
     * @todo To create new user with system role
     * @param object $data Submitted form data
     */
    function insert_newuser($data) {
        global $DB, $USER, $CFG;
        $data->confirmed = 1;
        $data->deleted = 0;
        $data->mnethostid = $CFG->mnet_localhost_id;
        if (!$user = $DB->get_record('user', array('email' => $data->email))) {
            $data->id = $DB->insert_record('user', $data);

            $data->userid = $data->id;
        } else {
            $data->userid = $user->id;
        }
        if (!$localuser = $DB->get_record('local_users', array('userid' => $data->userid))) {
            $data->localid = $DB->insert_record('local_users', $data);
        }
        $ctx = new stdClass();
        $ctx->id = -1;
        $ctx->contextlevel = CONTEXT_USER;
        $ctx->instanceid = $data->userid;
        $ctx->depth = 2;
        if (!$context = $DB->get_record('context', array('contextlevel' => $ctx->contextlevel, 'instanceid' => $ctx->instanceid))) {
            $ctx->id = $DB->insert_record('context', $ctx);
            $ctx->path = '/1/' . $ctx->id;
            $DB->update_record('context', $ctx);
        } else {
            $ctx->id = $context->id;
        }
        $role = new stdClass();
        $role->id = -1;
        $role->roleid = $data->roleid;
        $role->contextid = $DB->get_field('context', 'id', array('contextlevel' => CONTEXT_SYSTEM));
        $role->userid = $data->userid;
        $role->timemodified = time();
        $role->modifierid = $USER->id;
        if (!$roleid = $DB->get_record('role_assignments', array('roleid' => $role->roleid, 'contextid' => $role->contextid, 'userid' => $role->userid))) {
            $role->id = $DB->insert_record('role_assignments', $role);
        } else {
            $role->id = $roleid->id;
        }
        $scl = new stdClass();
        $scl->id = -1;
        $scl->userid = $data->userid;
        $scl->schoolid = $data->schoolid;
        $scl->roleid = $data->roleid;
        $scl->timecreated = time();
        $scl->timemodified = time();
        $scl->usermodified = $USER->id;
        if (!$school = $DB->get_record('local_school_permissions', array('userid' => $scl->userid, 'schoolid' => $scl->schoolid, 'roleid' => $scl->roleid))) {
            $scl->id = $DB->insert_record('local_school_permissions', $scl);
        } else {
            $scl->id = $school->id;
        }
        return $data;
    }

    function get_all_users() {
        global $DB, $CFG;
        return $DB->get_records_sql("SELECT * FROM {user} WHERE id <> {$CFG->siteguest} AND deleted = 0");
    }

    /* To get rolename for logged in user */

    function get_rolename($userid) {
        global $DB;
        $return = $DB->get_records_sql("SELECT ra.*, r.shortname, r.name FROM {role_assignments} ra, {role} r WHERE r.id = ra.roleid AND ra.userid = {$userid}");
        foreach($return as $ret){
            return $ret;
        }
    }

    /**
     * @method names
     * @todo to get the names of hierarchy elements
     * @param object $data   
     * @return array, names info
     */
    function names($data) {
        global $DB, $CFG;
        $list = new stdClass();
        if (isset($data->schoolid)) {
            $list->school = $DB->get_field('local_school', 'fullname', array('id' => $data->schoolid));
        }
        if (isset($data->programid)) {
            $list->program = $DB->get_field('local_program', 'fullname', array('id' => $data->programid));
        }
        if (isset($data->curriculumid)) {
            $list->curriculum = $DB->get_field('local_curriculum', 'fullname', array('id' => $data->curriculumid));
        }
        if (isset($data->courseid)) {
            $course = $DB->get_record('local_cobaltcourses', array('id' => $data->courseid));
            $list->coursename = $course->fullname;
            $list->courseid = $course->shortname;
        }
        return $list;
    }

    /**
     * @method get_coursestatus
     * @param int $courseid Course ID
     * @param int $userid User ID
     * @param $sem Semester
     * @todo To check the status of course for a particular user
     */
    function get_coursestatus($courseid, $userid, $sem = false) {
        global $DB, $CFG;
        $status = 'Not Enrolled';
        $rejected = $DB->get_record_sql("SELECT cls.* FROM {local_user_clclasses} AS uc JOIN {local_clclasses} AS cls ON cls.id = uc.classid AND cls.cobaltcourseid = {$courseid} AND uc.userid = {$userid} AND uc.registrarapproval = 2");
        if (!empty($rejected)) {
            $status = 'Rejected';
        }
        $sql = "SELECT cls.* FROM {local_user_clclasses} AS uc JOIN {local_clclasses} AS cls ON cls.id = uc.classid AND cls.cobaltcourseid = {$courseid} AND uc.userid = {$userid} AND uc.registrarapproval = 1";
        $enrolled = $DB->get_record_sql($sql);
        if (!empty($enrolled)) {
            if ($sem)
                return $DB->get_field('local_semester', 'fullname', array('id' => $enrolled->semesterid));
            $status = 'Enrolled (Inprogress)';
            $completed = $DB->get_record_sql("SELECT * FROM {local_user_classgrades} WHERE userid = {$userid} AND classid = {$enrolled->id}");
            if (!empty($completed)) {
                $status = 'Completed (With grade ' . $completed->gradeletter . ')';
            }
        }
        return $status;
    }

    /* To delete user */

    function cobalt_delete_user($userid) {
        global $DB;
        // $DB->delete_records('local_users', array('userid' => $userid));
        // $DB->delete_records('context', array('instanceid' => $userid, 'contextlevel' => CONTEXT_USER));
        // $DB->delete_records('role_assignments', array('userid' => $userid));
		$DB->set_field('local_users', 'deleted', 1, array('userid' => $userid));
        $DB->set_field('user', 'deleted', 1, array('id' => $userid));
        return true;
    }

    /* Action icons */

    function get_different_actions($plugin, $page, $id, $visible) {
        global $DB, $USER, $OUTPUT;
        $role = $this->get_rolename($id);
        if ($id == $USER->id) {
            return html_writer::link(new moodle_url('/local/' . $plugin . '/' . $page . '.php', array('id' => $id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
        } else if (is_siteadmin($id)) {
            return '';
        } else {
            $buttons = array();
            $buttons[] = html_writer::link(new moodle_url('/local/' . $plugin . '/' . $page . '.php', array('id' => $id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
            if ($role && $role->shortname == 'student') {
                $buttons[] = html_writer::link(new moodle_url('/local/profilechange/changeprofile.php', array('id' => $id, 'flag' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
            } else {
                $buttons[] = html_writer::link(new moodle_url('/local/' . $plugin . '/' . $page . '.php', array('id' => $id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
            }
            if ($visible) {
                $buttons[] = html_writer::link(new moodle_url('/local/' . $plugin . '/' . $page . '.php', array('id' => $id, 'visible' => $visible, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
            } else {
                $buttons[] = html_writer::link(new moodle_url('/local/' . $plugin . '/' . $page . '.php', array('id' => $id, 'visible' => $visible, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
            }
            return implode('', $buttons);
        }
    }

    /**
     * @method createtabview
     * @todo provides the tab view
     * @param  currenttab(string)
     * */
    function createtabview($mode, $id = -1) {
        global $OUTPUT;
        $tabs = array();
        $systemcontext =context_system::instance();
        $string = ($id > 0) ? get_string('edituser', 'local_users') : get_string('createuser', 'local_users');
        if (has_capability('local/users:manage', $systemcontext))
            $tabs[] = new tabobject('addnew', new moodle_url('/local/users/user.php'), $string);
        $tabs[] = new tabobject('browse', new moodle_url('/local/users/index.php'), get_string('browseusers', 'local_users'));
        $tabs[] = new tabobject('upload', new moodle_url('/local/users/upload.php'), get_string('uploadusers', 'local_users'));
        $tabs[] = new tabobject('info', new moodle_url('/local/users/info.php'), get_string('info', 'local_users'));
        echo $OUTPUT->tabtree($tabs, $mode);
    }

    /**
     * @method get_schoolnames
     * @todo to get school name based on role(admin, registrar)
     * @param object $user user detail
     * @param type $user
     * @return string, school fullname else valid statement based on condition
     */
    function get_schoolnames($user) {
        global $DB;
        $role = $this->get_rolename($user->id);
        if (is_siteadmin($user->id)) {
            return 'All';
        }
        $table = 'local_school_permissions';
        if ($role && $role->shortname == 'student') {
            $table = 'local_userdata';
        }
        $schools = $DB->get_records_sql("SELECT * FROM {{$table}} WHERE userid = {$user->id}");
        $scl = array();
        if ($schools) {
            foreach ($schools as $school) {
                $scl[] = $DB->get_field('local_school', 'fullname', array('id' => $school->schoolid));
            }
            return implode(', ', $scl) . '.';
        }
        return get_string('not_assigned', 'local_users');
    }

    /**
     * @method email_to_user
     * @todo To send a mail to users
     * @param object $data User data
     * @param int $id To check new user or existing
     */
    function email_to_user($data, $id) {
        global $DB, $CFG;
        $school = $DB->get_field('local_school', 'fullname', array('id' => $data->schoolid));
        $role = $DB->get_field('role', 'name', array('id' => $data->roleid));
        $url = $CFG->wwwroot;
        $email = $data->email;
        $from = 'registrar@cobaltlms.com';
        $subject = 'Appointment Confirmation';
        $body = 'Congratulations! You are appointed as "' . $role . '", for the school: "' . $school . '".<br/>
                    Username: "' . $data->username . '".<br/>
                    Password: "' . $data->password . '".<br/>
                    Please login to your account with following URL: "' . $url . '"';
        if ($id > 0) {
            $subject = 'New Login Credentials';
            $body = 'Hi! <p>Your Login details for the site "' . $CFG->wwwroot . '" are changed. Please use new Credentials for login.</p>
                    Username: "' . $data->username . '".<br/>
                    Password: "' . $data->password . '".<br/>';
        }
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
        $headers .= 'From: ' . $from . ' ' . "\r\n";
        mail($email, $subject, $body, $headers);
    }

    /**
     * @method get_usercount
     * @todo To get total number of cobaltusers 
     * @param string $extraselect used to add extra condition to get userlist
     * @param array $extraparams it holds values
     * @return int user count
     */
    function get_usercount($extraselect = '', array $extraparams = null) {
        global $DB, $CFG;
        $select = " id <> :guestid AND deleted = 0";
        $params = array('guestid' => $CFG->siteguest);
        if ($extraselect) {
            $select .= " AND $extraselect";
            $params = $params + (array) $extraparams;
        }
        $users = $DB->get_records_sql("SELECT * FROM {user} WHERE $select", $params);
        $records = array();
        if (!empty($users)) {
            $useridin = implode(',', array_keys($users));
            $hierarchy = new hierarchy();
            $schoollist = $hierarchy->get_assignedschools();
            $schoollist = $hierarchy->get_school_parent($schoollist, $selected = array(), $inctop = false, $all = false);
            if (is_siteadmin()) {
                $schoollist = $hierarchy->get_school_items();
            }
            if (!empty($schoollist)) {
                $schoolidin = implode(',', array_keys($schoollist));
                $records = $DB->get_records_sql("(SELECT u.* FROM {user} u
                                         JOIN {local_users} lu ON lu.userid = u.id
                                         JOIN {local_school_permissions} sp ON sp.userid = u.id

                                         WHERE u.id in ($useridin) AND sp.schoolid IN ($schoolidin))
                                         UNION
                                         (SELECT u.* FROM {user} u
                                         JOIN {local_users} lu ON lu.userid = u.id
                                         JOIN {local_userdata} ud ON ud.userid = u.id
                                        WHERE u.id in ($useridin) AND ud.schoolid IN ($schoolidin))");
            }
        }

        return sizeof($records);
    }

    /**
     * @method get_users_listing
     * @todo to get user list of school based on condition  
     * @param string $sort fieldname
     * @param string $dir specify the order to sort
     * @param int $page page number
     * @param int $recordsperpage records perpage
     * @param string $extraselect extra condition to select user
     * @param array $extraparams
     * @return array of objects , list of users
     */
    function get_users_listing($sort = 'lastaccess', $dir = 'ASC', $page = 0, $recordsperpage = 0, $extraselect = '', array $extraparams = null, $extracontext = null) {
        global $DB, $CFG;
        $extraselect;

        $select = "deleted <> 1 AND id <> :guestid";  //$select = "deleted=0";
        $params = array('guestid' => $CFG->siteguest);

        if ($extraselect) {
            $select .= " AND $extraselect";
            $params = $params + (array) $extraparams;
        }

        // If a context is specified, get extra user fields that the current user
        // is supposed to see.
        $extrafields = '';
        if ($extracontext) {
            $extrafields = get_extra_user_fields_sql($extracontext, '', '', array('id', 'username', 'email', 'firstname', 'lastname', 'city', 'country',
                'lastaccess', 'confirmed', 'mnethostid'));
        }
        /*
         * ###Bugreport#183-Filters
         * @author Naveen Kumar<naveen@eabyas.in>
         * (Resolved) Added $select parameters for conditions 
         */
        // warning: will return UNCONFIRMED USERS
        //  print_object($params);
        $users = $DB->get_records_sql("SELECT *
                                       FROM {user}
                                       WHERE $select", $params);

        $hierarchy = new hierarchy();
        $schoollist = $hierarchy->get_assignedschools();
        $schoollist = $hierarchy->get_school_parent($schoollist, $selected = array(), $inctop = false, $all = false);
        if (is_siteadmin()) {
            $schoollist = $hierarchy->get_school_items();
        }
        $schoolidin = implode(',', array_keys($schoollist));
        if ($users && $schoollist) {
            $useridin = implode(',', array_keys($users));
            return $DB->get_records_sql("select user.* from (SELECT u.id, u.username, u.email, u.firstname, u.lastname, u.city, u.country,
                                            u.lastaccess, u.confirmed, u.mnethostid, u.suspended FROM {user} u
                                        JOIN {local_users} lu ON lu.userid = u.id
                                        JOIN {local_school_permissions} sp ON sp.userid = u.id
                                        WHERE u.id in ($useridin) AND sp.schoolid IN ($schoolidin)
                                        UNION
                                        SELECT u.id, u.username, u.email, u.firstname, u.lastname, u.city, u.country,
                                            u.lastaccess, u.confirmed, u.mnethostid, u.suspended FROM {user} u
                                        JOIN {local_users} lu ON lu.userid = u.id
                                        JOIN {local_userdata} ud ON ud.userid = u.id
                                        WHERE u.id in ($useridin) AND ud.schoolid IN ($schoolidin)) user GROUP BY user.id ORDER BY user.$sort $dir LIMIT $page, $recordsperpage ");
        }
    }

}

/**
 * User filtering wrapper class.
 */
class filtering {

    var $_fields;
    var $_addform;
    var $_activeform;

    /**
     * Contructor
     * @param array array of visible user fields
     * @param string base url used for submission/return, null if the same of current page
     * @param array extra page parameters
     */
    function filtering($fieldnames = null, $baseurl = null, $extraparams = null) {
        global $SESSION;

        if (!isset($SESSION->filtering)) {
            $SESSION->filtering = array();
        }

        if (empty($fieldnames)) {
            $fieldnames = array('realname' => 0, 'lastname' => 1, 'firstname' => 1, 'email' => 1, 'city' => 1, 'country' => 1,
                'suspended' => 1, 'systemrole' => 1, 'assignedschool' => 1, 'username' => 1);
        }

        $this->_fields = array();

        foreach ($fieldnames as $fieldname => $advanced) {
            if ($field = $this->get_field($fieldname, $advanced)) {
                $this->_fields[$fieldname] = $field;
            }
        }
        // fist the new filter form
        $this->_addform = new user_add_filter_form($baseurl, array('fields' => $this->_fields, 'extraparams' => $extraparams));
        if ($adddata = $this->_addform->get_data()) {
            foreach ($this->_fields as $fname => $field) {
                $data = $field->check_data($adddata);
                if ($data === false) {
                    continue; // nothing new
                }
                if (!array_key_exists($fname, $SESSION->filtering)) {
                    $SESSION->filtering[$fname] = array();
                }
                $SESSION->filtering[$fname][] = $data;
            }
            // clear the form
            $_POST = array();
            $this->_addform = new user_add_filter_form($baseurl, array('fields' => $this->_fields, 'extraparams' => $extraparams));
        }
        // now the active filters
        $this->_activeform = new active_filter_form($baseurl, array('fields' => $this->_fields, 'extraparams' => $extraparams));
        if ($adddata = $this->_activeform->get_data()) {
            if (!empty($adddata->removeall)) {
                $SESSION->filtering = array();
            } else if (!empty($adddata->removeselected) and ! empty($adddata->filter)) {
                foreach ($adddata->filter as $fname => $instances) {
                    foreach ($instances as $i => $val) {
                        if (empty($val)) {
                            continue;
                        }
                        unset($SESSION->filtering[$fname][$i]);
                    }
                    if (empty($SESSION->filtering[$fname])) {
                        unset($SESSION->filtering[$fname]);
                    }
                }
            }
            // clear+reload the form
            $_POST = array();
            $this->_activeform = new active_filter_form($baseurl, array('fields' => $this->_fields, 'extraparams' => $extraparams));
        }
    }

    /**
     * Creates known user filter if present
     * @param string $fieldname
     * @param boolean $advanced
     * @return object filter
     */
    function get_field($fieldname, $advanced) {
        global $USER, $CFG, $DB, $SITE;

        switch ($fieldname) {
            case 'username': return new user_filter_text('username', get_string('username'), $advanced, 'username');
            case 'realname': return new user_filter_text('realname', get_string('fullnameuser'), $advanced, $DB->sql_fullname());
            case 'lastname': return new user_filter_text('lastname', get_string('lastname'), $advanced, 'lastname');
            case 'firstname': return new user_filter_text('firstname', get_string('firstname'), $advanced, 'firstname');
            case 'email': return new user_filter_text('email', get_string('email'), $advanced, 'email');
            case 'suspended': return new user_filter_yesno('suspended', get_string('suspended', 'auth'), $advanced, 'suspended');
            case 'systemrole': return new filter_globalrole('systemrole', get_string('globalrole', 'role'), $advanced);
            case 'assignedschool': return new user_filter_school('assignedschool', get_string('assignedschool', 'local_collegestructure'), $advanced);
            case 'firstaccess': return new user_filter_date('firstaccess', get_string('firstaccess', 'filters'), $advanced, 'firstaccess');
            case 'lastaccess': return new user_filter_date('lastaccess', get_string('lastaccess'), $advanced, 'lastaccess');

            default: return null;
        }
    }

    /**
     * Returns sql where statement based on active user filters
     * @param string $extra sql
     * @param array named params (recommended prefix ex)
     * @return array sql string and $params
     */
    function get_sql_filter($extra = '', array $params = null) {
        global $SESSION;

        $sqls = array();
        if ($extra != '') {
            $sqls[] = $extra;
        }
        $params = (array) $params;

        if (!empty($SESSION->filtering)) {
            foreach ($SESSION->filtering as $fname => $datas) {
                if (!array_key_exists($fname, $this->_fields)) {
                    continue; // filter not used
                }
                $field = $this->_fields[$fname];
                foreach ($datas as $i => $data) {
                    list($s, $p) = $field->get_sql_filter($data);
                    $sqls[] = $s;
                    $params = $params + $p;
                }
            }
        }

        if (empty($sqls)) {
            return array('', array());
        } else {
            $sqls = implode(' AND ', $sqls);

            return array($sqls, $params);
        }
    }

    /**
     * Print the add filter form.
     */
    function display_add() {
        $this->_addform->display();
    }

    /**
     * Print the active filter form.
     */
    function display_active() {
        $this->_activeform->display();
    }

}

?>

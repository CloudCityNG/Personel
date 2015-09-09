<?php

/**
 * Base class to avoid duplicating code.
 */
abstract class core_role_assign_user_selector_base extends user_selector_base {

    protected $context;

    /**
     * @param string $name control name
     * @param array $options should have two elements with keys groupid and courseid.
     */
    public function __construct($name, $options) {
        global $CFG;
        $options['accesscontext'] = context_system::instance();
        parent::__construct($name, $options);
        $this->classid = $options['classid'];
        $this->context = $options['accesscontext'];
        require_once($CFG->dirroot . '/group/lib.php');
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] = 'local/clclasses/classlib.php';
        $options['classid'] = $this->classid;
        return $options;
    }

}

class class_members_selector extends core_role_assign_user_selector_base {

    public function find_users($search) {
        global $DB;

        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['classid'] = $this->classid;

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $params = array_merge($params, $sortparams);


        $sql = "SELECT " . $this->required_fields_sql('u') . "
                FROM {user} AS u
                JOIN {local_user_clclasses} AS uc ON uc.userid = u.id
                WHERE $wherecondition
                AND uc.classid = :classid AND registrarapproval = 1
                ORDER BY $sort";
        $existingusers = $DB->get_records_sql($sql, $params);

        // No users at all.
        if (empty($existingusers)) {
            return array();
        }

        // We have users. Out put them in groups by context depth.
        // To help the loop below, tack a dummy user on the end of the results
        // array, to trigger output of the last group.
        $dummyuser = new stdClass;
        $dummyuser->contextid = 0;
        $dummyuser->id = 0;
        $dummyuser->component = '';
        $existingusers[] = $dummyuser;
        $results = array(); // The results array we are building up.
        $doneusers = array(); // Ensures we only list each user at most once.
        $currentcontextid = $this->context->id;
        $currentgroup = array();
        foreach ($existingusers as $user) {
            if (isset($doneusers[$user->id])) {
                continue;
            }
            $doneusers[$user->id] = 1;
            $groupname = $this->this_con_group_name($search, count($currentgroup));
            $results[$groupname] = $currentgroup;
            $currentgroup[$user->id] = $user;
        }
        return $results;
    }

    protected function this_con_group_name($search, $numusers) {
        // Special case in the System context.
        if ($search) {
            return get_string('extusersmatching', 'local_clclasses', $search);
        } else {
            return get_string('extusers', 'local_clclasses');
        }
    }

}

/**
 * User selector subclass for the list of users who are not in a certain group.
 * Used on the add group members page.
 */
class class_non_members_selector extends core_role_assign_user_selector_base {

    public function find_users($search) {
        global $DB;

        // Get list of allowed roles.
        $context = context_system::instance();

        // Get the search condition.
        list($searchcondition, $searchparams) = $this->search_sql($search, 'u');

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $orderby = ' ORDER BY ' . $sort;


        $class = $DB->get_record('local_clclasses', array('id' => $this->classid), '*', MUST_EXIST);
        $fields = "SELECT 
                " . $this->required_fields_sql('u');
        $sql = " FROM {user} AS u
                JOIN {local_userdata} d ON d.userid = u.id
                WHERE d.schoolid = {$class->schoolid}
                AND u.id NOT IN (SELECT distinct(userid) FROM {local_user_clclasses} WHERE classid = :classid AND registrarapproval=1)
                AND $searchcondition";
        $params = $searchparams;
        $params['schoolid'] = $class->schoolid;
        $params['classid'] = $class->id;
        $users = $DB->get_records_sql("$fields $sql", $params);
        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql("SELECT COUNT(DISTINCT u.id) $sql", $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $orderby, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('potusersmatching', 'local_clclasses', $search);
        } else {
            $groupname = get_string('potusers', 'local_clclasses');
        }

        return array($groupname => $availableusers);
    }

}

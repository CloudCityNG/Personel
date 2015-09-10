<?php

class programs {

    private static $_program;
    private $dbHandle;

    private function __construct() {
        
    }

    public static function getInstance() {
        if (!self::$_program) {
            self::$_program = new programs();
        }
        return self::$_program;
    }

    /**
     * @method cobalt_insert_program
     * @todo Inserts a new record
     * @param  $data(array)
     * @return Id of the inserted data
     * */
    function cobalt_insert_program($data) {
        global $DB;
        $hierarchy = new hierarchy();
        $data->id = $DB->insert_record('local_program', $data);
        $data->mincrhour = number_format($data->mincrhour , 2);
        $hierarchy->entity_settings($data);
        return $data->id;
    }

    /**
     * @method cobalt_update_program
     * @todo Update the details of the existing Programs
     * @param  $data(array)
     * */
    function cobalt_update_program($data) {
        global $DB;
        $DB->update_record('local_program', $data);
        $hierarchy = new hierarchy();
        $DB->delete_records('local_level_settings', array('levelid' => $data->id));
        $hierarchy->entity_settings($data);
    }

    /**
     * @method cobalt_delete_program
     * @todo Delete the records from local_program
     * @param  $id(int)
     * */
    function cobalt_delete_program($id) {
        global $DB;
        $DB->delete_records('local_program', array('id' => $id));
    }

    /**
     * @method get_dependency_list
     * @todo Checks the program dependency modules
     * @param  program(int)
     * */
    function get_dependency_list($program, $school) {
        global $DB, $CFG;
        $today = date('Y-m-d');
        $sql = "SELECT * FROM {local_event_activities} WHERE eventtypeid = 1 AND programid = {$program} AND schoolid = {$school} AND {$today} BETWEEN from_unixtime(startdate, '%Y-%m-%d') AND from_unixtime(enddate, '%Y-%m-%d') AND publish = 1";
        $event = $DB->get_records_sql($sql);
        if (!empty($event))
            return 1;
        $curriculum = $DB->get_records('local_curriculum', array('programid' => $program));
        if (!empty($curriculum))
            return 2;
        $module = $DB->get_records('local_module', array('programid' => $program));
        if (!empty($module))
            return 3;
        $user = $DB->get_records('local_userdata', array('programid' => $program));
        if (!empty($user))
            return 4;
        $batch = $DB->get_records('local_batches', array('programid' => $program));
        if (!empty($batch))
            return 5;
        return 0;
    }

    /**
     * @method createtabview
     * @todo provides the tab view
     * @param  currenttab(string)
     * */
    function createtabview($currenttab, $id = -1) {
        global $OUTPUT;
        $systemcontext = context_system::instance();
        $tabs = array();
        //$string = ($id>0) ? get_string('editprogram', 'local_programs') : get_string('createprogram', 'local_programs') ;

        if ($id > 0) {
            $programscreate_cap = array('local/programs:manage', 'local/programs:update');
            if (has_any_capability($programscreate_cap, $systemcontext))
                $tabs[] = new tabobject('create', new moodle_url('/local/programs/program.php'), get_string('editprogram', 'local_programs'));
        }
        else {
            $programsupdate_cap = array('local/programs:manage', 'local/programs:create');
            if (has_any_capability($programsupdate_cap, $systemcontext))
                $tabs[] = new tabobject('create', new moodle_url('/local/programs/program.php'), get_string('createprogram', 'local_programs'));
        }

        $tabs[] = new tabobject('view', new moodle_url('/local/programs/index.php'), get_string('programlist', 'local_programs'));
        $tabs[] = new tabobject('upload', new moodle_url('/local/programs/upload.php'), get_string('uploadprograms', 'local_programs'));
        $tabs[] = new tabobject('info', new moodle_url('/local/programs/info.php'), get_string('help', 'local_programs'));
        $tabs[] = new tabobject('report', new moodle_url('/local/programs/report.php'), get_string('report', 'local_programs'));
        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

    /*
     * function to get shortnames of programs
     */

    function get_snames() {
        global $DB;
        $results = $DB->get_records('local_program');
        return $results;
    }

    /**
     * @method get_departments
     * @todo Getting departments for given schoolid
     * @param int $id school id
     * @return array of objects, department list
     * */
    function get_departments($id) {
        global $DB;
        $results = $DB->get_records('local_department', array('schoolid' => $id));
        return $results;
    }

    /**
     * @method name
     * @todo creating new object(which includes department name, school, type and level)
     * @param object $list department info
     * @return object
     * */
    function name($list) {
        global $DB, $CFG;
        $name = new stdClass();
        $name->department = ($list->departmentid) ? $DB->get_field('local_department', 'fullname', array('id' => $list->departmentid)) : 'Not Assigned';
        $name->school = $DB->get_field('local_school', 'fullname', array('id' => $list->schoolid));
        $name->type = ($list->type == 1) ? 'Online' : 'Offline';
        $name->level = ($list->programlevel == 1) ? 'Undergraduate' : 'Post Graduate';
        return $name;
    }

    /**
     * @method  program_capabilities
     * @todo used to provide default capabilities list
     * @param array $unsetlist(used to remove capability from default list)
     * @return array capabilities list
     * */
    function program_capabilities($unsetlist = null) {
        global $DB, $CFG;
        $capabilities_array = array('local/programs:manage', 'local/programs:delete', 'local/programs:update', 'local/programs:visible', 'local/programs:view', 'local/programs:create');
        if ($unsetlist) {
            foreach ($unsetlist as $key => $value)
                $updatedunsetlist[] = 'local/programs:' . $value;
            $capabilities_array = array_diff($capabilities_array, $updatedunsetlist);
        }

        return $capabilities_array;
    }// end of function

}// end of class

?>

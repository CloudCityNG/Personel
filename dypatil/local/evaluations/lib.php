<?php

//class evaluation {
//code
define('EVALUATION_ANONYMOUS_YES', 1);
define('EVALUATION_ANONYMOUS_NO', 2);
define('EVALUATION_MIN_ANONYMOUS_COUNT_IN_GROUP', 2);
define('EVALUATION_DECIMAL', '.');
define('EVALUATION_THOUSAND', ',');
define('EVALUATION_RESETFORM_RESET', 'evaluation_reset_data_');
define('EVALUATION_RESETFORM_DROP', 'evaluation_drop_evaluation_');
define('EVALUATION_MAX_PIX_LENGTH', '400'); //max. Breite des grafischen Balkens in der Auswertung
define('EVALUATION_DEFAULT_PAGE_COUNT', 20);

/**
 * check for multiple_submit = false.
 * if the evaluation is global so the classid must be given
 *
 * @global object
 * @global object
 * @param int $evaluationid
 * @param int $classid
 * @return boolean true if the evaluation already is submitted otherwise false
 */
function evaluation_is_already_submitted($evaluationid, $classid = false) {
    global $USER, $DB;

    $params = array('userid' => $USER->id, 'evaluation' => $evaluationid);
    if (!$trackings = $DB->get_records_menu('evaluation_tracking', $params, '', 'id, completed')) {
        return false;
    }

    if ($classid) {
        $select = 'completed IN (' . implode(',', $trackings) . ') AND class_id = ?';
        if (!$values = $DB->get_records_select('evaluation_value', $select, array($classid))) {
            return false;
        }
    }

    return true;
}

/**
 * if the completion of a evaluation will be continued eg.
 * by pagebreak or by multiple submit so the complete must be found.
 * if the param $tmp is set true so all things are related to temporary completeds
 *
 * @global object
 * @global object
 * @global object
 * @param int $evaluationid
 * @param boolean $tmp
 * @param int $classid
 * @param string $guestid
 * @return int the id of the found completed
 */
function evaluation_get_current_completed($evaluationid, $tmp = false, $classid = false, $guestid = false) {

    global $USER, $CFG, $DB;

    $tmpstr = $tmp ? 'tmp' : '';

    if (!$classid) {
        if ($guestid) {
            $params = array('evaluation' => $evaluationid, 'guestid' => $guestid);
            return $DB->get_record('evaluation_completed' . $tmpstr, $params);
        } else {
            $params = array('evaluation' => $evaluationid, 'userid' => $USER->id);
            return $DB->get_record('evaluation_completed' . $tmpstr, $params);
        }
    }

    $params = array();

    if ($guestid) {
        $userselect = "AND fc.guestid = :guestid";
        $params['guestid'] = $guestid;
    } else {
        $userselect = "AND fc.userid = :userid";
        $params['userid'] = $USER->id;
    }
    //if classid is set the evaluation is global.
    //there can be more than one completed on one evaluation
    $sql = "SELECT DISTINCT fc.*
               FROM {evaluation_value{$tmpstr}} fv, {evaluation_completed{$tmpstr}} fc
              WHERE fv.class_id = :classid
                    AND fv.completed = fc.id
                    $userselect
                    AND fc.evaluation = :evaluationid";
    $params['classid'] = intval($classid);
    $params['evaluationid'] = $evaluationid;

    if (!$sqlresult = $DB->get_records_sql($sql, $params)) {
        return false;
    }
    foreach ($sqlresult as $r) {
        return $DB->get_record('evaluation_completed' . $tmpstr, array('id' => $r->id));
    }
}

/**
 * get all positions of pagebreaks in the given evaluation
 *
 * @global object
 * @param int $evaluationid
 * @return array all ordered pagebreak positions
 */
function evaluation_get_all_break_positions($evaluationid) {
    global $DB;

    $params = array('typ' => 'pagebreak', 'evaluation' => $evaluationid);
    $allbreaks = $DB->get_records_menu('evaluation_item', $params, 'position', 'id, position');
    if (!$allbreaks) {
        return false;
    }
    return array_values($allbreaks);
}

/**
 * this returns the position where the user can continue the completing.
 *
 * @global object
 * @global object
 * @global object
 * @param int $evaluationid
 * @param int $classid
 * @param string $guestid this id will be saved temporary and is unique
 * @return int the position to continue
 */
function evaluation_get_page_to_continue($evaluationid, $classid = false, $guestid = false) {
    global $CFG, $USER, $DB;

    //is there any break?

    if (!$allbreaks = evaluation_get_all_break_positions($evaluationid)) {
        return false;
    }

    $params = array();
    if ($classid) {
        $classselect = "AND fv.class_id = :classid";
        $params['classid'] = $classid;
    } else {
        $classselect = '';
    }

    if ($guestid) {
        $userselect = "AND fc.guestid = :guestid";
        $usergroup = "GROUP BY fc.guestid";
        $params['guestid'] = $guestid;
    } else {
        $userselect = "AND fc.userid = :userid";
        $usergroup = "GROUP BY fc.userid";
        $params['userid'] = $USER->id;
    }

    $sql = "SELECT MAX(fi.position)
               FROM {evaluation_completedtmp} fc, {evaluation_valuetmp} fv, {evaluation_item} fi
              WHERE fc.id = fv.completed
                    $userselect
                    AND fc.evaluation = :evaluationid
                    $classselect
                    AND fi.id = fv.item
         $usergroup";
    $params['evaluationid'] = $evaluationid;

    $lastpos = $DB->get_field_sql($sql, $params);

    //the index of found pagebreak is the searched pagenumber
    foreach ($allbreaks as $pagenr => $br) {
        if ($lastpos < $br) {
            return $pagenr;
        }
    }
    return count($allbreaks);
}

/**
 * get the count of completeds depending on the given groupid.
 *
 * @global object
 * @global object
 * @param object $evaluation
 * @param int $groupid
 * @param int $classid
 * @return mixed count of completeds or false
 */
function evaluation_get_completeds_group_count($evaluation, $groupid = false, $classid = false) {
    global $CFG, $DB;

    if ($classid > 0 AND ! $groupid <= 0) {
        $sql = "SELECT id, COUNT(item) AS ci
                  FROM {evaluation_value}
                 WHERE class_id  = ?
              GROUP BY item ORDER BY ci DESC";
        if ($foundrecs = $DB->get_records_sql($sql, array($classid))) {
            $foundrecs = array_values($foundrecs);
            return $foundrecs[0]->ci;
        }
        return false;
    }
    if ($values = evaluation_get_completeds_group($evaluation, $groupid)) {
        return count($values);
    } else {
        return false;
    }
}

/**
 * get the completeds depending on the given groupid.
 *
 * @global object
 * @global object
 * @param object $evaluation
 * @param int $groupid
 * @param int $classeid
 * @return mixed array of found completeds otherwise false
 */
function evaluation_get_completeds_group($evaluation, $groupid = false, $classeid = false) {
    global $CFG, $DB;

    if (intval($groupid) > 0) {
        $query = "SELECT fbc.*
                    FROM {evaluation_completed} fbc, {groups_members} gm
                   WHERE fbc.evaluation = ?
                         AND gm.groupid = ?
                         AND fbc.userid = gm.userid";
        if ($values = $DB->get_records_sql($query, array($evaluation->id, $groupid))) {
            return $values;
        } else {
            return false;
        }
    } else {
        if ($classeid) {
            $query = "SELECT DISTINCT fbc.*
                        FROM {evaluation_completed} fbc, {evaluation_value} fbv
                        WHERE fbc.id = fbv.completed
                            AND fbc.evaluation = ?
                            AND fbv.classe_id = ?
                        ORDER BY random_response";
            if ($values = $DB->get_records_sql($query, array($evaluation->id, $classeid))) {
                return $values;
            } else {
                return false;
            }
        } else {
            if ($values = $DB->get_records('evaluation_completed', array('evaluation' => $evaluation->id))) {
                return $values;
            } else {
                return false;
            }
        }
    }
}

function evaluation_init_evaluation_session() {
    //initialize the evaluation-Session - not nice at all!!
    global $SESSION;
    if (!empty($SESSION)) {
        if (!isset($SESSION->evaluation) OR ! is_object($SESSION->evaluation)) {
            $SESSION->evaluation = new stdClass();
        }
    }
}

/**
 * load the available item plugins from given subdirectory of $CFG->dirroot
 * the default is "mod/evaluation/item"
 *
 * @global object
 * @param string $dir the subdir
 * @return array pluginnames as string
 */
function evaluation_load_evaluation_items($dir = 'local/evaluations/item') {
    global $CFG;
    $names = get_list_of_plugins($dir);
    $ret_names = array();

    foreach ($names as $name) {
        require_once($CFG->dirroot . '/' . $dir . '/' . $name . '/lib.php');
        if (class_exists('evaluation_item_' . $name)) {
            $ret_names[] = $name;
        }
    }
    return $ret_names;
}

/**
 * load the available item plugins to use as dropdown-options
 *
 * @global object
 * @return array pluginnames as string
 */
function evaluation_load_evaluation_items_options() {
    global $CFG;

    $evaluation_options = array("pagebreak" => get_string('add_pagebreak', 'local_evaluations'));

    if (!$evaluation_names = evaluation_load_evaluation_items('local/evaluations/item')) {
        return array();
    }

    foreach ($evaluation_names as $fn) {
        $evaluation_options[$fn] = get_string($fn, 'local_evaluations');
    }
    asort($evaluation_options);
    $evaluation_options = array_merge(array(' ' => get_string('select')), $evaluation_options);
    return $evaluation_options;
}

/**
 * get the list of available templates.
 * if the $onlyown param is set true so only templates from own class will be served
 * this is important for droping templates
 *
 * @global object
 * @param object $class
 * @param string $onlyownorpublic
 * @return array the template recordsets
 */
function evaluation_get_template_list($class, $onlyownorpublic = '') {
    global $DB, $CFG;

    switch ($onlyownorpublic) {
        case '':
            $templates = $DB->get_records_select('evaluation_template', 'class = ? OR ispublic = 1', array($class), 'name');
            break;
        case 'own':
            $templates = $DB->get_records('evaluation_template', array('class' => $class), 'name');
            break;
        case 'public':
            $templates = $DB->get_records('evaluation_template', array('ispublic' => 1), 'name');
            break;
    }
    return $templates;
}

/**
 * load the lib.php from item-plugin-dir and returns the instance of the itemclass
 *
 * @global object
 * @param object $item
 * @return object the instanz of itemclass
 */
function evaluation_get_item_class($typ) {
    global $CFG;

    //get the class of item-typ
    $itemclass = 'evaluation_item_' . $typ;
    //get the instance of item-class
    if (!class_exists($itemclass)) {
        require_once($CFG->dirroot . '/local/evaluations/item/' . $typ . '/lib.php');
    }
    return new $itemclass();
}

/**
 * prints the given item as a preview.
 * each item-class has an own print_item_preview function implemented.
 *
 * @global object
 * @param object $item the item what we want to print out
 * @return void
 */
function evaluation_print_item_preview($item) {
    global $CFG;
    if ($item->typ == 'pagebreak') {
        return;
    }
    //get the instance of the item-class
    $itemobj = evaluation_get_item_class($item->typ);
    $itemobj->print_item_preview($item);
}

/**
 * load the available items for the depend item dropdown list shown in the edit_item form
 *
 * @global object
 * @param object $evaluation
 * @param object $item the item of the edit_item form
 * @return array all items except the item $item, labels and pagebreaks
 */
function evaluation_get_depend_candidates_for_item($evaluation, $item) {
    global $DB;
    //all items for dependitem
    $where = "evaluation = ? AND typ != 'pagebreak' AND hasvalue = 1";
    $params = array($evaluation->id);
    if (isset($item->id) AND $item->id) {
        $where .= ' AND id != ?';
        $params[] = $item->id;
    }
    $dependitems = array(0 => get_string('choose'));
    $evaluationitems = $DB->get_records_select_menu('evaluation_item', $where, $params, 'position', 'id, name');

    if (!$evaluationitems) {
        return $dependitems;
    }
    //adding the choose-option
    foreach ($evaluationitems as $key => $val) {
        $dependitems[$key] = $val;
    }
    return $dependitems;
}

/**
 * save the changes of a given item.
 *
 * @global object
 * @param object $item
 * @return boolean
 */
function evaluation_update_item($item) {
    global $DB;
    return $DB->update_record("evaluation_item", $item);
}

/**
 * here the position of the given item will be set to the value in $pos
 *
 * @global object
 * @param object $moveitem
 * @param int $pos
 * @return boolean
 */
function evaluation_move_item($moveitem, $pos) {
    global $DB;

    $params = array('evaluation' => $moveitem->evaluation);
    if (!$allitems = $DB->get_records('evaluation_item', $params, 'position')) {
        return false;
    }
    if (is_array($allitems)) {
        $index = 1;
        foreach ($allitems as $item) {
            if ($index == $pos) {
                $index++;
            }
            if ($item->id == $moveitem->id) {
                $moveitem->position = $pos;
                evaluation_update_item($moveitem);
                continue;
            }
            $item->position = $index;
            evaluation_update_item($item);
            $index++;
        }
        return true;
    }
    return false;
}

/**
 * get the position of the last pagebreak
 *
 * @param int $evaluationid
 * @return int the position of the last pagebreak
 */
function evaluation_get_last_break_position($evaluationid) {
    if (!$allbreaks = evaluation_get_all_break_positions($evaluationid)) {
        return false;
    }
    return $allbreaks[count($allbreaks) - 1];
}

/**
 * this creates a pagebreak.
 * a pagebreak is a special kind of item
 *
 * @global object
 * @param int $evaluationid
 * @return mixed false if there already is a pagebreak on last position or the id of the pagebreak-item
 */
function evaluation_create_pagebreak($evaluationid) {
    global $DB;

    //check if there already is a pagebreak on the last position
    $lastposition = $DB->count_records('evaluation_item', array('evaluation' => $evaluationid));
    if ($lastposition == evaluation_get_last_break_position($evaluationid)) {
        return false;
    }

    $item = new stdClass();
    $item->evaluation = $evaluationid;

    $item->template = 0;

    $item->name = '';

    $item->presentation = '';
    $item->hasvalue = 0;

    $item->typ = 'pagebreak';
    $item->position = $lastposition + 1;

    $item->required = 0;

    return $DB->insert_record('evaluation_item', $item);
}

////////////////////////////////////////////////
//functions to handle the templates
////////////////////////////////////////////////
////////////////////////////////////////////////

/**
 * creates a new template-record.
 *
 * @global object
 * @param int $classid
 * @param string $name the name of template shown in the templatelist
 * @param int $ispublic 0:privat 1:public
 * @return int the new templateid
 */
function evaluation_create_template($classid, $name, $ispublic = 0) {
    global $DB;

    $templ = new stdClass();
    $templ->class = ($ispublic ? 0 : $classid);
    $templ->name = $name;
    $templ->ispublic = $ispublic;

    $templid = $DB->insert_record('evaluation_template', $templ);
    return $DB->get_record('evaluation_template', array('id' => $templid));
}

/**
 * creates new template items.
 * all items will be copied and the attribute evaluation will be set to 0
 * and the attribute template will be set to the new templateid
 *
 * @global object
 * @uses CONTEXT_MODULE
 * @uses CONTEXT_CLASS
 * @param object $evaluation
 * @param string $name the name of template shown in the templatelist
 * @param int $ispublic 0:privat 1:public
 * @return boolean
 */
function evaluation_save_as_template($evaluation, $name, $ispublic = 0) {
    global $DB;
    $fs = get_file_storage();

    if (!$evaluationitems = $DB->get_records('evaluation_item', array('evaluation' => $evaluation->id))) {
        return false;
    }

    if (!$newtempl = evaluation_create_template($evaluation->classid, $name, $ispublic)) {
        return false;
    }

    //files in the template_item are in the context of the current class or
    //if the template is public the files are in the system context
    //files in the evaluation_item are in the evaluation_context of the evaluation
    //if ($ispublic) {
    //    $s_context = get_system_context();
    //} else {
    //    $s_context = context_class::instance($newtempl->class);
    //}
//    $cm = get_classmodule_from_instance('evaluation', $evaluation->id);
    //   $f_context = context_module::instance($cm->id);
    //create items of this new template
    //depend items we are storing temporary in an mapping list array(new id => dependitem)
    //we also store a mapping of all items array(oldid => newid)
    $dependitemsmap = array();
    $itembackup = array();
    foreach ($evaluationitems as $item) {

        $t_item = clone($item);

        unset($t_item->id);
        $t_item->evaluation = 0;
        $t_item->template = $newtempl->id;
        $t_item->id = $DB->insert_record('evaluation_item', $t_item);
        //copy all included files to the evaluation_template filearea
        $itemfiles = $fs->get_area_files($f_context->id, 'mod_evaluation', 'item', $item->id, "id", false);
        if ($itemfiles) {
            foreach ($itemfiles as $ifile) {
                $file_record = new stdClass();
                $file_record->contextid = $s_context->id;
                $file_record->component = 'mod_evaluation';
                $file_record->filearea = 'template';
                $file_record->itemid = $t_item->id;
                $fs->create_file_from_storedfile($file_record, $ifile);
            }
        }

        $itembackup[$item->id] = $t_item->id;
        if ($t_item->dependitem) {
            $dependitemsmap[$t_item->id] = $t_item->dependitem;
        }
    }

    //remapping the dependency
    foreach ($dependitemsmap as $key => $dependitem) {
        $newitem = $DB->get_record('evaluation_item', array('id' => $key));
        $newitem->dependitem = $itembackup[$newitem->dependitem];
        $DB->update_record('evaluation_item', $newitem);
    }

    return true;
}

/**
 * creates new evaluation_item-records from template.
 * if $deleteold is set true so the existing items of the given evaluation will be deleted
 * if $deleteold is set false so the new items will be appanded to the old items
 *
 * @global object
 * @uses CONTEXT_CLASS
 * @uses CONTEXT_MODULE
 * @param object $evaluation
 * @param int $templateid
 * @param boolean $deleteold
 */
function evaluation_items_from_template($evaluation, $templateid, $deleteold = false) {
    global $DB, $CFG;

    require_once($CFG->libdir . '/completionlib.php');

    $fs = get_file_storage();

    if (!$template = $DB->get_record('evaluation_template', array('id' => $templateid))) {
        return false;
    }
    //get all templateitems
    if (!$templitems = $DB->get_records('evaluation_item', array('template' => $templateid))) {
        return false;
    }

    //files in the template_item are in the context of the current class
    //files in the evaluation_item are in the evaluation_context of the evaluation
    //  if ($template->ispublic) {
    $s_context = get_system_context();
    //  } else {
    //       $s_context = context_class::instance($evaluation->class);
    // }
    // $class = $DB->get_record('class', array('id'=>$evaluation->class));
    //  $cm = get_classmodule_from_instance('evaluation', $evaluation->id);
    //  $f_context = context_module::instance($cm->id);
    //if deleteold then delete all old items before
    //get all items
    if ($deleteold) {
        if ($evaluationitems = $DB->get_records('evaluation_item', array('evaluation' => $evaluation->id))) {
            //delete all items of this evaluation
            foreach ($evaluationitems as $item) {
                evaluation_delete_item($item->id, false);
            }
            //delete tracking-data
            $DB->delete_records('evaluation_tracking', array('evaluation' => $evaluation->id));

            $params = array('evaluation' => $evaluation->id);
            if ($completeds = $DB->get_records('evaluation_completed', $params)) {
                $completion = new completion_info($class);
                foreach ($completeds as $completed) {
                    // Update completion state
                    if ($completion->is_enabled($cm) && $evaluation->completionsubmit) {
                        $completion->update_state($cm, COMPLETION_INCOMPLETE, $completed->userid);
                    }
                    $DB->delete_records('evaluation_completed', array('id' => $completed->id));
                }
            }
            $DB->delete_records('evaluation_completedtmp', array('evaluation' => $evaluation->id));
        }
        $positionoffset = 0;
    } else {
        //if the old items are kept the new items will be appended
        //therefor the new position has an offset
        $positionoffset = $DB->count_records('evaluation_item', array('evaluation' => $evaluation->id));
    }

    //create items of this new template
    //depend items we are storing temporary in an mapping list array(new id => dependitem)
    //we also store a mapping of all items array(oldid => newid)
    $dependitemsmap = array();
    $itembackup = array();
    foreach ($templitems as $t_item) {
        $item = clone($t_item);
        unset($item->id);
        $item->evaluation = $evaluation->id;
        $item->class = $evaluation->classid;
        $item->template = 0;
        $item->position = $item->position + $positionoffset;

        $item->id = $DB->insert_record('evaluation_item', $item);

        //moving the files to the new item
        $templatefiles = $fs->get_area_files($s_context->id, 'mod_evaluation', 'template', $t_item->id, "id", false);
        if ($templatefiles) {
            foreach ($templatefiles as $tfile) {
                $file_record = new stdClass();
                $file_record->contextid = $f_context->id;
                $file_record->component = 'mod_evaluation';
                $file_record->filearea = 'item';
                $file_record->itemid = $item->id;
                $fs->create_file_from_storedfile($file_record, $tfile);
            }
        }

        $itembackup[$t_item->id] = $item->id;
        if ($item->dependitem) {
            $dependitemsmap[$item->id] = $item->dependitem;
        }
    }

    //remapping the dependency
    foreach ($dependitemsmap as $key => $dependitem) {
        $newitem = $DB->get_record('evaluation_item', array('id' => $key));
        $newitem->dependitem = $itembackup[$newitem->dependitem];
        $DB->update_record('evaluation_item', $newitem);
    }
}

/**
 * prints the given item in the completion form.
 * each item-class has an own print_item_complete function implemented.
 *
 * @param object $item the item what we want to print out
 * @param mixed $value the value
 * @param boolean $highlightrequire if this set true and the value are false on completing so the item will be highlighted
 * @return void
 */
function evaluation_print_item_complete($item, $value = false, $highlightrequire = false) {
    global $CFG;
    if ($item->typ == 'pagebreak') {
        return;
    }

    //get the instance of the item-class
    $itemobj = evaluation_get_item_class($item->typ);
    $itemobj->print_item_complete($item, $value, $highlightrequire);
}

/**
 * compares the value of the itemid related to the completedid with the dependvalue.
 * this is used if a depend item is set.
 * the value can come as temporary or as permanently value. the deciding is done by $tmp.
 *
 * @global object
 * @global object
 * @param int $completeid
 * @param int $itemid
 * @param mixed $dependvalue
 * @param boolean $tmp
 * @return bool
 */
function evaluation_compare_item_value($completedid, $itemid, $dependvalue, $tmp = false) {
    global $DB, $CFG;

    $dbvalue = evaluation_get_item_value($completedid, $itemid, $tmp);

    //get the class of the given item-typ
    $item = $DB->get_record('evaluation_item', array('id' => $itemid));

    //get the instance of the item-class
    $itemobj = evaluation_get_item_class($item->typ);
    return $itemobj->compare_value($item, $dbvalue, $dependvalue); //true or false
}

/**
 * get the value from the given item related to the given completed.
 * the value can come as temporary or as permanently value. the deciding is done by $tmp
 *
 * @global object
 * @param int $completeid
 * @param int $itemid
 * @param boolean $tmp
 * @return mixed the value, the type depends on plugin-definition
 */
function evaluation_get_item_value($completedid, $itemid, $tmp = false) {
    global $DB;

    $tmpstr = $tmp ? 'tmp' : '';
    $params = array('completed' => $completedid, 'item' => $itemid);
    return $DB->get_field('evaluation_value' . $tmpstr, 'value', $params);
}

/**
 * this function checks the correctness of values.
 * the rules for this are implemented in the class of each item.
 * it can be the required attribute or the value self e.g. numeric.
 * the params first/lastitem are given to determine the visible range between pagebreaks.
 *
 * @global object
 * @param int $firstitem the position of firstitem for checking
 * @param int $lastitem the position of lastitem for checking
 * @return boolean
 */
function evaluation_check_values($firstitem, $lastitem) {
    global $DB, $CFG;

    $evaluationid = optional_param('evaluationid', 0, PARAM_INT);

    //get all items between the first- and lastitem
    $select = "evaluation = ?
                    AND position >= ?
                    AND position <= ?
                    AND hasvalue = 1";
    $params = array($evaluationid, $firstitem, $lastitem);
    if (!$evaluationitems = $DB->get_records_select('evaluation_item', $select, $params)) {
        //if no values are given so no values can be wrong ;-)
        return true;
    }

    foreach ($evaluationitems as $item) {
        //get the instance of the item-class
        $itemobj = evaluation_get_item_class($item->typ);
        //the name of the input field of the completeform is given in a special form:
        //<item-typ>_<item-id> eg. numeric_234
        //this is the key to get the value for the correct item
        $formvalname = $item->typ . '_' . $item->id;

        if ($itemobj->value_is_array()) {
            //get the raw value here. It is cleaned after that by the object itself
            $value = optional_param_array($formvalname, null, PARAM_RAW);
        } else {
            //get the raw value here. It is cleaned after that by the object itself
            $value = optional_param($formvalname, null, PARAM_RAW);
        }
        $value = $itemobj->clean_input_value($value);

        //check if the value is set
        if (is_null($value) AND $item->required == 1) {
            return false;
        }
        $itemobj->check_value($value, $item);

        //now we let check the value by the item-class
        if (!$itemobj->check_value($value, $item)) {
            return false;
        }
    }
    //if no wrong values so we can return true

    return true;
}

////////////////////////////////////////////////
////////////////////////////////////////////////
////////////////////////////////////////////////
//functions to handle the values
////////////////////////////////////////////////

/**
 * cleans the userinput while submitting the form.
 *
 * @param mixed $value
 * @return mixed
 */
function evaluation_clean_input_value($item, $value) {
    $itemobj = evaluation_get_item_class($item->typ);
    return $itemobj->clean_input_value($value);
}

/**
 * deletes all evaluation_items related to the given template id
 *
 * @global object
 * @uses CONTEXT_CLASS
 * @param object $template the template
 * @return void
 */
function evaluation_delete_template($template) {
    global $DB;

    //deleting the files from the item is done by evaluation_delete_item
    if ($t_items = $DB->get_records("evaluation_item", array("template" => $template->id))) {
        foreach ($t_items as $t_item) {
            evaluation_delete_item($t_item->id, false, $template);
        }
    }
    $DB->delete_records("evaluation_template", array("id" => $template->id));
}

/**
 * deletes an item and also deletes all related values
 *
 * @global object
 * @uses CONTEXT_MODULE
 * @param int $itemid
 * @param boolean $renumber should the kept items renumbered Yes/No
 * @param object $template if the template is given so the items are bound to it
 * @return void
 */
function evaluation_delete_item($itemid, $renumber = true, $template = false) {
    global $DB;

    $item = $DB->get_record('evaluation_item', array('id' => $itemid));

    //deleting the files from the item
    $fs = get_file_storage();

    if ($template) {
        if ($template->ispublic) {
            $context = get_system_context();
        } else {
            //       $context = context_class::instance($template->class);
        }
        //    $templatefiles = $fs->get_area_files($context->id,
        //                               'evaluation',
        //                             'template',
        //                           $item->id,
        //                         "id",
        //                       false);

        if ($templatefiles) {
            $fs->delete_area_files($context->id, 'evaluation', 'template', $item->id);
        }
    } else {
        // if (!$cm = get_classmodule_from_instance('evaluation', $item->evaluation)) {
        //      return false;
    }
//        $context = context_module::instance($cm->id);
    //      $itemfiles = $fs->get_area_files($context->id,
    //                                'mod_evaluation',
    //                              'item',
    //                            $item->id,
    //                          "id", false);

    if ($itemfiles) {
        $fs->delete_area_files($context->id, 'evaluation', 'item', $item->id);
    }
    //}

    $DB->delete_records("evaluation_value", array("item" => $itemid));
    $DB->delete_records("evaluation_valuetmp", array("item" => $itemid));

    //remove all depends
    $DB->set_field('evaluation_item', 'dependvalue', '', array('dependitem' => $itemid));
    $DB->set_field('evaluation_item', 'dependitem', 0, array('dependitem' => $itemid));

    $DB->delete_records("evaluation_item", array("id" => $itemid));
    if ($renumber) {
        evaluation_renumber_items($item->evaluation);
    }
}

/**
 * this saves the values of an completed.
 * if the param $tmp is set true so the values are saved temporary in table evaluation_valuetmp.
 * if there is already a completed and the userid is set so the values are updated.
 * on all other things new value records will be created.
 *
 * @global object
 * @param int $userid
 * @param boolean $tmp
 * @return mixed false on error or the completeid
 */
function evaluation_save_values($usrid, $tmp = false) {
    global $DB;

    $completedid = optional_param('completedid', 0, PARAM_INT);

    $tmpstr = $tmp ? 'tmp' : '';
    $time = time();
    $timemodified = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));

    if ($usrid == 0) {
        return evaluation_create_values($usrid, $timemodified, $tmp);
    }
    $completed = $DB->get_record('evaluation_completed' . $tmpstr, array('id' => $completedid));
    if (!$completed) {
        return evaluation_create_values($usrid, $timemodified, $tmp);
    } else {
        $completed->timemodified = $timemodified;
        return evaluation_update_values($completed, $tmp);
    }
}

/**
 * this saves the values from anonymous user such as guest on the main-site
 *
 * @global object
 * @param string $guestid the unique guestidentifier
 * @return mixed false on error or the completeid
 */
function evaluation_save_guest_values($guestid) {
    global $DB;

    $completedid = optional_param('completedid', false, PARAM_INT);

    $timemodified = time();
    if (!$completed = $DB->get_record('evaluation_completedtmp', array('id' => $completedid))) {
        return evaluation_create_values(0, $timemodified, true, $guestid);
    } else {
        $completed->timemodified = $timemodified;
        return evaluation_update_values($completed, true);
    }
}

/**
 * this function create a complete-record and the related value-records.
 * depending on the $tmp (true/false) the values are saved temporary or permanently
 *
 * @global object
 * @param int $userid
 * @param int $timemodified
 * @param boolean $tmp
 * @param string $guestid a unique identifier to save temporary data
 * @return mixed false on error or the completedid
 */
function evaluation_create_values($usrid, $timemodified, $tmp = false, $guestid = false) {
    global $DB;

    $evaluationid = optional_param('evaluationid', false, PARAM_INT);
    $anonymous_response = optional_param('anonymous_response', false, PARAM_INT);
    $classid = optional_param('clid', false, PARAM_INT);

    $tmpstr = $tmp ? 'tmp' : '';
    //first we create a new completed record
    $completed = new stdClass();
    $completed->evaluation = $evaluationid;
    $completed->userid = $usrid;
    $completed->guestid = $guestid;
    $completed->timemodified = $timemodified;
    $completed->anonymous_response = $anonymous_response;

    $completedid = $DB->insert_record('evaluation_completed' . $tmpstr, $completed);

    $completed = $DB->get_record('evaluation_completed' . $tmpstr, array('id' => $completedid));

    //the keys are in the form like abc_xxx
    //with explode we make an array with(abc, xxx) and (abc=typ und xxx=itemnr)
    //get the items of the evaluation
    if (!$allitems = $DB->get_records('evaluation_item', array('evaluation' => $completed->evaluation))) {
        return false;
    }
    foreach ($allitems as $item) {
        if (!$item->hasvalue) {
            continue;
        }
        //get the class of item-typ
        $itemobj = evaluation_get_item_class($item->typ);

        $keyname = $item->typ . '_' . $item->id;

        if ($itemobj->value_is_array()) {
            $itemvalue = optional_param_array($keyname, null, $itemobj->value_type());
        } else {
            $itemvalue = optional_param($keyname, null, $itemobj->value_type());
        }

        if (is_null($itemvalue)) {
            continue;
        }

        $value = new stdClass();
        $value->item = $item->id;
        $value->completed = $completed->id;
        $value->class_id = $classid;

        //the kind of values can be absolutely different
        //so we run create_value directly by the item-class
        $value->value = $itemobj->create_value($itemvalue);
        $DB->insert_record('evaluation_value' . $tmpstr, $value);
    }
    return $completed->id;
}

/**
 * this saves the temporary saved values permanently
 *
 * @global object
 * @param object $evaluationcompletedtmp the temporary completed
 * @param object $evaluationcompleted the target completed
 * @param int $userid
 * @return int the id of the completed
 */
function evaluation_save_tmp_values($evaluationcompletedtmp, $evaluationcompleted, $userid) {
    global $DB;

    $tmpcplid = $evaluationcompletedtmp->id;
    if ($evaluationcompleted) {
        //first drop all existing values
        $DB->delete_records('evaluation_value', array('completed' => $evaluationcompleted->id));
        //update the current completed
        $evaluationcompleted->timemodified = time();
        $DB->update_record('evaluation_completed', $evaluationcompleted);
    } else {
        $evaluationcompleted = clone($evaluationcompletedtmp);
        $evaluationcompleted->id = '';
        $evaluationcompleted->userid = $userid;
        $evaluationcompleted->timemodified = time();
        $evaluationcompleted->id = $DB->insert_record('evaluation_completed', $evaluationcompleted);
    }

    //save all the new values from evaluation_valuetmp
    //get all values of tmp-completed
    $params = array('completed' => $evaluationcompletedtmp->id);
    if (!$values = $DB->get_records('evaluation_valuetmp', $params)) {
        return false;
    }
    foreach ($values as $value) {
        //check if there are depend items
        $item = $DB->get_record('evaluation_item', array('id' => $value->item));
        if ($item->dependitem > 0) {
            $check = evaluation_compare_item_value($tmpcplid, $item->dependitem, $item->dependvalue, true);
        } else {
            $check = true;
        }
        if ($check) {
            unset($value->id);
            $value->completed = $evaluationcompleted->id;
            $DB->insert_record('evaluation_value', $value);
        }
    }
    //drop all the tmpvalues
    $DB->delete_records('evaluation_valuetmp', array('completed' => $tmpcplid));
    $DB->delete_records('evaluation_completedtmp', array('id' => $tmpcplid));
    return $evaluationcompleted->id;
}

/**
 * sends an email to the teachers of the class where the given evaluation is placed.
 *
 * @global object
 * @uses FORMAT_PLAIN
 * @param object $cm the classmodule-record
 * @param object $evaluation
 * @param object $class
 * @return void
 */
function evaluation_send_email_anonym($id, $evaluation, $classid) {
    global $CFG;

    if ($evaluation->email_notification == 0) { // No need to do anything
        return;
    }

//    $teachers = evaluation_get_receivemail_users($id);

    if ($teachers) {

        $strevaluations = get_string('modulenameplural', 'evaluation');
        $strevaluation = get_string('modulename', 'evaluation');
        $strcompleted = get_string('completed', 'evaluation');
        $printusername = get_string('anonymous_user', 'evaluation');

        foreach ($teachers as $teacher) {
            $info = new stdClass();
            $info->username = $printusername;
            $info->evaluation = format_string($evaluation->name, true);
            $info->url = $CFG->wwwroot . '/local/evaluations/show_entries_anonym.php?id=' . $id . '&clid=' . $classid . '';

            $postsubject = $strcompleted . ': ' . $info->username . ' -> ' . $evaluation->name;
            $posttext = evaluation_send_email_text($info, $class);

            if ($teacher->mailformat == 1) {
                $posthtml = evaluation_send_email_html($info, $classid, $id);
            } else {
                $posthtml = '';
            }

            $eventdata = new stdClass();
            $eventdata->name = 'submission';
            $eventdata->component = 'mod_evaluation';
            $eventdata->userfrom = $teacher;
            $eventdata->userto = $teacher;
            $eventdata->subject = $postsubject;
            $eventdata->fullmessage = $posttext;
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml = $posthtml;
            $eventdata->smallmessage = '';
            message_send($eventdata);
        }
    }
}

/**
 * get the values of an item depending on the given groupid.
 * if the evaluation is anonymous so the values are shuffled
 *
 * @global object
 * @global object
 * @param object $item
 * @param int $groupid
 * @param int $classid
 * @param bool $ignore_empty if this is set true so empty values are not delivered
 * @return array the value-records
 */
function evaluation_get_group_values($item, $groupid = false, $classid = false, $ignore_empty = false) {

    global $CFG, $DB;

    //if the groupid is given?
    if (intval($groupid) > 0) {
        if ($ignore_empty) {
            $ignore_empty_select = "AND fbv.value != '' AND fbv.value != '0'";
        } else {
            $ignore_empty_select = "";
        }

        $query = 'SELECT fbv .  *
                    FROM {evaluation_value} fbv, {evaluation_completed} fbc, {groups_members} gm
                   WHERE fbv.item = ?
                         AND fbv.completed = fbc.id
                         AND fbc.userid = gm.userid
                         ' . $ignore_empty_select . '
                         AND gm.groupid = ?
                ORDER BY fbc.timemodified';
        $values = $DB->get_records_sql($query, array($item->id, $groupid));
    } else {
        if ($ignore_empty) {
            $ignore_empty_select = "AND value != '' AND value != '0'";
        } else {
            $ignore_empty_select = "";
        }

        if ($classid) {
            $select = "item = ? AND class_id = ? " . $ignore_empty_select;
            $params = array($item->id, $classid);
            $values = $DB->get_records_select('evaluation_value', $select, $params);
        } else {
            $select = "item = ? " . $ignore_empty_select;
            $params = array($item->id);
            $values = $DB->get_records_select('evaluation_value', $select, $params);
        }
    }
    $params = array('id' => $item->evaluation);
    if ($DB->get_field('local_evaluation', 'anonymous', $params) == EVALUATION_ANONYMOUS_YES) {
        if (is_array($values)) {
            shuffle($values);
        }
    }
    return $values;
}

/**
 * count users which have completed a evaluation
 *
 * @global object
 * @uses EVALUATION_ANONYMOUS_NO
 * @param object $cm
 * @param int $group single groupid
 * @return int count of userrecords
 */
function evaluation_count_complete_users($cm, $group = false) {
    global $DB;

    $params = array(EVALUATION_ANONYMOUS_NO, $cm);

    $fromgroup = '';
    $wheregroup = '';
    if ($group) {
        $fromgroup = ', {groups_members} g';
        $wheregroup = ' AND g.groupid = ? AND g.userid = c.userid';
        $params[] = $group;
    }

    $sql = 'SELECT COUNT(u.id) FROM {user} u, {evaluation_completed} c' . $fromgroup . '
              WHERE anonymous_response = ? AND u.id = c.userid AND c.evaluation = ?
              ' . $wheregroup;

    return $DB->count_records_sql($sql, $params);
}

/**
 * get users which have completed a evaluation
 *
 * @global object
 * @uses CONTEXT_MODULE
 * @uses EVALUATION_ANONYMOUS_NO
 * @param object $cm
 * @param int $group single groupid
 * @param string $where a sql where condition (must end with " AND ")
 * @param array parameters used in $where
 * @param string $sort a table field
 * @param int $startpage
 * @param int $pagecount
 * @return object the userrecords
 */
function evaluation_get_complete_users($cm, $group = false, $where = '', array $params = null, $sort = '', $startpage = false, $pagecount = false) {

    global $DB;

    // $context = context_module::instance($cm->id);

    $params = (array) $params;

    $params['anon'] = EVALUATION_ANONYMOUS_NO;
    $params['instance'] = $cm;

    $fromgroup = '';
    $wheregroup = '';
    if ($group) {
        $fromgroup = ', {groups_members} g';
        $wheregroup = ' AND g.groupid = :group AND g.userid = c.userid';
        $params['group'] = $group;
    }

    if ($sort) {
        $sortsql = ' ORDER BY ' . $sort;
    } else {
        $sortsql = '';
    }

    $ufields = user_picture::fields('u');
    $sql = 'SELECT DISTINCT ' . $ufields . ', c.timemodified as completed_timemodified
            FROM {user} u, {evaluation_completed} c ' . $fromgroup . '
            WHERE ' . $where . ' anonymous_response = :anon
                AND u.id = c.userid
                AND c.evaluation = :instance
              ' . $wheregroup . $sortsql;

    if ($startpage === false OR $pagecount === false) {
        $startpage = false;
        $pagecount = false;
    }
    return $DB->get_records_sql($sql, $params, $startpage, $pagecount);
}

/**
 * get users which have the viewreports-capability
 *
 * @uses CONTEXT_MODULE
 * @param int $cmid
 * @param mixed $groups single groupid or array of groupids - group(s) user is in
 * @return object the userrecords
 */
function evaluation_get_viewreports_users($cmid, $groups = false) {

    // $context = context_module::instance($cmid);
    //description of the call below:
    //get_users_by_capability($context, $capability, $fields='', $sort='', $limitfrom='',
    //                          $limitnum='', $groups='', $exceptions='', $doanything=true)
    return get_users_by_capability($context, 'mod/evaluation:viewreports', '', 'lastname', '', '', $groups, '', false);
}

/**
 * get users which have the receivemail-capability
 *
 * @uses CONTEXT_MODULE
 * @param int $cmid
 * @param mixed $groups single groupid or array of groupids - group(s) user is in
 * @return object the userrecords
 */
function evaluation_get_receivemail_users($cmid, $groups = false) {

    //$context = context_module::instance($cmid);
    //description of the call below:
    //get_users_by_capability($context, $capability, $fields='', $sort='', $limitfrom='',
    //                          $limitnum='', $groups='', $exceptions='', $doanything=true)
    return get_users_by_capability($context, 'mod/evaluation:receivemail', '', 'lastname', '', '', $groups, '', false);
}

/**
 * Move save the items of the given $evaluation in the order of $itemlist.
 * @param string $itemlist a comma separated list with item ids
 * @param stdClass $evaluation
 * @return bool true if success
 */
function evaluation_ajax_saveitemorder($itemlist, $evaluation) {
    global $DB;

    $result = true;
    $position = 0;
    foreach ($itemlist as $itemid) {
        $position++;
        $result = $result && $DB->set_field('evaluation_item', 'position', $position, array('id' => $itemid, 'evaluation' => $evaluation->id));
    }
    return $result;
}

/**
 * renumbers all items of the given evaluationid
 *
 * @global object
 * @param int $evaluationid
 * @return void
 */
function evaluation_renumber_items($evaluationid) {
    global $DB;

    $items = $DB->get_records('evaluation_item', array('evaluation' => $evaluationid), 'position');
    $pos = 1;
    if ($items) {
        foreach ($items as $item) {
            $DB->set_field('evaluation_item', 'position', $pos, array('id' => $item->id));
            $pos++;
        }
    }
}

/**
 * sends an email to the teachers of the classe where the given evaluation is placed.
 *
 * @global object
 * @global object
 * @uses EVALUATION_ANONYMOUS_NO
 * @uses FORMAT_PLAIN
 * @param object $cm the classemodule-record
 * @param object $evaluation
 * @param object $classe
 * @param int $userid
 * @return void
 */
function evaluation_send_email($cm, $evaluation, $classe, $userid) {
    global $CFG, $DB;

    if ($evaluation->email_notification == 0) {  // No need to do anything
        return;
    }

    $user = $DB->get_record('user', array('id' => $userid));

    if (isset($cm->groupmode) && empty($classe->groupmodeforce)) {
        $groupmode = $cm->groupmode;
    } else {
        $groupmode = $classe->groupmode;
    }

    if ($groupmode == SEPARATEGROUPS) {
        $groups = $DB->get_records_sql_menu("SELECT g.name, g.id
                                               FROM {groups} g, {groups_members} m
                                              WHERE g.classeid = ?
                                                    AND g.id = m.groupid
                                                    AND m.userid = ?
                                           ORDER BY name ASC", array($classe->id, $userid));
        $groups = array_values($groups);

        $teachers = evaluation_get_receivemail_users($cm->id, $groups);
    } else {
        $teachers = evaluation_get_receivemail_users($cm->id);
    }

    if ($teachers) {

        $strevaluations = get_string('modulenameplural', 'evaluation');
        $strevaluation = get_string('modulename', 'evaluation');
        $strcompleted = get_string('completed', 'evaluation');

        if ($evaluation->anonymous == EVALUATION_ANONYMOUS_NO) {
            $printusername = fullname($user);
        } else {
            $printusername = get_string('anonymous_user', 'evaluation');
        }

        foreach ($teachers as $teacher) {
            $info = new stdClass();
            $info->username = $printusername;
            $info->evaluation = format_string($evaluation->name, true);
            $info->url = $CFG->wwwroot . '/local/evaluations/show_entries.php?' .
                    'id=' . $cm->id . '&' .
                    'userid=' . $userid . '&' .
                    'do_show=showentries';

            $postsubject = $strcompleted . ': ' . $info->username . ' -> ' . $evaluation->name;
            $posttext = evaluation_send_email_text($info, $classe);

            if ($teacher->mailformat == 1) {
                $posthtml = evaluation_send_email_html($info, $classe, $cm);
            } else {
                $posthtml = '';
            }

            if ($evaluation->anonymous == EVALUATION_ANONYMOUS_NO) {
                $eventdata = new stdClass();
                $eventdata->name = 'submission';
                $eventdata->component = 'mod_evaluation';
                $eventdata->userfrom = $user;
                $eventdata->userto = $teacher;
                $eventdata->subject = $postsubject;
                $eventdata->fullmessage = $posttext;
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml = $posthtml;
                $eventdata->smallmessage = '';
                message_send($eventdata);
            } else {
                $eventdata = new stdClass();
                $eventdata->name = 'submission';
                $eventdata->component = 'mod_evaluation';
                $eventdata->userfrom = $teacher;
                $eventdata->userto = $teacher;
                $eventdata->subject = $postsubject;
                $eventdata->fullmessage = $posttext;
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml = $posthtml;
                $eventdata->smallmessage = '';
                message_send($eventdata);
            }
        }
    }
}

/**
 * this function updates a complete-record and the related value-records.
 * depending on the $tmp (true/false) the values are saved temporary or permanently
 *
 * @global object
 * @param object $completed
 * @param boolean $tmp
 * @return int the completedid
 */
function evaluation_update_values($completed, $tmp = false) {
    global $DB;

    $classid = optional_param('clid', false, PARAM_INT);
    $tmpstr = $tmp ? 'tmp' : '';

    $DB->update_record('evaluation_completed' . $tmpstr, $completed);
    //get the values of this completed
    $values = $DB->get_records('evaluation_value' . $tmpstr, array('completed' => $completed->id));

    //get the items of the evaluation
    if (!$allitems = $DB->get_records('evaluation_item', array('evaluation' => $completed->evaluation))) {
        return false;
    }
    foreach ($allitems as $item) {
        if (!$item->hasvalue) {
            continue;
        }
        //get the class of item-typ
        $itemobj = evaluation_get_item_class($item->typ);

        $keyname = $item->typ . '_' . $item->id;

        if ($itemobj->value_is_array()) {
            $itemvalue = optional_param_array($keyname, null, $itemobj->value_type());
        } else {
            $itemvalue = optional_param($keyname, null, $itemobj->value_type());
        }

        //is the itemvalue set (could be a subset of items because pagebreak)?
        if (is_null($itemvalue)) {
            continue;
        }

        $newvalue = new stdClass();
        $newvalue->item = $item->id;
        $newvalue->completed = $completed->id;
        $newvalue->class_id = $classid;

        //the kind of values can be absolutely different
        //so we run create_value directly by the item-class
        $newvalue->value = $itemobj->create_value($itemvalue);

        //check, if we have to create or update the value
        $exist = false;
        foreach ($values as $value) {
            if ($value->item == $newvalue->item) {
                $newvalue->id = $value->id;
                $exist = true;
                break;
            }
        }
        if ($exist) {
            $DB->update_record('evaluation_value' . $tmpstr, $newvalue);
        } else {
            $DB->insert_record('evaluation_value' . $tmpstr, $newvalue);
        }
    }

    return $completed->id;
}

/**
 * if the user completes a evaluation and there is a pagebreak so the values are saved temporary.
 * the values are not saved permanently until the user click on save button
 *
 * @global object
 * @param object $evaluationcompleted
 * @return object temporary saved completed-record
 */
function evaluation_set_tmp_values($evaluationcompleted) {
    global $DB;

    //first we create a completedtmp
    $tmpcpl = new stdClass();
    foreach ($evaluationcompleted as $key => $value) {
        $tmpcpl->{$key} = $value;
    }
    unset($tmpcpl->id);
    $tmpcpl->timemodified = time();
    $tmpcpl->id = $DB->insert_record('evaluation_completedtmp', $tmpcpl);
    //get all values of original-completed
    if (!$values = $DB->get_records('evaluation_value', array('completed' => $evaluationcompleted->id))) {
        return;
    }
    foreach ($values as $value) {
        unset($value->id);
        $value->completed = $tmpcpl->id;
        $DB->insert_record('evaluation_valuetmp', $value);
    }
    return $tmpcpl;
}

/**
 * deletes the given temporary completed and all related temporary values
 *
 * @global object
 * @param int $tmpcplid
 * @return void
 */
function evaluation_delete_completedtmp($tmpcplid) {
    global $DB;

    $DB->delete_records('evaluation_valuetmp', array('completed' => $tmpcplid));
    $DB->delete_records('evaluation_completedtmp', array('id' => $tmpcplid));
}

/**
 * deletes a completed given by completedid.
 * all related data such values or tracking data also will be deleted
 *
 * @global object
 * @param int $completedid
 * @return boolean
 */
function evaluation_delete_completed($completedid) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/completionlib.php');

    if (!$completed = $DB->get_record('evaluation_completed', array('id' => $completedid))) {
        return false;
    }

    if (!$evaluation = $DB->get_record('local_evaluation', array('id' => $completed->evaluation))) {
        return false;
    }

    //if (!$class = $DB->get_record('class', array('id'=>$evaluation->class))) {
    //    return false;
    //}
    //if (!$cm = get_classmodule_from_instance('local_evaluation', $evaluation->id)) {
    //    return false;
    //}
    //first we delete all related values
    $DB->delete_records('evaluation_value', array('completed' => $completed->id));

    //now we delete all tracking data
    $params = array('completed' => $completed->id, 'evaluation' => $completed->evaluation);
    if ($tracking = $DB->get_record('evaluation_tracking', $params)) {
        $DB->delete_records('evaluation_tracking', array('completed' => $completed->id));
    }

    // Update completion state
    $completion = new completion_info($class);
    if ($completion->is_enabled($cm) && $evaluation->completionsubmit) {
        $completion->update_state($cm, COMPLETION_INCOMPLETE, $completed->userid);
    }
    //last we delete the completed-record
    return $DB->delete_records('evaluation_completed', array('id' => $completed->id));
}

/**
 * prints the given item in the show entries page.
 * each item-class has an own print_item_show_value function implemented.
 *
 * @param object $item the item what we want to print out
 * @param mixed $value
 * @return void
 */
function evaluation_print_item_show_value($item, $value = false) {
    global $CFG;
    if ($item->typ == 'pagebreak') {
        return;
    }

    //get the instance of the item-class
    $itemobj = evaluation_get_item_class($item->typ);
    $itemobj->print_item_show_value($item, $value);
}

/**
 * deletes all items of the given evaluationid
 *
 * @global object
 * @param int $evaluationid
 * @return void
 */
function evaluation_delete_all_items($evaluationid) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/completionlib.php');

    if (!$evaluation = $DB->get_record('local_evaluation', array('id' => $evaluationid))) {
        return false;
    }
    //
    //if (!$cm = get_coursemodule_from_instance('evaluation', $evaluation->id)) {
    //    return false;
    //}
    //if (!$course = $DB->get_record('class', array('id'=>$evaluation->classid))) {
    //    return false;
    //}

    if (!$items = $DB->get_records('evaluation_item', array('evaluation' => $evaluationid))) {
        return;
    }
    foreach ($items as $item) {
        evaluation_delete_item($item->id, false);
    }
    if ($completeds = $DB->get_records('evaluation_completed', array('evaluation' => $evaluation->id))) {
        $completion = new completion_info($course);
        foreach ($completeds as $completed) {
            // Update completion state
            if ($completion->is_enabled($cm) && $evaluation->completionsubmit) {
                $completion->update_state($cm, COMPLETION_INCOMPLETE, $completed->userid);
            }
            $DB->delete_records('evaluation_completed', array('id' => $completed->id));
        }
    }

    $DB->delete_records('evaluation_completedtmp', array('evaluation' => $evaluationid));
}

/**
 * this function toggled the item-attribute required (yes/no)
 *
 * @global object
 * @param object $item
 * @return boolean
 */
function evaluation_switch_item_required($item) {
    global $DB, $CFG;

    $itemobj = evaluation_get_item_class($item->typ);

    if ($itemobj->can_switch_require()) {
        $new_require_val = (int) !(bool) $item->required;
        $params = array('id' => $item->id);
        $DB->set_field('evaluation_item', 'required', $new_require_val, $params);
    }
    return true;
}

//}
?>
<?php
require_once($CFG->dirroot . '/config.php');
/*
 * get the list of tags in a particular activity
 */

function get_custom_tags($itemtype, $cm = null, $itemid = null) {
    global $DB, $CFG;
    $sql = "SELECT tag.* FROM {tag} tag
                JOIN {tag_instance} inst ON inst.tagid = tag.id
                JOIN {local_tag_mapping} map ON map.taginstanceid = inst.id
                WHERE inst.itemtype = '{$itemtype}' ";
    if ($cm) {
        $sql .= " AND map.courseid = {$cm->course} AND map.activityid = {$cm->id} ";
    }

    if ($itemid) {
        $sql .= " AND inst.itemid = {$itemid}";
    }
    $sql .= " GROUP BY inst.tagid";
    $records = $DB->get_records_sql($sql);
    $rec = array();
    if ($records)
        foreach ($records as $record) {
            $rec[$record->id] = $record->rawname;
        }
    return $rec;
}

/*
 * get the list of selected tags for the item
 */

function get_selected_customtags($cm, $itemtype, $itemid) {
    global $DB, $CFG;
    $rec = get_custom_tags($itemtype, $cm, $itemid);
    $rec = implode(',', array_keys($rec));
    return $rec;
}

/*
 * adds the custom tag element to the form
 */

function create_customtag_element($mform, $cm, $itemtype, $itemid = null) {
    $tags = get_custom_tags($itemtype);
    $disable = '';
    if (empty($tags)) {
        $tags = array('none');
        $disable = 'disabled="disabled"';
    }
    $mform->addElement('header', 'tagshdr', get_string('tags', 'tag'));
    $thistag = $mform->addElement('select', 'tags', get_string('tags'), $tags, $disable);
    $thistag->setMultiple(true);
    /* Set the assigned tags selected by default */
    $mform->setType('tags', PARAM_INT);
    $mform->addElement('static', 'staticinfotags', '', get_string('othertags', 'tag'));
    $mform->addElement('textarea', 'othertags', '');
}

/*
 * assign the tags to the activity
 * unassigns the non-selected tags from the activity
 * Inserts the new tags
 */

function customtags_set($data, $cm, $itemid, $itemtype) {
    global $DB, $CFG, $USER;
    $tags = array();
    if ($data->othertags) {
        $tags = explode(',', $data->othertags);
        $tags = array_map('trim', $tags);
    }
    $prev_tags = array();
    if (isset($data->tags) && $data->tags[0])
        foreach ($data->tags as $d_tag) {
            $prev_tags[$d_tag] = $DB->get_field('tag', 'rawname', array('id' => $d_tag));
        }
    $tags = array_merge($tags, $prev_tags);
    //require_once($CFG->dirroot.'/tag/lib.php');
    //tag_set($activityname, $itemid, $tags);
    $tagids = $DB->get_fieldset_select('tag_instance', 'tagid', 'itemtype = ? AND itemid = ?', array("$itemtype", $itemid));
    foreach ($tagids as $tagid) {
        if (in_array($tagid, $data->tags))
            continue;
        $inst = $DB->get_record('tag_instance', array('tagid' => $tagid, 'itemtype' => "$itemtype", 'itemid' => $itemid));
        $DB->delete_records('local_tag_mapping', array('taginstanceid' => $inst->id));
        $DB->delete_records('tag_instance', array('id' => $inst->id));
    }

    foreach ($tags as $tag) {
        $tag_check = strtolower($tag);
        $new_inst = new stdClass();
        $new_inst->courseid = $data->course;
        $new_inst->activityid = $cm->id;
        $new_inst->itemid = $itemid;
        $new_inst->itemtype = $itemtype;
        $new_inst->user = $USER->id;
        $new_inst->time = time();
        $new_inst->timemodified = time();
        if ($exist = $DB->get_record('tag', array('name' => "$tag_check"))) {
            $new_inst->tagid = $exist->id;
        } else {
            $newtag = new stdClass();
            $newtag->userid = $USER->id;
            $newtag->name = $tag_check;
            $newtag->rawname = $tag;
            $newtag->tagtype = 'default';
            $newtag->timemodified = time();
            $new_inst->tagid = $DB->insert_record('tag', $newtag);
        }
        if (!$DB->record_exists('tag_instance', array('tagid' => $new_inst->tagid, 'itemtype' => "$itemtype", 'itemid' => $itemid))) {
            $new_inst->taginstanceid = $DB->insert_record('tag_instance', $new_inst);
            $DB->insert_record('local_tag_mapping', $new_inst);
        }
    }
}

/*
 * List of all tags assigned to the particular activity
 * Displayed in separated by commas
 */

function print_sortby_customtag($cm, $itemtype, $customtag, $url = null) {
    global $DB, $CFG;

    $url_components = (object) parse_url($url);

    $sql = "SELECT tag.* FROM {tag} tag
                JOIN {tag_instance} inst ON inst.tagid = tag.id
                JOIN {local_tag_mapping} map ON map.taginstanceid = inst.id
                WHERE inst.itemtype = '{$itemtype}' ";
    if ($cm) {
        $sql .= " AND map.courseid = {$cm->course} AND map.activityid = {$cm->id} ";
    }
    $sql .= " GROUP BY inst.tagid";
    //$sql = "SELECT tag.* FROM {local_tags} tag JOIN {local_tag_instance} inst
    //            ON inst.tagid = tag.id WHERE inst.courseid = {$cm->course} AND inst.activityid = {$cm->id} AND activityname = '$activityname' GROUP BY inst.tagid";
    $tags = $DB->get_records_sql($sql);
    //print_object($tags);
    $tagarray = array();
    foreach ($tags as $tag) {
        $count = $DB->count_records_sql("SELECT COUNT(*) FROM {tag_instance} inst JOIN {local_tag_mapping} map ON map.taginstanceid = inst.id WHERE inst.tagid = ? AND inst.itemtype = ? AND map.courseid = ? AND map.activityid = ? ", array($tag->id, "$itemtype", $cm->course, $cm->id));
        $append = isset($url_components->query) ? '&' : '?';
        $redirect = $url . $append . 'customtag=' . $tag->id;
        $tagname = html_writer::tag('a', $tag->rawname, array('href' => $redirect));
        //$tagname = html_writer::tag('font', $tagname, array('size'=>($count*1.5).'em'));
        $tagarray[] = $tagname;
    }
    $array1 = array();
    $array2 = array();
    $i = 1;
    foreach ($tagarray as $t_array) {
        if ($i <= 10)
            $array1[] = $t_array;
        else
            $array2[] = $t_array;
        $i++;
    }
    $output = html_writer::start_tag('div', array('class' => 'custom_tags'));
    $output .= get_string('tags') . ' :  ';
    $output .= html_writer::tag('span', implode(', ', $array1), array());
    if ($array2) {
        $output .= html_writer::tag('span', ', ' . implode(', ', $array2), array('class' => 'hidden_tags'));
        $output .= html_writer::tag('span', 'more..', array('class' => 'more_tags'));
    }
    $output .= html_writer::end_tag('div');

    if ($customtag) {
        if (!$DB->get_record('tag', array('id' => $customtag))) {
            print_error('invalidtagid');
        }
        $output .= html_writer::start_tag('div', array('class' => 'custom_tags'));
        $output .= get_string('relatedtotag', 'local_tags') . ': <font size="3px">"' . $DB->get_field('tag', 'rawname', array('id' => $customtag)) . '"</font>';
        $output .= "&nbsp;&nbsp;&nbsp;<a href='$url'>View all</a>";
        $output .= html_writer::end_tag('div');
    }
    if (!empty($tagarray))
        echo $output;
}

/*
 * provides the search field to search items by the entered tag name
 */

function print_searchby_customtag($searchtag, $url) {
    global $DB, $CFG;
    ?>
    <div style="text-align: right;">
        <form method="post" action="<?php echo $url; ?>" style="display:inline">
            <fieldset class="invisiblefieldset">
                <legend class="accesshide">Search Tags</legend>
                <label class="accesshide" for="searchtag">Search terms</label>
                <input id="searchtag" name="searchtag" type="text" size="18" value="<?php echo $searchtag; ?>" alt="search">

                <input value="Search by Tag" type="submit">
            </fieldset>
        </form>
    </div>
    <?php
    if ($searchtag) {
        $output = html_writer::start_tag('div', array('class' => 'custom_tags'));
        $output .= get_string('relatedtotag', 'local_tags') . ': <font size="3px">"' . $searchtag . '"</font>';
        $output .= "&nbsp;&nbsp;&nbsp;<a href='$url'>View all</a>";
        $output .= html_writer::end_tag('div');
        echo $output;
    }
}

/*
 * Search the tags in the course
 */

function customtag_search_box($courseid) {
    global $DB, $CFG;
    ?>
    <div style="text-align: right;">
        <form method="post" action="index.php?id=<?php echo $courseid; ?>" style="display:inline">
            <fieldset class="invisiblefieldset">
                <legend class="accesshide">Search Tags</legend>
                <label class="accesshide" for="searchtag">Search terms</label>
                <input id="searchtag" name="searchquery" type="text" size="18" value="" alt="search">

                <input value="Search Tags" type="submit">
            </fieldset>
        </form>
    </div>
    <?php
}

/*
 * Displays the list of tags available in the course
 */

function customtag_print_search_results($courseid, $query) {
    global $DB, $CFG;
    $sql = "SELECT tag.* FROM {tag} tag
                JOIN {tag_instance} inst ON inst.tagid = tag.id
                JOIN {local_tag_mapping} map ON map.taginstanceid = inst.id
                WHERE map.courseid = {$courseid} ";

    if (!empty($query)) {
        $sql .= " AND tag.rawname LIKE '%$query%' ";
    }
    $sql .= " GROUP BY inst.tagid ";
    //custom tags added to the moodle course activities.
    $custom_tags = $DB->get_records_sql($sql);

    $sql = "SELECT inst.* FROM {tag_instance} inst
                        JOIN {tag} tag ON tag.id = inst.tagid
                        JOIN {sociopedia_pages} page ON page.id = inst.itemid
                        JOIN {sociopedia_subsociopedias} sub ON sub.id = page.subsociopediaid
                        JOIN {sociopedia} socio ON socio.id = sub.sociopediaid
                        WHERE inst.itemtype = 'sociopedia_pages' AND socio.course = {$courseid}";
    if ($query) {
        $sql .= " AND tag.rawname LIKE '%$query%' ";
    }
    //tag informations of the sociopedia activity
    $socio_tags = $DB->get_records_sql($sql);

    $sql = "SELECT inst.* FROM {tag_instance} inst
                        JOIN {tag} tag ON tag.id = inst.tagid
                        JOIN {wiki_pages} page ON page.id = inst.itemid
                        JOIN {wiki_subwikis} sub ON sub.id = page.subwikiid
                        JOIN {wiki} wiki ON wiki.id = sub.wikiid
                        WHERE inst.itemtype = 'wiki_pages' AND wiki.course = {$courseid}";
    if ($query) {
        $sql .= " AND tag.rawname LIKE '%$query%' ";
    }
    //tag informations of the wiki activity
    $wiki_tags = $DB->get_records_sql($sql);
    $default_tags = $socio_tags + $wiki_tags;
    $default_id_in = array_filter(array_keys($default_tags));

    $tags = array();
    foreach ($default_tags as $d_tag) {
        $tags[$d_tag->tagid] = $DB->get_field('tag', 'rawname', array('id' => $d_tag->tagid));
    }

    foreach ($custom_tags as $c_tag) {
        $tags[$c_tag->id] = $c_tag->rawname;
    }

    $tagarray = array();
    //List of all tags added custom for the activities and the default the default activities
    if ($tags) {
        foreach ($tags as $tagid => $tagname) {
            $count = $DB->count_records_sql("SELECT COUNT(*) FROM {tag_instance} inst
                                            JOIN {local_tag_mapping} map ON map.taginstanceid = inst.id
                                            WHERE inst.tagid = ? AND map.courseid = ? ", array($tagid, $courseid));
            $count += $DB->count_records_select('tag_instance', 'tagid = ' . $tagid . ' AND id IN (' . implode(',', $default_id_in) . ')');
            //$count is the count of activities which are tagged with this tagid

            $tagname = html_writer::tag('a', $tagname, array('href' => 'index.php?id=' . $courseid . '&tagid=' . $tagid));
            $tagname = html_writer::tag('font', $tagname, array('size' => ($count * 1.5) . 'em'));
            $tagarray[] = $tagname;
        }
    }
    if ($query) {
        echo '<h3>' . get_string('searchresults', 'local_tags') . '"' . $query . '" : ' . sizeof($tags) . ' </h3>';
    }
    if (!$tags) {
        echo '<h3>' . get_string('noresultsfound', 'local_tags') . ' </h3>';
    }
    $output = html_writer::start_tag('div', array('class' => 'custom_tags course_tags_cloud'));
    $output .= implode('&nbsp;&nbsp;', $tagarray);
    $output .= html_writer::end_tag('div');
    echo $output;
}

/*
 * Limit the description
 */

function limit_the_text($description, $url) {
    $message = strip_tags($description);
    $desc = substr($message, 0, 400);
    if (strlen($message) > 400) {
        $desc .= '.....' . html_writer::tag('a', 'more', array('href' => $url, 'target' => '_blank'));
    }
    return html_writer::tag('p', $desc, array());
}

/*
 * get the tags of the particular activity in a course
 */

function get_taggedactivities_by_name($instance, $courseid) {
    global $DB, $CFG;
    return $DB->get_records_sql("SELECT inst.* FROM {tag_instance} inst
                                            JOIN {local_tag_mapping} map ON map.taginstanceid = inst.id
                                            WHERE inst.tagid = ? AND inst.itemtype = ? AND map.courseid = ? GROUP BY inst.itemid ", array($instance->tagid, "$instance->itemtype", $courseid));
}

/*
 * get the wiki or sociopedia activitiy details by the pageid
 */

function get_activity_by_page($pageid, $module) {
    global $DB, $CFG;
    $sql = 'SELECT w.*
            FROM {' . $module . '} w, {' . $module . '_sub' . $module . 's} s, {' . $module . '_pages} p
            WHERE p.id = ? AND
            p.sub' . $module . 'id = s.id AND
            s.' . $module . 'id = w.id';

    return $DB->get_record_sql($sql, array($pageid));
}

/*
 * returns the table to display the tagged activities
 */

function get_taggedactivity_table($heading) {
    global $DB, $CFG;
    $table = new html_table();
    $table->head = array($heading);
    $table->width = '100%';
    $table->data = array();
    return $table;
}

/*
 * displays all the tagged activities in the course
 */

function customtag_items_list($instance, $courseid) {
    global $DB, $CFG;
    $tagname = $DB->get_field('tag', 'rawname', array('id' => $instance->tagid));
    switch ($instance->itemtype) {
        case 'forum_discussions' : echo '<br/>';
            echo '<h3>' . get_string('tagdiscussions', 'local_tags') . ' "' . $tagname . '"</h3>';
            $activities = get_taggedactivities_by_name($instance, $courseid);
            foreach ($activities as $activity) {
                $record = $DB->get_record($instance->itemtype, array('id' => $activity->itemid));
                $url = $CFG->wwwroot . '/mod/forum/discuss.php?d=' . $record->id;
                $data = html_writer::tag('a', $record->name, array('href' => $url, 'target' => '_blank'));
                $post = $DB->get_record('forum_posts', array('id' => $record->firstpost));
                $data .= limit_the_text($post->message, $url);
                $table = get_taggedactivity_table($DB->get_field('forum', 'name', array('id' => $record->forum)));
                $table->data[] = array($data);
                echo html_writer::tag('div', html_writer::table($table), array('style' => 'border: 1px solid; margin: 1% 0;'));
            }
            break;
        case 'sociopedia_pages' : echo '<br/>';
            echo '<h3>' . get_string('tagsociopedias', 'local_tags') . ' "' . $tagname . '"</h3>';
            $activities = $DB->get_records('tag_instance', array('tagid' => $instance->tagid, 'itemtype' => "$instance->itemtype"));
            foreach ($activities as $activity) {
                $sociopedia = get_activity_by_page($activity->itemid, 'sociopedia');
                if ($sociopedia->course == $courseid) {
                    $page = $DB->get_record($instance->itemtype, array('id' => $activity->itemid));
                    $url = $CFG->wwwroot . '/mod/sociopedia/view.php?pageid=' . $page->id;
                    $data = html_writer::tag('a', $page->title, array('href' => $url, 'target' => '_blank'));
                    $data .= limit_the_text($page->cachedcontent, $url);
                    $table = get_taggedactivity_table($sociopedia->name);
                    $table->data[] = array($data);
                    echo html_writer::tag('div', html_writer::table($table), array('style' => 'border: 1px solid; margin: 1% 0;'));
                }
            }
            break;
        case 'wiki_pages' : echo '<br/>';
            echo '<h3>' . get_string('tagwikis', 'local_tags') . ' "' . $tagname . '"</h3>';
            $activities = $DB->get_records('tag_instance', array('tagid' => $instance->tagid, 'itemtype' => "$instance->itemtype"));
            foreach ($activities as $activity) {
                $wiki = get_activity_by_page($activity->itemid, 'wiki');
                if ($wiki->course == $courseid) {
                    $page = $DB->get_record($instance->itemtype, array('id' => $activity->itemid));
                    $url = $CFG->wwwroot . '/mod/wiki/view.php?pageid=' . $page->id;
                    $data = html_writer::tag('a', $page->title, array('href' => $url, 'target' => '_blank'));
                    $data .= limit_the_text($page->cachedcontent, $url);
                    $table = get_taggedactivity_table($wiki->name);
                    $table->data[] = array($data);
                    echo html_writer::tag('div', html_writer::table($table), array('style' => 'border: 1px solid; margin: 1% 0;'));
                }
            }
            break;
        case 'storywall' :
            break;
        case 'townhall' :
            break;
        case 'resourcecentral' :
            break;
        case 'feeds' :
            break;
        default :
            break;
    }
}

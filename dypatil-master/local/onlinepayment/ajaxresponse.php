<?php

require_once(dirname(__FILE__) . '/../../config.php');
// cost the course
global $CFG, $USER, $DB;
$dcode = $_POST['discountcode'];
$classid = $_POST['classid'];
$type = $_POST['type'];

function get_percentvalue($total, $discount) {
    $sum = ($total * $discount);
    $discountrate = ($sum / 100);
    return ($discountrate);
}

$today = date('Y-m-d');
if ($type == 'mooctype') {
    $sql = "SELECT cl.*, disc.discount FROM {local_classcost} cl JOIN {local_costdiscounts} disc ON disc.costid = cl.id
                WHERE cl.courseid = {$classid} AND '{$today}' BETWEEN FROM_UNIXTIME(disc.startdate, '%Y-%m-%d') AND FROM_UNIXTIME(disc.enddate, '%Y-%m-%d')
                AND disc.discountcode = '{$dcode}'";
    $costinfo = $DB->get_record_sql($sql);
    //$costinfo=$DB->get_record('local_classcost',array('courseid'=>$classid,'discountcode'=>$dcode));

    if ($costinfo) {
        $total = $costinfo->coursecost;
        $discount = number_format($costinfo->discount, 1);
        $dis = get_percentvalue($total, $discount);
        echo $final = ($total - $dis);
    } else
        echo 0;
}

if ($type == 'classtype') {
    //$costinfo=$DB->get_record('local_classcost',array('classid'=>$classid,'discountcode'=>$dcode));
    $sql = "SELECT cl.*, disc.discount FROM {local_classcost} cl JOIN {local_costdiscounts} disc ON disc.costid = cl.id
                WHERE cl.classid = {$classid} AND '{$today}' BETWEEN FROM_UNIXTIME(disc.startdate, '%Y-%m-%d') AND FROM_UNIXTIME(disc.enddate, '%Y-%m-%d')
                AND disc.discountcode = '{$dcode}'";
    $costinfo = $DB->get_record_sql($sql);
    // print_object( $costinfo);
    if (!empty($costinfo)) {
        if ($costinfo->classcost != 0) {
            $total = ($costinfo->classcost);
            $discount = number_format($costinfo->discount, 1);
            $dis = get_percentvalue($total, $discount);
            echo $final = ($total - $dis);
        } else {
            //$cinfo=$DB->get_record_sql("select distinct cc.id,(cc.credithours*clcost.credithourcost) as price,clcost.discount as discount
            //                         from {local_clclasses} cl
            // JOIN {local_cobaltcourses} cc  ON cc.id=cl.cobaltcourseid
            // JOIN {local_classcost} clcost ON clcost.classid=cl.id and clcost.discountcode='$dcode'
            //  where  cl.id=$classid ");

            $sql = "SELECT distinct cc.id, (cc.credithours*clcost.credithourcost) as price, disc.discount as discount
        FROM {local_clclasses} cl
        JOIN {local_cobaltcourses} cc  ON cc.id=cl.cobaltcourseid
        JOIN {local_classcost} clcost ON clcost.classid=cl.id
        JOIN {local_costdiscounts} disc ON disc.costid = clcost.id AND '{$today}' BETWEEN FROM_UNIXTIME(disc.startdate, '%Y-%m-%d') AND FROM_UNIXTIME(disc.enddate, '%Y-%m-%d')
        AND disc.discountcode='$dcode'
        WHERE cl.id= {$classid} ";
            $cinfo = $DB->get_record_sql($sql);

            $total = ($cinfo->price);
            $discount = number_format($cinfo->discount, 1);
            $dis = get_percentvalue($total, $discount);
            echo $final = ($total - $dis);
        }
    } else {
        echo(0);
    }
}
?>
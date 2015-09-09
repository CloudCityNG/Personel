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
 * @subpackage  onlinepayment
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/../../simpay/AuthorizeNet.php');
$api_login_id = '8p84NGfS';
$transaction_key = '7dr773YS273cK2P4';
$amount = "5.99";
$fp_timestamp = time();
$fp_sequence = "123" . time(); // Enter an invoice or other unique number.
$fingerprint = AuthorizeNetSIM_Form::getFingerprint($api_login_id, $transaction_key, $amount, $fp_sequence, $fp_timestamp);


global $CFG, $DB, $OUTPUT;
$systemcontext = context_system::instance();
echo $OUTPUT->header();
//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

$PAGE->set_url('/local/onlinepayment/gateway.php');

//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
?>
</br></br></br>
<form method='post' action="https://test.authorize.net/gateway/transact.dll">
    <input type='hidden' name="x_login" value="<?php echo $api_login_id ?>" />
    <input type='hidden' name="x_fp_hash" value="<?php echo $fingerprint ?>" />
    <input type='hidden' name="x_amount" value="<?php echo $amount ?>" />
    <input type='hidden' name="x_fp_timestamp" value="<?php echo $fp_timestamp ?>" />
    <input type='hidden' name="x_fp_sequence" value="<?php echo $fp_sequence ?>" />
    <input type='hidden' name="x_version" value="3.1">
    <input type='hidden' name="x_show_form" value="payment_form">
    <input type='hidden' name="x_test_request" value="false" />
    <input type='hidden' name="x_method" value="cc">
    <input type='submit' value="Proceed to Payment Gateway to complete payment using your Credit Card">
</form>
</br></br></br></br></br></br></br></br></br></br></br></br>

<?php
echo $OUTPUT->footer();
?>
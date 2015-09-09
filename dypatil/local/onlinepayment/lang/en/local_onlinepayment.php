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
 * Strings for component 'local_rate_course', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    local plugin
 * @subpackage Online Payment
 * @copyright  2013 eabyas
 * @license    http://www.eabyas.in 
 *
 * Code was Rewritten for Moodle 2.X By Atar + Plus LTD for Comverse LTD.
 * @copyright &copy; 2013 eAbyas info solutions.
 * @license eAbyas info solutions.
 */
$string['pluginname'] = 'Online Payment';
$string['billingmodule'] = 'Billing Module';
$string['invalidtaxtype'] = 'Invalid Tax Type';
$string['invalidtaxrate'] = 'Invalid Tax Rate';
$string['createtaxtypepage'] = 'This page allows you to define a new type of tax to be levied upon the respective payment transactions. Fill in the details below by defining the name of the tax, the name to be displayed and provide the necessary description if any. ';
$string['createtaxtype'] = 'Create Tax Type';
$string['edittaxtype'] = 'Edit Tax Type';
$string['taxname'] = 'Tax Name';
$string['missingtaxname'] = 'Missing Tax Name';
$string['displaytaxname'] = 'Display Name';
$string['missingdisplaytaxname'] = 'Missing Display Name';
$string['typedescription'] = 'Description';
$string['createrate'] = 'Create Rate';
$string['notypescreatedyet'] = 'No Tax types are created yet.';
$string['deletetaxtype'] = 'Delete Tax Type';
$string['tax'] = 'Tax';
$string['totalamount'] = 'Total Amount';
$string['paymentdate'] = 'Payment Date';
$string['deltypeconfirm'] = 'Are you sure? You really want to delete this Tax type!';
$string['deletetaxrate'] = 'Delete Tax Rate';
$string['delrateconfirm'] = 'Are you sure? You really want to delete this Tax Rate!';
$string['createtaxratepage'] = 'This page allows you to define a new tax rate based on the geographical locations to be levied upon the respective payment transactions. Select the type of tax to be defined, the name of the tax rate and choose the country and define its tax rate accordingly.';
$string['viewpaymentstatuspage'] = 'Upon successful transactions done by the students, the complete payment details like Service Id, Student Name, Transaction Id, Module Names and Id\'s, Total Amount etc...  are displayed here for the final review. Filters allow you to customize the view based on the Name of the students.';
$string['viewstudentstatuspage'] = 'This page displays the list of successful transactions that have been processed along with the Semester name, Transaction Id, List of Modules, Total Amount, Payment Date etc. For more information on a particular transaction, click on "View Details".';
$string['createtaxrate'] = 'Create Tax Rate';
$string['edittaxrate'] = 'Edit Tax Rate';
$string['taxtype'] = 'Tax Type';
$string['missingtypeid'] = 'Missing Tax Type';
$string['missingcountry'] = 'Missing Country';
$string['taxrate'] = 'Tax Rate';
$string['missingtaxrate'] = 'Missing Tax Rate';
$string['noratescreatedyet'] = 'No Tax Rates are created yet.';
$string['taxratelist'] = 'Tax Settings';
$string['entervalidrate'] = 'Enter Valid Rate';
$string['deleteratesuccess'] = 'Tax Rate "{$a->name}" deleted successfully.';
$string['deletetypesuccess'] = 'Tax Type "{$a->name}" deleted successfully.';
$string['updateratesuccess'] = 'Tax Rate "{$a->name}" updated successfully.';
$string['updatetypesuccess'] = 'Tax Type "{$a->name}" updated successfully.';
$string['createratesuccess'] = 'Tax Rate "{$a->name}" created successfully.';
$string['createtypesuccess'] = 'Tax Type "{$a->name}" created successfully.';
$string['ratecreateddontdelete'] = 'Tax rate is created for this "{$a->name}", you can not delete it.';
$string['paidon'] = 'Paid On';
$string['taxratesettings']='Tax Rate Settings';
$string['semester'] = 'Semester';
$string['studentname'] = 'Student Name';
$string['amount'] = 'Amount';
$string['orders'] = 'Orders';
$string['code'] = 'Code';
$string['vatamount'] = 'VAT (amount)';
$string['vatamountinpounds'] = 'VAT amount (in pounds)';
$string['vatpercent'] = 'VAT rate';
$string['startdategreatertoday'] = 'Startdate should not be less than Today date';
$string['mypayments'] = 'My Payments';
$string['paymentsdonedontchange'] = 'Payments done for this module, you can not change the price.';
$string['code'] = 'Code';
$string['monthlyvatreport'] = 'Monthly VAT Accounting Report';
$string['paymentreport'] = 'Payment Report';
$string['selectmonth'] = 'Select Month';
$string['endgreaterthanstart'] = 'End date should be greater than or equal to Start date';
$string['entervaliddates'] = 'Enter valid Date';
$string['entervalidcost'] = 'Enter Valid Cost';
$string['entervaliddiscount'] = 'Enter valid Discount';
$string['enterdiscountcode'] = 'Enter Discount Code';
$string['enteruniquediscountcode'] = 'Enter Unique Discount Code';
$string['transactionid'] = 'Transaction ID';
$string['amountpaid'] = 'Amount Paid';
$string['amountinpounds'] = 'Amount (in pounds)';
$string['paymentmethod'] = 'Payment Method';
$string['addprice'] = 'Add Price';
$string['paymentstatus'] = 'Payment Status';
$string['modcostsettings'] = 'Module Cost Settings';
$string['nopaymentsdone'] = 'No payment records found.';
$string['paymentdetails'] = 'Payment Status';
$string['paymentorders'] = 'Payment Orders';
$string['serviceid'] = 'Service ID';
$string['accountingperiod'] = 'Accounting Period';
$string['createaccountingperiodpage'] = 'This page allows you to define the accounting period i.e. the period/duration for which the account books are balanced and the financial statements are prepared for a particular school. In order to create a new accounting period, select the school for which the period has to be set and define the duration start and end dates.';
$string['viewtaxratesettingspage'] = 'This page allows you to view the list of tax settings that have been created and are in use for different payment transactions. You can also edit or modify the data based on the requirement. Click on the \'Create Tax Rate\' button to define a new tax rate.';
$string['noperiodcreatedyet'] = 'No Accounting Period is created yet.';
$string['school'] = 'School';
$string['enddateshouldbegreater'] = 'End date should be greater than the Start date';
$string['deleteperiodsuccess'] = 'Accounting Period for the school "{$a->fullname}" deleted successfully.';
$string['deleteperiod'] = 'Delete Accounting Period';
$string['delperiodconfirm'] = 'Are you sure? You really want to delete this Accounting Period!';
$string['updateperiodsuccess'] = 'Accounting Period for the school "{$a->fullname}" updated successfully.';
$string['createperiodsuccess'] = 'Accounting Period for the school "{$a->fullname}" created successfully.';
$string['onlinepay_heading']='Online Payment';
$string['onlinepay_select']='Select';
//$string['onlinepay_course']='Class/Module';
$string['onlinepay_enrollementdate']='Enrollement Date';
$string['onlinepay_status']='Status';
$string['onlinepay_cost']='Price';
$string['accperiodsettings']='Accounting Period Settings';
$string['paynow']='Pay Now';
$string['status']='Status';
$string['enternumericvalue']='You must enter the Numeric values here.';
$string['transactionid']='Transaction Id';
$string['payment_link']='Payment Status';
$string['onlinepay_shortname']='Class';
$string['make_payment']='This page displays the list of courses along with the payment details when the respective accounting period is enabled. Students can select the courses based on their convenience and process the payment transactions.
</br> Note*:  The total amount is inclusive of the taxes levied based upon the country.';
$string['modcost'] = 'Module Cost';
$string['modcostsettingspage'] = 'This page allows you view the list of prices for each PGD/PGC module. To manage the view of Modules, apply filters. To add a price to a module, click on \'Add\' button and to update the price of a module, click on \'Update\' button.';
$string['noteforaccperiodpage'] = '<b>Note:</b> Once the accounting period is set and if order details or Invoice or generated then you cannot edit or remove accounting period.';
$string['paymentdonedontchange'] = 'The Transaction is done for this College {$a->name}, You can\'t edit or delete.';
$string['dontchangetaxrate'] = 'The Transaction is done for this Tax type {$a->name}, You can\'t edit or delete.';
$string['dontchangetaxtype'] = 'The Tax rate is created for this Tax type {$a->name}, You can\'t edit or delete.';
$string['viewvatreportpage'] = 'This page displays the list of overall monthly report of the VAT levied on a particular student on the basis of payments made for a particular class/module. The Sub-Total displays the VAT levied on a particular student while the Grand Total is the sum of the VAT levied on all students. Apply filters to customize the view based on the month, start date and end date.';
$string['classname'] = 'Class Name';
$string['modname'] = 'Module Name';
$string['price'] = 'Price';
$string['discount'] = 'Discount';
$string['addnewdiscount'] = 'Add New Discount';
$string['nodiscounts'] = 'No Discounts added yet.';
$string['applicablefrom'] = 'Applicable From';
$string['enrolleddate'] = 'Enrolled Date';
$string['discountcode'] = 'Discount Code';
$string['discounts'] = 'Discounts';
$string['viewdetails'] = 'View Details';
$string['updateddate'] = 'Updated Date';
$string['modifiedby'] = 'Modified By';
$string['course'] = 'Course';
$string['report'] = 'Report';
$string['reports'] = 'Reports';
$string['vatreport'] = 'VAT Report';
$string['module'] = 'Module';
$string['mooc'] = 'Mooc';
$string['credithourcost'] = 'Credit Hour Cost';
$string['filters'] = 'Filters';
$string['class'] = 'Class';
$string['priceinpounds'] = 'Price (In Pounds)';
$string['credithours'] = 'Credit Hours';
$string['classmodname'] = 'Class/ Module ';
$string['taxtype_help'] = 'Denotes the type of tax rate that has to be created. Select from the options given below.';
$string['taxname_help'] = 'Defines the name or context of the tax rate which has to be created.';
$string['country_help'] = 'Denotes the country or place for which the tax rate will be assigned /defined.';
$string['taxrate_help'] = 'Define the percentage of tax rate for the given tax name.';
$string['country'] = 'Country';
$string['viewbillingreportpage'] = 'This page displays the list of overall report on the payments made by the students for a particular class/module along with the amount paid by each student. Apply filters to customize the view based on the school, course, module and the duration.';
$string['norecordsfound'] = 'No Records Found';
$string['pageinprogress'] = 'This page is Inprgress';
$string['notransactiosdoneyet'] = 'No transactions are done yet.';
$string['confirmation_heading']='Payment Confirmation';
$string['payment_confirmation']='<h4>Payment Information for the following Courses : </h4> ';
$string['payment_missingcourselist']='Please Select the course, To Process';
$string['transactionperiod_closed']='Transaction period got closed. Sorry you cannot pay now';
$string['noenrollments_currentcourses']='Not yet enrolled to current study period or there is no paid Course';
$string['finalamount']='Amount';
$string['vatpercent_help']='VAT is added to the paid amount and is calculated for the final amount after discount if any, for the class/module. VAT is not applicable for the students seeking financial aid.';
$string['discount_percent']='Discount%';
$string['discount_code']='DiscountCode';
$string['final_price']='Final Price';
$string['no_pendingcourses']='No Pending Modules';
$string['missingstartdate_payment']='Start date should be current date or greater than current date ';
$string['nocurrentsemesteravailable']='No current Study Period is available';
$string['addonemorediscountcode']='Add Additional Discount code';
$string['myclclassespay']='My Class Payments';
$string['onlinepayment:manage']='onlinepayment:manage';
$string['onlinepayment:view']='onlinepayment:view';

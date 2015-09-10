<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/../../lib/tcpdf/tcpdf.php');
global $CFG, $DB, $USER;
$schoolid = required_param('sid', PARAM_INT);      // Course Module ID.
$programid = required_param('pid', PARAM_INT);  // User selection.
$pgmtype = required_param('ptype', PARAM_INT);
$today = date('d-M-Y');
$schoolname = $DB->get_field('local_school', 'fullname', array('id' => $schoolid));
$Pgmname = $DB->get_field('local_program', 'fullname', array('id' => $programid));
if ($pgmtype == 1) {
    $pgm = get_string('undergard', 'local_admission') . get_string('program', 'local_programs');
} else {
    $pgm = get_string('grad', 'local_admission') . get_string('program', 'local_programs');
}
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->setPageOrientation($orientation = 'L', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false);
$pdf->AddPage();

$html = '
<form method="post" action="#" enctype="multipart/form-data" style="font-size:32px;">
<img src="logo.jpg" height="30" width="300"/>
<h2 style="text-align:center;">' . get_string('apl_form', 'local_programs') . '</h2>

<table style="padding-left:80px;" width="750">
<tr><td>' . get_string('schoolname', 'local_collegestructure') . ' : ' . $schoolname . '</td><td>' . get_string('programname', 'local_programs') . ' :' . $Pgmname . '</td></tr><br>
<tr><td>' . get_string('typeofprogram', 'local_programs') . ' : ' . $pgm . '</td><td>' . get_string('doa', 'local_admission') . ' :' . $today . '</td></tr><br>
<tr><td colspan="2"><h3>' . get_string('appdetails', 'local_admission') . '</h3></td></tr>
<tr><td colspan="2"><hr></td></tr>
<tr><td>' . get_string('adtype', 'local_admission') . '</td>
<td><input type="checkbox" name="newapplicant" value="newapplicant"> ' . get_string('newapp', 'local_admission') . ' <input type="checkbox" name="tapplicant" value="tapplicant"> ' . get_string('traapp', 'local_admission') . '</td></tr>
<br>
<tr><td>Student Type</td><td><input type="checkbox" name="local" value="local"> ' . get_string('localstu', 'local_admission') . '
<input type="checkbox" name="mature" value="mature"> ' . get_string('matstu', 'local_admission') . ' <br>
<input type="checkbox" name="international" value="international"> ' . get_string('interstu', 'local_admission') . '
</td></tr>
<br>
<tr><td colspan="2"><h3>' . get_string('nameheading', 'local_admission') . '</h3></td></tr>
<tr><td colspan="2"><hr></td></tr>
<tr>
<td>' . get_string('firstname', 'local_admission') . '</td>
<td><input type="text" name="firstname"  size="20"/></td>
</tr><br>
<tr>
<td>' . get_string('middlename', 'local_admission') . '</td>
<td><input type="text" name="middlename"  size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('lastname', 'local_admission') . '</td>
<td><input type="text" name="lastname"  size="20" /></td>
</tr><br>
<tr><td colspan="2"><h3>' . get_string('genderheading', 'local_admission') . '</h3></td></tr>
<tr><td colspan="2"><hr></td></tr>

<tr>
<td>' . get_string('genderheading', 'local_admission') . '</td>
<td><input type="radio" name="gender" id="rqa" value="Female" />' . get_string('female', 'local_admission') . '
<input type="radio" name="gender" id="rqa" value="Male" />' . get_string('male', 'local_admission') . '
</td>
</tr><br>
<tr><td colspan="2"><h3>' . get_string('dobheading', 'local_admission') . '</h3></td></tr>
<tr><td colspan="2"><hr></td></tr>

<tr>
<td>' . get_string('dobheading', 'local_admission') . '</td>
<td><input type="text" name="dob"  size="20" /></td>
</tr><br>
<tr><td colspan="2"><h3>' . get_string('countryheading', 'local_admission') . '</h3></td></tr>
<tr><td colspan="2"><hr></td></tr>

<tr>
<td>' . get_string('countryheading', 'local_admission') . '</td>
<td><input type="text" name="cob"  size="20" /></td>
</tr><br>
<tr><td colspan="2"><h3>' . get_string('placeheading', 'local_admission') . '</h3></td></tr>
<tr><td colspan="2"><hr></td></tr>

<tr>
<td>' . get_string('placeheading', 'local_admission') . '</td>
<td><input type="text" name="pob"  size="20" /></td>
</tr><br>
<tr><td colspan="2"><h3>' . get_string('addressheading', 'local_admission') . '</h3></td></tr>
<tr><td colspan="2"><hr></td></tr>
<!--<tr><td colspan="2">' . get_string('addressheading', 'local_admission') . '</td></tr>-->
<tr>
<td>' . get_string('fathername', 'local_admission') . '</td>
<td><input type="text" name="fathername"  size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('pincode', 'local_admission') . '</td>
<td><input type="text" name="pobox"  size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('region', 'local_admission') . '</td>
<td><input type="text" name="region"  size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('town', 'local_admission') . '</td>
<td><input type="text" name="town"  size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('hno', 'local_admission') . '</td>
<td><input type="text" name="hno"  size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('pcountry', 'local_admission') . '</td>
<td><input type="text" name="country"  size="20" /></td>
</tr><br>
<tr><td colspan="2"><h3>' . get_string('personalinfo', 'local_admission') . '</h3></td></tr>
<tr><td colspan="2"><hr></td></tr>

<tr>
<td>' . get_string('phone', 'local_admission') . '</td>
<td><input type="text" name="mobileno"  size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('email', 'local_admission') . '</td>
<td><input type="text" name="email"  size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('howlong', 'local_admission') . '</td>
<td><input type="text" name="howlong" size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('same', 'local_admission') . '</td>
<td><input type="checkbox" name="yes" value="yes"> Yes <input type="checkbox" name="no" value="no"> No</td>
</tr><br>
<tr><td colspan="2"><h3>' . get_string('primaryschool', 'local_admission') . '</h3></td></tr>
<tr><td colspan="2"><hr></td></tr>
<tr>
<td>' . get_string('schoolid', 'local_collegestructure') . '</td>
<td><input type="text" name="ps" size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('primaryyear', 'local_admission') . '</td>
<td><input type="text" name="py" size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('primaryscore', 'local_admission') . '</td>
<td><input type="text" name="pscore" size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('pnc', 'local_admission') . '</td>
<td><input type="text" name="pplace" size="20" /></td>
</tr><br>
<tr><td colspan="2"><h3>' . get_string('undergraduat', 'local_admission') . '</h3></td></tr>
<tr><td colspan="2"><hr></td></tr>
<tr>
<td>' . get_string('ugin', 'local_admission') . '</td>
<td><input type="text" name="ugin" size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('schoolid', 'local_collegestructure') . '</td>
<td><input type="text" name="ss" size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('primaryyear', 'local_admission') . '</td>
<td><input type="text" name="sy" size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('primaryscore', 'local_admission') . '</td>
<td><input type="text" name="sscore" size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('pnc', 'local_admission') . '</td>
<td><input type="text" name="splace" size="20" /></td>
</tr><br>
<tr><td colspan="2"><h3>' . get_string('fulladdressheading', 'local_admission') . '</h3></td></tr>
<tr><td colspan="2"><hr></td></tr>
<tr>
<td>' . get_string('contactname', 'local_admission') . '</td>
<td><input type="text" name="coname"  size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('pincode', 'local_admission') . '</td>
<td><input type="text" name="ppobox"  size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('city', 'local_admission') . '</td>
<td><input type="text" name="city"  size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('state', 'local_admission') . '</td>
<td><input type="text" name="state"  size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('hno', 'local_admission') . '</td>
<td><input type="text" name="phno"  size="20" /></td>
</tr><br>
<tr>
<td>' . get_string('pcountry', 'local_admission') . '</td>
<td><input type="text" name="pcountry"  size="20" /></td>
</tr><br>
</table>
</form>
<br><br>
<input type="checkbox" value="yes" name="self"> ' . get_string('declare', 'local_admission') . '<br><br>
Signature:                                  
';
$pdf->writeHTML($html, true, 0, true, 0);
ob_clean();
$pdf->Output('application.pdf', 'I');

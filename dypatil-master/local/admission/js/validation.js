function validation(a,b)
{
if(a==0)
{
alert("Please select Admission Type");
return false;
}
if(b==0)
{
alert("Please select Student Type");
return false;
}
}
//
function checklist(a)
{

if(a==0)
{
alert("Please Select Filters to Accept an Applicant");
return false;
}
var chks = document.getElementsByName('check_list[]');
var hasChecked = false;
for (var i = 0; i < chks.length; i++)
{
if (chks[i].checked)
{
hasChecked = true;
break;
}
}
if (hasChecked == false)
{
alert("Please select Applicant");
return false;
}
}
function check(a,b)
{

if(a==0) {
alert("Please Select Filters to Accept an Applicant");
return false;
}
else
{
window.location.href = "accept.php?cur="+a+"&id="+b;
}
}
function checkcurculum()
{

var a=document.getElementById('cur').value;
if(a==0) {
alert("Please select curculum");
return false;
}
}
function check_all() {

var a=document.getElementsByTagName('select')[0].value;
var b=document.getElementsByTagName('select')[1].value;
var c=document.getElementsByTagName('select')[2].value;
if(a=='') {
alert("Please select school");
return false;
}
if(b=='') {
alert("Please select program");
return false;
}
if(c=='') {
alert("Please select curculum");
return false;
}
}
function crculumenable() {
document.getElementsByTagName('select')[0].removeAttribute("disabled");
}




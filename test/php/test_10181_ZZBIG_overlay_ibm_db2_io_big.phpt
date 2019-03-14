--TEST--
XML i Toolkit: IBM_DB2 inout SRVPGM - Big data
--SKIPIF--
<?php require_once('skipifdb2.inc'); ?>
--FILE--
<?php
// see connection.inc param details ...
require_once('connection.inc');
// call IBM i
if ($i5persistentconnect) $conn = db2_pconnect($database,$user,$password);
else $conn = db2_connect($database,$user,$password);
if (!$conn) die("Bad connect: $database,$user");
$stmt = db2_prepare($conn, "call $procLib.iPLUG512K(?,?,?,?)");
if (!$stmt) die("Bad prepare: ".db2_stmt_errormsg());
$clobIn = getxml();
$clobOut = "";
$ret=db2_bind_param($stmt, 1, "ipc", DB2_PARAM_IN);
$ret=db2_bind_param($stmt, 2, "ctl", DB2_PARAM_IN);
$ret=db2_bind_param($stmt, 3, "clobIn", DB2_PARAM_IN);
$ret=db2_bind_param($stmt, 4, "clobOut", DB2_PARAM_OUT);
$ret=db2_execute($stmt);
if (!$ret) die("Bad execute: ".db2_stmt_errormsg());
// -----------------
// output processing
// -----------------
// dump raw XML (easy test debug)
// var_dump($clobOut);
// xml check via simplexml vs. expected results
$xmlobj = simplexml_load_string($clobOut);
if (!$xmlobj) die("Bad XML returned");
$allpgms = $xmlobj->xpath('/script/pgm');
if (!$allpgms) die("Missing XML pgm info");
// -----------------
// output pgm call
// -----------------
// only one program this XML script
$pgm = $allpgms[0];
$name = $pgm->attributes()->name;
$lib  = $pgm->attributes()->lib;
$func = $pgm->attributes()->func;
// pgm parms
$parm = $pgm->xpath('overlay');
if (!$parm) die("Missing XML pgm parms ($lib/$name.$func)\n");
$cnt = count($parm);
if ($cnt <> 3) die("Missing 3 overlays (count = $cnt)\n");
for ($i=1;$i<=count($parm);$i++) {
  $top = "";
  if (isset($parm[$i-1]->attributes()->top)) {
    $top = "top=" . $parm[$i-1]->attributes()->top;
  }
  $cmt  = $parm[$i-1]->attributes()->comment;
  $off  = $parm[$i-1]->attributes()->offset;
  $var  = $parm[$i-1]->data->attributes()->var;
  $data = (string)$parm[$i-1]->data;
  echo "$i) $var offset=$off $top $cmt = $data ($lib/$name.$func)\n";
}

// good
echo "Success ($lib/$name.$func)\n";

//     D zzbig           PR            10I 0
//     D  myNam1                    32000A
//     D  myNam2                    32000A
//     D  myNam3                    32000A
//     D  myNam4                    32000A
//     D  myNam5                    32000A
//     D  myNam6                    32000A
//     D  myNam7                    32000A
//     D  myNam8                    32000A
//     D  myNam9                    32000A
//      *+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//      * zzbig: check big
//      *+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//     P zzbig           B                   export
//     D zzbig           PI            10I 0
//     D  myNam1                    32000A
//     D  myNam2                    32000A
//     D  myNam3                    32000A
//     D  myNam4                    32000A
//     D  myNam5                    32000A
//     D  myNam6                    32000A
//     D  myNam7                    32000A
//     D  myNam8                    32000A
//     D  myNam9                    32000A
function getxml() {
$clob = <<<ENDPROC
<?xml version='1.0'?>
<script>
<pgm name='ZZSRV' lib='xyzlibxmlservicexyz' func='ZZBIG'>
 <parm io='in'>
  <data var='myNam1' type='32000A'>Ranger1</data>
 </parm>
 <parm io='in'>
  <data var='myNam2' type='32000A'>Ranger2</data>
 </parm>
 <parm io='in'>
  <data var='myNam3' type='32000A'>Ranger3</data>
 </parm>
 <parm io='in'>
  <data var='myNam4' type='32000A'>Ranger4</data>
 </parm>
 <parm io='in'>
  <data var='myNam5' type='32000A'>Ranger5</data>
 </parm>
 <overlay io='out' offset='0' comment='prev parm'>
   <data var='myOver5' type='10A'>R5</data>
 </overlay>
 <parm io='in'>
  <data var='myNam6' type='32000A'>Ranger6</data>
 </parm>
 <parm io='in'>
  <data var='myNam7' type='32000A'>Ranger7</data>
 </parm>
 <parm io='in'>
  <data var='myNam8' type='32000A'>Ranger8</data>
 </parm>
 <parm io='in'>
  <data var='myNam9' type='32000A'>Ranger9</data>
 </parm>
 <overlay io='out' offset='0' top='on' comment='top parm'>
   <data var='myOver1' type='10A'>R1</data>
 </overlay>
 <overlay io='out' offset='192000' top='on' comment='middle parms'>
   <data var='myOverx' type='10A'>R1</data>
 </overlay>
</pgm>
</script>
ENDPROC;
return test_lib_replace($clob);
}
?>
--EXPECTF--
%s
Success (%s)


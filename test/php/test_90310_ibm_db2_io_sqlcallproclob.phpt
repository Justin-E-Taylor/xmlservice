--TEST--
XML i Toolkit: IBM_DB2 inout SQL - stored proc lob animal1
--SKIPIF--
<?php require_once('skipifdb2.inc'); ?>
--FILE--
<?php
// see connection.inc param details ...
require_once('connection.inc');

// -------------
// call IBM i
// -------------
if ($i5persistentconnect) $conn = db2_pconnect($database,$user,$password);
else $conn = db2_connect($database,$user,$password);
if (!$conn) die("Bad connect: $database,$user");
$stmt = db2_prepare($conn, "call $procLib.iPLUG15M(?,?,?,?)");
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
// output processing (XML input info)
// -----------------
// dump raw XML (easy test debug)
// var_dump($clobIn);
$xmlobj = simplexml_load_string($clobIn);
if (!$xmlobj) die("Bad XML input");
// -----------------
// output processing
// -----------------
// dump raw XML (easy test debug)
// var_dump($clobOut);
// xml check via simplexml vs. expected results
$xmlobj = simplexml_load_string($clobOut);
if (!$xmlobj) die("Bad XML returned");
$len = strlen($clobOut);
if ( $len < 149000 && $len > 160000 ) die("Wrong size output $len \n");
else echo "size in range 149000 < $len < 160000 \n";

// good
echo "Success\n";

function getxml() {
$clob = <<<ENDPROC
<?xml version='1.0'?>
<script>
<sql>
<prepare>call match2(?, ?, ?, ?)</prepare>
<execute>
<parm io='in'>1</parm>
<parm io='in'>5</parm>
<parm io='out'>frog is wrong</parm>
<parm io='out'>9</parm>
</execute>
<fetch block='all' desc='on'/>
</sql>
</script>
ENDPROC;
return $clob;
}
?>
--EXPECTF--
%s
Success



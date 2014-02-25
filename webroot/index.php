<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script>
        var UID = <?php echo substr(time(),-5);?>;
        var SERVERIP = '127.0.0.1';
        var SERVERPORT = '8991';
        var SOURCEURL = 'http://www.gamble.com/webroot/gambleflash/';
    </script>
    <script type="text/javascript" src="./js/jquery.js"></script>
    <script type="text/javascript" src="./js/swfobject/swfobject.js"></script>
    <script type="text/javascript" src="./js/global.js?<?php echo time() ?>"></script>
</head>

<body>
<!--load swf-->
<div id="main"><p style="text-align: center;">Loading...</p></div>
<script type="text/javascript">
    Global.LoadSwf();
</script>

</body>
</html>
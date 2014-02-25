<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>test js socket</title>
    <script type="text/javascript" src="jquery-1.9.1.js"></script>
    <script type="text/javascript" src="jsocket.js"></script>
    <script type="text/javascript" src="swfobject.js"></script>
</head>
<body>

<body>
<div id="connect" style="display:none;">connect to server sucess!</div>

<div>
Api:
<input type='text' size=50 id='api' value="" />eg: (user\\login)
<br />
arg:
<input type='text' size=100 id='arg' value = "" />
<input type='button' value='CALL' onclick='call();'>eg: {"uid":"1","params":{"uid":"1"}}
<br>
<input type="button" value="点击平滑重启服务器" onclick="javascript:reloadSevr();">
<input type="button" value="点击关闭服务器" onclick="javascript:shutdownSevr();">
</div>

<div id='mySocket'/>

<script type='text/javascript'>
//    var host = '10.0.0.6';
    var host = '127.0.0.1';
    var port = 8991;
    var socket = new jSocket();

    socket.onReady = function(){
        socket.connect(host, port);
    }

    socket.onConnect = function(success, msg){
        if(success){
            $("#connect").show();
        }else{
            alert('Connection to the server could not be estabilished: ' + msg);
        }
    }
    socket.onData = function(data){
        console.log((data));

    }

    // Setup our socket in the div with the id="socket"
    socket.setup('mySocket');
</script>


<script type='text/javascript'>
    function call() {
        var api = $.trim($("#api").val());
        var arg = $.trim($('#arg').val());
        if (api == '') {
            alert('params is wrong');
            return;
        }
        if (arg) {
            var args = "{\"a\":\"" + api + "\", \"p\":" + arg + "}";
        } else {
            var args = "{\"a\":'" + api + "'}";
        }
        console.log(args);
//        var str = '{"a":"user\\\\login", "p":{"uid":"1000","params":{"uid":"10000"}}}';
//        console.log(str);
//        return;
        socket.write(args);
    }

    function reloadSevr(){
        var post = JSON.stringify({"a":"server\\reload"});
        socket.write(post);
    }
    function shutdownSevr(){
        var post = JSON.stringify({"a":"server\\shutdown"});
        socket.write(post);
    }
</script>

</body>

</body>
</html>
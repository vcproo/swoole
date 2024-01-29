<?php
// header('Access-Control-Allow-Origin:*');
//创建WebSocket Server对象，监听0.0.0.0:9502端口。
$server = new Swoole\WebSocket\Server('0.0.0.0', 9502);
//用于存储设备id和客户端的关系
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$redis_key_name = "msgUserList";
$redis->del($redis_key_name); 
//监听WebSocket连接打开事件。
$server->on('Open', function ($ws, $request) {
    $name = '用户'.rand(10000,99999);
    global $server;//调用外部的server
    global $redis;
    global $redis_key_name;
    $redis->hSet($redis_key_name,$request->fd,$name);
    $ws->push($request->fd, json_encode(array('name'=>$name,'fd'=>$request->fd,'type'=>'init','msg'=>'建立连接')));
    //向所有用户发送该用户进入房间
    foreach ($server->connections as $fd) {
        // 需要先判断是否是正确的websocket连接，否则有可能会push失败
        if ($server->isEstablished($fd)) {
            if($request->fd == $fd){
                $server->push($fd, json_encode(array('type'=>'enter','fd'=>$request->fd,'name'=>$name,'msg'=>'你已进入房间')));
            }else{
                $server->push($fd, json_encode(array('type'=>'enter','fd'=>$request->fd,'name'=>$name,'msg'=>$name.' 进入房间')));
            }
           
        }
    }
    // echo "Open: 建立连接\n";
});

//监听WebSocket消息事件。
$server->on('Message', function (Swoole\WebSocket\Server $sw, $frame) {
    // echo "Message: {$frame->data}\n";
    
    $data = json_decode($frame->data);
    global $server;//调用外部的server
    //发送消息
    if($data->type == 'msg'){
        foreach ($server->connections as $fd) {
            // 需要先判断是否是正确的websocket连接，否则有可能会push失败
            if ($server->isEstablished($fd)) {
                $server->push($fd, json_encode(array('type'=>'msg','fd'=>$data->fd,'name'=>$data->name,'msg'=>$data->msg)));
            }
        }
    }
 
});

//监听WebSocket连接关闭事件。
$server->on('Close', function ($ws, $fd) {
    global $redis;
    global $server;//调用外部的server
    global $redis_key_name;//调用外部的server
    // echo "client-{$fd} is closed\n";
    $msguserlist = $redis->hgetall($redis_key_name);
    $redis->hDel($redis_key_name,$fd);
    foreach ($server->connections as $fd_u) {
        // 需要先判断是否是正确的websocket连接，否则有可能会push失败
        if ($server->isEstablished($fd_u)) {
            if($fd_u != $fd){
                $server->push($fd_u, json_encode(array('type'=>'close','fd'=>$fd,'name'=>$msguserlist[$fd],'msg'=>$msguserlist[$fd].' 离开了房间')));
            }
        }
    }
    
});
$server->start();
?>

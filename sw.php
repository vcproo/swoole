<?php
require 'db.php';
// header('Access-Control-Allow-Origin:*');
//创建WebSocket Server对象，监听0.0.0.0:9502端口。
$server = new Swoole\WebSocket\Server('0.0.0.0', 9502);
//用于存储设备id和客户端的关系
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$redis_key_name = "fd_device";
$redis->del($redis_key_name); 
//监听WebSocket连接打开事件。
$server->on('Open', function ($ws, $request) {
   
    $ws->push($request->fd, json_encode(array('type'=>'init_open','success'=>true,'msg'=>'建立连接','data'=>["fd"=>$request->fd])));
    echo "Open: 建立连接\n";
});
//付款后主动推送
$server->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
    //记录日志
    file_put_contents('./requestLog.txt', json_encode($request->post). "\n", FILE_APPEND | LOCK_EX);
    echo "request: 接收请求\n";
    global $server;//调用外部的server
    global $conn;
    global $redis;
    global $redis_key_name;
    $server->isEstablished($request->post['device_id']);
    //获取
    // var_dump($request->post['device_id']);
    $fd_device = $redis->hgetall($redis_key_name);
    if(isset($request->post['device_id'])){
        if($request->post['device_id']){
            
            //判断该设备是否存在绑定关系
            if (array_key_exists('device_'.$request->post['device_id'], $fd_device)) {
                //获取fd 
                $fdstr = $fd_device['device_'.$request->post['device_id']];
                if($fdstr){
                    $fd = explode('_',$fdstr)[1];
                    //需要先判断是否是正确的websocket连接，否则有可能会push失败
                    if ($server->isEstablished($fd)) {
                        $server->push($fd, json_encode(array('type'=>'pay_success','device_id'=>$request->post['device_id'],'msg'=>'有人支付成功-'.$fd)));
                    }else{
                        //推送失败记录日志
                        file_put_contents('./error.txt', 'code:10001'.' 失败原因：当前fd('.$fd.')websocket连接不正确。'.' 参数：'.json_encode($request->post). "\n", FILE_APPEND | LOCK_EX);
                    }
                }else{
                    //断开链接或者没建立连接记录日志
                    file_put_contents('./error.txt', 'code:10002'.' 失败原因：并没有找到该设备('.$request->post['device_id'].')的fd。'.' 参数：'.json_encode($request->post). "\n", FILE_APPEND | LOCK_EX);
                }
            } else {
               //断开链接或者没建立连接记录日志
               file_put_contents('./error.txt', 'code:10002'.' 失败原因：并没有找到该设备('.$request->post['device_id'].')的fd。'.' 参数：'.json_encode($request->post). "\n", FILE_APPEND | LOCK_EX);
            }
           
        }
       
    }
    
    // var_dump($fd_device);
    // foreach ($server->connections as $fd) {
    //     // 需要先判断是否是正确的websocket连接，否则有可能会push失败
    //     if ($server->isEstablished($fd)) {
    //         $device_id = $serv->getClientInfo($fd);
    //         echo "request: {$device_id}\n";
    //     }
    // }

    // $sql = "SELECT t1.fd_id,t1.device_id FROM `device_fd` AS t1,`order` AS t2 where t1.device_id = t2.device_id and t1.is_delete = 1 and t2.is_delete = 1 and t2.pay_status = 1";
    // $sql = "SELECT device_id FROM `order` where is_delete = 1 and pay_status = 1";
    // $res = $conn->query($sql);
    
    // $list = $res->fetch_all(MYSQLI_ASSOC);
    // if(!empty($list)){
    //     foreach($list as $key=>$value){
    //         // 需要先判断是否是正确的websocket连接，否则有可能会push失败
    //         if ($server->isEstablished($value['fd_id'])) {
    //             $server->push($value['fd_id'], json_encode(array('type'=>'pay_success','device_id'=>$value['device_id'])));
    //         }
    //     }
    // }
    // $server->connections 遍历所有websocket连接用户的fd，给所有用户推送
    // foreach ($server->connections as $fd) {
    //     // 需要先判断是否是正确的websocket连接，否则有可能会push失败
    //     if ($server->isEstablished($fd)) {
    //         $server->push($fd, $request->get['message']);
    //     }
    // }
});

//监听WebSocket消息事件。
$server->on('Message', function (Swoole\WebSocket\Server $sw, $frame) {
    echo "Message: {$frame->data}\n";
  
    //将fd和设备id存入数据库
    global $conn;
    global $redis;
    global $server;//调用外部的server
    global $redis_key_name;
    $server->push($frame->fd, json_encode(array('type'=>'message','success'=>true,'msg'=>'接收到消息')));
    $data = json_decode($frame->data);
  
    //绑定设备和fd
    if($data->type == 'init_device_fd'){
        // 将设备ID与fd进行关联
        $sw->bind($frame->fd,  $data->device_id);
        // 将设备ID和FD的映射关系存储到Redis中
        $redis->hSet($redis_key_name,'device_'.$data->device_id, 'fd_'.$frame->fd);
        // $server->push($frame->fd, "bind {$data->device_id} success");
    //     // 设置时区
    //     date_default_timezone_set('Asia/Shanghai');
    //     $time   =date('Y-m-d H:i:s');
    //     $sql = "UPDATE device_fd SET is_delete = 0 ,create_time='{$time}' where device_id = '{$data->device_id}'";
    //     $conn->query($sql);
    //     $sql = "INSERT INTO device_fd (`device_id`,`fd_id`) VALUES ('{$data->device_id}','{$frame->fd}')";
    //     $res = $conn->query($sql);
    }
});

//监听WebSocket连接关闭事件。
$server->on('Close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
    global $redis;
    global $server;//调用外部的server
    global $redis_key_name;//调用外部的server
    if(isset($server->getClientInfo($fd)['uid'])){
        $device_id = $server->getClientInfo($fd)['uid'];
        $redis->hDel($redis_key_name, 'device_'.$device_id);
    }
    
});
$server->start();
?>

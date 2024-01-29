<?php
// 要发送请求的URL
// $url = 'http://192.165.0.37/acc/2.php';


// 关闭cURL会话
// curl_close($curl);
//创建Server对象，监听 127.0.0.1:9501 端口。
// $http = new Swoole\Http\Server('0.0.0.0', 9502);

// $http->on('Request', function ($request, $response) {
//     $response->header('Content-Type', 'text/html; charset=utf-8');
//     $response->end('<h1>Hello Swoole. #' . rand(1000, 9999) . '</h1>');
// });

// $http->start();
//  $url = 'http://192.168.68.128:9502';

// //  // 要传递的参数
//  $params = array();

//  // 初始化cURL会话
//  $curl = curl_init();

//  // 设置cURL选项
//  curl_setopt($curl, CURLOPT_URL, $url);
//  curl_setopt($curl, CURLOPT_PORT, 9502); // 设置端口号为8080
//  curl_setopt($curl, CURLOPT_POST, true);
//  curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
//  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

//  // 发送POST请求并获取响应
//  $response = curl_exec($curl);
// var_dump($response);
//  // 检查请求是否成功
//  if ($response === false) {
//      $error = curl_error($curl);
//      // 处理错误
//      echo 'cURL Error: ' . $error;
//  } else {
//      // 处理响应
//     //  echo 'Response: ' . $response;
//     //  echo 'Response: ' . $response;
//  }

//  // 关闭cURL会话
//  curl_close($curl);
// $curl = curl_init();
// curl_setopt($curl, CURLOPT_URL, 'http://192.168.68.128:9502');
// curl_setopt($curl, CURLOPT_PORT, 9502);
// curl_setopt($curl, CURLOPT_POST, true);
// curl_setopt($curl, CURLOPT_POSTFIELDS, 'param1=value1&param2=value2');
// curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
// $result = curl_exec($curl);

// if ($result === false) {
//     $error = curl_error($curl);
//     $error_code = curl_errno($curl);
//     echo "Curl request failed. Error: $error. Error code: $error_code";
// }

// curl_close($curl);
$device_id = rand(1,4);
apiPost($device_id);
function apiPost($device_id){
    // 要发送请求的URL
    $url = 'http://192.165.1.209';

    // 要传递的参数
    $params = array(
        'device_id' => $device_id
    );

    // 初始化cURL会话
    $curl = curl_init();

    // 设置cURL选项
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_PORT, 9502); // 设置端口号为8080
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // 发送POST请求并获取响应
    $response = curl_exec($curl);

    // 检查请求是否成功
    if ($response === false) {
        $error = curl_error($curl);
        // 处理错误
        echo 'cURL Error: ' . $error;
    } else {
        // 处理响应
        echo $device_id;
        // echo 'Response: ' . $response;
    }

    // 关闭cURL会话
    curl_close($curl);
}


?>
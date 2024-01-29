## 环境
linux centos7 apache php8 redis
## 注意事项
- 开启端口并对外开放
- php安装swoole扩展 
## 目录结构
sw.php 在命令窗口执行 开启swoole服务
    ```xshell
    php sw.php
    ```
web.html web2.html web3.html web4.html 建立websocket服务
request.php 是php发起的给swoole发起的request请求
## 说明
sw.php是php开启的swoole服务，这个是想要建立websocket服务的，需要sw.php和web.html相互配合。目的是想要进行实时通讯，比如现在开启sw.php web.html web2.html web3.html web4.html 这时前台和服务器就可以互相通讯了
现在模拟的是 web web3 web2 web4代表了四个不同的设备。在运行 sw.php后并分别在浏览器上打开web web2 web3 web4 那么就会在服务器建立了长链接。并分别将各自设备的客户端id（fd）与设备id绑定并存到redis中（由于断开连接后重新连接客户端的id（fd）会改变，所以存到redis中，便于快速获取。也可以存在数据库或其他方式）。现在swoole和websocket的长连接就建立好了。如果此时需要支付或者其他外界因素来控制机器，比如用户扫码支付，那支付的逻辑时在手机上完成的已经脱离了设备，所以设备不能确定在什么时候去请求服务器，而是服务器在接收到用户支付成功后主动推给目标设备，设备接收到消息后从而开启设备使用设备。(用户扫码支付肯定是带有设备id的要不然后台也不能确定是哪个设备)。这时request.php这个文件就是模拟支付成功后，给设备发送信息的逻辑，其实就是一个curl请求，ip+端口（swoole的端口），这时swoole.php中的request方法就监听到request.php的请求，这样swoole和request请求也建立了关系。这样以后无论是扫码支付开启设备，还是app平板开启设备，只需要通过curl请求即可间接通过swoole来控制设备。
## 其他
swmsg.php 
message.html
实现了简易聊天室的demo 运行方式同样是在命令窗口执行swmsg.php,浏览器打开message.html，多个人都可以一起使用，一起聊天。


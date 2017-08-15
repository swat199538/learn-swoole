<?php
//websocket0.0.0.0:9502
$ws = new swoole_websocket_server("0.0.0.0", 9502);

////WebSocket
//$ws->on('open', function ($ws, $request) {
//    var_dump($request);
//    $ws->push($request->fd, "hello, welcome\n");
//});

//websocket握手
$ws->on('handshake', function (\swoole_http_request $request, \swoole_http_response $response){

    print_r($request->get);
    // if (如果不满足我某些自定义的需求条件，那么返回end输出，返回false，握手失败) {
    //    $response->end();
    //     return false;
    // }

    // websocket握手连接算法验证
    $secWebSocketKey = $request->header['sec-websocket-key'];
    $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
    if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
        $response->end();
        return false;
    }
    echo $request->header['sec-websocket-key'];
    $key = base64_encode(sha1(
        $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
        true
    ));

    $headers = [
        'Upgrade' => 'websocket',
        'Connection' => 'Upgrade',
        'Sec-WebSocket-Accept' => $key,
        'Sec-WebSocket-Version' => '13',
    ];

    // WebSocket connection to 'ws://127.0.0.1:9502/'
    // failed: Error during WebSocket handshake:
    // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
    if (isset($request->header['sec-websocket-protocol'])) {
        $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
    }

    foreach ($headers as $key => $val) {
        $response->header($key, $val);
    }

    $response->status(101);
    $response->end();
    echo "connected!" . PHP_EOL;
    return true;
});


//WebSocket
$ws->on('message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
    $ws->push($frame->fd, "server: {$frame->data}");
});

//WebSocket
$ws->on('close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();

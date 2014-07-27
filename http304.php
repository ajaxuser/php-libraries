<?php
//当前时间
$current_time = time();
//缓存时间
$max_age = 10;
//存在于HTTP_IF_MODIFIED_SINCE并且当前时间小于此时间max-age时候
if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $current_time - strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) < $max_age) {
    header("Last-Modified: " . $_SERVER['HTTP_IF_MODIFIED_SINCE']);
    header("Cache-Control: " . $max_age);
    header("HTTP/1.1 304 Not Modified");
    exit();
}

$last_modified = gmdate('D, d M Y H:i:s', $current_time). ' GMT';
header("Last-Modified: " . $last_modified);
header("Cache-Control: " . $max_age);

//以下为代码逻辑
header("Content-Type:text/html;charset=utf-8");
echo "<html><body><h1>Hello World!</h1></body></html>";

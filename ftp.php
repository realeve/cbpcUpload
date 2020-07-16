<?php
include 'ftp_lib.php';

/*	libin
    2020-07-15: 上传PDF文件至MES服务器；
*/

$config = [
    'host'=>'mes.cdyc.cbpm',
    'user'=>'ftper',
    'pass'=>'5guang10se',
    'port'=>'2001'
];
$dirName = "nepal/" ;

function createNonceStr($length = 32)
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

function getFtp()
{
    global $config;
    $ftp = new FtpConn($config);
    $result = $ftp->connect();
    if (!$result) {
        handleErr($ftp->get_error_msg());
        return false;
    }
    return $ftp;
}

function handlePost()
{
    global $dirName;
    /*
    $conn_id = ftp_connect('10.9.3.5',2001);
    $login_result = ftp_login($conn_id, 'ftper', '5guang10se');
    */

    $ftp = getFtp();
    if (!$ftp) {
        return;
    }
    if (!$_FILES || !$_FILES["file"]) {
        echo '{"status":"0","msg":"未读取到文件"}';
        return;
    }

    // 500M大小限制
    if ($_FILES["file"]["size"] < 1024 * 1024 * 500) {
        if ($_FILES["file"]["error"] > 0) {
            echo '{"status":"0","msg":"文件类型或大小错误"}';
        } else {
            $file = $_FILES["file"];
            $name = $file["name"];
            $filename = $file["name"];

            if (strpos($filename, '.')==-1) {
                $fileType = '';
                $arr = explode('/', $file['type']);
                $fileType = '.'.$arr[count($arr)-1];
                $filename = time().'_'.createNonceStr(8).$fileType;
            }

            $remote_file = $dirName.$filename;
            $return['msg'] = "上传失败";
            if ($ftp->upload($file['tmp_name'], $remote_file)) {
                $return['msg'] = "上传成功";
            }
            $return['url'] = $remote_file;
            $return['status'] = 1;
            $return['name'] = $name;

            // 关闭链接
            $ftp->close();

            echo json_encode($return);
        }
    }
}

function handleErr($error='上传文件失败')
{
    $return['status'] = 0;
    $return['msg'] = $error;
    echo json_encode($return);
}

// 删除文件
// URL: http://10.8.1.25/ftp?name=NRB10_B82000001_20200715.pdf
function handleGet()
{
    global $dirName;
    if (isset($_GET['name'])) {
        $ftp = getFtp();
        if (!$ftp) {
            return;
        }
        $filename = $dirName.$_GET['name'];
        if ($ftp->delete_file($filename)) {
            $return['status'] = 1;
            $return['msg'] = '文件删除成功';
        } else {
            $return['status'] = 0;
            $return['msg'] = '文件'.$filename.'删除失败';
        }
        $ftp->close();
    } else {
        $return['status'] = 0;
        $return['msg'] = '请求参数错误，需要指定name字段';
    }

    if (isset($_GET['callback'])) {
        echo $_GET['callback'].'('.json_encode($return).')';
    } else {
        echo json_encode($return);
    }
}

function init()
{
    $requestType = $_SERVER['REQUEST_METHOD'];
    // 指定允许其他域名访问 // 正式布署请设置为前端资源主站

    // 响应类型
    header('Access-Control-Allow-Methods:GET,POST,PUT,OPTIONS');
    header('Access-Control-Allow-Headers:x-requested-with,content-type');

    if ($requestType == "OPTIONS") {
        header('Access-Control-Allow-Credentials:true');
        header('Access-Control-Max-Age:1728000');
        header('Content-Type:text/plain charset=UTF-8');
        header('Content-Length: 0', true);
        header("status: 204");
        header("HTTP/1.0 204 No Content");
    } else {
        header("Content-type: application/json");
        if ($requestType == "POST") {
            handlePost();
        } elseif ($requestType == "GET") {
            handleGet();
        } else {
            handleErr();
        }
    }
}

init();

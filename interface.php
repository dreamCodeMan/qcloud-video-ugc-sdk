<?php

/**
 * @Author: 吴坚强
 * @Date:   2018-05-05 22:25:53
 * @Last Modified by:   吴坚强
 * @Last Modified time: 2018-05-08 18:06:47
 */

$secret_id  = "AKIDNYVCdoJQyGJ5br******";
$secret_key = "0mIadRzDCwQfeXB*****";

$flag = $_POST['flag'];
//$flag = $flag ? $$flag : $_GET['flag'];

switch ($flag) {
case 'signature':
    $current = time();
    $expired = $current + 86400;

    $arg_list = array(
        "secretId"         => $secret_id,
        "currentTimeStamp" => $current,
        "expireTime"       => $expired,
        "random"           => rand(),
    );

    // 计算签名
    $orignal   = http_build_query($arg_list);
    $signature = base64_encode(hash_hmac('SHA1', $orignal, $secret_key, true) . $orignal);
    $info      = array('code' => 0, 'codeDesc' => 'Success', 'signature' => $signature);
    echo json_encode($info);
    break;
case 'convert':
    $fileId = $_POST['fileId'];
    //$fileId        = $fileId ? $$fileId : $_GET['fileId'];
    $COMMON_PARAMS = array(
        'Nonce'     => rand(),
        'Timestamp' => time(),
        'Action'    => 'ConvertVodFile',
        'SecretId'  => $secret_id,
    );

    $PRIVATE_PARAMS = array(
        'Region' => 'gz',
        'fileId' => $fileId,
    );
    createRequest("vod.api.qcloud.com", "GET", $COMMON_PARAMS, $secret_key, $PRIVATE_PARAMS);
    break;
default:
    echo '参数不对';
}

function createRequest($HttpUrl, $HttpMethod, $COMMON_PARAMS, $secretKey, $PRIVATE_PARAMS) {
    $FullHttpUrl = $HttpUrl . "/v2/index.php";

    /***************对请求参数 按参数名 做字典序升序排列，注意此排序区分大小写*************/
    $ReqParaArray = array_merge($COMMON_PARAMS, $PRIVATE_PARAMS);
    ksort($ReqParaArray);

    $SigTxt = $HttpMethod . $FullHttpUrl . "?";

    $isFirst = true;
    foreach ($ReqParaArray as $key => $value) {
        if (!$isFirst) {
            $SigTxt = $SigTxt . "&";
        }
        $isFirst = false;

        /*拼接签名原文时，如果参数名称中携带_，需要替换成.*/
        if (strpos($key, '_')) {
            $key = str_replace('_', '.', $key);
        }

        $SigTxt = $SigTxt . $key . "=" . $value;
    }

    $Signature = base64_encode(hash_hmac('sha1', $SigTxt, $secretKey, true));

    /***************拼接请求串,对于请求参数及签名，需要进行urlencode编码********************/
    $Req = "Signature=" . urlencode($Signature);
    foreach ($ReqParaArray as $key => $value) {
        $Req = $Req . "&" . $key . "=" . urlencode($value);
    }

    /*********************************发送请求********************************/
    $Req = "https://" . $FullHttpUrl . "?" . $Req;
    $Rsp = file_get_contents($Req);

    echo $Rsp;
}

?>
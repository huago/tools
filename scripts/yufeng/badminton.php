<?php
require('phpQuery/phpQuery/phpQuery.php');

//$date = '20210220';
$date = date('Ymd', strtotime('+4day'));
$pageName = 'page.' . $date . '.html';
$startTime1 = 16;
$startTime2 = 17;

getPageContent($date, $pageName);

$availableFields = getAvailableFields($pageName);

bookFields($availableFields, $date, $startTime1, $startTime2);

function getPageContent($date, $pageName)
{
    $cmd = "curl -H 'Host: webssl.xports.cn' -H 'Content-Type: application/x-www-form-urlencoded; charset=utf-8' -H 'Cookie: JSESSIONID=A53587D9C89C71092DAB37B88F9A2F61; gr_session_id_ade9dc5496ada31e_a642094a-43f6-4fcf-ae72-61451c01fa4e=true; gr_session_id_ade9dc5496ada31e=a642094a-43f6-4fcf-ae72-61451c01fa4e; gr_user_id=95e5ef43-7f73-4ce3-a90d-80872717a6f9; Hm_lpvt_bc864c0a0574a7cabe6b36d53206fb69=1613624620; Hm_lvt_bc864c0a0574a7cabe6b36d53206fb69=1613623902' -H 'Accept: */*' -H 'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 14_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/8.0.2(0x1800022c) NetType/WIFI Language/zh_CN' -H 'Referer: https://webssl.xports.cn/aisports-weixin/court/1101000301/1002/1254/20210218?venueName=%E5%8C%97%E4%BA%AC%E5%A4%A9%E9%80%9A%E8%8B%91%E4%BD%93%E8%82%B2%E9%A6%86&serviceName=%E7%BE%BD%E6%AF%9B%E7%90%83&fullTag=0&defaultFullTag=0' -H 'Accept-Language: zh-cn' -H 'X-Requested-With: XMLHttpRequest' --compressed 'https://webssl.xports.cn/aisports-weixin/court/ajax/1101000301/1002/1254/'" . $date . "'?fullTag=0&curFieldType=1254'";

    exec($cmd, $pageContent);

    file_put_contents($pageName, $pageContent);
}

function bookFields($availableFields, $date, $startTime1, $startTime2)
{
    $i = 0;
    foreach ($availableFields as $fieldNum => $fieldList) {

        $available1 = $available2 = false;
        $field1 = $field2 = array();
        foreach ($fieldList as $field) {
            if ($field['startTime'] == $startTime1) {
                $available1 = true;

                $field1 = $field;
            }

            if ($field['startTime'] == $startTime2) {
                $available2 = true;

                $field2 = $field;
            }
        }

        if ($available1 && $available2) {
            $result = commit($field1, $date);
            if ($result['error'] == 0) {
                $i++;
            }
            $result = commit($field2, $date);
            if ($result['error'] == 0) {
                $i++;
            }
        }

        if ($i >= 4) {
            break;
        }
    }
}

function getAvailableFields($pageName)
{
    $fileContent = file_get_contents($pageName);
    $doc = phpQuery::newDocumentHTML($fileContent);

    phpQuery::selectDocument($doc);

    $halfTime =  pq('div.half-time');
    $doc = phpQuery::newDocumentHTML($halfTime);

    phpQuery::selectDocument($doc);

    $data = array();
    foreach(pq('span') as $span) {
        $state = $span->getAttribute('state');
        if ($state != 0) {
            continue;
        }

        $fieldNum = $span->getAttribute('field-num');
        $data[$fieldNum][] = array(
            'price' => $span->getAttribute('price'),
            'startTime' => $span->getAttribute('start-time') / 2,
            'fieldInfo' => $span->getAttribute('field-segment-id')
        );
    }

    return $data;
}

function commit($field, $date)
{
    $tmp = array(
        'venueId' => 1101000301,
        'serviceId' => 1002,
        'fieldType' => 1254,
        'day' => $date,
        'fieldInfo' => $field['fieldInfo']
    );
    $tmpStr = json_encode($tmp);
    $cmd = "curl -H 'Host: webssl.xports.cn' -H 'Accept: */*' -H 'X-Requested-With: XMLHttpRequest' -H 'Accept-Language: zh-cn' -H 'Content-Type: application/json' -H 'Origin: https://webssl.xports.cn' -H 'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 14_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/8.0.2(0x1800022c) NetType/WIFI Language/zh_CN' -H 'Referer: https://webssl.xports.cn/aisports-weixin/court/1101000301/1002/1254/20210218?venueName=%E5%8C%97%E4%BA%AC%E5%A4%A9%E9%80%9A%E8%8B%91%E4%BD%93%E8%82%B2%E9%A6%86&serviceName=%E7%BE%BD%E6%AF%9B%E7%90%83&fullTag=0&defaultFullTag=0' -H 'Cookie: JSESSIONID=A53587D9C89C71092DAB37B88F9A2F61; gr_session_id_ade9dc5496ada31e_a642094a-43f6-4fcf-ae72-61451c01fa4e=true; gr_session_id_ade9dc5496ada31e=a642094a-43f6-4fcf-ae72-61451c01fa4e; gr_user_id=95e5ef43-7f73-4ce3-a90d-80872717a6f9; Hm_lpvt_bc864c0a0574a7cabe6b36d53206fb69=1613624620; Hm_lvt_bc864c0a0574a7cabe6b36d53206fb69=1613623902' --data-binary '" . $tmpStr . "' --compressed 'https://webssl.xports.cn/aisports-weixin/court/commit'";
    exec($cmd, $result);

    return $result;
}
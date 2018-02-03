<?php

/**
 * Created by PhpStorm.
 * User: kurisu
 * Date: 2018/01/07
 * Time: 21:41
 */

main();

function main()
{

    $conf = require_once('config.php');
    shell_exec('git config user.name ' . $conf['user.name']);
    shell_exec('git config user.email ' . $conf['user.email']);

    shell_exec('git add -A');
    shell_exec('git commit -m "init"');
    shell_exec('git push origin master');

    $arr = [
        '_ _ 3 _ _ 3 _ _ ',
        '_ 3 3 3 3 3 3 _ ',
        '_ 3 1 2 2 1 3 _ ',
        '_ 3 3 3 3 3 3 _ ',
        '3 _ _ 3 3 _ _ _ ',
        '_ 3 3 3 3 3 _ _ ',
        '_ _ _ 3 3 3 _ _ ',
    ];

    //首先获取日志, 获取第一次提交的hash值
    $firstHash = getFirstCommitHash();

    if (empty($arr)) {
        throw new Exception('arr is empty');
    }
    $col = strlen($arr[0]);
    if (round($col / 2) > $conf['colNumber']) {
        throw new Exception('col number more than github calendar col number');
    }

    if ($conf['autoCenter']) {
        $space = round(($conf['colNumber'] - round($col / 2)) / 2);
    } elseif ($conf['beginningBlank']) {
        $space = $conf['beginningBlank'];
    } else {
        $space = 0;
    }

    $targetArr = [];
    foreach ($arr as $row) {
        for ($i = 0; $i < $space; ++$i) {
            $row = '_ ' . $row;
        }
        $tmp = explode(' ', $row);
        array_splice($tmp, count($tmp) - 1, 1);
        $targetArr[] = $tmp;
    }

    $intArr = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9',];
    $firstDate = getFirstDayDate($conf['colNumber']);

    //循环
    foreach ($targetArr as $rowNumber => $row) {
        foreach ($row as $colNumber => $value) {
            $time = calDate($firstDate, $colNumber, $rowNumber);
            if (in_array($targetArr[$rowNumber][$colNumber], $intArr)) {
                for ($i = 0; $i < (int)$targetArr[$rowNumber][$colNumber]; ++$i) {
                    shell_exec('git commit --amend --date="' . date('Y', $time) . '-' . date('m', $time) . '-' . date('d', $time) . 'T11:11:00+0800" -C ' . $firstHash);
                    shell_exec('git pull --no-edit origin master ');
                    shell_exec('git push origin master');
                }
            } elseif ($targetArr[$rowNumber][$colNumber] != $conf['zeroChar']) {
                shell_exec('git commit --amend --date="' . date('Y', $time) . '-' . date('m', $time) . '-' . date('d', $time) . 'T11:11:00+0800" -C ' . $firstHash);
                shell_exec('git pull --no-edit origin master ');
                shell_exec('git push origin master');
            }
        }
        echo '进度' . $rowNumber / count($targetArr) . '%' . "\n";
    }

}

/**
 * @return mixed
 * 获取第一次提交的hash值
 */
function getFirstCommitHash()
{
    //获取日志
    exec('git log --stat ', $output);
    $logs = [];
    foreach ($output as $log) {
        if (preg_match('/commit \w{40}/U', $log)) {
            $logs[] = $log;
        }
    }
    return str_replace('commit ', '', $logs[count($logs) - 1]);
}


/**
 * @param $firstDate
 * @param $weekNumber
 * @param $dayNumber
 * @return false|int
 * 根据第一周的第一天算出之后任意周的第任意天的日期,
 */
function calDate($firstDate, $weekNumber, $dayNumber)
{
    return strtotime("+$weekNumber week +$dayNumber day", $firstDate);

}

/**
 * @param $colNumber
 * @return false|int
 */
function getFirstDayDate($colNumber)
{
    if (!$colNumber) {
        return time();
    }
    return strtotime('last Sunday', strtotime("-" . --$colNumber . " weeks"));
}

<?php

//<h3 align="center">Total Cumulative Pips (Quest)</h3>
$content = file('https://www.fxblue.com/fxbluechart.aspx?c=ch_cumulativepips&id=binaforexquest');
//<h3 align="center">Total Growth (Quest)</h3>
$content = file('https://www.fxblue.com/fxbluechart.aspx?c=ch_cumulativereturn&id=binaforexquest');
//<h3 align="center">Monthly Gain/Loss (Quest)</h3>
$content = file('https://www.fxblue.com/fxbluechart.aspx?c=ch_monthlyreturn&id=binaforexquest');
//<h3 align="center">Quest Stats</h3>
$content = file('https://www.fxblue.com/fxbluechart.aspx?c=ch_accountstats&id=binaforexquest');


$content = implode('', preg_replace('/[\s\t\r\n]+/', '', $content));

if (preg_match("/data\.addRows\(\[\['Start',0\],(.+)\]\);/", $content, $match) > 0) {
    $data = json_decode("[{$match[1]}]");
    print_r($data);
} else {
    echo 'no match';
}




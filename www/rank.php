<?php
$type = $_GET['type']==1 ? 1 : 2;

$userList = Host::$userList;

if ($type==1) {
    $title = '全局击杀排行榜';
    $userField = 'killNumAll';
} else {
    $title = '单次击杀排行榜';
    $userField = 'killNumOne';
}

usort($userList, function ($u1, $u2) use($userField){
    return $u1->{$userField} > $u2->{$userField} ? -1 : 1;
});
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?= $title; ?></title>
    <script src="/static/jquery.min.js"></script>
    <style type="text/css">
        #page { width: 90%; margin: 0 auto;}
        #readMe { position: absolute; left: 50px; top: 50px; background: #f0f0f0;}
    </style>
</head>

<body>
<h3><?= $title; ?></h3>
<p>开服时间:<?= date(TIME_CHINA_TPL, START_TIME);?>, 由于排名存在内存中，每次重启都会丢失！</p>
<table border="1">
    <tr>
        <td width="50">名次</td>
        <td width="100">玩家Id</td>
        <td width="200">玩家昵称</td>
        <td width="100">击杀数</td>
    </tr>
    <?php
    $rank = 0;
    foreach ($userList as $user):
        $rank++;
    ?>
    <tr>
        <td><?= $rank; ?></td>
        <td><?= $user->id; ?></td>
        <td><?= $user->name; ?></td>
        <td><?= $user->{$userField}; ?></td>
    </tr>
    <?php
    endforeach;
    ?>
</table>

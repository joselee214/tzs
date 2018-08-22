
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <title> <?php echo $this->__j7action['title']?:'通知说';?> </title>
    <meta name="keywords" content="keywords"/>
    <meta name="description" content="description"/>
    <link rel="shortcut icon" href="/favicon.ico"/>
    <meta content="always" name="referrer"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="stylesheet" href="/res/zcss/panel.css"/>
    <link rel="stylesheet" href="/res/zcss/btn.css"/>
    <link rel="stylesheet" href="/res/zcss/cell.css"/>
    <link rel="stylesheet" href="/res/zcss/icon.css"/>
    <link rel="stylesheet" href="/res/zcss/card.css"/>
    <link rel="stylesheet" href="/res/zcss/color.css"/>
    <link rel="stylesheet" href="/res/zcss/col.css"/>
    <link rel="stylesheet" href="/res/zcss/row.css"/>
    <link rel="stylesheet" href="/res/css/uiswitch.css"/>


    <link rel="stylesheet" href="/res/reset.css"/>
    <link rel="stylesheet" href="/res/page.css"/>
    <link rel="stylesheet" href="/res/tzs.css"/>

    <script src="/res/js/jquery/jquery3.2.1.min.js" charset="utf-8"></script>
    <script src="/res/js/jquery/jquery.plus.js" charset="utf-8"></script>

    <?php
    require __DIR__.'/../_layout/loadVue.php';
    ?>

</head>
<body>

<div class="page-main">


    <?php
        if ( empty(slot_has('show_foot_text')) ):
    ?>
    <div class="page-main-top-placeholder page-main-placeholder"></div>
    <?php
        endif;
    ?>
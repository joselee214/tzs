<html>
<head>
    <title>Develop</title>
</head>

<frameset rows="60px, 30px, *" bordercolor="#E8EEF7" frameborder="0" border="0" framespacing="0">

    <frame src="<?php echo $this->rewrite_base;?>/index/AdminIndex/ushow/topheader" name="topheader" noresize scrolling="no"/>
    <frame src="<?php echo $this->rewrite_base;?>/index/AdminIndex/ushow/modulemenu" name="modulemenu" noresize scrolling="no"/>

    <frameset cols="125,*" rows="*" frameborder="1" border="1" bordercolor="#abbcd6">
        <frame src="<?php echo $this->rewrite_base;?>/index/AdminIndex/ushow/treemenu" name="treemenu" scrolling="auto">
        <frame src="<?php echo $this->rewrite_base;?>/index/AdminIndex/ushow/welcome" name="mainframe" scrolling="auto">
    </frameset>

</frameset>
</html>

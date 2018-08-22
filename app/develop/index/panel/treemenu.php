<style>
    body {
        background: #E8EEF7;
    }
</style>

<link href="<?php echo $this->adminrootfiles; ?>tree.css" rel="stylesheet" type="text/css"/>

<div style="line-height: 180%;">
    <?php
    function showmenu($d,$per=0,$adminrootdir='')
    {
        if( $d['link'] )
            $u = str_replace('{SYS_ENV}',RuntimeData::registry('SYS_ENV'),$d['link']);
        else
        {
            if( strpos($d['url'],'/')>0 )
            {
                $u = $adminrootdir.substr($d['url'], strpos($d['url'],'/'));
            }
            else
            {
                $u = $adminrootdir.$d['url'];
            }
        }
        $margin = 10*$per;
        echo '<div id="f'.$d['id'].'">';
        if( $d['is_show'] )
        {
            if( empty($u) || $u=='/' )
                echo '<div style="margin-left:'.$margin.'px;" id="'.$d['id'].'">'.'-'.'<span>'.$d['name'].'</span></div>';
            else
                echo '<div style="margin-left:'.$margin.'px;" id="'.$d['id'].'">'.'-'.'<a target="mainframe" href="'.$u.'" style="color:#'.($d['is_show']?'000':'aaa').';">'.$d['name'].'</a></div>';
        }
        if( isset($d['child']) )
        {
            foreach($d['child'] as $e)
            {
                showmenu($e,$per+1,$adminrootdir);
            }
        }
        echo '</div>';
    }


    if( empty($this->treemenu['url']) || $this->treemenu['url']=='/' )
        echo '<div style="margin: 10px;"><span>'.$this->treemenu['name'].'</span></div>';
    else
        echo '<div style="margin: 10px;"><a target="mainframe" href="'.$this->treemenu['url'].'">'.$this->treemenu['name'].'</a></div>';

    if( isset($this->treemenu['child']) )
        foreach($this->treemenu['child'] as $em)
        {
            showmenu($em,0,$this->rewrite_base);
        }
    ?>
</div>

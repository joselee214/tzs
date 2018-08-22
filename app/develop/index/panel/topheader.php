<style>
    body{background:#1a76b7;}
</style>

<div style="margin: 5px 20px;">
    <div style="text-align: left;font-size: 16px;color: #ffffff;">
    Admin Panel
    </div>
    <div style="text-align: right;color: #ffffff;">
        <?php if(isset($this->_meuid)){?>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Logined UID : <?php echo $this->_meuid;?>
        <?php }?>
    </div>
</div>
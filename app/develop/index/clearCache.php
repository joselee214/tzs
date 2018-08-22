
    <div class='content-head'>
        ClearCache:
    </div>

    <div class="content-notice2">
        <div class='content-notice'>
            <form action="<?php echo $this->rewrite_base;?>/index/clearCache/" method="POST">
                CacheKey : <input type="text" name="cachekey" value="<?php echo $this->cachekey; ?>" size="71">
<!--                Uid : <input type="text" name="cacheuid" value="--><?php //echo $this->cacheuid; ?><!--" size="10">-->
                <select name="ctype">
                    <option value="Memcache" <?php echo $this->ctype=='Default'?'selected':'';?>>Default</option>
<!--                    <option value="Memcache" --><?php //echo $this->ctype=='Memcache'?'selected':'';?><!-->Memcache</option>-->
<!--                    <option value="RedisString" --><?php //echo $this->ctype=='RedisString'?'selected':'';?><!-->Redis_String</option>-->
<!--                    <option value="RedisList" --><?php //echo $this->ctype=='RedisList'?'selected':'';?><!-->Redis_List</option>-->
<!--                    <option value="RedisHash" --><?php //echo $this->ctype=='RedisHash'?'selected':'';?><!-->Redis_Hash</option>-->
<!--                    <option value="RedisSet" --><?php //echo $this->ctype=='RedisSet'?'selected':'';?><!-->Redis_Set</option>-->
<!--                    <option value="RedisSortedSet" --><?php //echo $this->ctype=='RedisSortedSet'?'selected':'';?><!-->Redis_Sorted_Set</option>-->
                </select>
                <input type="submit" name="submittype" value="GET">
                <input type="submit" name="submittype" value="DEL">
            </form>
        </div>
    </div>

    <?php if( $this->cachekey && in_array($this->submittype,array('GET','UGAL')) ){ ?>
    <div class='content-notice2'>
        serialize Length:<?php
        echo( strlen( is_scalar($this->cachevalue)?$this->cachevalue:serialize($this->cachevalue) )); ?>
    </div>
    <?php }
    if( $this->cachekey && in_array($this->submittype,array('GET','DEL','UGAL','UDAL')) ){ ?>
    <div class='content-notice2'>
        <?php var_dump($this->cachevalue); ?>
    </div>
    <?php } ?>


    <div id="shohelp">
    <?php
    //if($this->ctype=='Memcache'){
    ?>
        <?php
        if( $this->allr )
        foreach($this->allr as $ec) { ?>
        <div class='content-notice'>
            <form action="?" method="POST">
                <input type="text" name="cachekey" value="<?php echo $ec; ?>" size="100">
                <input type="submit" name="submittype" value="GET">
                <input type="submit" name="submittype" value="DEL">
            </form>
        </div>
        <?php } ?>

		<div class='content-notice' style="clear: both;">
			<div>
                常用删除缓存
			</div>
            <?php
            foreach($this->alld as $ec) { ?>
            <div class='content-notice'>
                <form action="?" method="POST">
                    <input type="text" name="cachekey" value="<?php echo $ec; ?>" size="100">
                    <input type="submit" name="submittype" value="GET">
                    <input type="submit" name="submittype" value="DEL">
                </form>
            </div>
            <?php } ?>
		</div>
    <?php //} ?>

    </div>



    <div class="page-main-bottom-placeholder page-main-placeholder tc" style="padding-top: 50px;font-size: 13px;">
        <?php
        if ( empty(slot_has('show_foot_text')) ):
        ?>

        <div>
        <?php
        if ( RuntimeData::get('j7_probizinfo', '_meuid') ):
        ?>
        <a href="/index/index/logout" style="color: green;">退出登录/切换账户</a>
        <?php endif; ?>
            <a href="/index/help" class="c3">使用帮助</a>
        </div>
        <div style="color: #cccccc;">请使用 IE9+ 或 Chrome 等浏览器 <br/> 欢迎使用微信小程序【通知说】</div>
        <div style="padding: 10px 0 20px;">
            <img src="/res/images/tzsqr.jpg" style="width: 120px;">
        </div>
        <?php
        endif;
        ?>

    </div>

</div>
</body>
</html>
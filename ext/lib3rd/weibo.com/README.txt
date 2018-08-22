站内应用Demo使用教程

1. 在open.weibo.com创建站内应用，得到API KEY
2. 编辑应用属性，设置"应用页面"中的“站内应用地址”
3. 修改config.php中的WB_AKEY为App Key，WB_SKEY为App Secret，CANVAS_PAGE为“应用页面”中设置的”站内应用地址“
4. 上传代码到PHP空间
5. 编辑应用属性，设置"应用页面"中的"应用实际地址"为刚刚上传代码的index.php的地址，比如："http://xxxxx.sinaapp.com/index.php"，设置“Iframe高度“为2000px。
6. 访问刚刚设置的“站内应用地址”即可。

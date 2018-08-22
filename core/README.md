#基本特性

0. DI 实现 Action->Service->DAO 的调用,通过配合注释,IDE实现大部分业务类方法自动提示, 极大适合懒癌患者
1. 脚手架支持,生成DAO/Service/Action/Module文件夹, (eg:j7sample项目, "php web/develop.php cli" 会有提示)
2. ActiveRecord支持,(表结构更新后,需刷新map文件 php web/develop.php cli/daomap db=0)
3. Cache / Queue / Route / Filter / Dispatcher 等核心组件自定义实现
4. 操作mysql实现基本默认缓存,适合懒癌者
5. 多种方式 Action 重用 _j7_system_replace_action / Action->__j7replace Action->_forword / view->include_action view->J7Action view->include_view
6. 非常简单易用的使用Queue
7. 简单AOP实现
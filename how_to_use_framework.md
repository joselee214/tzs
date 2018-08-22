#框架使用

#初始化安装

0. 注意clone的相关权限问题
1. clone 下来代码, 放于 xclient 目录 (以下以xclient代称) 
2. [可能需要] 
3. [可能需要] 代码里加入了composer.json , 如果使用,可以通过"composer update"来更新composer
4. 配置安装过程
    4.1 建立数据库 xclient , 导入 doc/init.sql , 修改 configs/db/data_source.php , 配置数据库连接
    4.2 安装 memcache / redis , 同样在 configs/db/data_source.php 进行配置
    4.3 Apache服务器根目录指向 xclient/web , nginx配置参考 xclient/doc/sample.conf , 反正就是把根目录指向 xclient/web
5. 测试
    5.1 打开浏览器 http://yourdomain/ , 应该有登录界面, 用户名密码 1 / 1 即可登录.
    5.2 打开 xclient/web/index.php & develop.php ,即可以看到本次运行的application是develop,环境配置是dev,可以随意看看
    5.3 发生错误?...看情况...xclient代码在php5.5/5.6/7下跑过,没啥问题...注意必须php PDO扩展,memcache/redis扩展按需配置

#新建application & 脚手架 生成module&controller

0. 到命令行执行 [ php jsj.php ] ,(注意Windows下php执行的引入), 看到几行提示
    几个概念: application: 应用,即一个应用,如frontend,backend,可以通过入口文件来指定一个app,绑定一个域名,框架支持多域名
             module: 控制器分组,即文件夹,比如 user , group 等
             Action: 控制器controller,即最小业务执行单元
             DAO: 一般意义上的model,数据操作辅助,一个DAO对应一个数据源,这是逻辑上的分类,不存在什么硬性区分
             Service: 业务代码,Action通过Service调用DAO来操作数据源
             j7data_***: ActiveRecord辅助对象,用以操作数据库
         注意:这里 Service与DAO都是单例的,不需要new,调用时候,由于框架已经准备好了中间文件,故直接在Action里使用 $this->xxXxService-> 来调用方法
1. 执行 [ php jsj.php c app name=frontend ] 生成application基础结构
    参数说明: [ name=*** 即生成的application文件夹名,可以按需指定 ]
    
2. 执行 [ php jsj.php c module name=index app=frontend ]
    可以看到生成了
        文件夹 app/frontend/action/index 
        及文件 app/frontend/action/common/frontend_index_common.php
    参数说明: [ name=*** 生成的文件夹名,即module ]
             [ app=*** 即选择的application,上一行命令生成的 ]
3. 执行 [ php jsj.php c action name=index app=frontend module=index ]
    可以看到生成了
        文件 xclient/app/frontend/action/index/indexAction.php 
        以及 xclient/app/frontend/view/index/index/index.php
    参数说明:   [ name=*** 即controller控制器名 ]
               [ module=*** 即所在文件夹,即上一行命令生成的]
               [ return=view|json|jsonp , view即php模板,json | jsonp,默认view]
4. 这时候可能需要检查引入文件,打开入口文件 xclient/web/index.php , 并且把里面 "develop" 修改为 "frontend"
    主要代码 J7Initalize::getInstance('frontend','prod'); 说明:
        第一个参数frontend 即 application , 可以根据不同域名载入不同application来执行
        第二个参数是配置的环境,如dev/test/sit/prod, 系统会自动读取配置文件比如 configs/app_dev.php 来使用不同配置
    
5. 打开浏览器 http://yourdomain/ , 你可以看到结果 , 修改view文件 xclient/app/frontend/view/index/index/index.php ,再刷新试试


#连接数据库操作 & 最基本的 MVC 展现

0. 在数据库里新建表...这里用数据表 "fepage_configure" 来示范
    !!! 新建DAO/Service时候,脚手架会自动刷新辅助文件,如果删除Service或DAO,可以执行[ php jsj.php cc ] 来手动刷新辅助文件.
    
1. 执行 [ php jsj.php c dao db=0 table=fepage_configure app=frontend ]
    生成了文件 xclient/app/frontend/dao/fepageConfigureDbDAO.php 
        这个文件是ORM操作单表的业务类,可以在里面实现业务方法,默认继承基类实现了一些方法 getByPk/getByFk/get 主要是为了做代码提示使用
    参数说明: [ db=** 配置文件里的数据库连接对象,在configs/db/data_source.php修改 ]
             [ table=** , 表名,多个表以,分隔,默认以驼峰形式生成DAO文件]
             [ name=*** 生成的DAO名,若无则通过表名转换而来 ]
             [ pk=** 逻辑主键,若无则通过数据库读出 ]
             [ map=** 手动指定ActiveRecord对象,一般无 ]
             [ class=DbCrudDAO|CachePKDbCrudDAO|CacheListDbCrudDAO|DAO 生成的DAO基类,各有各的演出,默认为直接操作数据库的DbCrudDAO ]

2. 可能需要执行 [ php jsj.php d db=0 app=frontend tables=fepage_configure ]
    可以看到生成了 xclient/app/frontend/dao/map/j7data_fepage_configure.class.php 文件,这个文件是辅助ActiveRecord的文件,都是生成的,不要在里面写业务代码
    如果本application独占数据库,则可执行[ php jsj.php d db=0 app=frontend ] , 修改数据表结构请重新执行此行命令,刷新辅助文件

3. 执行 [ php jsj.php c service name=test3 app=frontend ]
    生成文件 xclient/app/frontend/service/test3Service.php 这个是具体业务类,大部分业务在services里实现
    参数说明: name / app 同上

4. 在 Test3Service 里引入DAO & 新建方法如下:

        /** @return \j7f\frontend\j7data_fepage_configure */
        public function getOrAddfePage($name,$text)
        {
            if( $ret = $this->fepageConfigureDbDAO->getByPk($name) )
            {
                return $ret->_text($text)->save();
            }
            else
            {
                if( $this->fepageConfigureDbDAO->add(['name'=>$name,'text'=>$text,'config'=>'']) )
                    return $this->fepageConfigureDbDAO->getByPk($name);
            }
        }

5. 修改 xclient/app/frontend/action/index/indexAction.php 文件类 内容如下 :

        public $name = 'name';
        public $text = 'text';
    
        public function execute()
        {
            $this->_ret = $this->test3Service->getOrAddfePage($this->name,$this->text);
            return $this->_setResultType('json');
        }

6. 修改文件: xclient/app/frontend/view/index/index/index.php 加入:

        <?php
        var_dump( $this->ret->_name() );
        var_dump( $this->ret['name'] );
        ?>

7. 打开浏览器 http://xclient/?name=ddd&text=222 , 你可以注意到数据库表fepage_configure新增了一行内容

8. 这里注意到 浏览器里 ?name=ddd&text=222 是把GET的值注入到 frontend_index_index_Action 里的同名属性的,在view里也是使用同名属性进行传值的, 这里获取的ORM对象可以通过方法访问属性值,也可以通过数组访问


#基本使用详解

0. 配置文件读取与环境设置,一般在引入文件index.php 里 的代码 J7Initalize::getInstance('frontend','prod'); 来进行设置
    prod / test/ sit / test /dev 等, 只有prod意味着是线上版本,有部分代码指定这个,其它随意,需要注意
    配置文件的读取顺序,  如app=frontend,环境=test 时候, 代码:$dbc = config('db','db.php');
        配置文件优先选择 app/frontend/configs/db_test.php > app/frontend/configs/db.php > configs/db_test.php  > configs/db.php
        可以在配置文件里使用require来处理通用的设置
        
1. Action 参数:
    Action获取参数是通过在 xxxAction 里 public $page=1; 声明来实现的,不分get/post
    尽量避免 $_GET / $_POST / $_REQUEST 等, 若有需要,通过 RuntimeData::request('server') 或者 RuntimeData::request() 去拿
    可以通过 $this->__requestMethod 属性 或者 方法 Util::isPost() 或者 Util::isAjax() 来判断
        
2. 响应模式说明(模板)
    在Action里 return $this->_setResultType('default'); 意味着响应某个模式,具体参见 Action 类的 public $_config 属性
        return "default";  即默认的php渲染模板
        return "json"; 或者 return "jsonp"; 即把 $this->_ret 做json处理返回,数据需绑定在 $this->_ret 上
    Action->$_config 的配置里,type即响应格式,对应着 core/resulttype 下几种响应模式, 开发者也可自行在 ext/resulttype 下定义响应配置

3. 数据分页
    2.1 脚手架[ php jsj.php c action name=i2 app=frontend module=index return=json  ]
    2.2 编辑文件: app/frontend/action/index/i2Action.php , 内容如下:
    
        public $name = 'name';
        public $text = 'text';
    
        public function execute()
        {
            $this->_ret = $this->test3Service->getOrAddfePage($this->name,$this->text);
            return $this->_setResultType('json');
        }
        
    2.3 编辑文件 /www/wanda/xclient/app/frontend/service/test3Service.php , 添加方法:
    
        public function getPage(\J7Page $ps)
        {
            return $this->getItems($this->fepageConfigureDbDAO,$ps);
        }
        
    2.4 访问: http://xclient/index/i2?page=1&limit=3

#进阶使用, 多application / 缓存 / debug / 数据库基类缓存 / 配置详解&

#高级使用, initalize/route/filter/dispatcher/resulttype/aop 使用



#常见问题:

1. "Fatal error: Class 'j7data_******' not found "
    这是ActiveRecord 辅助文件没有生成, , php jsj.php d db=** app=** tables=*** 可以修复
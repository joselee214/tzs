<?php
require_once J7SYS_EXTENSION_DIR . '/lib3rd/oss/sdk.class.php';
require_once J7SYS_EXTENSION_DIR . '/lib3rd/imagick.class.php';
class UtilityService extends basic_service
{
    const IMG_SAVE_TO_LOCAL = true;


    public function __construct()
    {
        parent::__construct();
    }

    public function __j7test($test)
    {
        return 'test:' . $test;
    }

    private function sendmail($to, $toname, $subject, $body)
    {
        //Util::sendmail($to, $nickname, $subject,$body);

        $body = '<div style="background: #6c4c31;padding: 5px;"><div style="color: #ffffff;padding: 3px;font-weight: bold;">' . $subject . '</div><div style="background: #E8EEF7;padding: 10px;line-height:26px;">' . $body . '</div></div>';

        // set mailer to use SMTP
        $smtpmail = config('smtpmail', 'mailconfig.php');

        $mail = new PHPMailer();
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = true;
        $mail->IsSMTP();
        $mail->From = $smtpmail['username'];
        $mail->FromName = $smtpmail['fromname'];

        $mail->Username = $smtpmail['username'];
        $mail->Password = $smtpmail['password'];
        $mail->Host = $smtpmail['mailserver'];
        $mail->Port = $smtpmail['mailport'];
        if (is_array($to)) {
            foreach ($to as $eto) {
                $mail->addAddress($eto, $toname);
            }
        } else
            $mail->addAddress($to, $toname);
        $mail->WordWrap = 50;
        $mail->IsHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        if (!$mail->Send()) {
//            ob_clean();
            return array('success' => 0, 'error' => $mail->ErrorInfo);
        } else {
            return array('success' => 1);
        }
    }

    /**
     * 邮件发送内容模版
     * @param array $params
     */
    public function getMsgBodyTplMail($params = [])
    {
        $type = $params['type']??'';
        $content = '';
        switch ($type)
        {
            case 'biznotification':
                $content = '新业务:'.$params['msg'].',请去<a href="'.$params['link'].'">处理地址</a>及时处理';
                break;
            case 'resetpassword':
                $content = "验证码为:【&nbsp;".$params['vcode']."&nbsp;】&nbsp;&nbsp;&nbsp;(10分钟有效)<br/><br/><br/><br/><a href=\"".$params['link']."\">直接通过链接取回密码</a>";
                break;
            default: //reg
                $content = "验证码为:【&nbsp;".$params['vcode']."&nbsp;】&nbsp;&nbsp;&nbsp;(10分钟有效)<br/><br/><br/><br/><a href=\"".$params['link']."\">注册激活</a>";
                break;
        }
        return $content;
    }

    /**
     * SMS发送内容模版
     * @param array $params
     */
    public function getMsgBodyTplSMS($params = [])
    {
        $smsConfig = config('smsconfig', 'smsconfig.php');
        $type = $params['type']??'';
        $content = '';

        if( $smsConfig['type'] == 'aliyun' )
        {
            switch ($type)
            {
                case 'biznotification': //业务通知
                    $content = ['title'=>$params['msg'],'signName'=>'家居新零售服务平台','templateCode'=>'SMS_122287585'];
                    break;
                default: //验证码
                    $content = ['code'=>$params['vcode'],'signName'=>'家居新零售服务平台','templateCode'=>'SMS_114040041'];
                    break;
            }
        }
        else
        {
            switch ($type)
            {
                case 'biznotification':
                    $content = '业务通知:'.$params['msg'].' [家居新零售]';
                    break;
                default: //验证码
                    $content = '验证码:【'.$params['vcode'].'】[家居新零售]';
                    break;
            }
        }
        return $content;
    }

    /**
     * @param $to
     * @param $body
     * @return array
     */
    private function sendSMS($to, $body)
    {
        if (is_array($to)) {
            if (count($to) > 100) {
                return array('success' => 0, 'error' => '发送手机号码上限为100个.');
            }
            $to = implode(',', $to);
        }
        $smsConfig = config('smsconfig', 'smsconfig.php');

        if( $smsConfig['type'] == 'aliyun' )
        {
            if( !isset($body['signName']) || !isset($body['templateCode']) )
            {
                echo '不规则的发送消息...';
                echo var_export($body,true);
                return false;
            }
            else
            {
                require_once J7SYS_EXTENSION_DIR.'/lib3rd/aliyunsms/SmsApi.php';
                $sms = new Aliyun\DySDKLite\Sms\SmsApi($smsConfig['accessKeyId'], $smsConfig['accessKeySecret']); // 请参阅 https://ak-console.aliyun.com/ 获取AK信息

                $biz = $body;
                unset($biz['templateCode']);
                unset($biz['signName']);

                $response = $sms->sendSms(
                    $body['signName'], // 短信签名
                    $body['templateCode'], // 短信模板编号
                    $to, // 短信接收者
                    $biz, // 短信模板中字段的值
                    $body['outId']??''   // 流水号,选填
                );
                echo var_export($response,true);
                return $response;
            }
        }
        elseif( $smsConfig['type'] == 'url' )
        {
            $post_data = array(
                'name=' . $smsConfig['name'],
                'pwd=' . $smsConfig['pwd'],
                'dest=' . $to,
                'content=' . $body . '【优品尚家】',
            );

            $post_data = implode('&', $post_data);

            $url = $smsConfig['url'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            ob_start();
            curl_exec($ch);
            $result = ob_get_contents();
            ob_end_clean();

            $r = strstr($result, 'success');
            if ($r) {
                return array('success' => 1);
            } else {
                return array('success' => 0, 'error' => 'send error.');
            }
        }
    }


    /**
     * 发送消息分2步
     * 1.获取要发送的模版内容$body = $this->UtilityService->getMsgBodyTpl1(array(xxx))
     * 2.调用sendMsg方法$this->UtilityService->sendMsg(array(xxx))
     *
     * @param array $params
     * @return array
     *
     *sendemail
     *eg:
     *          $body = $this->UtilityService->getMsgBodyTpl1(array(xxx));
     *          $p = array('type'=>'email','to'=>'du.songzhi@niuwo.com','toname'=>'aaa','subject'=>'testsubject','body'=>'test');
     *          $r = $this->UtilityService->sendMsg($p);
     *          echo '<pre>';
     *          print_r($r);
     *           exit;
     *
     * sendsms
     * eg:
     *          $body = $this->UtilityService->getMsgBodyTpl1(array(xxx));
     *          $p = array('type'=>'sms','to'=>13917558594,'body'=>'test');
     *          $r = $this->UtilityService->sendMsg($p);
     *          echo '<pre>';
     *          print_r($r);
     *          exit;
     *
     */
    public function __q_sendMsg($params = [])
    {
        $inArray = array('email', 'sms');

        if (!in_array($params['type'], $inArray)) {
            die('params type is wrong!');
        }

        if ($params['type'] == 'email') {
            $to = $params['to'];
            $toname = $params['toname'];
            $subject = $params['subject'];
            $body = $params['body'];
            return $this->sendmail($to, $toname, $subject, $body);
        }

        if ($params['type'] = 'sms') {
            $to = $params['to'];
            $body = $params['body'];
            return $this->sendSMS($to, $body);
        }

    }

    public function sendMsg($params = [])
    {
        $to = $params['to'];
        $_resendlimit = $params['_resendlimit']??30;
        if( $_resendlimit )
        {
            $cachekey = $to.'_resendlimit';
            $data = $this->__cache->get($cachekey);
            if($data)
            {
                return false;
            }
            $this->__cache->set($cachekey,1,$_resendlimit);
        }
        $this->__queue('smsandmail')->__q_sendMsg($params);
        return true;
    }


    public function generateValidateCode($key='resetpassword',$length=4,$cachetime=600)
    {
        $cachekey = session_id().'_'.$key;
        $data = $this->__cache->get($cachekey);
        if( empty($data) )
        {
            $randval = mt_rand();
            $data = substr($randval,-$length);
            $this->__cache->set($cachekey,$data,$cachetime);
        }
        return $data;
    }

    public function vertifyValidateCode($value,$key='resetpassword')
    {
        $cachekey = session_id().'_'.$key;
        $data = $this->__cache->get($cachekey);
        if( $data && $data==$value )
        {
            $this->__cache->del($cachekey);
            return true;
        }
        return false;
    }


    /**
     * 阿里云接口 config文件路径（ J7SYS_EXTENSION_DIR . '/lib3rd/oss/conf.inc.php';__DIR__ . '/../ext/configs/ossconfig.php）
     */
    /**
     * 上传文件至oss服务器注意此方法目前只提供给oa使用存放文件为主
     * @param string $objname 上传的文件名字
     * @param $file_path      本地文件上传的绝对路径 "/usr/local/var/www/www.test.com/oss/demo/test.png"
     * @param string $bucket  默认的bucket目前只有2个可用的bucket(niuwouser,niuwofactory)
     * @param string $bucketDir     默认上传到服务器的目录名(注意没有'/'开头和结尾eg: 'test/dsz')
     * @param bool $coverFile  是否覆盖重名文件 默认不覆盖，直接检查是否存在重名文件有则返回错误，设置为true则可以覆盖
     * @return array          返回$result['success'] = 1为成功ossUrl为oos提供的访问文件路径localMappingUrl为实际我们需要的访问路径
     *
     * eg:
     *          $file_path = "/usr/local/var/www/www.test.com/oss/demo/test.png";
     *          $rr = $this->UtilityService->ossUploadByFile($objname = 'ddd.png', $file_path,'test/dsz/aa','niuwo');
     *          echo '<pre>';
     *          print_r($rr);
     *          exit;
     *
     *
     */
    public function adminOaOssUploadByFile($objname = 'xxx.txt', $file_path = null, $bucketDir = 'oa', $bucket = 'niuwooa', $coverFile = false)
    {
        $oss_sdk_service = new ALIOSS();
        //设置是否打开curl调试模式
        $oss_sdk_service->set_debug_mode(FALSE);
        //返回结果
        $result = array(
            'success' => 0,
            'localMappingUrl' => '',
            'ossUrl' => '',
            'errorMsg' => '',
        );

        try {
            //检查是否是有效的bucket
            $options = array(
                ALIOSS::OSS_CONTENT_TYPE => 'text/xml',
            );

            $res = $oss_sdk_service->get_bucket_acl($bucket, $options);
            if ($res->status == 404) {
                $result['errorMsg'] = 'bucket not exist on oss server.';
                return $result;
            }


            $object = $bucketDir . '/' . $objname;

            //检查oss服务器是否存在该重名对象
            if (!$coverFile) {
                $r = $oss_sdk_service->is_object_exist($bucket, $object);
                if ($r->header['_info']['http_code'] == 200) {
                    $result['errorMsg'] = 'file already exist on oss server.';
                    return $result;
                }
            }


            //创建oss服务器bucket目录
            $rcod = $oss_sdk_service->create_object_dir($bucket, $bucketDir);
            if ($rcod->status != 200) {
                $result['errorMsg'] = 'bucket create object dir error.';
                return $result;
            }

            //upload
            $response = $oss_sdk_service->upload_file_by_file($bucket, $object, $file_path);

            //$configDomain = config('BucketDomain', 'ossconfig.php');
            $xOssRequestUrl = $response->header['x-oss-request-url'];
            //$ossHost = $response->header['x-oss-requestheaders']['Host'];

//            $localMappingUrl = '';
//            if ($configDomain[$ossHost]) {
//                $localMappingUrl = str_replace($ossHost, $configDomain[$ossHost], $xOssRequestUrl);
//            }
            $result['success'] = 1;
            $result['ossUrl'] = $xOssRequestUrl;
            $result['localMappingUrl'] = $xOssRequestUrl;
            return $result;
        } catch (Exception $ex) {
            $result['errorMsg'] = $ex->getMessage();
            return $result;
        }
    }

    /**
     * 上传文件至oss服务器注意此方法目提供给前端使用存放文件为主
     * @param string $objname 上传的文件名字
     * @param $file_path      本地文件上传的绝对路径 "/usr/local/var/www/www.test.com/oss/demo/test.png"
     * @param string $bucket  默认的bucket目前只有2个可用的bucket(niuwouser,niuwofactory)
     * @param string $bucketDir     默认上传到服务器的目录名(注意没有'/'开头和结尾eg: 'test/dsz')
     * @param bool $coverFile  是否覆盖重名文件 默认不覆盖，直接检查是否存在重名文件有则返回错误，设置为true则可以覆盖
     * @return array          返回$result['success'] = 1为成功ossUrl为oos提供的访问文件路径localMappingUrl为实际我们需要的访问路径
     *
     * eg:
     *          $file_path = "/usr/local/var/www/www.test.com/oss/demo/test.png";
     *          $rr = $this->UtilityService->ossUploadByFile($objname = 'ddd.png', $file_path,'test/dsz/aa','niuwo');
     *          echo '<pre>';
     *          print_r($rr);
     *          exit;
     *
     *
     */
    public function ClientUploadByFile($objname = 'xxx.txt', $file_path = null, $bucketDir = 'design', $bucket = 'niuwofactory', $coverFile = false)
    {
        $oss_sdk_service = new ALIOSS();
        //设置是否打开curl调试模式
        $oss_sdk_service->set_debug_mode(FALSE);
        //返回结果
        $result = array(
            'success' => 0,
            'localMappingUrl' => '',
            'ossUrl' => '',
            'errorMsg' => '',
        );

        try {
            //检查是否是有效的bucket
            $options = array(
                ALIOSS::OSS_CONTENT_TYPE => 'text/xml',
            );

            $res = $oss_sdk_service->get_bucket_acl($bucket, $options);
            if ($res->status == 404) {
                $result['errorMsg'] = 'bucket not exist on oss server.';
                return $result;
            }

            $object = $bucketDir . '/' . $objname;
            //检查oss服务器是否存在该重名对象
            if (!$coverFile) {
                $r = $oss_sdk_service->is_object_exist($bucket, $object);
                if ($r->header['_info']['http_code'] == 200) {
                    $result['errorMsg'] = 'file already exist on oss server.';
                    return $result;
                }
            }

            //创建oss服务器bucket目录
            $rcod = $oss_sdk_service->create_object_dir($bucket, $bucketDir);
            if ($rcod->status != 200) {
                $result['errorMsg'] = 'bucket create object dir error.';
                return $result;
            }

            //upload
            $response = $oss_sdk_service->upload_file_by_file($bucket, $object, $file_path);

            //$configDomain = config('BucketDomain', 'ossconfig.php');
            $xOssRequestUrl = $response->header['x-oss-request-url'];
            //$ossHost = $response->header['x-oss-requestheaders']['Host'];

//            $localMappingUrl = '';
//            if ($configDomain[$ossHost]) {
//                $localMappingUrl = str_replace($ossHost, $configDomain[$ossHost], $xOssRequestUrl);
//            }
            $result['success'] = 1;
            $result['ossUrl'] = $xOssRequestUrl;
            $result['localMappingUrl'] = $xOssRequestUrl;
            return $result;
        } catch (Exception $ex) {
            $result['errorMsg'] = $ex->getMessage();
            return $result;
        }
    }




    private function uploadOssFile($objname = 'xxx.txt', $file_path = null, $bucketDir = 'public', $bucket = 'niuwofactory', $coverFile = false )
    {
        $this->__queue('oss')->ossUploadByFile_q($objname, $file_path, $bucketDir, $bucket, $coverFile);
    }

    public function ossUploadByFile_q($objname, $file_path = null, $bucketDir, $bucket = 'niuwofactory', $coverFile = false)
    {
        $oss_sdk_service = new ALIOSS();
        //设置是否打开curl调试模式
        $oss_sdk_service->set_debug_mode(FALSE);
        //返回结果
        $result = array(
            'success' => 0,
            'errorMsg' => '',
        );

        try {
            //检查是否是有效的bucket
            $options = array(
                ALIOSS::OSS_CONTENT_TYPE => 'text/xml',
            );

            $res = $oss_sdk_service->get_bucket_acl($bucket, $options);
            if ($res->status == 404) {
                $result['errorMsg'] = 'bucket not exist on oss server.';
                return $result;
            }


            $object = $bucketDir . '/' . $objname;

            //检查oss服务器是否存在该重名对象
            if (!$coverFile) {
                $r = $oss_sdk_service->is_object_exist($bucket, $object);
                if ($r->header['_info']['http_code'] == 200) {
                    $result['errorMsg'] = 'file already exist on oss server.';
                    return $result;
                }
            }


            //创建oss服务器bucket目录
            $rcod = $oss_sdk_service->create_object_dir($bucket, $bucketDir);
            if ($rcod->status != 200) {
                $result['errorMsg'] = 'bucket create object dir error.';
                return;
            }

            //upload
            $response = $oss_sdk_service->upload_file_by_file($bucket, $object, $file_path);

            $configDomain = config('BucketDomain', 'ossconfig.php');
            $xOssRequestUrl = $response->header['x-oss-request-url'];
            $ossHost = $response->header['x-oss-requestheaders']['Host'];

            $localMappingUrl = '';
            if ($configDomain[$ossHost]) {
                $localMappingUrl = str_replace($ossHost, $configDomain[$ossHost], $xOssRequestUrl);
            }
            $result['success'] = 1;
            $result['ossUrl'] = $xOssRequestUrl;
            $result['localMappingUrl'] = $localMappingUrl;
            return;
        } catch (Exception $ex) {
            $result['errorMsg'] = $ex->getMessage();
            return;
        }


    }

    //先生成osslog记录再去执行队列oss上传
    public function ossUploadFile($objname, $file_path = null, $bucketDir = 'public', $bucket = 'niuwofactory', $coverFile = false)
    {
        $data = 100000000 - date('Ymd');

        $bucketDir = OSS_BDIRUKET_IMG_DIR . '/' . $data;

        $result = array(
            'success' => 1,
            'localMappingUrl' => ''
        );

        //insert oss log
        if ($bucket == "niuwofactory") {
            $phpc = 0;

        } else {
            $phpc = 1;
        }
        $endpos = strrpos($objname, ".");
        $format = substr($objname, $endpos + 1);
        $type = '';
        $data = array(
            'phpc' => $phpc,
            'format' => strtolower($format),
            'oss_path' => $bucketDir,
            'type' => $type,
            'add_time' => time()
        );
        $oss_log_id = $this->OssLogService->addOssLog($data);
        //update oss_path
        if ( $oss_log_id_dir = $this->getDirByOssLogId($oss_log_id) ) {
            $bucketDir = $bucketDir . '/' . $oss_log_id_dir;
        }
        $this->OssLogService->updateOssLogByPk(array('oss_path' => $bucketDir), array('id' => $oss_log_id));
        //upload file with queue

        $objname = $this->getUniqueFileNameByOssLogId($oss_log_id, $objname);

        $this->uploadOssFile($objname, $file_path, $bucketDir, $bucket, $coverFile);

        $result['localMappingUrl'] = strtolower(IMG_DOMAIN . '/' . SITE_CONTENTDIR . '/' . $bucketDir . '/' . $this->getUniqueFileNameByOssLogId($oss_log_id, $objname));
        return $result;

    }

    public function getOssUrlByImgName($img_name)
    {
        $configDomain = config('PhpcDomain', 'ossconfig.php');
        $ossConfig = config('osstype', 'ossconfig.php');

        $oss_sdk_service = new ALIOSS();
        //设置是否打开curl调试模式
        $oss_sdk_service->set_debug_mode(FALSE);

        $type = false;
        $_pos = strrpos($img_name, "_");

        if ($_pos) { //有图片规格时
            $arr = explode('_', $img_name);
            $oss_log_id = $arr[0];
            $type_arr = explode('.', $arr[1]);
            $type = $type_arr[0];
            //若没有该规格图片die
            if (!isset($ossConfig[$type])) {
                echo '404';
                die;
            }
            //判断规格是否存在
            $_tmp = $this->OssLogService->getOssPath(array('pid' => $oss_log_id, 'type' => $type));
            if ($_tmp) { //规格记录存在
                $log = $_tmp[0];
                $object = $log['oss_path'] . '/' . $img_name;
                //检查oss服务器是否存在该对象    niuwouser,niuwofactory
                $bucket = $log['phpc'] == 1 ? "niuwouser" : "niuwofactory";
                $r = $oss_sdk_service->is_object_exist($bucket, $object);
                if ($r->header['_info']['http_code'] != 200) { //该规格实际图片oss上不存在
                    $log = $this->OssLogService->getOssPath(array('id' => $oss_log_id)); //原图记录
                    $log = $log[0];
                    if (empty($log) || !empty($log['type'])) {
                        echo '404';
                        die;
                    } else { //根据原图生规格图片
                        $object = $log['oss_path'] . '/' . $oss_log_id . '.' . $log['format']; //原图路径
                        //检查oss服务器是否存在该对象    niuwouser,niuwofactory
                        $bucket = $log['phpc'] == 1 ? "niuwouser" : "niuwofactory";

                        $r = $oss_sdk_service->is_object_exist($bucket, $object);
                        if ($r->header['_info']['http_code'] != 200) {
                            echo '404';
                            die;
                        } else {
                            $path = $configDomain[$log['phpc']] . '/' . $log['oss_path'] . '/' . $oss_log_id . '.' . $log['format'];
                            $tofile = IMG_SITE_CONTENTDIR . '/' . $log['oss_path'] . '/';
                            $this->createOssThrumbByType($img_name, $path, $tofile, $type);
                            //规格缩略图上传到oss
                            $this->uploadOssFile($img_name, $tofile . $img_name, $log['oss_path'], $bucket,false);
                            //$this->ossUploadByFileThrumb($img_name, $tofile . $img_name, $log['oss_path'], $bucket);
                        }

                    }

                } else { //该规格实际图片oss上存在
                    $path = $configDomain[$log['phpc']] . '/' . $log['oss_path'] . '/' . $img_name;
                    $tofile = IMG_SITE_CONTENTDIR . '/' . $log['oss_path'] . '/';
                    $this->createOssThrumbByType($img_name, $path, $tofile, $type);
                }
            } else { //规格图片的数据记录不存在
                $log = $this->OssLogService->getOssPath(array('id' => $oss_log_id));
                $log = $log[0];
                if (empty($log) || !empty($log['type'])) {
                    echo '404';
                    die;
                }
                $object = $log['oss_path'] . '/' . $oss_log_id . '.' . $log['format'];
                //检查oss服务器是否存在该对象    niuwouser,niuwofactory
                $bucket = $log['phpc'] == 1 ? "niuwouser" : "niuwofactory";
                $r = $oss_sdk_service->is_object_exist($bucket, $object);
                if ($r->header['_info']['http_code'] != 200) {
                    echo '404';
                    die;
                }
                //oss_log
                $data = array(

                    'phpc' => $log['phpc'],
                    'format' => strtolower($log['format']),
                    'oss_path' => $log['oss_path'],
                    'type' => $type,
                    'pid' => $oss_log_id,
                    'add_time' => time()
                );
                if (!$this->OssLogService->addOssLog($data)) {
                    echo 'insert log error.';
                    die;
                }
                $path = $configDomain[$log['phpc']] . '/' . $log['oss_path'] . '/' . $oss_log_id . '.' . $log['format'];
                $tofile = IMG_SITE_CONTENTDIR . '/' . $log['oss_path'] . '/';
                //本地生成缩略图
                $this->createOssThrumbByType($img_name, $path, $tofile, $type);
                //若有规格，并且oss也没该规格图片时
                //缩略图上传到oss
                $this->uploadOssFile($img_name, $tofile . $img_name, $log['oss_path'], $bucket,false);
                //$this->ossUploadByFileThrumb($img_name, $tofile . $img_name, $log['oss_path'], $bucket);
            }
        } else { //无规格
            $arr = explode('.', $img_name);
            $oss_log_id = $arr[0];
            $log = $this->OssLogService->getOssPath(array('id' => $oss_log_id));
            $log = $log[0];
            if (empty($log) || !empty($log['type'])) {
                echo '404';
                die;
            }
            $object = $log['oss_path'] . '/' . $img_name;
            //检查oss服务器是否存在该对象    niuwouser,niuwofactory
            $bucket = $log['phpc'] == 1 ? "niuwouser" : "niuwofactory";

            $r = $oss_sdk_service->is_object_exist($bucket, $object);
            if ($r->header['_info']['http_code'] != 200) {
                echo '404';
                die;
            }
            $path = $configDomain[$log['phpc']] . '/' . $log['oss_path'] . '/' . $oss_log_id . '.' . $log['format'];
            $tofile = IMG_SITE_CONTENTDIR . '/' . $log['oss_path'] . '/';
            $this->createOssThrumbByType($img_name, $path, $tofile, $type);
        }
    }

    public function createOssThrumbByType($img_name, $path, $tofile, $type = null)
    {
        $ossConfig = config('osstype', 'ossconfig.php');
        $image = new lib_image_imagick();
        if ($type) {
            $format = explode('_', $ossConfig[$type]);
            $width = $format[0];
            $height = $format[1];
            //$dir = '/usr/local/var/www/www.test.com/oss/demo';
            //$file_path = "/usr/local/var/www/www.test.com/oss/demo/test.png";
            $image->open($path);
            $image->thumbnail($width, $height,false);
            Util::TmpMkDir($tofile);
            $image->save_to($tofile . $img_name);
            $image->output();
        } else {
            Util::TmpMkDir($tofile);
            copy($path, $tofile . $img_name);
            $image->open($path);
            $image->output();
        }
    }

    /**
     * @param $id
     * @param $filename
     * @return string
     */
    public function getUniqueFileNameByOssLogId($id, $filename)
    {
        $tmp = pathinfo($filename);
        $objname = $id . '.' . strtolower($tmp['extension']);
        return $objname;
    }

    /*
     * @param $id
     * @return string
     */
    public function getDirByOssLogId($id)
    {
        $path = '';
        $aa = str_split($id, 3);
        $count = count($aa);
        foreach ($aa as $key => $val) {
            if ($key != $count - 1) {
                $path .= $val;
            }
        }
        return $path;
    }


    public function setTraceCodeT($type=null,$fid=null,$fsid=null,$uid=null,$tcode=null)
    {
        $__t = self::getTraceCodeT($type,$fid,$fsid,$uid,$tcode);
        if( isset($__t) && $__t && $__t!='0_0_0_0' ) //应该检查所有子元素不为0
        {
            $sessionManager = FactoryObject::Instance('sharedSessionManager');
            $sessionManager->setShareData('__t',$__t,'cookie');
        }
        return $__t;
    }

    public function getTraceCode($init=true)
    {
        return $_GET['__t']??($_SESSION['__t']??($_COOKIE['__t']??($init?'0_0_0_0':false)));
    }

    //用于替换生成....
    public function getTraceCodeT($type=null,$fid=null,$sid=null,$uid=null,$tcode=null)
    {
        //$type 渠道 0:pc 1:xcx //null不替换...默认不替换... 2:设计师引入...详见 UserService->register
        //uid: 推荐的用户id，用于返回积分
        //fid fis 用于后台数据收集
        //type_uid_fid_fsid_其它...
        if( empty($tcode) )
            $tcode = $this->getTraceCode();
        $ts = explode('_',$tcode);
        if( !$uid )
            $uid = RuntimeData::get('j7_probizinfo', '_meuid');
        if( $type!==null )
            $ts[0] = $type;
        if( $uid )
            $ts[1] = $uid?:0;
        if( is_integer($fid) )
            $ts[2] = $fid?:0;
        if( is_integer($sid) )
            $ts[3] = $sid?:0;
        $t = implode('_',$ts);
        return $t;
    }

    //后台管理权限折叠
    function pavePermissionTree($a,$pidpath=[],$permissionTreePave=null)
    {
        if( empty($permissionTreePave) )
            $permissionTreePave = ['actions'=>[],'fpids'=>[]];

        foreach ($a as $k=>$ep) {
            if( isset($ep['child']) && $ep['child'] )
            {
                $c = $ep['child'];
                unset($ep['child']);
                $newp = $pidpath;
                $newp[] = $ep['fpid'];
                $permissionTreePave = $this->pavePermissionTree($c,$newp,$permissionTreePave);
            }
            if( $k!=$ep['fpid'] )
            {
                echo '错误fpid:'.$ep['name'];
                die;
            }
            if( isset($permissionTreePave['fpids'][$k]) )
            {
                echo '重复fpid'.$ep['name'];
                die;
            }

            $ep['pidpath'] = $pidpath;
            $permissionTreePave['fpids'][$k] = $ep;

            if( isset($ep['action_class']) )
            {
                if( isset($permissionTreePave['actions'][$ep['action_class']]) )
                {
                    echo '重复action_class'.$ep['name'];
                    die;
                }
                $permissionTreePave['actions'][$ep['action_class']] = $ep;
            }
        }
        return $permissionTreePave;
    }

    function checkEmployeePermission($action_class,$uid,$as='factory',$permissionTreePave=[],$fid,$sid,$throw=true)
    {
        if( $as=='factory' )
        {
            $_factory = $this->FactoryService->getFactoryByFid($fid);
            if( $_factory['auid'] == $uid )
                return true;
            $_user = $this->FactoryService->getFactoryUserByFidAndUid($fid,$uid);
            $user_permission = $_user['permissions'];
        }
        else
        {
            $_sale = $this->saleService->getSaleBySid($sid);
            if( $_sale['auid'] == $uid )
                return true;
            $_user = $this->saleUserDbDAO->getSaleUserBySidAndUid($sid,$uid);
            $user_permission = $_user['permissions'];
        }

        $user_permission_arr = explode(',', $user_permission);

        if ($_user['status'] != FACTORY_USER_STATUS_NORMAL)
        {
            if( $throw )
            {
                $this->error = '该账号已被停用!';
                $this->tips = array('type' => 'error', 'str' => $this->error);
                throw new BizException(array('tips'=>$this->tips), 'bizexception_tips');
            }
            return false;
        }
        if( is_string($action_class) )
        {
            $action_class = [$action_class];
        }
        foreach ( $action_class as $eac )
        {
            if( $this->UtilityService->__checkEmployeePermission($eac,$user_permission_arr,$permissionTreePave) === false )
            {
                $name = isset($permissionTreePave['actions'][$eac])?$permissionTreePave['actions'][$eac]['name']:'';
                $ep = explode('_',$eac);
                $error = $name.':没有权限@code:'.$ep[2].'@'.$uid;
                if( $throw )
                {
                    $this->tips = array(
                        'type' => 'error',
                        'str' => $error,
                    );
                    throw new BizException(array('tips' => $this->tips), 'bizexception_tips');
                }
                return false;
            }
        }
        return true;
    }

    //权限结构验证
    function __checkEmployeePermission($action_class,$permission,$permissionTreePave=[])
    {
        if( isset($permissionTreePave['actions'][$action_class]) )
        {
            if( isset($permissionTreePave['actions'][$action_class]['checkPermission'])
                && $permissionTreePave['actions'][$action_class]['checkPermission']===false )
                return true;
            $fpid = $permissionTreePave['actions'][$action_class]['fpid'];
            if( $fpid && !in_array($fpid,$permission) )
            {
                return false;
            }
        }
        return true;
    }

    function viewHistroy($gcode,$uid=null,$gid=null,$fid=null,$sid=null)
    {
        $cache = J7Cache::instance();
        $savekey = 'viewhis_'.$gcode;
        if($uid)
        {
            $savekey = 'viewhis_'.$uid;
            $uservihis = $cache->get($savekey);
            if( empty($uservihis) )
            {
                //从数据库查
                if( $fid )
                {
                    $r = $this->userViewHistoryDbDAO->where(['uid'=>$uid,'fid>0'])->distinct('fid')->limit(20)->find();
                    $fids = array_column($r,'fid');
                    if($fids)
                    {
                        $uservihis = ['f'=>$fids,'g'=>[],'s'=>[]];
                    }
                }
                elseif ( $sid )
                {
                    $r = $this->userViewHistoryDbDAO->where(['uid'=>$uid,'sid>0'])->distinct('sid')->limit(20)->find();
                    $sids = array_column($r,'sid');
                    if($sids)
                    {
                        $uservihis = ['f'=>[],'g'=>[],'s'=>$sids];
                    }
                }

                if( empty($uservihis) )
                {
                    $uservihis = $cache->get('viewhis_'.$gcode);
                    $cache->del('viewhis_'.$gcode);
                }
            }
        }
        else
        {
            $uservihis = $cache->get($savekey);
        }
        $uservihis = $uservihis?:['f'=>[],'g'=>[],'s'=>[]];
        if( $gid || $fid || $sid )
        {
            if( $gid )
            {
                $uservihis['g'] = $uservihis['g']??[];
                array_unshift($uservihis['g'],intval($gid));
                $uservihis['g'] = array_slice(array_unique($uservihis['g']),0,20);
            }
            if( $fid )
            {
                $uservihis['f'] = $uservihis['f']??[];
                array_unshift($uservihis['f'],intval($fid));
                $uservihis['f'] = array_slice(array_unique($uservihis['f']),0,20);
            }
            if( $sid )
            {
                $uservihis['s'] = $uservihis['s']??[];
                array_unshift($uservihis['s'],intval($sid));
                $uservihis['s'] = array_slice(array_unique($uservihis['s']),0,20);
            }
            $cache->set($savekey,$uservihis);
        }
        if( $uid )
            $this->__queue()->insertViewLog($uid,$fid,$sid,$gid);  //__queue()->
        return $uservihis;
    }

    function insertViewLog($uid,$fid,$sid,$gid)
    {
        $gid = $gid?:0;
        $fid = $fid?:0;
        $sid = $sid?:0;
        $o = $this->userViewHistoryDbDAO->getOne(['uid'=>$uid,'fid'=>$fid,'sid'=>$sid,'gid'=>$gid]);
        if( empty($o) )
        {
            $o = $this->userViewHistoryDbDAO->_new(['uid'=>$uid,'fid'=>$fid,'sid'=>$sid,'gid'=>$gid]);
        }
        $o->_updated(time())->save();
    }
}
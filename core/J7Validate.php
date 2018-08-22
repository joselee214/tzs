<?php
class J7Validate
{
    /**
     * @static
     * @return J7Validate
     */
    public static function instance($instancename = 'J7Validate',$p=[])
    {
        return FactoryObject::Instance($instancename,$p);
    }

    protected $errors=[];

    /**
     * 从配置读出结构,并验证返回
     *
     * @param $params
     * @param $rules
     * @return bool
     */
    function validate($params,$allrules)
    {
        $ret = true;
        if( $allrules )
        {
            foreach ( $allrules as $k=>$rule )
            {
                if( ( is_int($k) && $this->validateRule($params,$rule) ) || ( is_string($k) && ( !isset($params[$k]) || $this->validate($params[$k],$rule) ) ) )
                {
                    $ret = $ret?true:false;
                }
                else
                {
                    $ret = false;
                }
            }
        }
        return $ret;
    }



    /**
     *

配置_validate_config()方法
<?php
return array(
    'execute'=>[    //咨询Action的方法
        ['key'=>'user','required'=>true,'errMsg'=>'用户名必须'],  //入参判断
        'user'=>[                                               //多维入参判断
            ['key'=>'username','required'=>true,'errCode'=>1001,'errMsg'=>'用户名必须'],
            ['key'=>'vcode','required'=>true,'type'=>'string','minLength'=>1,'errMsg'=>'验证码必须!'],
            ['key'=>'vcode','required'=>true,'type'=>'validate_code','errCode'=>1001,'errMsg'=>'验证码错误!'],
        ]
    ]
);

获取错误通过执行Action里的 _error() 方法

function _error($method=null)
{
    $errors = J7Validate::instance()->getErrors();
    $this->err = '';

    foreach ($errors as $error)
    {
        $this->err .= $error['errMsg']??'';
    }
}

     *
     *
     */

    /**
     * 验证单条规则
     *
     * @param $data array 一整个数据,逐条验证
     * @param $rule array  type 是必须的
     *      ['key'=>'name','maxLength'=>20,'minLength'=>1,'max'=>2030,'min'=>2000,      'errCode'=>1001,'errMsg'=>'xxxxxx!xxx'],
     *          有key时候,支持 maxLength | minLength | max | min  | allowEmpty | required
     *
     *      ['type'=>'numeric|numerical','key'=>'name',                 'errCode'=>1001,'errMsg'=>'xxxxxx!xxx']
     *      ['type'=>'scalar','key'=>'name',                            'errCode'=>1001,'errMsg'=>'xxxxxx!xxx']
     *      ['type'=>'string','key'=>'name',                            'errCode'=>1001,'errMsg'=>'xxxxxx!xxx']
     *      ['type'=>'int|integer','key'=>'name',                               'errCode'=>1001,'errMsg'=>'xxxxxx!xxx']
     *      ['type'=>'equal','key'=>'name','data'=>'fixData'            'errCode'=>1001,'errMsg'=>'xxxxxx!xxx']
     *      ['type'=>'in','key'=>'name','dataRanger'=>[1,2,3,4],        'errCode'=>1001,'errMsg'=>'xxxxxx!xxx']
     *      ['type'=>'email','key'=>'name',                             'errCode'=>1001,'errMsg'=>'xxxxxx!xxx']
     *      ['type'=>'match','key'=>'name','pattern'=>'/[13]\d{9}/',        'errCode'=>1001,'errMsg'=>'xxxxxx!xxx']
     *      ['type'=>'compare','key'=>'re_password','for'=>'password',       'errCode'=>1001,'errMsg'=>'xxxxxx!xxx']
     *
     *      ['type'=>'date','key'=>'name','format'=>'Y-m-d H:i:s',        'errCode'=>1001,'errMsg'=>'xxxxxx!xxx']
     *
     *      ['type'=>'closures','function'=>function($data,$validateInstance){},               'errCode'=>1001,'errMsg'=>'xxxxxx!xxx' ]
     * @return bool
     */
    function validateRule($data,$rule)
    {
        //优先判断 required
        $checkRet = true;
        if( in_array('required',$rule) || (isset($rule['required']) && $rule['required']===true) )
        {
            $checkRet = $this->_validateRequired($data,$rule);
        }
        //普遍 针对单结构 规则
        if ( $checkRet && isset($rule['key']) && $rule['key'] && isset($data[$rule['key']]) )
        {
            $checkRet = $this->_validateSimple($data,$rule);
        }

        if( $checkRet )
        {
            $checkRet = !isset($data[$rule['key']]) || empty($data[$rule['key']]) ;
            if( $checkRet )
                $checkRet = true;
            else
                $checkRet = $this->_validateType($data,$rule);
        }

        if( $checkRet===false )
        {
            $this->appendError($rule);
        }
        return $checkRet;
    }

    public function _validateType($data,$rule)
    {
        if( isset($rule['type']) )
        {
            switch ($rule['type'])
            {
                case 'numerical':
                case 'numeric':
                    return is_numeric( $data[$rule['key']] );
                    break;
                case 'string':
                    return is_string( $data[$rule['key']] );
                    break;
                case 'scalar':
                    return is_scalar( $data[$rule['key']] );
                    break;
                case 'integer':
                case 'int':
                    return is_integer( $data[$rule['key']] );
                    break;
                case 'equal':
                    return ($data[$rule['key']]==$rule['data']);
                    break;
                case 'in':
                    return in_array($data[$rule['key']],$rule['dataRanger']);
                    break;
                case 'email':
                    $pattern='/^([\w\.-]+)@([a-zA-Z0-9-]+)(\.[a-zA-Z\.]+)$/i';//包含字母、数字、下划线_和点.的名字的email
                    return preg_match($pattern,$data[$rule['key']]);
                    break;
                case 'match':
                    return preg_match($rule['pattern'],$data[$rule['key']]);
                    break;
                case 'compare':
                    return $data[$rule['key']] == $data[$rule['for']];
                    break;
                case 'date':
                    return $this->_validateTypeDate($data,$rule);
                    break;
                case 'closures':
                    return $this->_validateTypeClosures($data,$rule);
                    break;
            }
        }
        return true;
    }

    public function _validateTypeClosures($date,$rule)
    {
        $iFunction = $rule['function'];
        if( $iFunction instanceof Closure )
        {
            return $iFunction($date,$this);
        }
        return true;
    }

    public function _validateTypeDate($data,$rule)
    {
        $format = isset($rule['format'])?$rule['format']:'Y-m-d H:i:s';
        $d = DateTime::createFromFormat($format, $data[$rule['key']]);
        return $d && $d->format($format) == $data[$rule['key']];
    }


    public function _validateRequired($data,$rule)
    {
        return isset($data[$rule['key']])&&(!is_null($data[$rule['key']]));
    }

    public function _validateSimple($data,$rule)
    {
        $checkRet = true;
        $checkData = $data[$rule['key']];
        if( $checkRet && isset($rule['maxLength']) )
        {
            $checkRet = strlen($checkData) <= $rule['maxLength'];
        }
        if( $checkRet && isset($rule['minLength']) )
        {
            $checkRet = strlen($checkData) >= $rule['minLength'];
        }
        if( $checkRet && isset($rule['max']) )
        {
            $checkRet = $checkData <= $rule['max'];
        }
        if( $checkRet && isset($rule['min']) )
        {
            $checkRet = $checkData >= $rule['min'];
        }
        if( $checkRet && isset($rule['allowEmpty']) && $rule['allowEmpty']===false )
        {
            $checkRet = empty($checkData)?false:true;
        }
        return $checkRet;
    }






    /**
     * @return $this
     */
    public function clearErrors()
    {
        $this->errors = [];
        return $this;
    }
    public function getErrors()
    {
        return $this->errors;
    }
    public function appendError($d)
    {
        $this->errors[] = $d;
    }
}
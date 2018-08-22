<?php
class tzs_apis_signUp_Action extends tzs_apis_common
{

    public function __j7construct()
    {
        parent::__j7construct();
        if( is_null($this->_userme) || !isset($this->_userme['uid']) || !$this->_userme['uid'] )
            throw new BizException(['error'=>'not login'],'loginwx');

    }


    public $values;
    public $pid;

    public function signUp()
    {
        $this->_ret = ['status'=>0];
        $post = $this->tzsService->getPostById($this->pid);

        $values = $this->values; //json_decode($this->values,true);
        $values = array_filter($values);

        if( $post && $post->_can_sign_up() && $values )
        {
            $sign_options = array_filter(explode(PHP_EOL,$post->_sign_up_options()));
            $length_options = count($sign_options);

            $ret_values = [];
            for ($i=0;$i<$length_options;$i++)
            {
                $v = '';
                if( isset($values['sign_values_'.$i]) )
                {
                    $v = $values['sign_values_'.$i];
                }
                $ret_values[] = $v;
            }

            $str = json_encode($ret_values);
            $s = $this->tzsService->execSignUpLog($this->_userme['uid'],$this->pid,$str);

            $sign_options = $this->tzsService->getSignUpLogDetail($this->pid,$this->_userme['uid']);
            $this->_ret = ['status'=>1,'values'=>$values,'sign'=>$s,'sign_options'=>$sign_options[1],'user_is_sign'=>$sign_options[0]];
        }
    }

    public function deleteSign()
    {
        $this->tzsService->deleteSignUpLog($this->pid,$this->_userme['uid']);
        $sign_options = $this->tzsService->getSignUpLogDetail($this->pid,$this->_userme['uid']);
        $this->_ret = ['status'=>1,'sign_options'=>$sign_options[1],'user_is_sign'=>$sign_options[0]];
    }

}
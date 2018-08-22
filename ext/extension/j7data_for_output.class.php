<?php


class j7data_for_output extends J7Data
{
    /**
     * @return array
     */
    function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        //敏感信息
        if( get_called_class() == 'j7data_users' )
        {
            unset($data['username']);
            unset($data['email']);
            unset($data['mobile']);
            unset($data['encrypted_password']);
        }
        elseif( get_called_class() == 'j7data_wx_users' )
        {
            unset($data['session_id']);
            unset($data['unionid']);
            unset($data['session_key']);
            unset($data['openid']);
        }
        return $data;
    }
}
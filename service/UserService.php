<?php
class UserService extends basic_service
{

    /**
     * @param int $uid
     * @return j7data_users
     */
    function getUserById($uid)
    {
        return $this->userDbDAO->getByPk($uid);
    }

    function updateUser($uarr=[])
    {
        if( !isset($uarr['uid']) || empty($uarr['uid']) )
            return false;
        $this->userDbDAO->updateByPk($uarr);
    }

    /**
     * 通过条件获取user记录
     * @param $cond
     * @return j7data_users|null
     */
    function getUserByCond($cond)
    {
        return $this->userDbDAO->getUser($cond);
    }

    function setLogin($user,$normalLogin=true)
    {
        if (!$user)
            return;
        $uid = $user['uid'];

        FactoryObject::Instance('sharedSessionManager')->setLogin($uid);

        // add user login log
        if( $normalLogin )
        {
            $this->userLoginLogDbDAO->add(array(
                'uid' => $uid,
                'created' => Util::getTime(),
                'ip' => Util::getRemoteAddr()
            ));

            // update last_login
            $this->userDbDAO->updateByPk(array('last_login' => Util::getTime(), 'uid' => $uid));
        }
    }


    /**
     * 注册用户
     * @param array $user
     * @param int $identity 会员类型  1:普通用户 2:设计师账户 3:厂家官方号! 4:厂家员工  5:经销商主账号 6:经销商员工号
     * @throws Exception
     * @return int
     */
    function register($user, $identity = 1)
    {
        if (empty($user['mobile']) && empty($user['email']) && empty($user['username'])) {
            throw new Exception("没有填写邮箱或手机号");
        }

        if( empty($user['username']) )
            $user['username'] = isset($user['mobile']) ? $user['mobile'] : (isset($user['email']) ? $user['email'] : '');
        if ( $u = $this->userDbDAO->getUser(['username'=>$user['username']])) {
            return $u['uid'];
            //throw new Exception("此账户已经注册!");
        }

        $data = array(
            'email' => isset($user['email']) ? $user['email'] : '',
            'mobile' => isset($user['mobile']) ? $user['mobile'] : '',
            'username' => isset($user['username']) ? $user['username'] : '',
            'name' => isset($user['name']) ? $user['name'] : '',
            'avatarUrl' => isset($user['avatarUrl']) ? $user['avatarUrl'] : SITE_DOMAIN.DEFAULT_SITE_LOGO,
            'identity' => $identity,
        );

        if( isset($user['validate_status']) )
            $data['validate_status'] = $user['validate_status'];

        $passwordHash = substr(md5(serialize($data)), 0, 5);
        $data['encrypted_password'] = $this->userDbDAO->encryptPassword($user['pw'], $passwordHash);
        $data['trace_code'] = $this->UtilityService->getTraceCode();
        $uid = $this->userDbDAO->addUser($data);


        return $uid;
    }
}

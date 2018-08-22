<?php
class UserExtPlatformService extends basic_service
{

    public function getWxUsers($ps,$cond=[],$order='id desc')
    {
        return $this->pager($this->wxUsersDbDAO,$ps,$cond,$order);
    }

    public function getWxUserByUid($uid,$appid)
    {
        die('可以openid一对多');
        return $this->wxUsersDbDAO->getByFk(['appid'=>$appid,'uid'=>$uid]);
    }

    public function getWxUserById($id)
    {
        return $this->wxUsersDbDAO->getByFk(['id'=>$id]);
    }

    public function getWxUserByOpenid($openid,$appid)
    {
        $wxuser = $this->wxUsersDbDAO->getByPk(['appid'=>$appid,'openid'=>$openid]);
        return $wxuser;
    }

    //通过unionid 获取已注册uid
    public function getUidFromUnionid($unionid)
    {
        $wxuser = $this->wxUsersDbDAO->getOne(['unionid'=>$unionid,'uid > 0']);
        if( $wxuser && $wxuser->_uid() )
            return $wxuser->_uid();
        return null;
    }

    public function regWxUser($data)
    {
        $wxuser = $this->wxUsersDbDAO->_new($data);
        if( $tcode=$this->UtilityService->getTraceCode(false) )
        {
            $ts = explode('_',$tcode);
            $wxuser->_tcode($tcode)->_tuid($ts[1]??0)->_tfid($ts[2]??0)->_tsid($ts[3]??0);
        }
        return $wxuser;
    }

    public function updateQrTrace($data)
    {
        //扫码记录
        $this->usersQrTraceDbDAO->_new($data)->save();


        //处理扫码进店用户
        $sdata = ['openid'=>$data['openid'],'unionid'=>$data['unionid'],'appid'=>$data['appid'],'uid'=>$data['uid']];

        $sdata['fid'] = 0;
        $sdata['fsid'] = 0;
        if( isset($data['gof']) && $data['gof'] )
        {
            $sdata['fid'] = $data['gof'];
            $sdata['sid'] = $data['tsid'];
        }
        elseif( isset($data['gos']) && $data['gos'] )
        {
            $sdata['fid'] = 0;
            $sdata['sid'] = $data['gos'];
        }
        elseif( isset($data['gog']) && $data['gog'] )
        {
            $good = $this->goodsDbDAO->getByPk($data['gog']);
            if(empty($good))
                return false;
            $sdata['fid'] = $good['fid'];
            $sdata['sid'] = $data['tsid'];
        }
        elseif( isset($data['goc']) && $data['goc'] )
        {
            $coupon = $this->PromotionService->getCouponByCid($data['goc'],false);
            if(empty($coupon))
                return false;
            if( $coupon['fid'] )
                $sdata['fid'] = $coupon['fid'];
            if ( $coupon['sid'] )
                $sdata['sid'] = $coupon['sid'];
        }

        $d = $this->usersShopTraceDbDAO->getByFk(['appid'=>$sdata['appid'],'openid'=>$sdata['openid'],'fid'=>$sdata['fid']??0,'sid'=>$sdata['sid']??0]);

        if( empty($d) )
        {
            return $this->usersShopTraceDbDAO->_new($sdata)->save();
        }
        else
        {
            return $d-> fromArray($sdata)->save();
        }
    }

    function getQrUsers($ps,$cond,$order)
    {
        $ps = $this->getItems($this->usersQrTraceDbDAO, $ps, $cond, $order );
        $items = $ps->getItems();
        foreach ($items as &$item)
        {
            $item['mobile'] = '';
            if( $item['uid'] && ($u=$this->UserService->getUserById($item['uid'])) )
            {
                $item['mobile'] = $u['mobile'];
            }
            if( $wxuser = $this->wxUsersDbDAO->getByPk(['appid'=>$item['appid'],'openid'=>$item['openid']]) )
            {
                $item = array_merge( $wxuser->toArray() , J7Data::rArray($item) );
            }
        }
        $ps->setItems($items);
        return $ps;
    }

    function getShopUsers($ps,$cond,$order)
    {
        $ps = $this->getItems($this->usersShopTraceDbDAO, $ps, $cond, $order );
        $items = $ps->getItems();
        foreach ($items as &$item)
        {
            $item['mobile'] = '';
            if( $item['uid'] && ($u=$this->UserService->getUserById($item['uid'])) )
            {
                $item['mobile'] = $u['mobile'];
            }
            if( $wxuser = $this->wxUsersDbDAO->getByPk(['appid'=>$item['appid'],'openid'=>$item['openid']]) )
            {
                $item = array_merge( $wxuser->toArray() , J7Data::rArray($item) );
            }
        }
        $ps->setItems($items);
        return $ps;
    }

}

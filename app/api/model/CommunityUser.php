<?php
// +----------------------------------------------------------------------
// | ThinkNuan-x [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.nuan-x.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: tcyfree <1946644259@qq.com>
// +----------------------------------------------------------------------

namespace app\api\model;


use app\lib\exception\ParameterException;
use app\api\model\Callback as CallbackModel;
use think\Db;

class CommunityUser extends BaseModel
{
    protected $autoWriteTimestamp = true;
    protected $hidden = ['create_time','update_time'];
    public function community(){
        return $this->hasMany('Community','id','community_id');
    }
    /**
     * 社群成员
     */
    public function member()
    {
        return $this->belongsTo('UserInfo','user_id','user_id')->order('user_id asc');
    }

    /**
     * 根据用户获取社群分页
     *
     * @param $uid
     * @param $type
     * @param int $page
     * @param int $size
     * @return \think\Paginator
     */
    public static function getSummaryByUser($uid, $type, $page = 1, $size = 15)
    {
        $where['user_id'] = $uid;
        $where['status']  = ['neq',1];
        if($type == 2){
            $pagingData = self::with('community')
                ->where(function ($query){
                    $query->where('type',0)->whereOr('type',1);
                })
                ->where($where)
                ->order('create_time desc')
                ->paginate($size, true, ['page' => $page]);
        }else{
            $pagingData = self::with('community')
                ->where($where)
                ->order('create_time desc')
                ->paginate($size, true, ['page' => $page]);
        }

        return $pagingData;
    }

    /**
     * 根据用户获取社群分页,根据level降序排序
     *
     * @param $uid
     * @param $type
     * @param int $page
     * @param int $size
     * @return \think\Paginator
     */
    public static function getSummaryByUserOrderByLevel($uid, $type, $page = 1, $size = 15)
    {
        $where['c_u.user_id'] = $uid;
        $where['c_u.status']  = ['in','0,2'];
        $page = $page - 1;
        if($type == 2){
            $data = Db::table('qxd_community_user')
                ->alias('c_u')
                ->join('__COMMUNITY__ c','c_u.community_id = c.id')
                ->where($where)
                ->where(function ($query){
                    $query->where('c_u.type',0)->whereOr('c_u.type',1);
                })
                ->order('c.level DESC, c_u.create_time DESC')
                ->limit($page,$size)
                ->select()
                ->toArray();
        }else{
            $data = Db::table('qxd_community_user')
                ->alias('c_u')
                ->join('__COMMUNITY__ c','c_u.community_id = c.id')
                ->where($where)
                ->order('c.level DESC, c_u.create_time DESC')
                ->limit($page,$size)
                ->select()
                ->toArray();
        }
        return $data;
    }

    /**
     * @param $user_id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getCommunityMemberNum($user_id){
        $where['user_id'] = $user_id;
        $where['status'] = ['neq',1];

        $result = self::with('community')->where($where)->select();
        return $result;
    }

    /**
     * 判断此社群是否是该用户
     * @param $uid
     * @param $community_id
     * @return null|static
     * @throws ParameterException
     */
    public static function checkCommunityBelongsToUser($uid,$community_id)
    {
        $res = self::get(['user_id' => $uid, 'community_id' => $community_id]);
        if (!$res){
            throw new ParameterException([
                'msg' => '还未参加该社群！'
            ]);
        }

        return $res;
    }

    /**
     * 查找社群管理员和社长ID
     *
     * @param $community_id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getManagerID($community_id)
    {
        $where['community_id'] = $community_id;
        $where['type'] = ['neq',2];

        $res = self::where($where)
            ->field('user_id')
            ->select()->toArray();
        return $res;
    }

    /**
     * 判断用户是否为付费用户
     * @param $uid
     * @param $community_id
     * @return bool|null|static
     */
    public static function checkPayingUsers($uid, $community_id)
    {
        $where['user_id'] = $uid;
        $where['community_id'] = $community_id;
        self::checkCommunityBelongsToUser($uid,$community_id);
        $where['pay'] = 1;
        $res = self::get($where);
        if (!$res){
            return false;
        }else{
            return $res;
        }
    }

    /**
     * 注册恢复成员资格定时任务
     *
     * @param $uid
     * @param $community_id
     */
    public static function registerCallback($uid,$community_id)
    {
        $deadline = time() + config('setting.user_time_out');
        CallbackModel::registerCallback($community_id,$uid,$key_type = 2 ,$deadline);
    }

    /**
     * 恢复/暂停成员资格
     * 1.判断是否退群
     *
     * @param $community_id
     * @param $user_id
     * @param $status
     * @return bool
     */
    public static function resumeCommunityUser($community_id,$user_id,$status)
    {
        $res = self::get(['community_id' => $community_id, 'user_id' => $user_id]);
        if (!$res || $res->status == 1){
            return false;
        }
        self::update(['status' => $status, 'update_time' => time()],
            ['user_id' => $user_id, 'community_id' => $community_id, 'delete_time' => 0]);
        return true;
    }

    /**
     * 查找社群社长ID
     *
     * @param $community_id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getChiefID($community_id)
    {
        $where['community_id'] = $community_id;
        $where['type'] = 0;

        $res = self::where($where)
            ->field('user_id')
            ->find();
        return $res->user_id;
    }

    /**
     * 将普通用户变成付费用户
     *
     * @param $uid
     * @param $community_id
     */
    public static function updateUserPay($uid, $community_id)
    {
        $where['community_id'] = $community_id;
        $where['user_id'] = $uid;
        CommunityUser::update(['pay' => 1],$where);
    }

}
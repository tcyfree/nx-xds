<?php
// +----------------------------------------------------------------------
// | ThinkNuan-x [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.nuan-x.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: probe <1946644259@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2017/9/1/17:08
// +----------------------------------------------------------------------

namespace app\api\model;

use think\Db;
use app\api\model\UserProperty as UserPropertyModel;

class IncomeExpensesUser extends BaseModel
{
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    public function incomeExpenses()
    {
        return $this->hasOne('IncomeExpenses','id','ie_id');
    }

    public static function incomeExpensesSummary($uid,$page,$size)
    {
        $where['user_id'] = $uid;
        $pagingData = self::with('incomeExpenses')
            ->where($where)
            ->group('create_time')
            ->order(['create_time' => 'desc'])
            ->paginate($size, true, ['page' => $page]);

        return $pagingData;
    }

    /**
     * 获取用户总的收入或支出
     *
     * @param $uid
     * @param $type
     * @return float|int
     */
    public static function getSumIncomeOrExpenses($uid,$type)
    {
        $where['i_e_u.type'] = (string)$type;
        $where['i_e_u.user_id'] = $uid;
        $total = Db::name('income_expenses_user')
            ->alias('i_e_u')
            ->join('__INCOME_EXPENSES__ i_e','i_e_u.ie_id = i_e.id')
            ->where($where)
            ->sum('i_e.fee');

        return $total;

    }

    /**
     * 支出/收入，更新对应钱包金额
     *
     * @param $uid
     * @param $ie_id
     * @param $fee
     * @param $community_id
     */
    public static function postIncomeExpensesUser($uid, $ie_id, $fee, $community_id)
    {
        //支出用户,减少钱包金额
        $data['user_id'] = $uid;
        $data['ie_id'] = $ie_id;
        (new IncomeExpensesUser())->allowField(true)->save($data);
        UserPropertyModel::updateWallet($uid,$fee,false);

        //收入用户,增加钱包金额
        $community_user = CommunityUser::get(['community_id' => $community_id, 'type' => 0]);
        $data['user_id'] = $community_user->user_id;
        $data['type'] = 1;
        $income_fee = $fee*(1-config('fee.withdrawal_fee'));
        (new IncomeExpensesUser())->allowField(true)->save($data);
        UserPropertyModel::updateWallet($data['user_id'],$income_fee,true);
    }
}
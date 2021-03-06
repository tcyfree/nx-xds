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
// | DateTime: 2017/9/19/9:50
// +----------------------------------------------------------------------

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\Communication as CommunicationModel;
use app\api\model\CommunicationOperate as CommunicationOperateModel;
use app\api\model\Community as CommunityModel;
use app\api\model\Notice as NoticeModel;
use app\api\service\Token as TokenService;
use app\api\validate\CommunicationList;
use app\api\validate\CreateCommunication;
use app\api\validate\UUID;
use app\lib\exception\SuccessMessage;
use app\api\model\CommunityUser as CommunityUserModel;
use app\lib\exception\ParameterException;
use think\Db;
use think\Exception;

class Communication extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'deleteCommunication']
    ];

    /**
     * 发布内容
     * 1.被@的用户
     * 2.判断该用户是否为付费用户,付费用户才能发条目
     * 3.对管理员或社长放行
     * 4.暂停成员资格不能发条目
     *
     * @return \think\response\Json
     * @throws ParameterException
     */
    public function createCommunication()
    {
        (new CreateCommunication())->goCheck();
        $dataArray = input('post.');
        $uid = TokenService::getCurrentUid();
        CommunityModel::checkCommunityExists($dataArray['community_id']);
        $result = CommunityUserModel::checkCommunityBelongsToUser($uid,$dataArray['community_id']);
//        if ($result['type'] == 2 && $result['pay'] == 0 || $result['status'] ==2){
        if ($result['status'] ==2){
            throw new ParameterException([
//                'msg' => '你不是该社群付费用户或被暂停成员资格不能发条目哦'
                'msg' => '你被暂停成员资格不能发条目哦'
            ]);
        }
        $dataArray['user_id'] = $uid;
        $id = uuid();
        $dataArray['id'] = $id;
        $c_obj = new CommunicationModel();
        // 过滤数组中的非数据表字段数据
        $c_obj->allowField(true)->save($dataArray);
        //@用户，发消息通知
        if (isset($dataArray['@user_ids'])){
            foreach ($dataArray['@user_ids'] as $v){
                $data['id'] = uuid();
                $data['to_user_id'] = $v;
                $data['from_user_id'] = $uid;
                $data['communication_id'] = $id;
                NoticeModel::create($data);
            }
        }

        return json(new SuccessMessage(),201);
    }

    /**
     * 交流区列表
     * 1.判断当前用户是否点赞
     * 2.当前用户是否为付费用户
     * 3.对管理员或社长放行，
     * 4.暂停成员资格不能发条目
     * 5.成员状态
     *
     * @param int $page
     * @param int $size
     * @return array
     */
    public function getCommunicationList($page = 1, $size = 15)
    {
        (new CommunicationList())->goCheck();
        $community_id = input('get.community_id');
        $uid = TokenService::getCurrentUid();
        $pageData = CommunicationModel::getList($page,$size,$community_id);
        $data = $pageData->visible(['id','community_id','content','user_id','location','likes','comments','user_info.user_id','create_time','user_info.nickname','user_info.avatar'])->toArray();
        foreach ($data as &$v){
            CommunicationModel::where('id',$v['id'])->setInc('hits');
            $res = CommunicationOperateModel::get(['user_id' => $uid, 'communication_id' => $v['id'],'type' => 1,'delete_time' => 0]);
            if ($res){
                $v['do_like'] = true;
            }else{
                $v['do_like'] = false;
            }
            if ($uid == $v['user_id']){
                $v['own'] = true;
            }else{
                $v['own'] = false;
            }
            $v['community_user'] = CommunityUserModel::get(['user_id' => $v['user_id'],'community_id' => $v['community_id'],'delete_time' => 0]);
        }

        $result = CommunityUserModel::checkCommunityBelongsToUser($uid,$community_id);
//        if ($result['type'] == 2 && $result['pay'] == 0 || $result['status'] ==2){
        if ($result['status'] ==2){
            $data['send'] = false;
        }else{
            $data['send'] = true;
        }

        return [
            'data' => $data,
            'current_page' => $pageData->currentPage()
        ];
    }

    /**
     * 条目详情
     * 1. 判断是否存在
     *
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getCommunicationDetail(){
        (new UUID())->goCheck();
        $uid = TokenService::getCurrentUid();
        $id = input('get.id');
        $where['id'] = $id;
        $where['delete_time'] = 0;
        CommunicationModel::checkCommunicationExists($id);
        $data = CommunicationModel::with('userInfo')
            ->where($where)
            ->find();
        $data->hidden(['update_time','delete_time','user_info.id','user_info.user_id']);
        $res = CommunicationOperateModel::get(['user_id' => $uid, 'communication_id' => $data['id'],'type' => 1,'delete_time' => 0]);
        if ($res){
            $data['do_like'] = true;
        }else{
            $data['do_like'] = false;
        }

        if ($uid == $data['user_id']){
            $data['own'] = true;
        }else{
            $data['own'] = false;
        }
        return $data;

    }

    /**
     * 顶赞/取消顶赞
     * 1 自己给自己条目点赞不再收到通知
     * 2 update：如果数据不存在不会报错，只是影响行数为0
     *
     * @return \think\response\Json
     * @throws Exception
     */
    public function operateCommunityByUser()
    {
        (new UUID())->goCheck();
        $communication_id = input('put.id');
        CommunicationModel::checkCommunicationExists($communication_id);
        $uid = TokenService::getCurrentUid();
        $where['user_id'] = $uid;
        $where['communication_id'] = $communication_id;
        $where['type'] = 1;
        $where['delete_time'] = 0;
        $res = CommunicationOperateModel::get($where);
        Db::startTrans();
        try{
            if ($res){
                CommunicationOperateModel::update(['delete_time' => time()],['communication_id' => $communication_id, 'user_id' => $uid]);
                CommunicationModel::where('id',$communication_id)->setDec('likes');
                //update：如果数据不存在不会报错，只是影响行数为0
                NoticeModel::update(['delete_time' => time()],['from_user_id' => $uid,'communication_id' => $communication_id,'type' =>1]);
            }else{
                CommunicationOperateModel::create($where);
                CommunicationModel::where('id',$communication_id)->setInc('likes');
                $data['from_user_id'] = $uid;
                $data['communication_id'] = $communication_id;
                $data['type'] = 1;
                NoticeModel::createNotice($data);
            }
            Db::commit();
        }catch (Exception $ex)
        {
            Db::rollback();
            throw $ex;
        }
        return json(new SuccessMessage(),201);
    }

    /**
     * 删除条目
     * !!!!BUG：不能使用软删除，否则分页获取接口不能查询数据
     * @return \think\response\Json
     */
    public function deleteCommunication()
    {
        (new UUID())->goCheck();
        $communication_id = input('delete.id');
        CommunicationModel::checkCommunicationExists($communication_id);
        CommunicationModel::update(['delete_time' => time()],['id' => $communication_id]);

        return json(new SuccessMessage(),201);
    }

    /**
     * 用户条目列表
     *
     * 1 个人中心
     * 2 查看成员列表个人中心
     * @param int $page
     * @param int $size
     * @return array
     */
    public function getListByUser($page = 1, $size = 15)
    {
        $user_id = TokenService::getCurrentUid();
        $uid = input('get.id');
        if (!$uid){
            $uid = $user_id;
        }else{
            (new UUID())->goCheck();
        }

        $pageData = CommunicationModel::getListByUser($page, $size, $uid);
        $data = $pageData->visible(['id','content','user_id','location','likes','comments',
            'create_time','community.id','community.name'])->toArray();

        foreach ($data as &$v){
            CommunicationModel::where('id',$v['id'])->setInc('hits');
            $res = CommunicationOperateModel::get(['user_id' => $user_id, 'communication_id' => $v['id'],'type' => 1,'delete_time' => 0]);
            if ($res){
                $v['do_like'] = true;
            }else{
                $v['do_like'] = false;
            }

        }

        return [
            'data' => $data,
            'current_page' => $pageData->currentPage()
        ];

    }

}
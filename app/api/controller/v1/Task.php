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
// | DateTime: 2017/8/29/13:34
// +----------------------------------------------------------------------

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\ActPlanUser;
use app\api\validate\AccelerateTask;
use app\api\validate\Feedback;
use app\api\validate\GetFeedback;
use app\api\validate\TaskList;
use app\api\validate\TaskNew;
use app\api\service\Token as TokenService;
use app\api\model\Task as TaskModel;
use app\api\model\TaskRecord as TaskRecordModel;
use app\api\validate\TaskUpdate;
use app\api\validate\UUID;
use app\lib\exception\ParameterException;
use app\lib\exception\SuccessMessage;
use app\api\model\ActPlan as ActPlanModel;
use app\api\service\Community as CommunityService;
use think\Exception;
use think\Db;
use app\api\service\Task as TaskService;
use app\api\model\TaskAccelerate as TaskAccelerateModel;
use app\api\model\TaskFeedback as TaskFeedbackModel;

class Task extends BaseController
{
    /**
     * 创建任务
     * 1.鉴权
     * @return \think\response\Json
     * @throws Exception
     */
    public function createTask()
    {
        (new TaskNew())->goCheck();
        $uid = TokenService::getCurrentUid();
        $data['user_id'] = $uid;
        $id = uuid();

        $dataArray = input('post.');
        ActPlanModel::checkActPlanExists($dataArray['act_plan_id']);
        $dataArray['id'] = $id;

        $ts = new TaskService();
        $ts->checkAuthority($uid,$dataArray['act_plan_id']);

        Db::startTrans();
        try {
            TaskModel::create($dataArray);
            //更新任务数
            $where['id'] = $dataArray['act_plan_id'];
            ActPlanModel::where($where)->setInc('task_num');

            $data['task_id'] = $id;
            $data['type'] = 0;
            TaskRecordModel::create($data);
            Db::commit();
        }catch (Exception $ex){
            Db::rollback();
            throw $ex;
        }

        return json(new SuccessMessage(),201);
    }

    /**
     * 编辑任务
     * 1.鉴权
     */
    public function updateTask()
    {
        $validate = new TaskUpdate();
        $validate->goCheck();
        $uid = TokenService::getCurrentUid();
        $data['user_id'] = $uid;

        $dataArray = $validate->getDataByRule(input('put.'));
        $t_obj = TaskModel::get(['id' => $dataArray['id']]);
        if (!$t_obj){
           throw new ParameterException();
        }else{
            $ts = new TaskService();
            $ts->checkAuthority($uid,$t_obj->act_plan_id);
        }

        TaskModel::update($dataArray,['id' => $dataArray['id']]);
        $data['task_id'] = $dataArray['id'];
        $data['type'] = 1;
        TaskRecordModel::create($data);

        return json(new SuccessMessage(), 201);
    }

    /**
     * 任务列表
     * @param $id
     * @param int $page
     * @param int $size
     * @return array
     */
    public function getSummaryList($id,$page = 1, $size = 15)
    {
        (new TaskList())->goCheck();

        $uid = TokenService::getCurrentUid();
        CommunityService::checkJoinCommunityByUser($uid,$id);
        $pagingData = TaskModel::getSummaryList($id, $page, $size);
        $pagingArray = $pagingData->visible(['id','name'])
            ->toArray();
        $task_obj = new TaskService();
        $data['task'] = $task_obj->checkTaskFinish($pagingArray,$uid);
        $res = ActPlanUser::get(['act_plan_id' => $id, 'user_id' => $uid]);
        $data['flag'] = '0';
        if ($res){
            $data['flag'] = '1';
        }
        $res_data = ActPlanModel::checkActPlanExists($id);
        $data['mode'] = $res_data['mode'];
        return [
            'data' => $data,
            'current_page' => $pagingData->currentPage()
        ];
    }

    /**
     * 任务详情
     * @param $id
     * @return $this
     */
    public function getTaskDetail($id)
    {
        (new UUID())->goCheck();
        $uid = TokenService::getCurrentUid();
        TaskModel::checkTaskExists($id);
        TaskService::checkTaskByUser($uid,$id);
        $data = TaskModel::with('taskUser')->where(['id' => $id])->find();
        $return_data = $data->visible(['id','name','requirement','content','reference_time','task_user.user_id','task_user.finish','task_user.create_time'])->toArray();

        return $return_data;
    }

    /**
     * GO任务
     * @return \think\response\Json
     */
    public function goTask(){
        (new UUID())->goCheck();
        $task_id = input('post.id');
        $uid = TokenService::getCurrentUid();
        TaskModel::goTask($uid, $task_id);

        return json(new SuccessMessage(),201);
    }

    /**
     * 普通任务加速
     * 1. 自己不能给自己加速
     * @throws ParameterException
     */
    public function accelerateTask(){
        (new AccelerateTask())->goCheck();
        $uid = TokenService::getCurrentUid();
        $data = input('post.');
        if ($uid == $data['user_id']){
            throw new ParameterException([
                'msg' => '小样儿，自己不能给自己加速哦'
            ]);
        }
        TaskAccelerateModel::accelerateTask($uid,$data);

        return json(new SuccessMessage(),201);
    }

    /**
     * 正常结束普通任务回调接口
     */
    public function overTask()
    {

    }

    /**
     * 获取挑战模式下的状态
     * @return array
     * @throws ParameterException
     */
    public function getFeedbackStatus(){
        (new UUID())->goCheck();
        $task_id = input('get.id');
        $uid = TokenService::getCurrentUid();
        $mode = TaskModel::getTaskMode($task_id,$uid);
        if ($mode == 0){
            throw new ParameterException([
                'msg' => '此任务为普通模式'
            ]);
        }
        $res = TaskFeedbackModel::get(['user_id' => $uid, 'task_id' => $task_id]);
        if (!$res){
            return ['status' => null];
        }else{
            return ['status' => $res['status']];
        }

    }
    public function feedback()
    {
        $validate = new Feedback();
        $dataArray = input('post.');
        $dataRules = $validate->getDataByRules($dataArray,'status');
        $uid = TokenService::getCurrentUid();
        $res = TaskFeedbackModel::checkTaskFeedback($uid, $dataRules['task_id']);
        if ($res == false){
            TaskFeedbackModel::create($dataRules);
            return json(new SuccessMessage(),201);
        }else{
            if ($res['status'] == 0){

            }
        }

    }
}
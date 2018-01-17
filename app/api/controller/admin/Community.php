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
// | DateTime: 2018/1/17/11:47
// +----------------------------------------------------------------------

namespace app\api\controller\admin;

use app\api\controller\BaseController;
use app\api\model\Community as CommunityModel;

class Community extends BaseController
{
    public function getCommunityList($page = 1, $size = 15)
    {
        $pageData = CommunityModel::getList($page,$size);
        return $pageData;
    }
}
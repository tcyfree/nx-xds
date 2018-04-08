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
// | DateTime: 2018/3/30/10:43
// +----------------------------------------------------------------------

namespace app\api\validate;


class MiniQRCodeValidate extends BaseValidate
{
    protected $rule = [
//        'path' => 'require',
        'width' => 'between:1,1000',
        'auto_color' => 'boolean'
    ];
}
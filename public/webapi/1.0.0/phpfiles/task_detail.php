<?php require_once("../include/head.inc.php");?>
<?php require_once(SYS_ROOT_PATH."include/language.inc.php");?>
<script>whbRemoveMask();</script>

<div class="contentDIV">
<p><img src="<?php echo SYS_EXTJS_URL?>images/apple2.gif" width="16" height="16" /> <span class="titlestyle">功能描述：任务详情接口</span></p>
<p class="subtitlestyle">（一）服务接口请求地址：</p>
<table width="90%" border="1" class="dbTable">
  <tr class="td_header">
    <td width="15%">字段名称</td>
      <td width="15%">请求类型</td>
    <td width="85%">字段信息</td>
    </tr>
  <tr>
    <td>请求的地址</td>
    <td>GET</td>
    <td>v1/task/:id</td>
  </tr>
</table>
<p class="subtitlestyle">（二）参数列表：</p>
<table width="90%" border="1" class="dbTable">
    <?php require_once ("../include/required_or_optional.php"); ?>
    <?php require_once ("../include/token.required.php"); ?>
  <tr>
    <td>id</td>
    <td>任务主键ID</td>
    <td>是</td>
    <td></td>
  </tr>
</table>
<?php require_once ("../include/json_info.php"); ?>
<p><span class="subtitlestyle">（四）特别备注</span>（infor字段说明，仅列出部分关键字段）</p>
<table width="90%" border="1" class="dbTable">
    <tr class="td_header">
    <td width="118">参数名称</td>
    <td width="210">参数说明</td>
    <td width="567">备注</td>
  </tr>
    <tr>
        <td>id</td>
        <td>任务主键ID</td>
        <td>&nbsp;</td>
    </tr>
  <tr>
    <td>name</td>
    <td>任务名称</td>
    <td>&nbsp;</td>
  </tr>
    <tr>
        <td>requirement</td>
        <td>要求</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>content</td>
        <td>内容</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>reference_time</td>
        <td>参考用时</td>
        <td>&nbsp;时间（s）</td>
    </tr>
    <tr>
        <td class="inforstyle">task_user</td>
        <td></td>
        <td>&nbsp;若用户还未GO，则为<span class="titlestyle"><font color="red">null</font> </span></td>
    </tr>
    <tr>
        <td>user_id</td>
        <td></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>finish</td>
        <td>该任务是否完成</td>
        <td>&nbsp;0 否  1 是</td>
    </tr>
    <tr>
        <td>create_time</td>
        <td>GO时间</td>
        <td>&nbsp;若finish为0，则倒计时为:now-create_time</td>
    </tr>
    <tr>
        <td class="inforstyle">task_feedback</td>
        <td>反馈</td>
        <td>&nbsp;未提交反馈/普通模式，则为<span class="titlestyle"><font color="red">[]</font> </span></td>
    </tr>
    <tr>
        <td>content</td>
        <td>反馈内容</td>
        <td></td>
    </tr>
    <tr>
        <td>status</td>
        <td>反馈状态</td>
        <td>0 待点评 1 未通过点评 2 点评通过 3 失效</td>
    </tr>
    <tr>
        <td>reason</td>
        <td>原因</td>
        <td></td>
    </tr>
    <tr>
        <td>create_time</td>
        <td>反馈时间</td>
        <td></td>
    </tr>

</table>
<p>&nbsp;</p>
</div>


<?php require_once("../include/foot.inc.php");?>
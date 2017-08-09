<?php require_once("../include/head.inc.php");?>
<?php require_once(SYS_ROOT_PATH."include/language.inc.php");?>
<script>whbRemoveMask();</script>

<div class="contentDIV">
<p><img src="<?php echo SYS_EXTJS_URL?>images/apple2.gif" width="16" height="16" /> <span class="titlestyle">功能描述：好友添加接口</span></p>
<p class="subtitlestyle">（一）服务接口请求地址：</p>
<table width="90%" border="1" class="dbTable">
  <tr class="td_header">
    <td width="15%">字段名称</td>
    <td width="85%">字段信息</td>
    </tr>
  <tr>
    <td>请求的地址</td>
    <td>[sys_web_service]friend_add</td>
  </tr>
</table>
<p class="subtitlestyle">（二）POST参数列表：</p>
<table width="90%" border="1" class="dbTable">
    <tr class="td_header">
        <td width="107">参数名称</td>
        <td width="235">参数说明</td>
        <td width="530">备注</td>
    </tr>
    <tr>
        <td>token</td>
        <td width="235">登录令牌</td>
        <td width="530">&nbsp;</td>
    </tr>
    <tr>
        <td>friend_id</td>
        <td width="235">所要添加的对方主键id</td>
        <td width="530">对应所要访问客户的client_id</td>
    </tr>
     <tr>
        <td>keytype</td>
        <td width="235">&nbsp;</td>
        <td width="530">keytype=1,关注 &nbsp;&nbsp; keytype=2，黑名单</td>
    </tr>
</table>
<p class="subtitlestyle">（三）服务接口响应请求：</p>
<table width="90%" border="1" class="dbTable">
  <tr class="td_header">
    <td width="51%">响应结果</td>
    <td width="49%">备注</td>
  </tr>
  <tr>
    <td>{"success":true,"msg":"操作成功"}}</td>
    <td><p>&nbsp;</p></td>
  </tr>
<?php require_once("../include/error.inc.php");?>
</table>
</div>
<?php require_once("../include/foot.inc.php");?>
<?php require_once("../include/head.inc.php");?>
<script>whbRemoveMask();</script>

<div class="contentDIV">
<p><img src="<?php echo SYS_EXTJS_URL?>images/apple2.gif" width="16" height="16" /> <span class="titlestyle">功能描述：更新支付操作接口</span></p>
<p class="subtitlestyle">（一）服务接口请求地址：</p>
<table width="90%" border="1" class="dbTable">
  <tr class="td_header">
    <td width="15%">字段名称</td>
    <td width="85%">字段信息</td>
    </tr>
  <tr>
    <td>请求的地址</td>
    <td>[sys_web_service]payflag_save</td>
  </tr>
</table>
<p class="subtitlestyle">（二）POST参数列表：</p>
<table width="90%" border="1" class="dbTable">
  <tr class="td_header">
    <td width="19%">参数名称</td>
    <td width="21%">参数说明</td>
    <td width="60%">备注</td>
    </tr>
   <tr>
    <td>token</td>
    <td>登录令牌</td>
    <td>&nbsp;</td>
  </tr>
   <tr>
     <td>order_no</td>
     <td>商户订单号</td>
     <td></td>
   </tr>
   <tr>
       <td>paid</td>
       <td>是否已付款。</td>
       <td align="center"> boolean</td>
   </tr>
</table>
<p class="subtitlestyle">（三）服务接口响应请求：</p>
<table width="90%" border="1" class="dbTable">
  <tr class="td_header">
    <td width="51%">响应结果</td>
    <td width="31%">备注</td>
  </tr>
  <tr>
    <td><p>{"success":true,"msg":"操作成功！","infor":null}</p></td>
    <td><p>&nbsp;</p></td>
  </tr>
<?php require_once("../include/error.inc.php");?>
</table>
</div>


<?php require_once("../include/foot.inc.php");?>
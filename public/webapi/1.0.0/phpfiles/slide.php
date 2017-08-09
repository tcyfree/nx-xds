<?php require_once("../include/head.inc.php");?>
<?php require_once(SYS_ROOT_PATH."include/language.inc.php");?>
<script>whbRemoveMask();</script>

<div class="contentDIV">
<p><img src="<?php echo SYS_EXTJS_URL?>images/apple2.gif" width="16" height="16" /> <span class="titlestyle">功能描述：获取展示列表信息</span></p>
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
    <td>v1/:id/slide</td>
  </tr>
</table>
<p class="subtitlestyle">（二）</p>
<table width="90%" border="1" class="dbTable">
    <tr class="td_header">
        <td width="106">参数名称</td>
        <td>参数说明</td>
        <td width="533">备注</td>
    <tr class="">
        <td>id</td>
        <td>列表类型</td>
        <td width="533" align="left"><p>1：Banner列表</p>
            <p>2：广告列表 </p>
            <p>3：澳门经济数据走势图</p>
    </tr>
</table>
<p class="subtitlestyle">（三）服务接口响应请求：</p>
<table width="90%" border="1" class="dbTable">
  <tr class="td_header">
    <td width="47%">响应结果</td>
    <td width="53%">备注</td>
  </tr>
  <tr>
    <td>{"success":true,"msg":"操作成功",&quot;infor&quot;:json信息串}</td>
    <td><p>详见（四）特别备注</p></td>
  </tr>
  <?php require_once("../include/error.inc.php");?>
</table>
<p><span class="subtitlestyle">（四）特别备注</span>（infor字段说明）</p>
<table width="90%" border="1" class="dbTable">
  <tr class="td_header">
    <td>参数名称</td>
    <td width="226">参数说明</td>
    <td width="598">备注</td>
  </tr>
  <tr>
      <td>id</td>
      <td>主键id</td>
      <td></td>
  </tr>
 <tr>
     <td>title</td>
     <td>标题</td>
     <td><p>&nbsp;</p></td>
 </tr>
    <tr>
        <td>image</td>
        <td>图片url</td>
        <td></td>
    </tr>
  <tr>
    <td>url</td>
    <td>链接</td>
    <td>&nbsp;</td>
  </tr>
</table>
<p>&nbsp;</p>
</div>
<?php require_once("../include/foot.inc.php");?>
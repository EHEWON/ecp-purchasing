<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport"content="width=device-width, initial-scale=1">
<title>【{{env('APP_NAME')}}】【{{$user['purchaserName']}}】询价结果通知</title>
</head>
<body>
<p>恭喜您，【{{$user['purchaserName']}}】的询价项目，您已中标，请尽快登录系统<a href="{{env('BOSS_URL')}}"target="_blank">{{env('BOSS_URL')}}</a>查看。</p>
<p class="MsoNormal"align="left">
<span style="color:#969FA9 ; font-size: 14px;">发布日期：</span>
<span style=" color:#333 ; font-size: 14px;">{{$user['data']['content_array']['bill_date']}}</span>
</p>
<p class="MsoNormal"align="left">
<span style="color:#969FA9 ; font-size: 14px;">项目名称：</span>
<span style=" color:#333 ; font-size: 14px;">{{$user['data']['content_array']['inquiry_title']}}</span>
</p>
<p class="MsoNormal"align="left">
<span lang="AR-SA"><span style="color:#969FA9 ; font-size: 14px;">项目编号：</span>
<span style=" color:#333 ; font-size: 14px;">{{$user['data']['content_array']['bill_no']}}</span>
</p>
<p class="MsoNormal"align="left">
<span style="color:#969FA9 ; font-size: 14px;">报价截止时间：</span>
<span style=" color:#333 ; font-size: 14px;">{{$user['data']['content_array']['due_date']}}</span>
</p>
<p class="MsoNormal"align="left">
<span style="color:#969FA9 ; font-size: 14px;">联系人：</span>
<span style=" color:#333333 ; font-size: 14px;">{{$user['data']['content_array']['person_name']}}</span>
</p>
<p class="MsoNormal"align="left">
<span style="color:#969FA9 ; font-size: 14px;">联系方式：</span>
<span style=" color:#333333 ; font-size: 14px;">{{$user['data']['content_array']['person_phone']}}</span>
</p>
<p class="MsoNormal"align="left">
<span style="color:#969FA9 ; font-size: 14px;">付款条件：</span>
<span style=" color:#333 ; font-size: 14px;">{{$user['data']['content_array']['paycond_name']}}@if($user['data']['content_array']['paycond_name']==''&&!empty($user['data']['content_array']['other_pay_terms_info']))-{{$user['data']['content_array']['other_pay_terms_info']}}@endif</span>
</p>
<p class="MsoNormal"align="left">
<span style=" color:#969FA9 ; font-size: 14px;">中标清单：</span>
</p>
<table style="border-collapse: collapse; width: 100%;"border="1">
<tbody>
<tr>
<td style="width: 9;color: #999999;font-size: 14px;">序号</td>
<td style="width: 18.7213%;color: #999999;font-size: 14px;">物料名称</td>
<td style="width: 15.7855%;color: #999999;font-size: 14px;">物料描述</td>
<td style="width: 6.43983%;color: #999999;font-size: 14px;">订货数量</td>
<td style="width: 18.7213%;color: #999999;font-size: 14px;">询价单位</td>
<td style="width: 18.7213%;color: #999999;font-size: 14px;">确定供应商</td>
</tr>
@foreach($user['data']['content_array']['materials']as $key=>$material)
<tr>
<td style="width: 9%;">{{$key+1}}</td>
<td style="width: 18.7213%;color: #333;font-size: 14px;">{{$material['material_name']}}</td>
<td style="width: 15.7855%;color: #333;font-size: 14px;">{{$material['material_desc']}}</td>
<td style="width: 6.43983%;color: #333;font-size: 14px;">{{number_format($material['inquire_qty'],$material['precision'],'.',',')}}</td>
<td style="width: 18.7213%;color: #333;font-size: 14px;">{{$material['inquiry_unit_id_name']}}</td>
<td style="width: 18.7213%;color: #333;font-size: 14px;">{{$material['supplier_name']}}</td>
</tr>
@endforeach
</tbody>
</table>
</body>
</html>
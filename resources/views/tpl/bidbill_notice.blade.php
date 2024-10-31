
<p class="MsoNormal" align="left">
	<span style=" color:#969FA9 ; font-size: 14px;">
		发布日期：
	</span>
	<span style=" color:#333333 ; font-size: 14px;">
		{{$bill_date}}
	</span>
</p>
<p class="MsoNormal" align="left">
	<span style=" color:#969FA9 ; font-size: 14px;">
		项目名称：
	</span>
	<span style="color:#333333 ; font-size: 14px;">
		{{$bidbill_title}}
	</span>
</p>
<p class="MsoNormal" align="left">
	<span style=" color:#969FA9 ; font-size: 14px;">
		项目编号：
	</span>
	<span style=" color:#333333 ; font-size: 14px;">
		{{$bill_no}}
	</span>
</p>
<p class="MsoNormal" align="left">
	<span style=" color:#969FA9 ; font-size: 14px;">
		报价截止时间：
	</span>
	<span style=" color:#333333 ; font-size: 14px;">
		{{$due_date}}
	</span>
</p>
<p class="MsoNormal" align="left">
	<span style=" color:#969FA9 ; font-size: 14px;">
		联系人：
	</span>
	<span style=" color:#333333 ; font-size: 14px;">
		{{$person_name}}
	</span>
</p>
<p class="MsoNormal" align="left">
	<span style=" color:#969FA9 ; font-size: 14px;">
		联系方式：
	</span>
	<span style=" color:#333 ; font-size: 14px;">
		{{$person_phone}}
	</span>
</p>
<p class="MsoNormal" align="left">
	<span style=" color:#969FA9 ; font-size: 14px;">
		竞价时长：
	</span>
	<span style=" color:#333 ; font-size: 14px;">
		{{$bid_time}}
	</span>
</p>
<p class="MsoNormal" align="left">
	<span style=" color:#969FA9 ; font-size: 14px;">
		竞价清单：
	</span>
</p>
<table style="border-collapse: collapse; width: 100%;" border="1">
	<tbody>
		<tr>
			<td style="width: 9%;color: #999999;font-size: 14px;">
				序号
			</td>
			<td style="width: 22.7213%;color: #999999;font-size: 14px;">
				物料名称
			</td>
			<td style="width: 19.7855%;color: #999999;font-size: 14px;">
				物料描述
			</td>
			<td style="width: 10.43983%;color: #999999;font-size: 14px;">
				竞价数量
			</td>
			<td style="width: 22.7213%;color: #999999;font-size: 14px;">
				竞价单位
			</td>
		</tr>
		@foreach($materials as $key=>$material)
		<tr>
			<td style="width: 9%;color: #333;font-size: 14px;">
				{{$key+1}}
			</td>
			<td style="width: 22.7213%;color: #333;font-size: 14px;">
				{{$material['material_name']}}
			</td>
			<td style="width: 19.7855%;color: #333;font-size: 14px;">
				{{$material['material_desc']}}
			</td>
			<td style="width: 10.43983%;color: #333;font-size: 14px;">
				{{number_format($material['qty'],$material['precision'],'.',',')}}
			</td>
			<td style="width: 22.7213%;color: #333;font-size: 14px;">
				{{$material['unit_id_name']}}
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
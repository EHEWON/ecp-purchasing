<p>

	<p style="line-height: 2em; color: rgb(80, 80, 80); font-family: 微软雅黑, Arial; font-size: 14px;white-space: normal; display: inline-block;width:100%;">
		&nbsp;&nbsp;&nbsp;&nbsp;&#8203;&nbsp;&nbsp;&nbsp;&nbsp;&#8203;我司拟对【{{$name}}】进行招标，诚邀各单位进行投标，招标信息详情如下:
		</span>
	</p>
	<p style="line-height: 30px;font-size: 14px;font-weight: normal;">
		<strong style="padding: 0px; margin: 0px; color: #505050; font-family: 微软雅黑, Arial;font-size: 14px; text-align: justify; white-space: normal;">
			公开招标项目：
		</strong>
		{{$name}}公开招标
	</p>
	<p style="line-height: 30px;font-size: 14px;font-weight: normal;">
		<strong style="padding: 0px; margin: 0px; color: #505050; font-family: 微软雅黑, Arial;font-size: 14px; text-align: justify; white-space: normal;">
			采购类型：
		</strong>
		@if(!empty($pur_type_name))
                {{$pur_type_name}}
                @endif
	</p>
	<p style="line-height: 30px;font-size: 14px;font-weight: normal;">
		<strong style="padding: 0px; margin: 0px; color: #505050; font-family: 微软雅黑, Arial;font-size: 14px; text-align: justify; white-space: normal;">
			公开截止时间：
		</strong>
		{{$enroll_deadline}}
	</p>
        <p style="line-height: 30px;font-size: 14px;font-weight: normal;">
		<strong style="padding: 0px; margin: 0px; color: #505050; font-family: 微软雅黑, Arial;font-size: 14px; text-align: justify; white-space: normal;">
			标书费：
		</strong>
		@if(!empty($tender_fee))
               ¥ {{number_format($tender_fee,2,'.',',')}}
                @else
                无
                @endif
	</p>
        <p style="line-height: 30px;font-size: 14px;font-weight: normal;">
		<strong style="padding: 0px; margin: 0px; color: #505050; font-family: 微软雅黑, Arial;font-size: 14px; text-align: justify; white-space: normal;">
			保证金：
		</strong>
		@if(!empty($deposit))
                ¥ {{number_format($deposit,2,'.',',')}}
                @else
                无
                @endif
	</p>
	<p style="line-height: 30px;font-size: 14px;font-weight: normal;">
		<strong style="padding: 0px; margin: 0px; color: #505050; font-family: 微软雅黑, Arial;font-size: 14px; text-align: justify; white-space: normal;">
			招标内容说明：
		</strong>
		{{$pur_description}}
	</p>
	<p style="line-height: 30px;font-size: 14px;font-weight: normal;">
		<strong style="padding: 0px; margin: 0px; color: #505050; font-family: 微软雅黑, Arial;font-size: 14px; text-align: justify; white-space: normal;">
			联系人：
		</strong>
		{{$contact_name}}
	</p>
	<p style="line-height: 30px;font-size: 14px;font-weight: normal;">
		<strong style="padding: 0px; margin: 0px; color: #505050; font-family: 微软雅黑, Arial;font-size: 14px; text-align: justify; white-space: normal;">
			联系电话：
		</strong>
		{{$contact_tel}}
	</p>
	<p style="line-height: 30px;font-size: 14px;font-weight: normal;">
		<strong style="padding: 0px; margin: 0px; color: #505050; font-family: 微软雅黑, Arial;font-size: 14px; text-align: justify; white-space: normal;">
			联系邮箱：
		</strong>
		{{$email}}
	</p>
	<p style="line-height: 30px;font-size: 14px;font-weight: normal;">
		<strong style="padding: 0px; margin: 0px; color: #505050; font-family: 微软雅黑, Arial;font-size: 14px; text-align: justify; white-space: normal;">
			联系地址：
		</strong>
		{{$address}}
	</p>
</p>
</p>
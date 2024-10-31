<p><p style="line-height: 2em; color: rgb(80, 80, 80); font-family: 微软雅黑, Arial; font-size: 14px;white-space: normal;"><span style="color:black;display: inline-block;font-weight:bold; ">{{$supplier_name}} : </span></p><p style="line-height: 2em; color: rgb(80, 80, 80); font-family: 微软雅黑, Arial; font-size: 14px;white-space: normal; display: inline-block;width:100%;">&nbsp;&nbsp;&nbsp;&nbsp;&#8203;&nbsp;&nbsp;&nbsp;&nbsp;&#8203;恭喜！你参与的【{{$name}}】已经中标，详情请查阅附件，期待与您的合作。</span>
</p>
@if(!empty($eva_reports))
@foreach($eva_reports as $key=>$evaReport) 
<a href="{{$evaReport['attach_url']}}" target="_blank">{{$evaReport['attach_name']}}</a>
@endforeach
@endif
@if(!empty($win_reports))
@foreach($win_reports as $key=>$winReport) 
<a href="{{$winReport['attach_url']}}" target="_blank">{{$winReport['attach_name']}}</a>
@endforeach
@endif
@if(!empty($attachs))
@foreach($attachs as $key=>$attach) 
<a href="{{$attach['attach_url']}}" target="_blank">{{$attach['attach_name']}}</a>
@endforeach
@endif
</p>
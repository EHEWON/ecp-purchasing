
<div class="ecp_project_search">
    <div class="flex_box">
        <span class="title">招标类型</span>
        <div class="condition_li @if ( empty($request['biztypes'])) actived @endif">全部</div>
        <div class="condition_li @if (!empty($request['biztypes']) &&  $request['biztypes']=='1,A') actived @endif" name="biztypes" value="1,A">询价</div>
        <div class="condition_li @if ( !empty($request['biztypes']) && $request['biztypes']=='3,B') actived @endif" name="biztypes" value="3,B">竞价</div>
        <div class="condition_li @if (!empty($request['biztypes']) &&  $request['biztypes']=='2,5') actived @endif" name="biztypes" value="2,5">招标</div>
    </div>
    <div class="flex_box">
        <span class="title">公告类型</span>
        <div class="condition_li @if ( empty($request['antypes'])) actived @endif">全部</div>
        <div class="condition_li @if (!empty($request['antypes']) &&  $request['antypes']=='1,2,3') actived @endif" name="antypes" value="1,2,3">采购公告</div>
        <div class="condition_li @if (!empty($request['antypes']) &&  $request['antypes']=='A,B,5') actived @endif" name="antypes" value="A,B,5">结果公示</div>
    </div>
    <div class="flex_box">
        <span class="title">发布时间</span>
        <div class="condition_li @if ( empty($request['created'])) actived @endif">全部</div>
        <div class="condition_li @if ( !empty($request['created']) && $request['created']==7) actived @endif" name="created" value="7">近一周</div>
        <div class="condition_li @if ( !empty($request['created']) && $request['created']==30) actived @endif" name="created" value="30">近一个月</div>
        <div class="condition_li @if ( !empty($request['created']) && $request['created']=='custom') actived @endif" name="created" value="custom">自定义</div>
        <input type="text" style="@if ( empty($request['created']) || $request['created']!='custom') display:none; @endif" name="published_ats" id="published_ats" value="@if ( (!empty($request['published_ats']) && is_array($request['published_ats']))) {{$request['published_ats'][0]}} ~ {{$request['published_ats'][1]}} @endif" placeholder="选择日期（起 - 止）" autocomplete="off">
    </div>
    <div class="flex_box">
        <span class="title">公告失效</span>
        <div class="condition_li @if ( empty($request['expired'])) actived @endif">全部</div>
        <div class="condition_li @if ( !empty($request['expired'])&& $request['expired']==='N') actived @endif" name="expired" value="N">进行中</div>
        <div class="condition_li @if ( !empty($request['expired'])&&  $request['expired']==='Y') actived @endif" name="expired" value="Y">已截止</div>
    </div>
    <div class="flex_box mt_10">
        <span class="title">关键词</span>
        <div class="input_box">
            @if ( !empty($request['keyword']))
            <input type="text" name="keyword" value="{{$request['keyword']}}" placeholder='项目名称'>
            @else
            <input type="text" name="keyword" value="" placeholder='项目名称'>
            @endif
        </div>
        <div class="project_btn" onclick="getResult(1, {{$pagesize}}, 1)">搜索</div>
    </div>
</div>
<div class="tabel_box">
    <table>
        <thead>
            <tr>
                <th>类型</th>
                <th>项目名称</th>
                <th>来源单位</th>
                <th>发布时间</th>
                <th>剩余时间</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @if ( !empty($data) ) 
            @foreach ( $data as $item) 
           
            <tr>
                <td width="100">
                    @if ( $item['type']=='inquiry') 
                    <span class="tag tag-success">{{$item['type_name']}}</span>
                    @elseif ($item['type']=='bid_project')
                    <span class="tag tag-warning">{{$item['type_name']}}</span>
                    @else
                    <span class="tag tag-primary">{{$item['type_name']}}</span>
                    @endif
                </td>
                <td width="300">
                    <div class="title multiline_ellipsis_1">
                        @if ( $item['type']=='inquiry') 
                        <a href="/frontend/inquiry/{{$item['id']}}">{{$item['title']}}</a>
                        @elseif ($item['type']=='bid_bill')
                        <a href="/frontend/bidding/{{$item['id']}}">{{$item['title']}}</a>            
                        @elseif ($item['type']=='bid_project')
                        <a href="/frontend/tendering/{{$item['id']}}">{{$item['title']}}</a>
                        @endif
                    </div>
                </td>
                <td width="280">
                    <div class="company_name multiline_ellipsis_1">{{$item['org_name']}}</div>
                </td>
                <td width="170"><div class="time">{{$item['publish_date']}}</div></td>
                <td width="180"><div class="time">@if ( empty($item['left_time']) || $item['biztype']==='A' ) 已截止 @else 剩余{{$item['left_time']}} @endif</div></td>
                <td>
                    @if ( $item['type']=='inquiry') 
                    <a class="table_btn" href="/frontend/inquiry/{{$item['id']}}">查看详情</a>
                    @elseif ($item['type']=='bid_bill')
                    <a class="table_btn" href="/frontend/bidding/{{$item['id']}}">查看详情</a>     
                    @elseif ($item['type']=='bid_project')
                    <a class="table_btn" href="/frontend/tendering/{{$item['id']}}">查看详情</a>
                    @endif
                </td>
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>
    <div class="page_content clearfix" data-pagesize="{{$pagesize}}">
        {!! $pager !!}
    </div>
</div>

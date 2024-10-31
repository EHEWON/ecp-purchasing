<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>竞价公告 @if ( !empty($bid_bill)) {{$bid_bill['name']}} @endif</title>
        <meta id="meta_description" name="description" content="" />
        <link rel="icon" href="/front/EHAdmin.ico">
        <meta name="keywords" content="" />
        <link rel="stylesheet" href="/static/ecp_index/index.css">
        <link rel="stylesheet" href="/static/ecp_index/ecp_detail.css">
        <script src="/static/ecp_index/jquery.min.js"></script>
    </head>

    <body class="body_container">
        <div class="container_box">
            @include('tpl.search', [])
            <div class="container">
                <div class="ecp_nav_box">
                    <a href="/">首页</a><i>/</i>
                    <a href="/frontend/list?biztypes=3,B">竞价项目</a><i>/</i>
                    <span>项目详情</span>
                </div>

                <div class="status-tit-box">
                    <h3>公告详情</h3>
                    <ul class="inquiry-status ">
                        <li class="@if ( in_array($status,['A','B','I','K','L','C','D','E','H','M','G'])) active @endif">
                            <p>1</p>
                            采购公告
                        </li>
                        <li class="@if ( in_array($status,['A','B','I','K','L','C','D','E','H','M','G'])) active @endif">
                            <p>2</p>
                            报名阶段
                        </li>
                        <li class="@if ( in_array($status,['C','D','E','H'])) active @endif">
                            <p>3</p>
                            竞价阶段
                        </li>
                        <li class="@if ( in_array($status,['D','E'])) active @endif">
                            <p>3</p>
                            评标阶段
                        </li>
                        <li class="@if ( in_array($status,['E'])) active @endif">
                            <p>4</p>
                            结果公示
                        </li>
                    </ul>

                </div>

                <div class="project-box mt_12">
                    <h3>项目信息</h3>
                    <div class="project-info">
                        <p>
                            <span class="project-label">采购商</span>
                            <span class="project-value">{{$org_name}}</span>
                        </p>
                        <p>
                            <span class="project-label">项目编号</span>
                            <span class="project-value">@if ( !empty($bid_bill)) {{$bid_bill['bill_no']}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">项目名称</span>
                            <span class="project-value">@if ( !empty($bid_bill)) {{$bid_bill['name']}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">发布时间</span>
                            <span class="project-value">{{$publish_date}}</span>
                        </p>
                        <p>
                            <span class="project-label">报名截止时间</span>
                            <span class="project-value">@if ( !empty($bid_bill)) {{$bid_bill['enroll_date']}} @endif</span>
                        </p>
                        <div class="btn-box @if ( empty($left_time))  end @endif">
                            @if ( !empty($left_time))
                            <a href="/front/#/biddingManage/biddingDetail?id={{$src_bill_id}}">立即报名</a>
                            <span>仅剩{{$left_time}}</span>
                            @else
                            <a href="javascript:;" class="end active">已截止</a>
                            @endif

                        </div>
                    </div>
                </div>

                @if ( !empty($entry) && !in_array($status,['E'])) 
                <div class="materials-box mt_12">
                    <h3>物资信息</h3>
                    <table>
                        <thead>
                            <tr>
                                <th width="60">序号</th>
                                <th width="300">物料名称</th>
                                <th width="300">物料描述</th>
                                <th width="150">竞价数量</th>
                                <th width="150">竞价单位</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entry as $key=> $item)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$item['material_name']}}</td>
                                <td>{{$item['material_desc']}}</td>
                                <td>{{$item['qty']}}</td>
                                <td>{{$item['unit_id_name']}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @elseif ( !empty($entry) && in_array($status,['E']))
                <div class="materials-box mt_12">
                    <h3>物资信息</h3>
                    <table>
                        <thead>
                            <tr>
                                <th width="60">序号</th>
                                <th width="250">物料名称</th>
                                <th width="250">物料描述</th>
                                <th width="120">中标数量</th>
                                <th width="120">竞价单位</th>
                                <th width="160">中标供应商</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entry as $key=> $item)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$item['material_name']}}</td>
                                <td>{{$item['material_desc']}}</td>
                                <td>{{$item['qty']}}</td>
                                <td>{{$item['unit_id_name']}}</td>
                                <td>{{$item['supplier_name']}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                <div class="project-box mt_12 mb_20">
                    <h3>项目说明</h3>
                    <div class="project-info">
                        <p>
                            <span class="project-label">发票类型</span>
                            <span class="project-value">@if ( !empty($bid_bill)) {{$bid_bill['inv_type_name']}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">竞价时长（分钟）</span>
                            <span class="project-value">@if ( !empty($bid_bill)) {{$bid_bill['bid_time']}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">联系人</span>
                            <span class="project-value">@if ( !empty($bid_bill)) {{$bid_bill['person_name']}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">联系电话</span>
                            <span class="project-value">@if ( !empty($bid_bill)) {{$bid_bill['phone']}} @endif</span>
                        </p>
                    </div>
                </div>


            </div>
            @include('tpl.footer', [])
        </div>
    </body>

</html>
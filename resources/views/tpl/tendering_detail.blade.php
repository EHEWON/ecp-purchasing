<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>招标公示 - @if ( !empty($project)) {{$project['name']}} @endif</title>
        <meta id="meta_description" name="description" content="" />
        <meta name="keywords" content="" />
        <link rel="icon" href="/front/EHAdmin.ico">
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
                    <a href="/frontend/list?biztypes=2,5">招标项目</a><i>/</i>
                    <span>项目详情</span>
                </div>

                <div class="status-tit-box">
                    <h3>公告详情</h3>
                    <ul class="inquiry-status ">
                        <li class="active">
                            <p>1</p>
                            采购公告
                        </li>
                        <li class="active">
                            <p>2</p>
                            报名阶段
                        </li>
                        <li class="@if ( !empty($project) && $project['bid_document']=='1') active @endif">
                            <p>3</p>
                            标书阶段
                        </li>
                        <li class="@if ( in_array($status,['I','K','H'])) active @endif">
                            <p>4</p>
                            投标阶段
                        </li>
                        <li class="@if ( !empty($project) && $project['bid_decision']=='1') active @endif">
                            <p>5</p>
                            结果公示
                        </li>
                    </ul>

                </div>

                <div class="project-box mt_12">
                    <h3>项目信息</h3>
                    <div class="project-info">
                        <p>
                            <span class="project-label">采购商</span>
                            <span class="project-value">@if ( !empty($project)) {{$org_name}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">项目编号</span>
                            <span class="project-value">@if ( !empty($project)) {{$project['bill_no']}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">项目名称</span>
                            <span class="project-value">@if ( !empty($project)) {{$project['name']}} @endif</span>
                        </p>

                        <p>
                            <span class="project-label">发布时间</span>
                            <span class="project-value">@if ( !empty($project)) {{$project['setup_date']}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">报价截止时间</span>
                            <span class="project-value">@if ( !empty($project)) {{$project['enroll_deadline']}} @endif</span>
                        </p>
                        <div class="btn-box @if ( empty($left_time))  end @endif">
                            @if  (!empty($left_time))
                            <a href="/front/#/supplierTenders/entryDetails?id={{$src_bill_id}}">立即报名</a>
                            <span>仅剩{{$left_time}}</span>
                            @else
                            <a href="javascript:;" class="end active">已截止</a>
                            @endif

                        </div>
                    </div>
                </div>
                @if ( !empty($entry) && !empty($project) &&  $project['bid_decision']=='1') 
                <div class="materials-box mt_12">
                    <table>
                        <thead>
                            <tr>
                                <th width="60">序号</th>
                                <th width="250">招标内容</th>
                                <th width="250">采购项目</th>
                                <th width="120">总工期(天)</th>
                                <th width="120">说明</th>
                                <th width="160">中标供应商</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entry as $key=> $item)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$item['purentry_content']}}</td>
                                <td>{{$item['pur_project_name']}}</td>
                                <td>{{$item['work_load']}}</td>
                                <td>{{$item['comment']}}</td>
                                <td>{{$project['supplier_name']}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else (!empty($entry))
                <div class="materials-box mt_12">
                    <h3>物资信息</h3>
                    <table>
                        <thead>
                            <tr>
                                <th width="60">序号</th>
                                <th width="250">招标内容</th>
                                <th width="250">采购项目</th>
                                <th width="120">总工期(天)</th>
                                <th width="120">说明</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entry as $key=> $item)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$item['purentry_content']}}</td>
                                <td>{{$item['pur_project_name']}}</td>
                                <td>{{$item['work_load']}}</td>
                                <td>{{$item['comment']}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <div class="project-box mt_12">
                    <h3>项目说明</h3>

                    <div class="project-info">
                        <p>
                            <span class="project-label">联系人</span>
                            <span class="project-value">@if ( !empty($project)) {{$project['contact_name']}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">联系电话</span>
                            <span class="project-value">@if ( !empty($project)) {{$project['contact_tel']}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">资质要求</span>
                            <span class="project-value">@if ( !empty($project)) {{$project['qualification_required']}} @endif</span>
                        </p>

                    </div>
                </div>
            </div>
            @include('tpl.footer', [])
    </body>

</html>
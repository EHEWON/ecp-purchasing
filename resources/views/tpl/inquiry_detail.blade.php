<!--询价公告 详情-->
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>询价公告 @if (!empty($inquiry)){{$inquiry['title']}} @endif</title>
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
                    <a href="/frontend/list?biztypes=1,A">询价项目</a><i>/</i>
                    <span>项目详情</span>
                </div>

                <div class="status-tit-box">
                    <h3>公告详情</h3>
                    <ul class="inquiry-status ">
                        <li class="@if (in_array($status,['A','B','C','D','E']))active @endif">
                            <p>1</p>
                            采购公告
                        </li>
                        <li class="@if (in_array($status,['A','B','C','D','E']))active @endif">
                            <p>2</p>
                            报价阶段
                        </li>
                        <li class="@if (in_array($status,['B','C']))active @endif">
                            <p>3</p>
                            开标阶段
                        </li>
                        <li class="@if (in_array($status,['C']))active @endif">
                            <p>3</p>
                            比价阶段
                        </li>
                        <li class="@if (in_array($status,['C']))active @endif">
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
                            <span class="project-value">@if (!empty($inquiry)){{$inquiry['bill_no']}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">项目名称</span>
                            <span class="project-value">@if (!empty($inquiry)){{$inquiry['title']}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">发布时间</span>
                            <span class="project-value">{{$publish_date}}</span>
                        </p>
                        <p>
                            <span class="project-label">报价截止时间</span>
                            <span class="project-value">@if (!empty($inquiry)){{$inquiry['end_date']}} @endif</span>
                        </p>
                        <!-- end  判断已截止状态 -->
                        <div class="btn-box @if (empty($left_time))end @endif">
                            @if (!empty($left_time) &&  $biztype==='1')
                            <a href="/front/#/quoteManage/quoteAssistant?id={{$src_bill_id}}&type=add">立即报价</a>
                            <span>仅剩{{$left_time}}</span>
                            @else
                            <a href="javascript:;" class="end active">已截止</a>
                            @endif

                        </div>
                    </div>
                </div>

                @if (!empty($entry) && $biztype=='1')
                <div class="materials-box mt_12">
                    <h3>物资信息</h3>
                    <table>
                        <thead>
                            <tr>
                                <th width="60">序号</th>
                                <th width="300">物料名称</th>
                                <th width="300">物料描述</th>
                                <th width="150">询价数量</th>
                                <th width="150">询价单位</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entry as $key=> $item)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$item['material_name']}}</td>
                                <td>{{$item['material_desc']}}</td>
                                <td>{{$item['inquire_qty']}}</td>
                                <td>{{$item['inquiry_unit_id_name']}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @elseif (!empty($entry) && $biztype=='A')
                <div class="materials-box mt_12">
                    <h3>物资信息</h3>
                    <table>
                        <thead>
                            <tr>
                                <th width="60">序号</th>
                                <th width="250">物料名称</th>
                                <th width="250">物料描述</th>
                                <th width="100">中标数量</th>
                                <th width="100">询价单位</th>
                                <th width="200">中标供应商</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entry as $key=> $item)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$item['material_name']}}</td>
                                <td>{{$item['material_desc']}}</td>
                                <td>{{$item['qty']}}</td>
                                <td>{{$item['inquiry_unit_id_name']}}</td>
                                <td>{{$item['supplier_name']}}</td>
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
                            <span class="project-label">发票类型</span>
                            <span class="project-value">@if (!empty($inquiry)){{$inquiry['inv_type_name']}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">计税类型</span>
                            <span class="project-value">@if (!empty($inquiry)){{$inquiry['tax_cal_type_name']}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">联系人</span>
                            <span class="project-value">@if (!empty($inquiry)){{$inquiry['person_name']}} @endif</span>
                        </p>
                        <p>
                            <span class="project-label">联系电话</span>
                            <span class="project-value">@if (!empty($inquiry)){{$inquiry['phone']}} @endif</span>
                        </p>
                    </div>
                </div>


            </div>
            @include('tpl.footer', [])
    </body>


</html>
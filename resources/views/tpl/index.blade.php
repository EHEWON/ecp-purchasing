<!--ecp 首页-->
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>瑞招采首页</title>
        <link rel="stylesheet" href="/static/ecp_index/index.css">
        <link rel="icon" href="/front/EHAdmin.ico">
        <script src="/static/ecp_index/jquery.min.js"></script>
    </head>

    <body class="body_container">
        <div class="container_box">
            @include('tpl.search', [])
            <div class="container_center mt_12 pt_20">
                <div class="show_contet">
                    <div class="flex_box banner">
                        <div class="banner_left">
                            <img src="/static/ecp_index/banner.png" alt="login">
                        </div>
                        <div class="banner_right flex_direction_col">
                            <div class="align_center">欢迎来到瑞招采平台，点此注册/登录</div>
                            <div class="btn_box flex_box flex_direction_between mt_12">
                                <a href="/front/#/login" class="bth btn_login pointer">立即登录</a>
                                <a href="/front/#/register" class="bth btn_register pointer" type="primary">免费注册</a>
                            </div>
                            <div class="tab_box flex_box flex_direction_between mt_12">
                                <div class="tab_li actived">
                                    最新成交
                                </div>
                                <div class="tab_li">
                                    最新入驻
                                </div>
                            </div>
                            <div class="tab_menu_box actived">
                                @foreach($last_quote as $key => $quote) 
                                <span class="tab_menu_li ellipsis_nowrap">{{$quote['title']}}</span>
                                @endforeach
                            </div>
                            <div class="tab_menu_box">
                                @foreach($last_settled as $key => $settled) 
                                <span class="tab_menu_li ellipsis_nowrap">{{$settled['name']}}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <!-- 询价项目 -->
                    <div class="project">
                        <div class="project_title">
                            <span>询价项目</span>
                        </div>
                        <div class="project_box">
                            @if(!empty($inquiry['data']))
                            @foreach($inquiry['data'] as $key => $inquiryInfo) 
                            <div class="project_item">
                                <div class="item_title ellipsis_nowrap"><a href="/frontend/inquiry/{{$inquiryInfo['id']}}">
                                        {{$inquiryInfo['title']}}</a></div>
                                <div class="name">{{$inquiryInfo['org_name']}}</div>
                                <div class="time">发布时间：{{$inquiryInfo['publish_date']}}</div>
                                <div class="tag tag-warning">@if(!empty($inquiryInfo['left_time']))
                                    {{$inquiryInfo['left_time']}}
                                    @else
                                    已截止
                                    @endif
                                </div>
                                <div class="btn_box">
                                    <a href="/frontend/inquiry/{{$inquiryInfo['id']}}">
                                        <span>查看详情
                                            <img src="/static/ecp_index/icon_right.png" />
                                        </span>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                    <!-- 招标项目 -->
                    <div class="project">
                        <div class="project_title">
                            <span>招标项目</span>
                        </div>
                        <div class="project_box">
                            @if(!empty($tendering['data']))
                            @foreach($tendering['data'] as $key => $tenderingInfo) 
                            <div class="project_item">
                                <div class="item_title ellipsis_nowrap"><a href="/frontend/tendering/{{$tenderingInfo['id']}}">
                                        {{$tenderingInfo['title']}}</a></div>
                                <div class="name">{{$tenderingInfo['org_name']}}</div>
                                <div class="time">发布时间：{{$tenderingInfo['publish_date']}}</div>
                                <div class="tag tag-warning">@if(!empty($tenderingInfo['left_time']))
                                    {{$tenderingInfo['left_time']}}
                                    @else
                                    已截止
                                    @endif</div>
                                <div class="btn_box">
                                    <a href="/frontend/tendering/{{$tenderingInfo['id']}}">
                                        <span>查看详情
                                            <img src="/static/ecp_index/icon_right.png" />
                                        </span>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                    <!-- 竞价项目 -->
                    <div class="project">
                        <div class="project_title">
                            <span>竞价项目</span>
                        </div>
                        <div class="project_box">
                            @if(!empty($bidding['data']))
                            @foreach($bidding['data'] as $key => $biddingInfo) 
                            <div class="project_item">
                                <div class="item_title ellipsis_nowrap"> <a href="/frontend/bidding/{{$biddingInfo['id']}}">
                                        {{$biddingInfo['title']}}</a></div>
                                <div class="name">{{$biddingInfo['org_name']}}</div>
                                <div class="time">发布时间：{{$biddingInfo['publish_date']}}</div>
                                <div class="tag tag-warning">@if(!empty($biddingInfo['left_time']))
                                    {{$biddingInfo['left_time']}}
                                    @else
                                    已截止
                                    @endif</div>
                                <div class="btn_box">
                                    <a href="/frontend/bidding/{{$biddingInfo['id']}}">
                                        <span>查看详情
                                            <img src="/static/ecp_index/icon_right.png" />
                                        </span>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>

                </div>

            </div>
            @include('tpl.footer', [])
        </div>
        <script>
$(function () {
    $(".tab_box .tab_li").click(function () {
        let _index = $(this).index();
        $(this).parent().find(".actived").removeClass("actived");
        $(this).addClass("actived");
        $(".tab_menu_box").hide();
        $(".tab_menu_box").eq(_index).show();
    })
    $(".tab_box .tab_li").eq(0).click();
})
        </script>
    </body>
</html>
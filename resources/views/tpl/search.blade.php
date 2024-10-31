<div class="top_banner flex_center">
    <div class="search_box">
        <a href="/"><img src="/static/ecp_index/logo.png" alt="LOGO"></a>
        @if ( !empty($request['keyword']))
        <input class='search_input' value="{{$request['keyword']}}" placeholder='请输入搜索内容' clearable/>
        @else
        <input class='search_input' value="" placeholder='请输入搜索内容' clearable/>
        @endif

        <a class="search_btn flex_center"><img src="/static/ecp_index/search.png">搜索</a>
    </div>
</div>
<script>
    $(function () {
        //搜索
        $(".search_btn").click(function () {
            let _keyword = $(".search_input").val().trim();
            _url = '/frontend/list?keyword=' + encodeURIComponent(filterCharacters(_keyword, false));
            window.location.href = _url;
        });
    });
    function filterCharacters(str, is_limit_length) {
        str = decodeURI(str).replace(/_/, '%5F')
                .replace(/-/, '%2D')
                .replace(/-/, '%2D')
                .replace(/\//g, '%2F')
                .replace(/\r/g, ' ')
                .replace(/\n/g, ' ')
                .replace(/\t/g, ' ');
        if (is_limit_length) {
            str = str.substring(0, is_limit_length);
        }
        return $.trim(str);
    }
</script>
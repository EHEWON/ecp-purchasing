var getResult = function (page, pagesize) {
    let layer_index = layer.load(3, {shade: [0.5, '#f2f2f2']});
    var keyword = $('input[name=keyword]').val();
    let created_type = $("#created_type").val();
    let countrys = $("#countrys").val();
    let industry_id = $("#industry_id").val();
    let inquiry_type = $("#inquiry_type").val();
    let trade_terms = $("#trade_terms").val();
    let str = '';
    $(".condition_li.actived").each((index, item) => {
        let name = $(item).attr("name");
        let value = $(item).attr("value");
        if (typeof (name) != 'undefined') {
            str += "&" + name + '=' + value;
            if (name == "created" && value == "custom") {
                let published_ats = [];
                if ($("#published_ats").val()) {
                    published_ats.push($("#published_ats").val().split('~')[0].trim());
                    published_ats.push($("#published_ats").val().split('~')[1].trim());
                }
                if (published_ats.length > 0) {
                    str += '&published_ats[0]=' + published_ats[0] + '&published_ats[1]=' + published_ats[1];
                }
            }
        }
    })

    var mallurl = '/frontend/list?keyword=' + encodeURIComponent(filterCharacters(keyword, false)) + str;

    let pagesize1 = $('.ppe_search').attr("data-pagesize");

    if (typeof (pagesize) !== 'undefined') {
        mallurl += '&pagesize=' + pagesize;
    } else if (typeof (pagesize1) !== 'undefined') {
        mallurl += '&pagesize=' + pagesize1;
    }
    mallurl += '&page=' + page;

    $.get(mallurl, function (data) {
        layer.close(layer_index);
        if (data.code == '0000' && data.ret == 200) {
            $('.ajax_result_box').html(data.data);
            init();
        }
    }, 'json');
};

function init() {
    layui.use(["jquery", "laydate", "form"], function () {
        laydate = layui.laydate;
        laydate.render({
            elem: '#published_ats'
            , range: '~'
            , lang: 'zh'
            , trigger: 'click'
            , done: function (value, date) {
                $("input[name=published_ats]").val(value)
                getResult(1)
            }
        });
    })
}

$(function () {


    init();


    $(document).on('click', '.condition_li', function () {
        $(this).siblings().removeClass("actived")
        $(this).addClass("actived");
        if ($(this).attr('value') == 'custom') {
            $("input[name=published_ats]").show()
            init();
            return
        }
        getResult(1)
    })
})

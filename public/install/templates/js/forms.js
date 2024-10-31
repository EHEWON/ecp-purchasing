$(function () {

    $(".input").focus(function () {
        $(this).attr("class", "inputOn");
    }).blur(function () {
        $(this).attr("class", "input");
    });

    $("#dbhost").focus();
})


function CheckForm() {

    var format = /^[a-zA-Z0-9_@!.-]+$/;

    if ($("#dbhost").val() == "") {
        alert("请输入数据库服务器！");
        $("#dbhost").focus();
        return false;
    }

    if ($("#dbname").val() == "") {
        alert("请输入数据库名！");
        $("#dbname").focus();
        return false;
    }

    if (!format.exec($("#dbname").val())) {
        alert("数据库名非法！请使用[a-zA-Z0-9_@!.-]内的字符！！");
        $("#dbname").focus();
        return false;
    }

    if ($("#dbuser").val() == "") {
        alert("请输入数据库用户！");
        $("#dbuser").focus();
        return false;
    }


    if ($("#cpwd").val() == "false") {
        $.ajax({
            url: 'index.php',
            data: {
                s: 'checkDbPwd',
                dbhost: $("#dbhost").val(),
                dbuser: $("#dbuser").val(),
                dbpwd: $("#dbpwd").val(),
            },
            type: 'POST',
            dataType: 'html',
            success: function (data) {
                if (data == 'true') {
                    $('#cpwdTxt').html('<span class="correct">可用</span>');
                    $('#cpwd').val("true");

                    //验证没有问题，提交表单
                    document.form.submit();
                    return;
                } else {
                    $('#cpwdTxt').html('<span class="error">不可用</span>');
                    $("#dbpwd").focus();
                    $('#cpwd').val("false");
                    return false;
                }
            }
        });
    } else {

        //验证没有问题，提交表单
        document.form.submit();
        return;
    }
}

/**
 * 验证数据库账号密码是否正确
 * @constructor
 */
function CheckPwd() {
    $.ajax({
        url: 'index.php',
        data: {
            s: 'checkDbPwd',
            dbhost: $("#dbhost").val(),
            dbport: $("#dbport").val(),
            dbuser: $("#dbuser").val(),
            dbpwd: $("#dbpwd").val(),
        },
        type: 'POST',
        dataType: 'html',
        success: function (data) {
            if (data === 'true') {
                $('#cpwdTxt').html('<span class="correct">可用</span>');
                $('#cpwd').val("true");
            } else {
                $('#cpwdTxt').html('<span class="error">不可用</span>');
                $('#cpwd').val("false");
            }
        }
    });
}
/**
 * 验证数据库账号密码是否正确
 * @constructor
 */
function CheckRedisPwd() {
    $.ajax({
        url: 'index.php',
        data: {
            s: 'checkRedisPwd',
            redishost: $("#redishost").val(),
            redispwd: $("#redispwd").val(),
            redisport: $("#redisport").val()
        },
        type: 'POST',
        dataType: 'html',
        success: function (data) {
            if (data === 'true') {
                $('#cpRedisdTxt').html('<span class="correct">可用</span>');
                $('#cpRediswd').val("true");
            } else {
                $('#cpRedisdTxt').html('<span class="error">不可用</span>');
                $('#cpRediswd').val("false");
            }
        }
    });
}
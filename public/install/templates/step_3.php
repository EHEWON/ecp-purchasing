<?php if (!defined('IN_INSTALL')) exit('Request Error!'); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>瑞招采系统 安装向导 - 执行配置文件</title>
        <link href="templates/style/install.css" type="text/css" rel="stylesheet"/>
        <script type="text/javascript" src="templates/js/jquery.min.js"></script>
        <script type="text/javascript" src="templates/js/common.js"></script>
    </head>
    <body>
        <div class="header">
            <image src="/install/templates/images/eruilogo.png" mode="aspectFit|aspectFill|widthFix" lazy-load="false"  />
            <p class="hd_1">安装向导</p>
        </div>
        <div class="mainBody">
            <div class="text">
                <h4>正在安装...</h4>
                <div id="install">
                    <p>数据库连接文件创建完成！</p>
                </div>
            </div>
        </div>
        <div class="footer"><span class="step3"></span> <span class="copyright"><?php echo $cfg_copyright; ?></span></div>
    </body>
    <script>
        const importDb = (index = 1) => {
            $.ajax({
                url: 'index.php',
                data: {
                    s: 'importDb',
                    appname: '<?= $_POST['appname'] ?? '' ?>',
                    dbhost: '<?= $_POST['dbhost'] ?? '' ?>',
                    dbname: '<?= $_POST['dbname'] ?? '' ?>',
                    dbuser: '<?= $_POST['dbuser'] ?? '' ?>',
                    dbpwd: '<?= $_POST['dbpwd'] ?? '' ?>',
                    managemail: '<?= $_POST['managemail'] ?? '' ?>',
                    index: index,
                },
                type: 'POST',
                dataType: 'json',
                success: function (data) {
                    appendMsg(data.message, data.status ? 10 : 30)
                    if (data.isNext) {
                        setTimeout(() => importDb(index + 1), 50)
                    } else {
                        appendMsg('数据库已创建完成！', 20)
                        setTimeout(() => location.href = "?s=<?= md5('done') ?>", 2000)
                    }
                }
            });
        }

        // 追加提示信息
        const appendMsg = (message, status = 10) => {
            const classEnum = {10: '', 20: 'successMsg', 30: 'errorMsg'}
            const $install = $("#install")
            $install.append(`<p class="${classEnum[status]}">${message}</p>`);
            $install.scrollTop($install[0].scrollHeight);
        }

        $(function () {
            setTimeout(() => importDb(), 500)
        })
    </script>
</html>
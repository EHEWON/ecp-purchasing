<?php if (!defined('IN_INSTALL')) exit('Request Error!'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>瑞招采系统 安装向导 - 检测安装环境</title>
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
    <div class="forms">
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
            <tr align="left" class="head">
                <td width="30%" height="36">项目
                </td>
                <td width="30%">所需配置
                </td>
                <td width="15%">推荐配置
                </td>
                <td width="25%" align="right">当前服务器
                </td>
            </tr>
            <tr>
                <td height="26" class="firstCol">操作系统</td>
                <td>不限制</td>
                <td>Linux</td>
                <td class="endCol"><?php echo PHP_OS; ?></td>
            </tr>
            <tr>
                <td height="26" class="firstCol">PHP 版本</td>
                <td>8.0</td>
                <td>8.0</td>
                <td class="endCol"><?php echo getPHPVersion(); ?></td>
            </tr>
            <tr>
                <td height="26" class="firstCol">附件上传</td>
                <td>2M</td>
                <td>2M</td>
                <td class="endCol"><?php echo get_cfg_var("upload_max_filesize") ?: '不允许上传附件'; ?></td>
            </tr>
            <tr>
                <td height="26" class="firstCol">GD 库</td>
                <td>2.0</td>
                <td>2.1</td>
                <td class="endCol"><?php
                    $gdInfo = function_exists('gd_info') ? gd_info() : [];
                    echo empty($gdInfo['GD Version']) ? 'noext' : $gdInfo['GD Version'];
                    ?></td>
            </tr>
            <tr>
                <td height="26" class="firstCol">磁盘空间</td>
                <td>100M</td>
                <td>不限制</td>
                <td class="endCol">
                    <?php
                    if (function_exists('disk_free_space')) {
                        echo floor(disk_free_space('../') / (1024 * 1024)) . 'M';
                    } else {
                        echo 'unknow';
                    }
                    ?>
                </td>
            </tr>
        </table>
        <div class="hr_10"></div>
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
            <tr align="left" class="head">
                <td width="60%" height="36">扩展要求
                </td>
                <td width="25%">检查结果
                </td>
                <td width="15%" align="right">建议
                </td>
            </tr>
            <?php foreach ($extendArray as $item): ?>
                <tr>
                    <td height="26" class="firstCol"><?= $item['name'] ?></td>
                    <td><?= $item['status'] ? '支持' : '不支持' ?></td>
                    <td class="endCol">
                        <span class="<?= $item['status'] ? '' : 'col-red' ?>"><?= $item['status'] ? '无' : '需安装' ?></span>
                    </td>
                </tr>
            <?php endforeach ?>
        </table>
        <div class="hr_10"></div>
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
            <tr align="left" class="head">
                <td width="60%" height="36">函数名称
                </td>
                <td width="25%">检查结果
                </td>
                <td width="15%" align="right">建议
                </td>
            </tr>
            <?php foreach ($exists_array as $v): ?>
                <tr>
                    <td height="26" class="firstCol"><?php echo $v; ?>()</td>
                    <td><?= isFunExists($v) ? '支持' : '不支持' ?></td>
                    <td class="endCol"><?= isFunExistsTxt($v) ?></td>
                </tr>
            <?php endforeach ?>
        </table>
        <div class="hr_10"></div>
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
            <tr align="left" class="head">
                <td width="60%" height="36">文件权限检测
                </td>
                <td width="25%">所需状态
                </td>
                <td width="15%" align="right">当前状态
                </td>
            </tr>
            <?php
            foreach ($iswrite_array as $v) {
                ?>
                <tr align="left">
                    <td height="26" class="firstCol"><?php echo $v; ?></td>
                    <td>可写</td>
                    <td class="endCol"><?php isWrite($v); ?></td>
                </tr>
                <?php
            }
            ?>
        </table>
    </div>
</div>
<div class="footer">
    <span class="step2"></span>
    <span class="copyright"><?= $cfg_copyright; ?></span>
    <span class="formSubBtn">
        <form class="j-form" method="post" action="index.php">
            <a href="javascript:void(0);" onclick="history.go(-1);return false;" class="back">返 回</a>
            <a href="javascript:void(0);" class="j-submit submit">下一步</a>
            <input type="hidden" name="s" id="s" value="2">
        </form>
	</span>
</div>
<script>
    $(function () {
        // 环境检测是否通过
        var isPass = <?= $GLOBALS['isNext'] ? 'true' : 'false' ?>;
        console.log(isPass)
        // 表单提交
        $('.j-submit').click(function () {
            if (isPass) {
                $('.j-form').submit();
            } else {
                alert('环境检测不通过，请先修复');
            }
        })
    })
</script>
</body>
</html>
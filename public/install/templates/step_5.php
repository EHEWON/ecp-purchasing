<?php if(!defined('IN_INSTALL')) exit('Request Error!'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>瑞招采系统 安装向导 - 程序已安装</title>
<link href="templates/style/install.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="templates/js/jquery.min.js"></script>
<script type="text/javascript" src="templates/js/common.js"></script>
</head>
<body>
<div class="header">
  <image src="/install/templates/images/eruilogo.png" mode="aspectFit|aspectFill|widthFix" lazy-load="false"  />
  <p class="hd_1">安装向导</p>
</div>
<div class="mainBody">
	<div class="note">
        <div class="complete"><strong>现在您可以：</strong><br />
            <a href="../">访问首页</a><span>或</span><a href="../front/">登录后台</a><br /><br />
            您可以访问 <a href="https://ecp.erui.com/" target="_blank" class="link">ecp.erui.com</a> 获取更多帮助 </div>
    </div>
</div>
<div class="footer"> <span class="step4"></span> <span class="copyright"><?php echo $cfg_copyright; ?></span></div>
</body>
</html>

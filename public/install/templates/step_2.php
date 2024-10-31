<?php if (!defined('IN_INSTALL')) exit('Request Error!'); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>瑞招采系统 安装向导 - 配置数据文件</title>
        <link href="templates/style/install.css" type="text/css" rel="stylesheet"/>
        <script type="text/javascript" src="templates/js/jquery.min.js"></script>
        <script type="text/javascript" src="templates/js/common.js"></script>
        <script type="text/javascript" src="templates/js/forms.js"></script>
    </head>
    <body>
        <form name="form" id="form" method="post" action="index.php" autocomplete="off">
            <div class="header">
              <image src="/install/templates/images/eruilogo.png" mode="aspectFit|aspectFill|widthFix" lazy-load="false"  />
              <p class="hd_1">安装向导</p>
            </div>
            <div class="mainBody">
                <div class="table">
                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td height="40" colspan="2" align="left"><span class="title">基本信息</span></td>
                        </tr>
                        <tr>
                            <td width="30%" height="40" align="right">系统名称：</td>
                            <td><input type="text" value="" name="appname" id="appname" class="input" /></td>
                        </tr>
                        <tr>
                            <td width="30%" height="40" align="right">系统域名：</td>
                            <td><input type="text" name="host" id="host" class="input" value="<?php echo $host ?>"/>
                                <span class="cnote"></span></td>
                        </tr>
                        <tr>
                            <td height="40" colspan="2" align="left"><span class="title">填写数据库配置</span></td>
                        </tr>
                        <tr>
                            <td width="30%" height="40" align="right">数据库服务器：</td>
                            <td><input type="text" name="dbhost" id="dbhost" class="input" value="localhost"/>
                                <span class="cnote">数据库服务器地址, 一般为 localhost</span></td>
                        </tr>
                        <tr>
                            <td width="30%" height="40" align="right">数据库端口号：</td>
                            <td>
                                <input type="text" name="dbport" id="dbport" class="input" value="3306"/>
                                <span class="cnote">数据库端口号, 一般为 3306</span>
                            </td>
                        </tr>
                        <tr>
                            <td height="40" align="right">数据库名称：</td>
                            <td>
                                <input type="text" name="dbname" id="dbname" class="input" value=""/>
                                <span class="cnote">数据库的名称，如果没有请先新增</span>
                            </td>
                        </tr>
                        <tr>
                            <td height="40" align="right">数据库用户名：</td>
                            <td><input type="text" name="dbuser" id="dbuser" class="input" value=""/></td>
                        </tr>
                        <tr>
                            <td height="40" align="right">数据库密码：</td>
                            <td>
                                <input type="password" name="dbpwd" id="dbpwd" class="input" onblur="CheckPwd()"/>
                                <span class="cnote"><span id="cpwdTxt"></span></span>
                                <input type="hidden" name="cpwd" id="cpwd" value="false">
                            </td>
                        </tr>
                        <tr>
                            <td height="40" colspan="2" align="left"><span class="title">填写REDIS配置</span></td>
                        </tr>
                        <tr>
                            <td width="30%" height="40" align="right">REDIS服务器：</td>
                            <td><input type="text" name="redishost" id="redishost" class="input" value="localhost"/>
                                <span class="cnote">REDIS服务器地址, 一般为 localhost</span></td>
                        </tr>
                        <tr>
                            <td width="30%" height="40" align="right">REDIS端口号：</td>
                            <td>
                                <input type="text" name="redisport" id="redisport" class="input" value="6379"/>
                                <span class="cnote">REDIS端口号, 一般为 6379</span>
                            </td>
                        </tr>
                        <tr>
                            <td height="40" align="right">REDIS密码：</td>
                            <td>
                                <input type="password" name="redispwd" id="redispwd" class="input"  onblur="CheckRedisPwd()"/>
                                <span class="cnote"><span id="cpRedisdTxt"></span></span>
                                <input type="hidden" name="cpRediswd" id="cpwd" value="false">
                            </td>
                        </tr>


                        <tr>
                            <td height="40" colspan="2" align="left"><span class="title">填写邮件配置</span></td>
                        </tr>
                          <tr>
                            <td width="30%" height="40" align="right">邮件服务器驱动：</td>
                            <td><input type="text" name="maildriver" id="maildriver" class="input" value="smtp"/>
                                <span class="cnote">发送邮件服务器驱动一般为 SMTP</span></td>
                        </tr>
                        <tr>
                            <td width="30%" height="40" align="right">邮件服务器：</td>
                            <td><input type="text" name="mailhost" id="mailhost" class="input" value=""/>
                                <span class="cnote">邮件服务器一般为 smtp.XXX.com,请按照具体邮箱服务商填写</span></td>
                        </tr>
                        <tr>
                            <td width="30%" height="40" align="right">邮件端口号：</td>
                            <td>
                                <input type="text" name="mailport" id="mailport" class="input" value="25"/>
                                <span class="cnote"></span>
                            </td>
                        </tr>
                        <tr>
                            <td height="40" align="right">发送邮件账号：</td>
                            <td>
                                <input type="text" name="mailaccount" id="mailaccount" class="input" value=""/>
                                <span class="cnote"></span>
                            </td>
                        </tr>
                        <tr>
                            <td height="40" align="right">邮件密码：</td>
                            <td>
                                <input type="password" name="mailpwd" id="mailpwd" class="input"/>
                                <span class="cnote"><span id="cpwdTxt"></span></span>
                            </td>
                        </tr>
                        <tr>
                            <td height="40" colspan="2" align="left"><span class="title">默认管理员信息</span></td>
                        </tr>
                        <tr>
                            <td height="40" align="right">管理员账号：</td>
                            <td>
                                <div class="readonly">admin</div>
                            </td>
                        </tr>
                        <tr>
                            <td height="40" align="right">管理员邮箱：</td>
                            <td>
                                <input type="text" name="managemail" id="mailaccount" class="input" value=""/>
                            </td>
                        </tr>
                        <tr>
                            <td height="40" align="right">管理员密码：</td>
                            <td>
                                <div class="readonly">ecp@2024</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="footer"><span class="step3"></span> <span class="copyright"><?php echo $cfg_copyright; ?></span> <span
                    class="formSubBtn"> <a href="javascript:void(0);" onclick="history.go(-1);return false;"
                                       class="back">返 回</a> <a
                                       href="javascript:void(0);" onclick="CheckForm();return false;" class="submit">开始安装</a>
                    <input type="hidden" name="s" id="s" value="3">
                </span></div>
        </form>
    </body>
</html>
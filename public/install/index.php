<?php

header('Content-Type:text/html; charset=utf-8');

// 检测php版本号
if (phpversion() < '8.0') {
    exit('很抱歉，由于您的PHP版本过低，不能安装本软件，为了系统功能全面可用，请升级到PHP8.0或更高版本再安装，谢谢！');
}

// 不限制响应时间
// error_reporting(0);
set_time_limit(0);

// 设置系统路径
define('IN_INSTALL', true);
define('INSTALL_PATH', str_replace('\\', '/', dirname(__FILE__)));
define('ROOT_PATH', dirname(INSTALL_PATH, 2));
define('DS', DIRECTORY_SEPARATOR);
// 版权信息设置
$cfg_copyright = '© 2021-2024 ERUI.COM';

// 获取当前步骤
$s = getStep();

// 提示已经安装
if (is_file(INSTALL_PATH . '/install.lock') && $s != md5('done')) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: /front/index.html');
    exit;
}

// 执行相应操作
$GLOBALS['isNext'] = true;

// 获取当前步骤
function getStep() {
    $s1 = $_GET['s'] ?? 0;
    // 初始化参数
    $s2 = $_POST['s'] ?? 0;
    // 如果有GET值则覆盖POST值
    if ($s1 > 0 && in_array($s1, [1, 'checkDbPwd', 'checkRedisPwd', md5('done')])) {
        $s2 = $s1;
    }
    return $s2;
}

// 协议说明
if ($s == 0 && is_numeric($s)) {
    require_once(INSTALL_PATH . '/templates/step_0.php');
    exit();
}
// 环境检测
if ($s == 1 && is_numeric($s)) {
    // 获取检测的路径数据
    $iswrite_array = getIsWriteArray();
    // 获取检测的函数数据
    $exists_array = getExistsFuncArray();
    // 获取扩展要求数据
    $extendArray = getExtendArray();
    // 引入环境检测html
    require_once(INSTALL_PATH . '/templates/step_1.php');
    exit();
}
// 配置文件
if ($s == 2 && is_numeric($s)) {
    $host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    require_once(INSTALL_PATH . '/templates/step_2.php');
    exit();
}
// 正在安装
if ($s == 3 && is_numeric($s)) {
    require_once(INSTALL_PATH . '/templates/step_3.php');

    if ($_POST['s'] == 3) {

        // 初始化信息
        $dbhost = $_POST['dbhost'] ?? '';
        $dbname = $_POST['dbname'] ?? '';
        $dbuser = $_POST['dbuser'] ?? '';
        $dbpwd = $_POST['dbpwd'] ?? '';
        $dbport = $_POST['dbport'] ?? 3306;
        $testdata = $_POST['testdata'] ?? '';

        $host = $_POST['host'] ?? '';
        $appname = $_POST['appname'] ?? '';

        $redishost = $_POST['redishost'] ?? '';
        $redispwd = $_POST['redispwd'] ?? '';
        $redisport = $_POST['redisport'] ?? 6379;

        $maildriver = $_POST['maildriver'] ?? '';
        $mailhost = $_POST['mailhost'] ?? '';
        $mailport = $_POST['mailport'] ?? '';
        $mailaccount = $_POST['mailaccount'] ?? '';
        $mailpwd = $_POST['mailpwd'] ?? '';
        $managemail = $_POST['managemail'] ?? '';
        // 连接证数据库
        try {
            $dsn = "mysql:host={$dbhost};port={$dbport};charset=utf8";
            $pdo = new PDO($dsn, $dbuser, $dbpwd);
            $pdo->query("SET NAMES utf8"); // 设置数据库编码
        } catch (Exception $e) {
            insError('数据库连接错误，请检查！');
        }

        // 查询数据库
        $res = $pdo->query('show Databases');

        // 遍历所有数据库，存入数组
        $dbnameArr = [];
        foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $dbnameArr[] = $row['Database'];
        }

        // 检查数据库是否存在，没有则创建数据库
        if (!in_array(trim($dbname), $dbnameArr)) {
            if (!$pdo->exec("CREATE DATABASE `$dbname`")) {
                insError("创建数据库失败，请检查权限或联系管理员！");
            }
        }
        // 取出.env模板内容
        $config_str = readDataFile('.env.tpl');

        // 进行替换
        $config_str = str_replace('~app_name~', $appname, $config_str);
        $config_str = str_replace('~host~', $host, $config_str);
        $config_str = str_replace('~db_host~', $dbhost, $config_str);
        $config_str = str_replace('~db_name~', $dbname, $config_str);
        $config_str = str_replace('~db_user~', $dbuser, $config_str);
        $config_str = str_replace('~db_pwd~', $dbpwd, $config_str);
        $config_str = str_replace('~db_port~', $dbport, $config_str);
        $config_str = str_replace('~db_charset~', 'utf8', $config_str);
        $config_str = str_replace('~redis_host~', $redishost, $config_str);
        $config_str = str_replace('~redis_port~', $redisport, $config_str);
        $config_str = str_replace('~redis_pwd~', $redispwd, $config_str);
        $config_str = str_replace('~mail_driver~', $maildriver, $config_str);
        $config_str = str_replace('~mail_host~', $mailhost, $config_str);
        $config_str = str_replace('~mail_account~', $mailaccount, $config_str);
        $config_str = str_replace('~mail_pwd~', $mailpwd, $config_str);
        $config_str = str_replace('~mail_port~', $mailport, $config_str);
        $config_str = str_replace('~manage_mail~', $managemail, $config_str);


        if (file_exists(ROOT_PATH . DS . '.env')) {
            unlink(ROOT_PATH . DS . '.env');
        }
        if (file_exists(ROOT_PATH . '/public/front/static/config.js')) {
            unlink(ROOT_PATH  . '/public/front/static/config.js');
        }
        // 将替换后的内容写入.env文件
        $fp = fopen(ROOT_PATH . '/.env', 'w');
        fwrite($fp, $config_str);
        fclose($fp);
        list ($schme, $httpHost) = explode('://', $host);
        $configJs = 'var ipConfig = {
            AXIOS_TIMEOUT: 10000,
            SET_APP_BASE_API: \'' . $host . '\',
            SET_APP_IMG_API: \'' . $host . '\',
            SET_APP_WSS_API: \'' . ($schme === 'https' ? 'wss' : 'ws') . '://' . $httpHost . '\',
        }';
        $jsfp = fopen(ROOT_PATH  . '/public/front/static/config.js', 'w');
        fwrite($jsfp, $configJs);
        fclose($jsfp);
    }
    exit();
}
// 检测数据库信息
if ($s === 'checkDbPwd') {
    $dbhost = $_POST['dbhost'] ?? '';
    $dbuser = $_POST['dbuser'] ?? '';
    $dbpwd = $_POST['dbpwd'] ?? '';
    $dbport = $_POST['dbport'] ?? '';
    try {
        $dsn = "mysql:host={$dbhost};port={$dbport};charset=utf8";
        $pdo = new PDO($dsn, $dbuser, $dbpwd);
        echo 'true';
    } catch (Exception $e) {
        echo 'false';
    }
    exit();
}
// 检测数据库信息
if ($s === 'checkRedisPwd') {
    $redishost = $_POST['redishost'] ?? '';
    $redispwd = $_POST['redispwd'] ?? '';
    $redisport = $_POST['redisport'] ?? '';
    try {
        $redis = new Redis();
        $redis->connect($redishost, $redisport);
        if (!empty($redispwd)) {
            $redis->auth($redispwd);
        }
        $redis->select(0);
        echo 'true';
    } catch (Exception $e) {
        echo 'false';
    }
    exit();
}
// 逐步导入数据库
if ($s === 'importDb') {
    // 初始化信息
    $dbhost = $_POST['dbhost'] ?? '';
    $dbname = $_POST['dbname'] ?? '';
    $dbuser = $_POST['dbuser'] ?? '';
    $dbpwd = $_POST['dbpwd'] ?? '';
    $dbport = $_POST['dbport'] ?? 3306;
    $managemail = $_POST['managemail'] ?? '';
    $testdata = $_POST['testdata'] ?? '';
    $appname = $_POST['appname'] ?? '';
    // 执行的索引 (某条数据)
    $index = isset($_POST['index']) ? (int) $_POST['index'] : 1;

    // 连接证数据库
    try {
        $dsn = "mysql:host={$dbhost};port={$dbport};charset=utf8";
        $pdo = new PDO($dsn, $dbuser, $dbpwd);
        $pdo->query("SET NAMES utf8"); // 设置数据库编码
    } catch (Exception $e) {
        insError('数据库连接错误，请检查！');
    }

    // 验证数据库版本号
    // $version = $pdo->query('select version()')->fetchColumn();
    $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    if (version_compare($version, '5.7.0') == -1) {
        insError2("很抱歉，数据库版本号不能低于5.7.0，请检查！您当前是 {$version}");
    }

    // 数据库创建完成，开始连接
    $pdo->query("USE `$dbname`");

    // 读取数据库脚本数据
    $content = readDataFile('install.sql');
    $data = preg_split('/DROP TABLE IF EXISTS `.*?`;/', $content);
    if (!isset($data[$index])) {
        insError('数据库索引不正确');
    }

    // 获取表名
    $tableName = parseTableName($data[$index]);
    if (empty($tableName)) {
        insError('数据库执行脚本不正确');
    }

    // 生成sql语句
    $dropSql = "DROP TABLE IF EXISTS `{$tableName}`;\n";
    $sql = $dropSql . trim($data[$index]);

    try {
        // 执行sql内容
        if ($pdo->exec($sql) !== false) {
            $message = "创建数据表 [{$tableName}] 完成！";
            $status = true;
        } else {
            $error = $pdo->errorInfo();
            throw new Exception($error[2] ?? $error[0]);
        }
    } catch (\Throwable $e) {
        $message = "创建数据表 [{$tableName}] 失败！" . $e->getMessage();
        $status = false;
    }



    // 是否存在下一步
    $isNext = $index < count($data) - 1;
    if (!$isNext) {
        $time = date('Y-m-d H:i:s');
        $pdo->exec('INSERT INTO purchaser VALUES (\'1\', \'PLATFORM\', \'erui\', null, \'\', \'1\', \'1\','
                . ' \'' . $appname . '\', \'' . $appname . '\', null, null, null, \'' . $appname . '\','
                . ' null, null, null, \'0\', \'' . $time . '\', \'1\', '
                . '\'' . $time . '\', null, null, \'N\', \'APPROVED\');');
        $pdo->exec('INSERT INTO user VALUES (\'1\', \'PLATFORM\', \'admin\', \'admin\', \'' . $managemail . '\', '
                . '\'https://ecpapi2.erui.com/icons/pc/other/superAdministrators_38_38.png\', \'admin\','
                . ' \'admin\', null, \'SECRECY\', \'' . password_hash('ecp@2024', PASSWORD_DEFAULT) . '\', '
                . '\'1\', \'1\', \'1\', \'1\', \'' . $time . '\', \'' . $time . '\', \'N\', \'0\', \'136\', null, null, \'1\');');
    }
    // 返回结果
    exit(jsonEncode(compact('index', 'message', 'status', 'isNext')));
}
// 安装完成
if ($s === md5('done')) {
    require_once(INSTALL_PATH . '/templates/step_4.php');
    $fp = fopen(INSTALL_PATH . '/install.lock', 'w');
    fwrite($fp, '程序已正确安装，重新安装请删除本文件');
    fclose($fp);
    exit();
}

// 获取扩展要求数据
function getExtendArray(): array {
    $data = [
        [
            'name' => 'PDO Mysql',
            'status' => extension_loaded('PDO') && extension_loaded('pdo_mysql'),
        ],
        [
            'name' => 'Mysqlnd',
            'status' => extension_loaded('mysqlnd'),
        ],
        [
            'name' => 'JSON',
            'status' => extension_loaded('json')
        ],
        [
            'name' => 'Fileinfo',
            'status' => extension_loaded('fileinfo')
        ],
        [
            'name' => 'CURL',
            'status' => extension_loaded('curl'),
        ],
        [
            'name' => 'OpenSSL',
            'status' => extension_loaded('openssl'),
        ],
        [
            'name' => 'GD',
            'status' => extension_loaded('gd'),
        ],
        [
            'name' => 'BCMath',
            'status' => extension_loaded('bcmath'),
        ],
        [
            'name' => 'Mbstring',
            'status' => extension_loaded('mbstring'),
        ],
        [
            'name' => 'SimpleXML',
            'status' => extension_loaded('SimpleXML'),
        ],
        [
            'name' => 'REDIS',
            'status' => extension_loaded('redis'),
        ],
        [
            'name' => 'HASH',
            'status' => extension_loaded('hash'),
        ],
        [
            'name' => 'ICONV',
            'status' => extension_loaded('iconv'),
        ],
        [
            'name' => 'ICONV',
            'status' => extension_loaded('iconv'),
        ],
        [
            'name' => 'XLSWRITER',
            'status' => extension_loaded('xlswriter'),
        ],
        [
            'name' => 'ZIP',
            'status' => extension_loaded('zip'),
        ],
    ];
    foreach ($data as $item) {
        !$item['status'] && setIsNext(false);
    }
    return $data;
}

// 检测php版本号
function getPHPVersion() {
    $version = phpversion();
    $versionInt = versionToInteger(phpversion());
    if ($versionInt < 8000) {
        echo "<span class=\"col-red\"><strong>{$version}</strong> (请使用php8.1)</span>";
        setIsNext(false);
    } else {
        echo "<span>{$version}</span>";
    }
}

// 匹配mysql表名
function parseTableName(string $sql) {
    preg_match('/CREATE TABLE `(.*?)`/', $sql, $matches);
    if (empty($matches)) {
        return null;
    }
    return $matches[1];
}

function jsonEncode(array $data) {
    return json_encode($data, JSON_UNESCAPED_UNICODE);
}

/**
 * 将版本转为数字
 * @param string $version
 * @return int
 */
function versionToInteger(string $version): int {
    list($major, $minor, $sub) = explode('.', $version);
    return intval($major * 10000 + $minor * 100 + $sub);
}

// 获取检测的路径数据
function getIsWriteArray(): array {
    return [
        '/',
        '/public/front/static/',
        '/public/install/',
        '/public/static/upload/',
        '/public/download/',
    ];
}

// 获取检测的函数数据
function getExistsFuncArray(): array {
    return ['curl_init', 'chmod', 'bcadd', 'mb_substr', 'simplexml_load_string', 'json_encode', 'imagecreate', 'putenv', 'getenv'];
}

// 测试可写性
function isWrite($file) {
    if (is_writable(ROOT_PATH . $file)) {
        echo '<span>可写</span>';
    } else {
        echo '<span class="col-red">不可写</span>';
        setIsNext(false);
    }
}

// 测试函数是否存在
function isFunExists($func) {
    $state = function_exists($func);
    if ($state === false) {
        setIsNext(false);
    }
    return $state;
}

// 测试函数是否存在
function isFunExistsTxt($func) {
    if (isFunExists($func)) {
        echo '<span>无</span>';
    } else {
        echo '<span class="col-red">需安装</span>';
        setIsNext(false);
    }
}

// 清除txt中的BOM
function clearBOM($contents) {
    $charset[1] = substr($contents, 0, 1);
    $charset[2] = substr($contents, 1, 1);
    $charset[3] = substr($contents, 2, 1);
    if (ord($charset[1]) == 239 &&
            ord($charset[2]) == 187 &&
            ord($charset[3]) == 191) {
        return substr($contents, 3);
    } else {
        return $contents;
    }
}

// 设置是否允许下一步
function setIsNext(bool $bool) {
    $GLOBALS['isNext'] = $bool;
}

// 获取data文件夹中的文件内容
function readDataFile(string $file) {
    return file_get_contents(INSTALL_PATH . '/data/' . $file);
}

function insInfo($str) {
    echo '<script>$("#install").append("' . $str . '<br>");</script>';
}

function insError($str, $isExit = false) {
    insInfo("<span class='col-red'>$str</span>");
    exit();
}

/**
 * 打印调试函数 html
 * @param $content
 * @param bool $export
 */
function pre($content, bool $export = false) {
    $output = $export ? var_export($content, true) : print_r($content, true);
    echo "<pre>{$output}</pre>";
    exit();
}

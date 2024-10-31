<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>在线接口列表 <?php echo count($routes) ?></title>
    <link href="https://cdn.bootcss.com/semantic-ui/2.2.2/semantic.min.css" rel="stylesheet">
    <script src="https://cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://cdn.bootcss.com/semantic-ui/2.2.2/semantic.min.js"></script>
    <meta name="robots" content="none"/>
</head>
<body>
<br/>

<div class="ui text container" style="max-width: none !important; width: 95%" id="menu_top">
    <?php if (!empty($errorMessage)) :?>
    <div class="ui floating message">
        <div class="ui error message">
            <strong>错误：<?php echo $errorMessage ?>  </strong>
        </div>
    </div>
    <?php endif ?>
    <div class="" style="max-width: none !important;">
        <div class="wide stretched column">
            <table class="ui red celled striped table celled striped table" >
                <thead>
                <tr>
                    <th>#</th>
                    <th>URI</th>
                    <th>名称as</th>
                    <th>类型</th>
                    <th>控制器</th>
                    <th>方法</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($routes as $k=>$v) :?>
                    <tr>
                        <td><?php echo $k+1; ?></td>
                        <td>
                            <a href="<?php echo $v['link'] ?>" target='_blank'>
                                <?php echo $v['uri']; ?>
                            </a>
                        </td>
                        <td><?php echo $v['as']; ?></td>
                        <td><?php echo $v['method']; ?></td>
                        <td><?php echo $v['controller']; ?></td>
                        <td><?php echo $v['action']; ?></td>
                        <td><?php echo $v['desc']; ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>



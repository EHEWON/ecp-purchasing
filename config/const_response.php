<?php

return array(
	//通用部分
	'SUCCESS' 			        => array('code' =>'0000',  'msg' =>'成功'),
	'REQ_DATA_ERROR'            => array('code' =>'9999',  'msg' =>'系统异常'),
	'CALL_API_ERROR'            => array('code' =>'9998',  'msg'=>'接口失败'),
	'CALL_API_TIMEOUT'			=> array('code' =>'9997',  'msg'=>'接口超时'),
	'REQ_CODE_NO_EXIST'			=> array('code' =>'9996',  'msg'=>'请求码不存在'),
	'NO_SERVICE'                => array('code' => '9995', 'msg' => '请先设置Service或者重写方法！'),
    'NO_PRIMARY_KEY'            => array('code' => '9994', 'msg' => '主键丢失'),
	'NO_DATA'                   => array('code' => '9993', 'msg' => '编辑信息不存在'),

	//用户系列
	'USER_NO_EXIST' 			=> array('code' =>'1010', 'msg' =>'账号不存在'),
	'PASSWORD_ERROR' 			=> array('code' =>'1020', 'msg' =>'密码错误'),
	'USER_NO_RIGHTS' 			=> array('code' =>'1030', 'msg' =>'没有权限'),
	'USER_MOBILE_EXIT'         	=> array('code' =>'1080', 'msg' =>'该用户已注册'),

	'USER_EXVIEW'				=> array('code' =>'1080', 'msg' =>'用户审核中'),
	//token部分
	'TOKEN_ERROR'         		=> array('code' =>'6100', 'msg' =>'登陆错误,请重新登陆'),  //TOKEN错误
	'TOKEN_EXP'           		=> array('code' =>'6200', 'msg' =>'登陆过期,请重新登陆'),  // TOKEN过期
	'TOKEN_SIGN_ERROR'    		=> array('code' =>'6300', 'msg' =>'登陆错误,请重新登陆'),  //TOKEN签名错误

	//账户系列
	'ACC_BALANCE_EXIT'         => array('code' =>'4810', 'msg' =>'账户已存在'),
	'ACC_NO_EXIST'             => array('code' =>'4820', 'msg' =>'账户不存在'),

	//web部分
	'LOGIN_ERR'  			=>array('code'=>'1000','msg'=>'验证错误，请重新登陆'),

	//队列部分
	'QUEUE_NO_RULES' 		  =>array('code'=>'1001','msg'=>'消息或规则不存在'),

	//短信部分
	'CAPTCHA_ERROR'			  => array('code'=>'6010','msg'=>'验证码错误'),
	'CAPTCHA_EXP'			  => array('code'=>'6020','msg'=>'验证码过期'),
	'CAPTCHA_FILE'			  => array('code'=>'6030','msg'=>'短信发送失败'),
);

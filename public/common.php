<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
//生成发送请求的验证 token
//这里的key可以是包含用户信息的内容，不用用户+不同的权限
	function makeToken($user)
{
	$admin = $user; //获取前台传来的用户账号
	$time = time();
	$end_time = time()+86400/2;
	$info = $admin. '.' .$time.'.'.$end_time;//设置token过期时间为一天
	//根据以上信息信息生成签名（密钥为 siasqr)
	$signature = hash_hmac('md5',$info,'rongye');
	//最后将这两部分拼接起来，得到最终的Token字符串
	$token = $info . '.' . $signature;
	return $token;
}
//后台同理验证，
	function checkToken($key,$token)
{
	if(!isset($token) || empty($token))
		{
			$msg['code']='400';
            $msg['msg']='非法请求';
            return json_encode($msg,JSON_UNESCAPED_UNICODE);
		}
		//对比token
		$explode = explode('.',$token);//以.分割token为数组
		if(!empty($explode[0]) && !empty($explode[1]) && !empty($explode[2]) && !empty($explode[3]) )
		{
			$info = $explode[0].'.'.$explode[1].'.'.$explode[2];//信息部分
	        $true_signature = hash_hmac('md5',$info,'rongye');//正确的签名
			if(time() > $explode[2])
			{
				$msg['code']='401';
	            $msg['msg']='Token已过期,请重新登录';
	            return json_encode($msg,JSON_UNESCAPED_UNICODE);
			}
			if ($true_signature == $explode[3])
			{
			    $msg['code']='200';
	            $msg['msg']='Token合法';
	            return json_encode($msg,JSON_UNESCAPED_UNICODE);
			}
			else
			{
			    $msg['code']='400';
	            $msg['msg']='Token不合法';
	            return json_encode($msg,JSON_UNESCAPED_UNICODE);	
			}
		}
		else
		{
            $msg['code']='400';
            $msg['msg']='Token不合法';
            return json_encode($msg,JSON_UNESCAPED_UNICODE);
		}
}

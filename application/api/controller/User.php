<?php
namespace app\api\controller;
use think\Db;
class User
{
	public function register()
	{
		$data=[];
		$data['uname']=trim(input('username'));
		$data['password']=trim(md5(input('password')));
		$data['regtime']=time();
		$info=$data['uname'].'.'.$data['regtime'];
		$data['token']=$info.'.'.hash_hmac('md5',$info,'gordon');
		$res=db('user')->insert($data);
		if($res>0)
        {
            return ["code"=>200,"message"=>"注册成功","data"=>$res];
        }
        else
        {
            return ["code"=>404,"message"=>"注册失败","data"=>$res];
        }
	}

		public function login()
	{
		$data=[];
		$data['uname']=trim(input('username'));
		$data['password']=trim(md5(input('password')));
		$res=db("user")->field("token")->where("uname=? and password=?",[$data['uname'],$data['password']])->find();
		if($res)
		{
			return ["code"=>200,"message"=>"登录成功","data"=>$res];
		}
		else
		{
			return ["code"=>404,"message"=>"登录失败","data"=>$res];
		}

	}
	
}

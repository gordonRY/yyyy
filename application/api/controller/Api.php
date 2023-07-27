<?php
namespace app\api\controller;
use think\Db;
use lib\myClass;
class Api
{
	 protected $name;
	 protected $pd_token=0;
  public function __construct()
    {
		$token=input('token');
	//	$token="rongyexxxx.1597896361.950b77d34d2575841d256a662d5a6243";
		if($token)
		{	
			$arr=explode('.',$token);
			$info=$arr[0].'.'.$arr[1];
			$this->name=$arr[0];
			$tokens=$info.'.'.hash_hmac('md5',$info,'gordon');
			if($tokens==$token)
			{
				$this->pd_token=1;
			}
			else
			{
				$this->pd_token=2;
			}
		}
		else
		{
			$this->pd_token=2;
		}
    }

	//获取用户信息
	public function getinfo()
	{
		if($this->pd_token==1)
		{
				return ['data'=>['uname'=>$this->name],'code'=>200,"message"=>"用户获取成功"];	
		}
		elseif($this->pd_token==2)
		{
			return ['data'=>[],'code'=>888,"message"=>"用户没有登录"];

		}
	}
	public function site_upload()
	{
		 // 获取表单上传文件 例如上传了001.jpg
		$file = request()->file('file');
		// 移动到框架应用根目录/uploads/ 目录下
		$info = $file->move( ROOT_PATH . 'public' . DS . 'uploads');
		$res=array();
		if($info)
		{
			// 成功上传后 获取上传信息
			// 输出 jpg
			$res['Extension']=$info->getExtension();
			$res['SaveName']='\uploads'.DS.$info->getSaveName();
			$res['Filename']=$info->getFilename(); 
			return ["data"=>$res,"code"=>200,"message"=>"上传完成"];
		}
		else
		{
			// 上传失败获取错误信息
			$res['Error']=$file->getError();
			return ["data"=>$res,"code"=>404,"message"=>"上传失败"];
		}
	}

	//网址添加
	public function site_insert()
	{
	    $data['site_name']=input("site_name");
    	$data['site_url']=input("site_url");
        $data['site_order']=input("site_order");
        $data['site_farther']=input("father");
	    $data['uid']=1;
        	$data['site_icon']=input("image_adr");
        	$db=db("sites");
        	$res=$db->insertGetId($data);//insert直接添加，成功反回1； insertGetId获取主键id
        if($res>0)
        {
            return ["code"=>200,"message"=>"添加网站成功","data"=>$res];
        }
        else
        {
            return ["code"=>404,"message"=>"添加网站失败","data"=>$res];
        }

	}
	//网段添加
	public function subnet_insert()
	{
	    $data['subnet']=input("subnet");
    	$data['area']=input("area");
        $data['info']=input("info");
        $data['sub']=input("sub");
        $sub= trim($data['sub']);
        $res=current(db("iprelation")->field("relation")->where('sub',$sub)->select());
        if($res)
        {
            $data['subnet'].=$res['relation'];
            $db=db("subnet");
            $res=$db->insertGetId($data);//insert直接添加，成功反回1； insertGetId获取主键id
            if($res>0)
            {
                return ["code"=>200,"message"=>"添加网站成功","data"=>$res];
            }
            else
            {
                return ["code"=>404,"message"=>"添加网站失败","data"=>$res];
            }

        }
        else
        {
            return ["code"=>404,"message"=>"填写的子网掩码不在数据库中！","data"=>[]];
        }

	}

//网段删除
		public function subnet_del()
	{
		$id=trim(input("id"),",");
		$arr=explode(",",$id);
		$res=db("subnet")->delete($arr);
		if($res>=1)
        {
            return ["code"=>200,"message"=>"删除网段成功","data"=>1];
        }
        else
        {
            return ["code"=>404,"message"=>"删除网段失败","data"=>0];
        }
	}
//网段查询
	public function subnet_select()
    {
        $page=input("page");
        $limit=input("limit");
        $start=($page-1)*$limit;
        $end=$page*$limit;
        $data=db("subnet")->limit($start,$end)->order("order desc")->select();
        $count=current(db("subnet")->field("count(id) as count")->select())['count'];
        $msg="查询成功";
        $code=0;
        return['code'=>$code,"msg"=>$msg,"data"=>$data,"count"=>$count];
    }
    //网段修改
	public function subnet_updata()
	{
		$data=array();
		$id=input("id");
		$data['subnet']=input("subnet");
		$data['area']=input('area');
		$data['info']=input('info');
        $data['order']=input('order');
        $data['sub']=input('sub');
		$res=db('subnet')->where("id",$id)->update($data);
		if($res>=0)
		{
 			return['code'=>200,"msg"=>"修改成功","data"=>$res];
		}
		else
		{
			return['code'=>404,"msg"=>"网段修改失败","data"=>$res];
		}

	}

	//网址删除
		public function site_del()
	{
		$sid=trim(input("sid"),",");
		$arr=explode(",",$sid);
		$res=db("sites")->delete($arr);
		if($res>=1)
        {
            return ["code"=>200,"message"=>"删除网站成功","data"=>1];
        }
        else
        {
            return ["code"=>404,"message"=>"删除网站失败","data"=>0];
        }
	}

	public function site_select()
    {
        $page=input("page");
        $limit=input("limit");
        $farther=input("farther");
        $start=($page-1)*$limit;
        $end=$page*$limit;
        $uid=1;
        $data=db("sites")->where("uid=$uid and site_farther=$farther")->limit($start,$end)->order("site_order desc")->select();
        $count=current(db("sites")->field("count(sid) as count")->where("uid=1")->select())['count'];
        $msg="查询成功";
        $code=0;
        return['code'=>$code,"msg"=>$msg,"data"=>$data,"count"=>$count];
    }


    public function yyyy_info()
    {
		if($this->pd_token==1)
		{
			$st=input("st");
			$et=input("et");
			if($st && $et)
			{
				//sqlserver 语法使用
				$db=Db::connect('database.db2');
				$data=$db->query("exec [dbo].getswNum '$st', '$et'");	
				if(current($data))
				{
					$data=current($data);
				}
				cache('infos',$data,3600);		
			}
			else
			{
				if(cache('infos'))
				{
					$data=cache('infos');
				}
				else
				{
					$data=array();
				}
			}
			$code=0;
			$msg="查询成功";
			$count=count($data);
			return['code'=>$code,"msg"=>$msg,"data"=>$data,"count"=>$count];
		}
    	
    }

	public function yyyy_zyhone()
	{
		$bh=input("zybh");//住院号
		if($bh)
		{
		$db=Db::connect('database.db3');
		$res=$db->query("select SickID,Name,Sex,ComeDiagnosis,AreaName from sickman.sickmanview where ManualNo=:bh",["bh"=>$bh]);
		halt($res);
		$res=CURRENT($res);
		$sickid=$res['SickID'];
		$result=$db->query('SELECT sickid,yzid,orderby,dtInput,feeName,Gg,frequency,Start_dt,FirstTimes,EndTimes,YsName,Zcz_Name,yztype,SExecTime,Status FROM [dbo].[Hzyzrec] where SickID=:sickid and yztype=1 and Commited=1 order by orderby asc',['sickid'=>$sickid]);
		$count=count($result);
		}
		else
		{
			$result=[];
			$res=[];
			$count=0;
		}
		$code=0;
		$msg="查询成功";
		return['code'=>$code,"msg"=>$msg,"data"=>$result,"count"=>$count,"info"=>$res];	
	}

	public function yyyy_zyhone_updata()
	{
			/*Gg: ""
			Start_dt: "2020-04-25 09:34:00.000"
			YsName: "李园        "
			Zcz_Name: "贡倩雯      "
			dtInput: "2020-04-25 09:34:00.000"
			feeName: "儿内科常规护理"
			frequency: ""
			orderby: "11514116.0000"
			sickid: "137187"
			yzid: "11460652"*/
		$Gg=trim(input("Gg"));
		$Start_dt=trim(input("Start_dt"));
		$YsName=trim(input("YsName"));
		$Zcz_Name=trim(input("Zcz_Name"));
		$dtInput=trim(input("dtInput"));
		$feeName=trim(input("feeName"));
		$frequency=trim(input("frequency"));
		$orderby=trim(input("orderby"));
		$sickid=trim(input("sickid"));
		$yzid=input("yzid");
		$db=Db::connect('database.db3');
		$res=$db->execute("update [dbo].[Hzyzrec] set orderby=:orderby,Gg=:Gg,Start_dt=:Start_dt,YsName=:YsName,Zcz_Name=:Zcz_Name,dtInput=:dtInput,feeName=:feeName,frequency=:frequency  where YzID=:yzid and yztype=1 and sickid=:sickid ",["orderby"=>$orderby,"Gg"=>$Gg,"Start_dt"=>$Start_dt,"YsName"=>$YsName,"Zcz_Name"=>$Zcz_Name,"dtInput"=>$dtInput,"feeName"=>$feeName,"frequency"=>$frequency,"yzid"=>$yzid,"sickid"=>$sickid]);
		if($res>=0)
		{
			$msg="修改成功";
			$code=200;
			$data=1;
		}
		else{
			$msg="修改失败";
			$code=202;
			$data=1;
		}
		return['code'=>$code,"msg"=>$msg,"data"=>$data];	
	}

		public function yyyy_zyhzero()
	{
		$bh=input("zybh");//住院号
		if($bh)
		{
		$db=Db::connect('database.db3');
		$res=$db->query("select SickID,Name,Sex,ComeDiagnosis,AreaName from sickman.sickmanview where ManualNo=:bh",["bh"=>$bh]);
		$sickid=$res[0]['SickID'];
		$result=$db->query('SELECT orderby,yzid,SickID,dtInput,feeName,End_dt,frequency,FirstTimes,EndTimes,pishi,Psjg,StopChkOpName,Zcz_Name,yztype,SExecTime,YsName FROM [dbo].[Hzyzrec] where SickID=:sickid and yztype=0 order by orderby',['sickid'=>$sickid]);
		$count=count($result);
		}
		else
		{
			$result=[];
			$res=[];
			$count=0;
		}
		$code=0;
		$msg="查询成功";

		return['code'=>$code,"msg"=>$msg,"data"=>$result,"count"=>$count,"info"=>$res];	
	}


    public function ip_select()
    {
        $page=input("page");
        $limit=input("limit");
        $start=($page-1)*$limit;
        //$end=$page*$limit;

        //关联查询
        //$data=db("ip")->limit($start,$end)->select();

        $sql="select i.*,s.subnet from admin_ip i join admin_subnet s on s.id=i.netsegid order by i.id desc limit $start,$limit ";
        $data=Db::query($sql);

        $count=current(db("ip")->field("count(id) as count")->select())['count'];
        $msg="查询成功";
        $code=0;
        return['code'=>$code,"msg"=>$msg,"data"=>$data,"count"=>$count];
    }

    //网段下拉列表
    public function subnet_sel()
    {
        $data=db("subnet")->field(['id','subnet','area'])->select();
        $msg="查询成功";
        $code=0;
        return['code'=>$code,"msg"=>$msg,"data"=>$data];

    }

    //分配IP地址  按照从小到大分配
    public function ip_dhcp()
    {
        //接收网段
        $info=input("info");
        $info=explode("@",$info);
        $sid=$info[0];
        $subnet=$info[1];
        //查询该网段已经使用的地址
        $res1=db('ip')->field("address")->where('netsegid',$sid)->select();
        $res3=array();
        foreach ($res1 as $val)
        {
            $res3[]=$val['address'];

        }
        //获取网段所有的地址
        $obj = new myClass();
        $res2=$obj->getIpBySegment($subnet);
        $data=current(array_diff($res2,$res3));
        if($data)
        {
            return['code'=>"200","msg"=>"新建成功！","data"=>$data];
        }
        else
        {
            return['code'=>"404","msg"=>"网段全部用完！","data"=>$data];
        }
    }

    public function ip_insert()
    {
        $data['name']=input('ip_name');
        $data['mac']=input('mac');
        $data['address']=input('ip_addr');
        $data['mark']=input('mark');
		$data['mark2']=input('mark2');
        $str=input('wd');
        $str=explode("@",$str);
        $data['netsegid']=$str[0];
        $arr=explode(".",$data['address']);
        $data['addr1']=$arr[0];
        $data['addr2']=$arr[1];
        $data['addr3']=$arr[2];
        $data['addr4']=$arr[3];
        $db=db("ip");
        $res=$db->insertGetId($data);//insert直接添加，成功反回1； insertGetId获取主键id
        if($res>0)
        {
            return ["code"=>200,"message"=>"添加ip成功","data"=>$res];
        }
        else
        {
            return ["code"=>404,"message"=>"添加ip失败","data"=>$res];
        }
    }
    public function ip_delete()
    {
        $data['id']=input('id');
        $db=db("ip");
        $res=$db->delete($data['id']);
        if($res>0)
        {
            return ["code"=>200,"message"=>"删除ip成功","data"=>$res];
        }
        else
        {
            return ["code"=>404,"message"=>"删除ip失败","data"=>$res];
        }
    }

    public function ip_update()
    {
        $id=input('id');
        $data['name']=input('ip_name');
        $data['mac']=input('mac');
        $new_ip=input('ip_addr');
		//验证IP地址变化
		$old_ip=input('ip_addr_copy');
		if($old_ip!=$new_ip)
		{
			$data['address']=$new_ip;
			$arr=explode(".",$data['address']);
			$data['addr1']=$arr[0];
			$data['addr2']=$arr[1];
			$data['addr3']=$arr[2];
			$data['addr4']=$arr[3];
		}
        $data['mark']=input('mark');
		$data['mark2']=input('mark2');
        $data['netsegid']=input('wd');

        $db=db("ip");
        $res=$db->where('id',$id)->update($data);
		$sql=$db->getLastSql();
        if($res>=0)
        {
            return ["code"=>200,"message"=>"编辑ip成功","data"=>$res];
        }
        else
        {
            return ["code"=>404,"message"=>"编辑ip失败","data"=>$res];
        }
    }

    public function ip_search()
    {
        $page=input("page");
        $limit=input("limit");
        $start=($page-1)*$limit;
        $search=input('search');
        $search_sub=input('search_sub');
        //判断有没有网段
        if($search and $search_sub)
        {
			//既搜索了网段叶搜索了其他
			$sql="select i.*,s.subnet from admin_ip i join admin_subnet s on i.netsegid=s.id where (i.name like '%$search%' or  i.mark like '%$search%' or i.address like '%$search%') and s.id='$search_sub' order by addr4 limit $start,$limit  "; 
            $data= Db::query($sql);
            $sql="select i.id from admin_ip i join admin_subnet s on i.netsegid=s.id where (i.name like '%$search%' or i.mark like '%$search%' or i.address like '%$search%' )and s.id='$search_sub' ";
            $info=Db::query($sql);
            $count=count($info);
            return ["code"=>0,"message"=>"ip+网段查询ip成功1","data"=>$data,"count"=>$count];
        }
        else
        {
            if($search)
            {
                $obj = new myClass();
                $res2=$obj->checkStr($search);
                if($res2==1)
                {
                    //中文
                    $sql="select i.*,s.subnet from admin_ip i join admin_subnet s on i.netsegid=s.id where i.name like '%$search%' or i.mark like '%$search%' order by addr4 limit $start,$limit  ";
                    $data= Db::query($sql);
                    $sql="select i.id from admin_ip i join admin_subnet s on i.netsegid=s.id where i.name like '%$search%' or i.mark like '%$search%' ";
                    $info=Db::query($sql);
                    $count=count($info);
                    return ["code"=>0,"message"=>"姓名查询ip成功","data"=>$data,"count"=>$count];
                }
                elseif ($res2==2)
                {
                    //数字
                    $sql="select i.*,s.subnet from admin_ip  i join admin_subnet s on i.netsegid=s.id where i.address like '%$search%' order by addr4 limit $start,$limit  ";
                    $data= Db::query($sql);
                    $sql="select i.id from admin_ip  i join admin_subnet s on i.netsegid=s.id where i.address like '%$search%' limit $start,$limit  ";
                    $info= Db::query($sql);
                    $count=count($info);
                    return ["code"=>0,"message"=>"IP地址查询ip成功","data"=>$data,"count"=>$count];
                }
                else{

                    return ["code"=>0,"message"=>"查询ip失败，输入的字符有误","data"=>[]];
                }

            }
            else
            {
                $sql="select i.*,s.subnet from admin_ip i join admin_subnet s on i.netsegid=s.id where  s.id='$search_sub' order by addr4 limit $start,$limit  ";
                $data= Db::query($sql);
                $sql="select i.id from admin_ip i join admin_subnet s on i.netsegid=s.id where  s.id='$search_sub' ";
                $info=Db::query($sql);
                $count=count($info);
                return ["code"=>0,"message"=>"ip+网段查询ip成功2","data"=>$data,"count"=>$count];
            }
        }


    }

//IP页面 网段select查询
    public function ip_sel_search()
    {
        $data=db("subnet")->order("order desc")->select();
        $msg="查询成功";
        $code=0;
        return['code'=>$code,"msg"=>$msg,"data"=>$data];
    }




	//物资类别展示 select 选择 
	pubLic function goods_sel()
	{
		$page=input('page');
		$limit=input('limit');
		$fid=input('fid');
		$start=($page-1)*$limit;
		$sql="select * from admin_goods where fid=$fid limit $start,$limit ";
		$data=Db::query($sql);
		$all=db("goods")->field('id')->where('fid',$fid)->select();
		$count=count($all);
		return ["code"=>0,"message"=>"物资查询成功","data"=>$data,"count"=>$count];
	
	}

    //物资类别展示 
    pubLic function goods_total()
    {
        $page=input('page');
        $limit=input('limit');
        $start=($page-1)*$limit;
        $data=db('goods')->limit($start,$limit)->order('sy_num desc')->select();
        $all=db("goods")->field('id')->select();
        $count=count($all);
        return ["code"=>0,"message"=>"物资查询成功","data"=>$data,"count"=>$count];
    
    }

    //物资添加
    public function goods_insert()
    {
        $data['dhdate']=input('date');
        $data['mark']=input('mark');
        $data['name']=input('name');
        $data['num']=input('num');
        $data['sy_num']=input('num');
        $data['fid']=input('sel');
        $db=db("goods");
        $res=$db->insertGetId($data);//insert直接添加，成功反回1； insertGetId获取主键id
        if($res>0)
        {
            return ["code"=>200,"message"=>"添加ip成功","data"=>$res];
        }
        else
        {
            return ["code"=>404,"message"=>"添加ip失败","data"=>$res];
        }
    }

    //物资删除
    pubLic function goods_del()
    {
        $id=input('id');
        $db=db("goods");
        //判断有没有物资详情
        $res=db('goods')->field(['num','sy_num'])->where('id',$id)->select();
        $num=current($res)['num'];
        $sy_num=current($res)['sy_num'];
        //一样说明没有详细可以删除
        if($num==$sy_num)
        {
            $res=$db->delete(['id'=>$id]);//insert直接添加，成功反回1； insertGetId获取主键id
            if($res>0)
            {
                return ["code"=>200,"message"=>"删除物资成功","data"=>$res];
            }
            else
            {
                return ["code"=>404,"message"=>"删除ip失败","data"=>$res];
            }
        }
        else
        {
            return ["code"=>505,"message"=>"无法删除","data"=>[]];
        }


 
    }
    //物资类别select下拉框展示
    public function goods_select_f()
    {
        $sql="select * from admin_goods_f";
        $data=Db::query($sql);
        return ["code"=>0,"message"=>"物资父级查询成功","data"=>$data];
    } 

    //添加物资详情
    public function goods_del_add()
    {
        $data['fid']=input('fid');
        $data['where_g']=input('where_g');
        $data['who_g']=input('who_g');
        $data['why_g']=input('why_g');
        $data['mark']=input('mark');
        $data['num']=input('num');
        $data['add_time']=time();
        $num=input('sy_num');
        //判断剩余数量，能不能添加
        $res=db('goods')->field('sy_num')->where('id',$data['fid'])->select();
        $sy_num=current($res)['sy_num'];

        //剩余数量大于0，可以添加
        if($sy_num>0)
        {
            $start=db('goods_d');
            $res=$start->insertGetId($data);
            if($res)
            {
                //剩余数量扣除
                $num=$num-$data['num'];
                $new['sy_num']=$num;
                $res=db('goods')->where('id',$data['fid'])->update($new);
                if($res)
                {
                    return ["code"=>200,"message"=>"物资详情添加成功","data"=>$res];
                } 
                else
                {
                    return ["code"=>0,"message"=>"物资详情添加失败","data"=>$res];
                }
            }
        }
        else
        {
            return["code"=>0,"message"=>"物资剩余数量不足，无法添加","data"=>[]];
        }

    }

    //获取物资详细信息
    public function goods_del_check()
    {
        $fid=input('fid');
        $data=db('goods_d')->where('fid',$fid)->select();
        $arr=array();
        foreach ($data as $key => $value) {
            $value['add_time']=date('Y-m-d',$value['add_time']);
            $arr[$key]=$value;
        }
       // $data['add_time']=date('Y-m-d H:m:s',$data['add_time']);
        return["code"=>0,"message"=>"获取物资详细信息成功","data"=>$arr];

    }

    public function goods_fid_search()
    {
        $fid=input('fid');
        $page=input('page');
        $limit=input('limit');
        $start=($page-1)*$limit;
        $data=db('goods')->where('fid',$fid)->order('sy_num desc,dhdate desc')->limit($start,$limit)->select();
        $all=db("goods")->field('id')->where('fid',$fid)->select();
        $count=count($all);
        return ["code"=>0,"message"=>"物资查询成功","data"=>$data,"count"=>$count];

    }

    public function goods_mark()
    {
        $id=input('id');
        $data['mark']=input('mark');
        $res=db('goods')->where('id',$id)->update($data);
        if($res>=0)
        {
             return ["code"=>200,"message"=>"备注修改成功"];
        }
        else
        {
            return ["code"=>0,"message"=>"备注修改不成功！"];
        }

    }

    public function goods_d_mark()
    {
        $id=input('id');
        $data['mark']=input('mark');
        $db=db('goods_d');
        $res=$db->where('id',$id)->update($data);
        if($res>=0)
        {
             return ["code"=>200,"message"=>"备注修改成功"];
        }
        else
        {
            return ["code"=>0,"message"=>"备注修改不成功！"];
        }

    }





}

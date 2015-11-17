<?php
// $connstr="DRIVER=Microsoft Access Driver (*.mdb);
// DBQ=".realpath("att2000.mdb");

// $connid=odbc_connect($connstr,"Admin","",SQL_CUR_USE_ODBC );
// var_dump($connid);

// $res = odbc_do($connid, "select * from userInfo", $flags);
// var_dump($res);

$databasepath="att2000.mdb";
$dbusername="";
$dbpassword="";
//include_once("class.php");
$access=new Access($databasepath,$dbusername,$dbpassword);
var_dump($access);

$c = $access->getcondrecord("USERINFO");
$list = $access->query("select * from USERINFO");
$row = $access->first_array("select * from USERINFO");

var_dump($row);
//FileName:class.php
//Summary: Access数据库操作类
//Author:  forest
//CreateTime: 2006-8-10
//LastModifed:
//copyright (c)2006
//http://freeweb.nyist.net/~chairy
//[email]chaizuxue@163.com[/email]
//   使用范例：
//$databasepath="database.mdb";
//$dbusername="";
//$dbpassword="";
//include_once("class.php");
//$access=new Access($databasepath,$dbusername,$dbpassword);

//--------------------------------------------------------------------
class Access
{
	var $databasepath,$constr,$dbusername,$dbpassword,$link;
	function Access($databasepath,$dbusername,$dbpassword)
	{
		$this->databasepath=$databasepath;
		$this->username=$dbusername;
		$this->password=$dbpassword;
		$this->connect();
	}

	function connect()
	{
		$this->constr="DRIVER={Microsoft Access Driver (*.mdb)}; DBQ=" . realpath($this->databasepath);
		$this->link=odbc_connect($this->constr,$this->username,$this->password,SQL_CUR_USE_ODBC);
		return $this->link;
		//if($this->link) echo "恭喜你,数据库连接成功!";
		//else echo "数据库连接失败!";
	}

	function query($sql)
	{
		return @odbc_exec($this->link,$sql);
	}

	function first_array($sql)
	{
		return odbc_fetch_array($this->query($sql));
	}

	function fetch_row($query)
	{
		return odbc_fetch_row($query);
	}

	function total_num($sql)//取得记录总数
	{
		return odbc_num_rows($this->query($sql));
	}

	function close()//关闭数据库连接函数
	{
		odbc_close($this->link);
	}

	function insert($table,$field)//插入记录函数
	{
		$temp=explode(',',$field);
		$ins='';
		for ($i=0;$i<count($temp);$i++)
		{
			$ins.="'".$_POST[$temp[$i]]."',";
		}
		$ins=substr($ins,0,-1);
		$sql="INSERT INTO ".$table." (".$field.") VALUES (".$ins.")";
		$this->query($sql);
	}

	function getinfo($table,$field,$id,$colnum)//取得当条记录详细信息
	{
		$sql="SELECT * FROM ".$table." WHERE ".$field."=".$id."";
		$query=$this->query($sql);
		if($this->fetch_row($query))
		{
			for ($i=1;$i<$colnum;$i++)
			{
				$info[$i]=odbc_result($query,$i);
			}
		}
		return $info;
	}

	function getlist($table,$field,$colnum,$condition,$sort="ORDER BY id DESC")//取得记录列表
	{
		$sql="SELECT * FROM ".$table." ".$condition." ".$sort;
		$query=$this->query($sql);
		$i=0;
		while ($this->fetch_row($query))
		{
			$recordlist[$i]=getinfo($table,$field,odbc_result($query,1),$colnum);
			$i++;
		}
		return $recordlist;
	}

	function getfieldlist($table,$field,$fieldnum,$condition="",$sort="")//取得记录列表
	{
		$sql="SELECT ".$field." FROM ".$table." ".$condition." ".$sort;
		$query=$this->query($sql);
		$i=0;
		while ($this->fetch_row($query))
		{
			for ($j=0;$j<$fieldnum;$j++)
			{
				$info[$j]=odbc_result($query,$j+1);
			}
			$rdlist[$i]=$info;
			$i++;
		}
		return $rdlist;
	}

	function updateinfo($table,$field,$id,$set)//更新记录
	{
		$sql="UPDATE ".$table." SET ".$set." WHERE ".$field."=".$id;
		$this->query($sql);
	}

	function deleteinfo($table,$field,$id)//删除记录
	{
		$sql="DELETE FROM ".$table." WHERE ".$field."=".$id;
		$this->query($sql);
	}

	function deleterecord($table,$condition)//删除指定条件的记录
	{
		$sql="DELETE FROM ".$table." WHERE ".$condition;
		$this->query($sql);
	}

	function getcondrecord($table,$condition="")// 取得指定条件的记录数
	{
		$sql="SELECT COUNT(*) AS num FROM ".$table." ".$condition;
		$query=$this->query($sql);
		$this->fetch_row($query);
		$num=odbc_result($query,1);
		return $num;
	}
}



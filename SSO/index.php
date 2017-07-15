<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- ▼ 引入CSS文件 ▼ -->
  <link href="https://cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
  <link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
  <!-- ▲ 引入CSS文件 ▲ -->
  
  <!-- ▼ 引入JS文件 ▼ -->
  <script src="https://cdn.bootcss.com/jquery/1.11.2/jquery.min.js"></script>
  <script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <!-- ▲ 引入JS文件 ▲ -->
  
  <script>
    // 关闭所有input的自动填充功能
    $("input").attr("autocomplete","off");
  </script>
</head>

<body style="font-family:Microsoft YaHei; background-color:#f9f9f9">
<br><br>

<?php
include("Functions/PublicFunc.php");
$AllModules=new Settings("AllModules.json");
$Sets=new Settings("GlobalSettings.json");
define("Prefix",$Sets->G("SessionPrefix",2,"System"));

$Action=isset($_GET['Action'])?$_GET['Action']:die("500-NAT");
$Module=isset($_GET['Module'])?$_GET['Module']:die("500-NMD");
$Service=isset($_GET['Service'])?$_GET['Service']:die("500-NSV");

// 获取当前模块介绍
$ModuleIntro=$AllModules->G("Name",2,$Module);
if($ModuleIntro==""){
  die('<center><font color="red" style="font-weight:bolder;font-size:30;">500-模块不存在</font></center>');
}

// 判断操作类型
if($Action=="LogIn"){
  include("SSO-LogIn.php");
}elseif($Action=="Authorize" && getSess("isLogged")=="1"){
  include("SSO-Authorize.php");
}elseif($Action=="LogOut"){
  session_destroy();
}else{
  die('<center><font color="red" style="font-weight:bolder;font-size:30;">500-操作错误</font></center>');
}


if(isset($_POST) && $_POST){
  $TimeStamp=time();
  $Ticket=md5($TimeStamp);
  
  $UserName=isset($_POST['UserName'])?$_POST['UserName']:"";
  $Password=isset($_POST['Password'])?$_POST['Password']:"";
  $AuthSure=isset($_POST['AuthSure'])?$_POST['AuthSure']:"";
  setSess(Prefix."isLogged","1");// 已经登录
  
  // 授权操作
  if($AuthSure=="1"){
    if(!isset($_SESSION[Prefix.'AuthModules'])){
      $_SESSION[Prefix.'AuthModules']=array();
    }
    array_push($_SESSION[Prefix.'AuthModules'],$Module);
  }else{
    // 不是授权操作，把用户资料写入Session
    setSess(Prefix."UserName",$UserName);
    setSess(Prefix."Password",$Password);
  }
  
  // 判断是否已经被授权
  if(!in_array($Module,getSess(Prefix."AuthModules"))){
    $AuthURL="?Action=Authorize&Module=$Module&Service=$Service";
    die(header("Location: $AuthURL"));
  }
  
  // 生成Ticket和Token，并跳回原系统登录页
  setSess(Prefix."Ticket",$Ticket);
  $Token=$Ticket.session_id();
  $DirectURL="http://".$Service."?Token=".$Token;
  header("Location: $DirectURL");
}
?>

</body>
</html>
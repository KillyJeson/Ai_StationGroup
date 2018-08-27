<!DOCTYPE html>
<html>
<head>
	<title>Ai_StationGroup注册表</title>
<style>
	li{
		list-style-type: none;
	}
	a{
		text-decoration: none;
		color: black;
	}
	em{
		font-size: 12px;
		margin-right: 20px;
		float: right;
	}
	li{
		background-color:#e8e8e8;
		width: 100%;
		border-bottom: 1px solid #c0c0c0;
		line-height: 200%;
		text-indent:1em;
	}
	li:hover{
		background-color:#c9c9c9;
	}
	#head{
		margin: 20px 0px;
		text-align: center;
		font-size: 28px;
		font-weight: 200;
		width: 100%;
	}
	#main{
		margin: 0 auto;
		width: 80%;
		text-align: center;
	}
	#mod,#class,#method{
		width:13%;
		float: left;
		text-align: left;
		border: 1px solid #c0c0c0;
		border-bottom: none;
		font-size: 20px;
	}
	#class,#method{
		margin-left: 10px;
	}
	#content{
		width:50%;
		margin-left: 10px;
		float: left;
		text-align: left;
		border: 1px solid #c0c0c0;
		border-bottom: none;
	}
	.current{
		background-color: #c9c9c9;
	}
</style>
</head>

<body>
<?php
isset($_GET["mod"]) ? $mod = $_GET["mod"] : $mod = "system";
isset($_GET["class"]) ? $class = $_GET["class"] : $class = "";
isset($_GET["method"]) ? $method = $_GET["method"] : $method = "";
isset($_GET["refresh"]) ? $refresh = $_GET["refresh"] : $refresh = "";
if ($refresh && $mod) {system::register()->register($mod);}
$data = system\classload::$configCache;
if ($mod) {$comment = include SP . "filecache" . D . "Comment" . D . $mod . ".php";}
?>
<div id="head">Ai_StationGroup注册表</div>
<div id="main">

	<div id="mod">
		<li style="background-color: #e9e9e9; text-align: center; font-size: 24px;text-indent:0em;">模块</li>
		<?php foreach ($data as $k => $arr) {?>
		<a href="?mod=<?php echo $k; ?>"><li <?php if (@$mod == $k) {echo 'class="current"';}?>><?php echo $k; ?></li></a>
		<?php }?>
	</div>

	<div id="class">
		<li style="background-color: #e9e9e9; text-align: center; font-size: 24px;text-indent:0em;">对像</li>
		<?php if (!empty($mod)) {?>
			<?php foreach ($data[$mod] as $s => $arr2) {?>
				<a href="?mod=<?php echo $mod; ?>&class=<?php echo $s; ?>"><li <?php if (@$_GET["class"] == $s) {echo 'class="current"';}?>><?php echo $s; ?></li></a>
		<?php }}?>
	</div>

	<div id="method">
		<li style="background-color: #e9e9e9; text-align: center; font-size: 24px;text-indent:0em;">方法</li>
		<?php if (!empty($mod) && !empty($class)) {?>
			<?php foreach ($data[$mod][$class] as $s => $arr2) {if ($s == "path") {continue;}?>
				<a href="?mod=<?php echo $mod; ?>&class=<?php echo $class; ?>&method=<?php echo $s; ?>"><li <?php if (@$_GET["method"] == $s) {echo 'class="current"';}?>><?php echo $s; ?></li></a>
		<?php }}?>
	</div>

<?php if (!empty($mod) && !empty($class) && !empty($method)) {
	$arr = explode("\n", $comment[$mod][$class][$method]);
	$lty = "Ai_" . $class . "::" . $method . "();";
	$yty = $mod . "::" . $class . "()->" . $method . "();";
	$uty = $mod . "\\Ai_" . $class . " as " . $class . "; " . $class . "::" . $method . "();";
} elseif (!empty($mod) && !empty($class)) {
	$arr = explode("\n", $comment[$mod][$class]["comment"]);
	$lty = "Ai_" . $class . "::{functionName}();";
	$yty = $mod . "::" . $class . "();";
	$uty = $mod . "\\Ai_" . $class . " as " . $class . "; " . $class . "::{functionName}();";
} elseif (!empty($mod)) {
	$arr = explode("\n", $comment[$mod]["comment"]);
	$lty = $mod . "::{className}()->{functionName}();";
	$yty = $mod . "::{className}()->{functionName}();";
	$uty = $mod . "::{className}()->{functionName}();";
} else {
	$arr = array();
	$lty = "";
	$yty = "";
	$uty = "";
}
if ($method != "__construct") {
	$arr[] = "全局调用：";
	$arr[] = "模块内：" . $lty;
	$arr[] = "模块外：" . $yty;
	$arr[] = "命名空间：" . $uty;
}
?>
	<div id="content">
		<li style="background-color: #e9e9e9; text-align: center; font-size: 24px;text-indent:0em;">注释<a href="?mod=<?php echo $mod; ?>&class=<?php echo $class; ?>&method=<?php echo $method; ?>&refresh=1"><em>刷新</em></a></li>
		<?php foreach ($arr as $k => $value) {?>
			<li><?php echo $value; ?></li>
		<?php }?>
	</div>
</div>
</body>
</html>
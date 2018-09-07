<!DOCTYPE html>
<html>
<head>
	<title>后台管理</title>
	<script src="/style/jquery-3.3.1.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		function GetVert(cid,obj) {
			var htmlobj=$.ajax({url:"/123.php",async:false});
			$("#"+obj).html(htmlobj.responseText);
		}
	</script>
	<style type="text/css">
		html{margin: 0px;}
		#top{
			height: 90px;
			width: 100%;
			line-height: 200%;
		}
		#logo{
			padding-left: 20px;
			font-size: 22px;
			float: left;
		}
		#nav{
			float:right;
			width:auto;
			font-size:18px;
			padding-right: 20px;
		}
		#nav li{
			float: left:;
			padding: 10px;
		}
	</style>
</head>
<body>
	<div id="top">
		<div id="logo">后台管理</div>
		<div id="nav">
			<li>SYSTEM</li>
			<li>CMS</li>
		</div>
	</div>
	<div id="left"></div>
	<div id="right"></div>
</body>
</html>
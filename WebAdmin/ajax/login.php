<?php
session_start();

// Set language for website
if(isset($_COOKIE['lang'])) {
	if ($_COOKIE['lang'] == "vi") {
		require_once '../include/lang_vi.php';
	} else {
		require_once '../include/lang_en.php';
	}
} else {
    setcookie('lang', 'en', time() + (86400 * 365), "/");
}

//Check if user not login
if (isset($_SESSION["staff_api_key"])) {
	header('Location: ../index.php');
	die();
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>RideSharing - <?php echo $lang['LOGINPAGE_TITLE'] ?></title>
		<meta name="description" content="description">
		<meta name="author" content="Evgeniya">
		<meta name="keyword" content="keywords">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="../plugins/bootstrap/bootstrap.css" rel="stylesheet">
		<link href="http://netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.css" rel="stylesheet">
		<link href='http://fonts.googleapis.com/css?family=Righteous' rel='stylesheet' type='text/css'>
		<link href="../css/style_v2.css" rel="stylesheet">
		<link href="../plugins/toast/resources/css/jquery.toastmessage.css" rel="stylesheet">
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
				<script src="http://getbootstrap.com/docs-assets/js/html5shiv.js"></script>
				<script src="http://getbootstrap.com/docs-assets/js/respond.min.js"></script>
		<![endif]-->
	</head>
<body>
<div class="container-fluid">
	<div id="page-login" class="row">
		<div class="col-xs-12 col-xs-offset-10">
			<!-- HeadSectionDl BEGIN -->
			<div id="languages">
			<a href="../index.php?lang=en"><img src="../img/en.png" /></a>
			<a href="../index.php?lang=vi"><img src="../img/vi.png" /></a>
			</div>
		</div>
		<div class="col-xs-12 col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
			<div class="box">
				<div class="box-content">
					<form method="post" action="../controller/checkLogin.php">
						<div class="text-center">
							<h3 class="page-header">Đăng nhập hệ thống - RideSharing</h3>
						</div>
						<div class="form-group">
							<label class="control-label">Email</label>
							<input type="email" class="form-control" name="email" />
						</div>
						<div class="form-group">
							<label class="control-label">Mật khẩu</label>
							<input type="password" class="form-control" name="password" />
						</div>
						<div class="text-center">
							<button type="submit" class="btn btn-primary">Đăng nhập</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/toast/javascript/jquery.toastmessage.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    <?php 
		if (isset($_SESSION["message"])) {
	?>
    $().toastmessage('showErrorToast', '<?php echo $_SESSION["message"] ?>')
    <?php
    		$_SESSION["message"] = null;
		}
	?>
})
</script>
</body>
</html>

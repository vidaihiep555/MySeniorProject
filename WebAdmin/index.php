<?php
session_start();

//Check if user not login
if (!isset($_SESSION["staff_api_key"])) {
	header('Location: ajax/login.php');
	die();
}
	
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>RideSharing - <?php echo $lang['MAINPAGE_TITLE'] ?></title>
		<meta name="description" content="description">
		<meta name="author" content="DevOOPS">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="plugins/bootstrap/bootstrap.css" rel="stylesheet">
		<link href="plugins/jquery-ui/jquery-ui.min.css" rel="stylesheet">
		<link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
		<link href='http://fonts.googleapis.com/css?family=Righteous' rel='stylesheet' type='text/css'>
		<link href="plugins/fancybox/jquery.fancybox.css" rel="stylesheet">
		<link href="plugins/xcharts/xcharts.min.css" rel="stylesheet">
		<link href="plugins/select2/select2.css" rel="stylesheet">
		<link href="plugins/justified-gallery/justifiedGallery.css" rel="stylesheet">
		<!--<link href="css/style_v2.css" rel="stylesheet">-->
		<link href="plugins/chartist/chartist.min.css" rel="stylesheet">
		<link href="plugins/toast/resources/css/jquery.toastmessage.css" rel="stylesheet">
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
				<script src="http://getbootstrap.com/docs-assets/js/html5shiv.js"></script>
				<script src="http://getbootstrap.com/docs-assets/js/respond.min.js"></script>
		<![endif]-->

		<!-- Bootstrap core CSS -->
	    <link href="assets/css/bootstrap.css" rel="stylesheet">
	    <!--external css-->
	    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
	    <link rel="stylesheet" type="text/css" href="assets/css/zabuto_calendar.css">
	    <link rel="stylesheet" type="text/css" href="assets/js/gritter/css/jquery.gritter.css" />
	    <link rel="stylesheet" type="text/css" href="assets/lineicons/style.css">    
	    
	    <!-- Custom styles for this template -->
	    <link href="assets/css/style.css" rel="stylesheet">
	    <link href="assets/css/style-responsive.css" rel="stylesheet">

	    <script src="assets/js/chart-master/Chart.js"></script>
	</head>
	<body>
		<section id="container" >
<!--Start Header-->
<!--<div id="screensaver">
	<canvas id="canvas"></canvas>
	<i class="fa fa-lock" id="screen_unlock"></i>
</div>
<div id="modalbox">
	<div class="devoops-modal">
		<div class="devoops-modal-header">
			<div class="modal-header-name">
				<span></span>
			</div>
			<div class="box-icons">
				<a class="close-link">
					<i class="fa fa-times"></i>
				</a>
			</div>
		</div>
		<form method="post" action="controller/checkLogin.php">
		<div class="devoops-modal-inner">
		</div>
		<div class="devoops-modal-bottom">
		</div>
		</form>
	</div>
</div>-->
			<header class="header black-bg">
				<div class="sidebar-toggle-box">
				  <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
				</div>
            	<!--logo start-->
	            <a href="index.html" class="logo"><b>UBERIDING</b></a>
	            <!--logo end-->
	            <div class="nav notify-row" id="top_menu">
	                <!--  notification start -->
	                <ul class="nav top-menu">
	                    <!-- settings start -->
	                    <!-- inbox dropdown end -->
	                </ul>
	                <!--  notification end -->
	            </div>
	            <div class="top-menu">
	            	<ul class="nav pull-right top-menu">
	                    <li><a class="logout" href="login.html">Logout ZZZZZ</a></li>
	            	</ul>
	            </div>
        	</header>
<!--End Header-->
			<aside>
				<div id="sidebar"  class="nav-collapse ">
				  <!-- sidebar menu start-->
					<ul class="sidebar-menu nav main-menu" id="nav-accordion">

						<p class="centered">
							<a href="profile.html">
								<img src="<?php echo isset($_SESSION['StaffProfile']['link_avatar'])?'data:image/jpeg;base64,'.$_SESSION['StaffProfile']['link_avatar']:'img/avatar.jpg' ?>" class="img-circle" width="60">
							</a>
						</p>
						<h5 class="centered">
							<span>
								<?php 
									if (isset($_SESSION["StaffProfile"]) && isset($_SESSION["StaffProfile"]["fullname"])) {
										echo $_SESSION["StaffProfile"]["fullname"];
									} else {
										echo "Khách";
									}
								?>
							</span>
						</h5>
						  	
					  <li class="mt">
					      <a class="active" href="index.html">
					          <i class="fa fa-dashboard"></i>
					          <span>DashboardAAAAAAAAAA</span>
					      </a>
					  </li>

					  <li class="dropdown">
						<a href="ajax/staff_list.php" class="ajax-link">
							<i class="fa fa-bar-chart-o"></i>
							<span class="hidden-xs">Quản lí nhân viên</span>
						</a>
					</li>

					  <li class="sub-menu">
					      <a href="javascript:;" >
					          <i class="fa fa-cogs"></i>
					          <span>Components</span>
					      </a>
					      <ul class="sub">
					          <li><a  href="calendar.html">Calendar</a></li>
					          <li><a  href="gallery.html">Gallery</a></li>
					          <li><a  href="todo_list.html">Todo List</a></li>
					      </ul>
					  </li>
					  <li class="sub-menu">
					      <a href="javascript:;" >
					          <i class="fa fa-book"></i>
					          <span>Extra Pages</span>
					      </a>
					      <ul class="sub">
					          <li><a  href="blank.html">Blank Page</a></li>
					          <li><a  href="login.html">Login</a></li>
					          <li><a  href="lock_screen.html">Lock Screen</a></li>
					      </ul>
					  </li>
					  <li class="sub-menu">
					      <a href="javascript:;" >
					          <i class="fa fa-tasks"></i>
					          <span>Forms</span>
					      </a>
					      <ul class="sub">
					          <li><a  href="form_component.html">Form Components</a></li>
					      </ul>
					  </li>
					  <li class="sub-menu">
					      <a href="javascript:;" >
					          <i class="fa fa-th"></i>
					          <span>Data Tables</span>
					      </a>
					      <ul class="sub">
					          <li><a  href="basic_table.html">Basic Table</a></li>
					          <li><a  href="responsive_table.html">Responsive Table</a></li>
					      </ul>
					  </li>
					  <li class="sub-menu">
					      <a href="javascript:;" >
					          <i class=" fa fa-bar-chart-o"></i>
					          <span>Charts</span>
					      </a>
					      <ul class="sub">
					          <li><a  href="morris.html">Morris</a></li>
					          <li><a  href="chartjs.html">Chartjs</a></li>
					      </ul>
					  </li>

					</ul>
				  <!-- sidebar menu end-->
				</div>
      		</aside>
			<section id="main-content">
				<section class="wrapper">
				  <div id="content" class="col-xs-12 col-sm-10">
				    <div id="about">
				      <div class="about-inner">
				        <h4 class="page-header">RideSharing - System management</h4>
				        <p>RideSharing team</p>
				      </div>
				    </div>
				    <div class="preloader">
				      <img src="img/devoops_getdata.gif" class="devoops-getdata" alt="preloader"/>

				    </div>
				    <div id="ajax-content"></div>
				    
				  </div>
				  
				</section>
			</section>

      <!--main content end-->
      <!--footer start-->
			<footer class="site-footer">
				<div class="text-center">
				  2014 - Alvarez.is
				  <a href="index.html#" class="go-top">
				      <i class="fa fa-angle-up"></i>
				  </a>
				</div>
			</footer>
			<!--End Container-->
			<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
			<!--<script src="http://code.jquery.com/jquery.js"></script>-->
			<script src="plugins/jquery/jquery.min.js"></script>
			<script src="plugins/toast/javascript/jquery.toastmessage.js"></script>
			<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
			<!-- Include all compiled plugins (below), or include individual files as needed -->
			<script src="plugins/bootstrap/bootstrap.min.js"></script>
			<script src="plugins/justified-gallery/jquery.justifiedGallery.min.js"></script>
			<script src="plugins/tinymce/tinymce.min.js"></script>
			<script src="plugins/tinymce/jquery.tinymce.min.js"></script>
			<!-- All functions for this theme + document.ready processing -->
			<script src="js/devoops.js"></script>
			<!--Reference the SignalR library. -->
			<script src="js/jquery.signalR-2.0.3.min.js"></script>
			<!--Reference the autogenerated SignalR hub script. -->
			<script src="http://52.11.206.209:8080/signalr/hubs"></script>
			<script>
			$(document).ready(function () {
				//Set the hubs URL for the connection
				$.connection.hub.url = "http://52.11.206.209:8080/signalr";

				// Declare a proxy to reference the hub.
				chat = $.connection.myHub;

				$.connection.hub.logging = true;

				$('#locked-screen').on('click', function (e) {
					e.preventDefault();
					$('body').addClass('body-screensaver');
					$('#screensaver').addClass("show");
					ScreenSaver();
				});
			});
			</script>
		</section>
	</body>
</html>
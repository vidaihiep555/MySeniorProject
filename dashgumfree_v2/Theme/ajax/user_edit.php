<?php
	session_start();
	require_once '../include/Config.php';
	if (!isset($_SESSION["staff_api_key"])) {
		header('Location: ../ajax/login.php');
		die();
	}

	if (!isset($_SESSION["user"])) {
		header('Location: ../index.php#ajax/user_list.php');
		die();
	} else {
		$user = $_SESSION["user"];
	}
?>

<div class="row">
	<div id="breadcrumb" class="col-xs-12">
		<a href="#" class="show-sidebar">
			<i class="fa fa-bars"></i>
		</a>
		<ol class="breadcrumb pull-left">
			<li><a href="#">Dashboard</a></li>
			<li><a href="#">Manage User</a></li>
			<li><a href="#">User Detail</a></li>
		</ol>
		<div id="social" class="pull-right">
			<a href="#"><i class="fa fa-google-plus"></i></a>
			<a href="#"><i class="fa fa-facebook"></i></a>
			<a href="#"><i class="fa fa-twitter"></i></a>
			<a href="#"><i class="fa fa-linkedin"></i></a>
			<a href="#"><i class="fa fa-youtube"></i></a>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-xs-12 col-sm-12">
		<div class="box">
			<div class="box-content">
				<form method='POST' action='controller/user.php' class="form-horizontal" role="form">
					<div class="form-group">
						<img class="img-rounded col-sm-4" src="data:image/jpeg;base64,<?php echo $user['link_avatar'] ?>" alt="">
						<div class="col-sm-8">
							<div class="form-group">
								<label class="col-sm-4 control-label" style="text-align:left">Họ tên:</label>
								<div class="col-sm-6">
								<input disabled type="text" class="form-control" placeholder="Full name" value="<?php echo $user['fullname'] ?>"
									data-toggle="tooltip" data-placement="bottom" title="Họ và tên" name="fullname">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" style="text-align:left">Điện thoại:</label>
								<div class="col-sm-6">
									<input disabled type="text" class="form-control" placeholder="Last name" value="<?php echo $user['phone'] ?>"
									data-toggle="tooltip" data-placement="bottom" title="Số điện thoại" name="phone">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" style="text-align:left">Email:</label>
								<div class="col-sm-6">
									<input disabled type="text" class="form-control" placeholder="Email" value="<?php echo $user['email'] ?>"
									data-toggle="tooltip" data-placement="bottom" title="Địa chỉ email" name="email">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" style="text-align:left">Chứng minh nhân dân:</label>
								<div class="col-sm-6">
									<input disabled type="text" class="form-control" placeholder="Chứng minh nhân dân" value="<?php echo $user['personalID'] ?>"
									data-toggle="tooltip" data-placement="bottom" title="Chứng minh nhân dân" name="personalID">
								</div>
								<div class="col-sm-1">
									<a target="_blank" href="ajax/personal_id.php#<?php echo $user['personalID_img'] ?>" onclick="return popup('ajax/personal_id.php#<?php echo $user['personalID_img'] ?>')" type="button" 
										class="btn btn-primary btn-app-sm btn-circle"><i class="fa fa-camera"></i>
									</a>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" style="text-align:left">Ngày tạo tài khoản:</label>
								<div class="col-sm-6">
									<input type="text" class="form-control" value="<?php echo $user['created_at'] ?>"
									disabled data-toggle="tooltip" data-placement="bottom" title="Ngày tạo tài khoản">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" style="text-align:left">Đã xác minh:</label>
								<div class="col-sm-6">
									<div class="toggle-switch toggle-switch-success">
										<label>
											<input name="status" type="hidden" value="<?php echo $user['status']?>">
											<input <?php echo $user['status']==4?'checked':'' ?> type="checkbox" name="identify">
											<div class="toggle-switch-inner"></div>
											<div class="toggle-switch-switch"><i class="fa fa-check"></i></div>
										</label>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" style="text-align:left">Cấp độ:</label>
								<div class="col-sm-6">
									<input type='hidden' name='status' value='<?php echo $user['status'] ?>'/>
									<?php
										$percent = round($user['status']/4*100);
									?>
									<div class="progress">
										<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $percent ?>" 
											aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percent ?>%;">
											<span><?php echo $percent ?>%</span>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" style="text-align:left">Khóa tài khoản:</label>
								<div class="col-sm-6">
									<div class="toggle-switch toggle-switch-success">
										<label>
											<input <?php echo $user['locked']==true?'checked':'' ?> type="checkbox" name="locked">
											<div class="toggle-switch-inner"></div>
											<div class="toggle-switch-switch"><i class="fa fa-check"></i></div>
										</label>
									</div>
								</div>
							</div>
							<input type='hidden' name='user_id' value='<?php echo $user['user_id'] ?>'/>
							<input type='hidden' name='act' value='edit'/>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="form-group">
						<div class="col-sm-offset-4 col-sm-2">
							<a href="index.php" type="cancel" class="btn btn-default btn-label-left">
							<span><i class="fa fa-clock-o txt-danger"></i></span>
								Quay lại
							</a>
						</div>
						<div class="col-sm-2">
							<button type="submit" class="btn btn-primary btn-label-left">
							<span><i class="fa fa-clock-o"></i></span>
								Cập nhật
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
// Run Select2 plugin on elements
function DemoSelect2(){
	$('#s2_with_tag').select2({placeholder: "Select OS"});
	$('#s2_country').select2();
}
// Run timepicker
function DemoTimePicker(){
	$('#input_time').timepicker({setDate: new Date()});
}
function popup(url) {
	newwindow=window.open(url,'name','height=300,width=500');
	if (window.focus) {newwindow.focus()}
	return false;
}
$(document).ready(function() {
	// Create Wysiwig editor for textare
	TinyMCEStart('#wysiwig_simple', null);
	TinyMCEStart('#wysiwig_full', 'extreme');
	// Add slider for change test input length
	FormLayoutExampleInputLength($( ".slider-style" ));
	// Initialize datepicker
	$('#input_date').datepicker({setDate: new Date()});
	// Load Timepicker plugin
	LoadTimePickerScript(DemoTimePicker);
	// Add tooltip to form-controls
	$('.form-control').tooltip();
	LoadSelect2Script(DemoSelect2);
	// Load example of form validation
	LoadBootstrapValidatorScript(DemoFormValidator);
	// Add drag-n-drop feature to boxes
	WinMove();
});
</script>

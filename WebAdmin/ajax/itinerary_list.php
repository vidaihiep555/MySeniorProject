<?php
session_start();
require_once '../include/Config.php';
?>
<div class="row">
	<div id="breadcrumb" class="col-xs-12">
		<a href="#" class="show-sidebar">
			<i class="fa fa-bars"></i>
		</a>
		<ol class="breadcrumb pull-left">
			<li><a href="#">Dashboard</a></li>
			<li><a href="#">Manage Itinerary</a></li>
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
	<div class="col-xs-12">
		<div class="box">
			<div class="box-content no-padding">
				<table class="table table-bordered table-striped table-hover table-heading table-datatable" id="datatable-1">
					<thead>
						<tr>
							<th>STT</th>
							<th>Tài Xế</th>
							<th>Khách Hàng</th>
							<th>Điểm Đi</th>
							<th>Điểm Đến</th>
							<th>Thời Gian</th>
							<th>Tình Trạng</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<!-- Start: list_row -->
						<?php
						require_once '/Config.php';
						$api_key = $_SESSION["staff_api_key"];
						$ch = curl_init();

						curl_setopt($ch, CURLOPT_URL, REST_HOST."/RESTFul/v1/staff/itineraries");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: '.$api_key));

						// execute the request
						$result = curl_exec($ch);

						// close curl resource to free up system resources
						curl_close($ch);

						$json = json_decode($result);
						
						$res = $json->{'itineraries'};

						$i = 1;
						foreach ($res as $value) {
						?>
						<tr>
							<td><?php echo $i++ ?></td>
							<td><img class="img-rounded" src="data:image/jpeg;base64,<?php echo $value->{'link_avatar'}==NULL?' ':$value->{'link_avatar'} ?>" alt="">
								<?php echo $value->{'fullname'}==NULL?' ':$value->{'fullname'} ?>
							</td>
							<td><?php echo $value->{'email'}==NULL?' ':$value->{'email'} ?></td>
							<td><?php echo $value->{'start_address'}==NULL?' ':$value->{'start_address'} ?></td>
							<td><?php echo $value->{'end_address'}==NULL?' ':$value->{'end_address'} ?></td>
							<td><?php echo $value->{'leave_date'}==NULL?' ':$value->{'leave_date'} ?></td>
							<td><?php 
									//$percent = round($value->{'status'}/4*100);
									$status = 'No definition';
									if ($value->{'status'} == 1) {
										$status = 'Not Accepted';
									} else if ($value->{'status'} == 2) {
										//$status = 'Accepted';
									} else {
										//$status = 'No definition';
									}
									echo $value->{'status'};
								?>		
								
							</td>
							<td><a href="controller/itinerary.php?itinerary_id=<?php echo $value->{'itinerary_id'} ?>&act=view" 
									class="btn btn-warning btn-app-sm btn-circle"><i class="fa fa-edit"></i></a>
								<a href="controller/itinerary.php?itinerary_id=<?php echo $value->{'itinerary_id'} ?>
									&act=delete" 
									class="btn btn-danger btn-app-sm btn-circle"><i class="fa fa-trash-o"></i></a> 
							</td>
						</tr>
						<?php
						}
						?>
					<!-- End: list_row -->
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
// Run Datables plugin and create 3 variants of settings
function AllTables(){
	TestTable1();
	TestTable2();
	TestTable3();
	LoadSelect2Script(MakeSelect2);
}
function MakeSelect2(){
	$('select').select2();
	$('.dataTables_filter').each(function(){
		$(this).find('label input[type=text]').attr('placeholder', 'Search');
	});
}
$(document).ready(function() {
	// Load Datatables and run plugin on tables 
	LoadDataTablesScripts(AllTables);
	// Add Drag-n-Drop feature
	WinMove();
});
</script>
<script type="text/javascript">
$(document).ready(function () {
    <?php 
		if (isset($_SESSION["message"])) {
	?>
    $().toastmessage('showSuccessToast', '<?php echo $_SESSION["message"] ?>')
    <?php
    		$_SESSION["message"] = null;
		}
	?>
})
</script>
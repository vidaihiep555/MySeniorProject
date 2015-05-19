<?php
session_start();
require_once '../include/Config.php';
if (!isset($_SESSION["staff_api_key"])) {
	header('Location: ../ajax/login.php');
	die();
}

if ((isset($_GET['act']) && isset($_GET['itinerary_id'])) || (isset($_POST['act']) && isset($_POST['itinerary_id']))) {
	$act = !isset($_GET['act'])?$_POST['act']:$_GET['act'];
	$itinerary_id = !isset($_GET['act'])?$_POST['itinerary_id']:$_GET['itinerary_id'];

	if ($act == 'view') {
		$api_key = $_SESSION["staff_api_key"];

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, REST_HOST."/RESTFul/v1/staff/itinerary/".$itinerary_id);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		// execute the request
		$result = curl_exec($ch);

		// close curl resource to free up system resources
		curl_close($ch);

		$itinerary = json_decode($result, true);
		//$itinerary['itinerary_id'] = $itinerary_id;

		if(isset($itinerary)) {
			$_SESSION['itinerary'] = $itinerary;
			//echo "here"; 
		}

		header('Location: ../index.php#ajax/itinerary_edit.php');
		die();
	} else if ($act == 'edit') {
		$locked = isset($_POST['locked'])?1:0;
		$status = $_POST['status'];
		
		$status = isset($_POST['identify'])?4:$status==4?3:$status;

		$data = array(
			'locked' => $locked,
			'status' => $status
			);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, REST_HOST."/RESTFul/v1/staff/itinerary/".$itinerary_id);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: '.$_SESSION['staff_api_key']));
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));

		// execute the request
		$result = curl_exec($ch);

		// close curl resource to free up system resources
		curl_close($ch);

		$json = json_decode($result);



		if (!$json->{'error'}) {
			$_SESSION['message'] = $json->{'message'};
		} else {
			$_SESSION['message'] = $json->{'message'};
		}

		header('Location: ../index.php#ajax/itinerary_list.php');
		die();
	} else if ($act == 'delete') {
		//Initial curl
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, REST_HOST."/RESTFul/v1/staff/itinerary/".$itinerary_id);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		//curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: '.$_SESSION['api_key']));

		// execute the request
		$result = curl_exec($ch);

		// close curl resource to free up system resources
		curl_close($ch);

		$json = json_decode($result);

		echo $_SESSION['staff_api_key'];
		print_r($itinerary_id);
		//echo $ch;
		print_r($json);

		//if (!$json->{'error'}) {
		//	$_SESSION['message'] = $json->{'message'};
		//} else {
		//	$_SESSION['message'] = $json->{'message'};
		//}

		echo "right h";

		//header('Location: ../index.php#ajax/itinerary_list.php');
		//die();
	} else {
		header('Location: ../index.php#ajax/itinerary_list.php');
		die();
	}
} else {
	echo "hhhhh";
	header('Location: ../index.php#ajax/itinerary_list.php');
	die();
}
?>
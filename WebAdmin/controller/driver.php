<?php
session_start();
require_once '../include/Config.php';
if (!isset($_SESSION["staff_api_key"])) {
	header('Location: ../ajax/login.php');
	die();
}

if ((isset($_GET['act']) && isset($_GET['user_id'])) || (isset($_POST['act']) && isset($_POST['user_id']))) {
	$act = !isset($_GET['act'])?$_POST['act']:$_GET['act'];
	$user_id = !isset($_GET['act'])?$_POST['user_id']:$_GET['user_id'];

	if ($act == 'view') {
		$api_key = $_SESSION["staff_api_key"];

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, REST_HOST."/RESTFul/v1/staff/driver/".$user_id);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Authorization: '.$api_key));

		// execute the request
		$result = curl_exec($ch);

		// close curl resource to free up system resources
		curl_close($ch);
		$user = json_decode($result, true);
		$user['user_id'] = $user_id;

		if(isset($user)) {
			$_SESSION['driver'] = $user;
		}
		
		header('Location: ../index.php#ajax/driver_edit.php');
		die();
	} else if ($act == 'edit') {
		$status = $_POST['status'];
		
		if (isset($_POST['identify'])) {
			$status = 2;
		} else {
			$status = 1;
		}

		$data = array(
			'status' => $status
			);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, REST_HOST."/RESTFul/v1/staff/driver/".$user_id);
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

		header('Location: ../index.php#ajax/driver_list.php');
		die();
	} else {
		header('Location: ../index.php#ajax/driver_list.php');
		die();
	}
} else {
	header('Location: ../index.php#ajax/driver_list.php');
	die();
}
?>
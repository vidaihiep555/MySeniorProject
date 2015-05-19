<?php
session_start();
require_once '../include/Config.php';
if (!isset($_SESSION["staff_api_key"])) {
	header('Location: ../ajax/login.php');
	die();
}

if ((isset($_GET['act']) && isset($_GET['staff_id'])) || (isset($_POST['act']) && isset($_POST['staff_id']))) {
	$act = !isset($_GET['act'])?$_POST['act']:$_GET['act'];
	$staff_id = !isset($_GET['staff_id'])?$_POST['staff_id']:$_GET['staff_id'];

	if ($act == 'view') {
		$api_key = $_SESSION["staff_api_key"];

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, REST_HOST."/RESTFul/v1/staffs/".$staff_id);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Authorization: '.$api_key));

		// execute the request
		$result = curl_exec($ch);

		// close curl resource to free up system resources
		curl_close($ch);
		$staff = json_decode($result, true);
		$staff['staff_id'] = $staff_id;

		if(isset($staff)) {
			$_SESSION['staff'] = $staff;
		}
		
		header('Location: ../index.php#ajax/staff_edit.php');
		die();
	} else if ($act == 'edit') {
		$fullname = $_POST['fullname'];
		$personalID = $_POST['personalID'];
		$email = $_POST['email'];
		$data = array(
			'fullname' => $fullname,
			'email' => $email,
			'personalID' => $personalID
		);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, REST_HOST."/RESTFul/v1/staffs/".$staff_id);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch,CURLOPT_HTTPHEADER, array('Authorization: '.$_SESSION['staff_api_key']));
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

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

		header('Location: ../index.php#ajax/staff_list.php');
		die();
	} else if ($act == 'delete') {
		//Initial curl
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, REST_HOST."/RESTFul/v1/staffs/".$staff_id);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: '.$_SESSION['staff_api_key']));

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

		header('Location: ../index.php#ajax/staff_list.php');
		die();
	} else if ($act == 'create') {
		if (isset($_POST['fullname'])) {
			//Initial curl
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, REST_HOST."/RESTFul/v1/staffs/".$staff_id);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: '.$_SESSION['staff_api_key']));

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
		} else {
			$_SESSION["staff"] = null;
		}

		//header('Location: ../index.php#ajax/staff_edit.php');
		die();
	} else {
		header('Location: ../index.php#ajax/staff_list.php');
		die();
	}
} else if (isset($_GET['act']) && $_GET['act'] == 'viewprofile') {
	$_SESSION['staff'] = $_SESSION['StaffProfile'];

	header('Location: ../index.php#ajax/staff_edit.php');
	die();
} else if ((isset($_GET['act']) && $_GET['act'] == 'create')) {
	$_SESSION['staff'] = null;

	header('Location: ../index.php#ajax/staff_edit.php');
	die();
} else if ((isset($_POST['act']) && $_POST['act'] == 'create')) {
	$fullname = $_POST['fullname'];
	$personalID = $_POST['personalID'];
	$email = $_POST['email'];
	$data = array(
		'fullname' => $fullname,
		'email' => $email,
		'personalID' => $personalID
	);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, REST_HOST."/RESTFul/v1/staff");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch,CURLOPT_HTTPHEADER, array('Authorization: '.$_SESSION['staff_api_key']));
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

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

	header('Location: ../index.php#ajax/staff_list.php');
	die();
} else {
	header('Location: ../index.php#ajax/staff_list.php');
	die();
}
?>
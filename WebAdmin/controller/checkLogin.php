<?php
require_once '../include/Config.php';

session_start();

if (isset($_SESSION["staff_api_key"])) {
	header('Location: ../index.php');
	die();
}

if (isset($_POST['email']) && isset($_POST['password'])) {
	$email = $_POST['email'];
	$password = $_POST['password'];

	$data = array('email' => $email, 'password' => $password);

	//Initial curl
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, REST_HOST."/RESTFul/v1/staff/login");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

	// execute the request
	$result = curl_exec($ch);

	// close curl resource to free up system resources
	curl_close($ch);

	$json = json_decode($result);

	if (!$json->{'error'}) {
		$_SESSION["staff_api_key"] = $json->{'apiKey'};
		$staff = array(
			'fullname' => $json->{'fullname'},
			'email' => $json->{'email'},
			'personalID' => $json->{'personalID'},
			'link_avatar' => $json->{'link_avatar'},
			'created_at' => $json->{'created_at'},
			'staff_id' => $json->{'staff_id'},
			'role' => $json->{'role'}
			);

		$_SESSION["StaffProfile"] = $staff;
		
		header('Location: ../index.php');
		die();
	} else {
		$_SESSION['message'] = $json->{'message'};
		
		header('Location: ../ajax/login.php');
		die();
	}
} else {
	header('Location: ../ajax/login.php');
	die();
}

?>
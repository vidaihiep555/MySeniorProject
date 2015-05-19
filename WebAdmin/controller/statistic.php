<?php
session_start();
require_once '../include/Config.php';
if (!isset($_SESSION["staff_api_key"])) {
	header('Location: ../ajax/login.php');
	die();
}

if (isset($_GET['view'])) {
	$api_key = $_SESSION["staff_api_key"];

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, REST_HOST."/RESTFul/v1/statistic/user");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER,array('Authorization: '.$api_key));

	// execute the request
	$result = curl_exec($ch);

	// close curl resource to free up system resources
	curl_close($ch);
	$stats = json_decode($result)->{'stats'};
	print_r($stats);

	if(isset($stats)) {
		$_SESSION['stats'] = $stats;
	}

	header('Location: ../index.php#ajax/statistic.php');
	die();
} else {
	header('Location: ../index.php');
	die();
}
?>
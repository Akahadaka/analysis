<?php
include('analysis.class.php');

$default_source = "test/Cu/";
$default_output = "test/";

if (isset($_POST['submit'])) {

	$default_source = $_POST['source'];
	$default_output = $_POST['output'];

	$data = new Analysis($_POST['source']);
	#$data->subfolders = TRUE;

	$data->process();

	$data->save($_POST['output']);

}



include('analysis.tpl.php');
?>
<?php
include('Cart.php');
include('Form.php');

$Booking = new Booking();

if($_SERVER['REQUEST_METHOD'] == 'POST') {

	$Form = new Form($Booking->form(),$_POST);
	$response = $Booking->create($_POST);

	if($response['request']['status'] == 'OK') {
		// successful transactions will return a url to be redirected to for payment or an invoice.
		header("Location: {$response['request']['url']}"); 
		exit;

	} else {
		$Form->msg($response['request']['msg'],$response['request']['status']);
	}
} else {
	$Form = new Form($Booking->form());
}
header('Content-type: text/html; charset=utf-8');
?>
<html>
<head>
<style style="text/css">
body { font:90% "Helvetica Neue",Helvetica,Arial,sans-serif; }
label { width: 10em; display: block; text-align: right; font-weight: bold; float: left; margin-right: 1em;}
input, select, textarea { width: 20em; display: block; }
.msg.ERROR { color: firebrick; font-weight: bold;}
</style>
</head>
<body>
<h1>Checkfront Shopping Card Demo</h1>
<form method="post" action="<?=$_SERVER['SCRIPT_NAME']?>?cart_id=<?=$_GET['cart_id']?>">
<fieldset>
<?php
echo $Form->msg();
if(!count($Form->fields)) {
	echo '<p>ERROR: Cannot fetch fields.</p>';
} else {
	foreach($Form->fields as $field_id => $data) {
		if(!empty($data['define']['layout']['lbl'])) {
			echo "<label for='{$field_id}'>" . $data['define']['layout']['lbl'] . ':</label>';
		}
		echo $Form->render($field_id);
		echo '<br />';
	}
	echo '<button type="submit"> Continue </button>';
}
?>
<pre style="margin-left: 10px">
<strong>Debug Information</strong>
Cart ID: <input type="text" readonly="readonly" name="cart_id" value="<?=$Booking->cart_id?>" /> 
</fieldset>
</form>
</body>
</head>
</html>

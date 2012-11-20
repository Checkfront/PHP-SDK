<?php
include('Cart.php');

$Booking = new Booking();

// start a new booking session
if(isset($_GET['slip'])) {
	// slips contain all the details needed to book the item
	$Booking->set($_GET['slip']);
	// let's redirect so it doesn't continue to append to the booking
	header("Location: {$_SERVER['SCRIPT_NAME']}?cart_id={$Booking->cart_id}");
	exit;
}

// CLEAR CART
if(isset($_GET['reset'])) {
	header("Location: {$_SERVER['SCRIPT_NAME']}");
	exit;
}


$date = (isset($_GET['date'])) ? date('Y-m-d',strtotime($_GET['date'])) : date('Y-m-d');

$items = $Booking->query_inventory(
	array(
		'start_date'=>$date,
		'end_date'=>$date,
		'param'=>array( 'guests'=>1),
	)
);
header('Content-type: text/html; charset=utf-8');
?>
<html>
<head>
<style style="text/css">body { font:90% "Helvetica Neue",Helvetica,Arial,sans-serif; }</style>
</head>
<body>
<h1>Checkfront Shopping Card Demo</h1>
<p>This is a bare bones example of how to query inventory items from the Checkfront API, add them to a booking session, and create a new booking.  This is a shopping cart style demo that allows you to add and remove multiple items to a booking before proceeding.</p>
<!-- it may be preferred to set this in your local session -->
<div style="width: 500px; float: left;">
<h3>Available Items</h3>
<form method="get" action="" accept-charset="utf-8">
Date: <input type="date" name="date" value="<?php echo $date?>" /> <input type="submit" value=" Search" />
<ul style="list-style: none; padding:0">
<?php

$date = (isset($_GET['date'])) ? date('Y-m-d',strtotime($_GET['date'])) : date('Y-m-d');

$items = $Booking->query_inventory(
	array(
		'start_date'=>$date,
		'end_date'=>$date,
		'param'=>array( 'guests'=>1),
	)
);

if(count($items)) {
	$c = 0;
	foreach($items as $item_id => $item) {
		echo '<li style="padding: 1em; border-bottom: solid 1px #ccc;">';
		echo "<input type='checkbox' name='slip[]' value='{$item['rate']['slip']}' id='item_{$item_id}' /><strong>{$item['name']}</strong><br />";
		echo " -  {$item['rate']['available']} available for {$item['rate']['date']}<br />";
		//	echo '<p>' . nl2br($item['summary']) . '</p>';
		echo "&nbsp; <small style='color: #999;'>SLIP: {$item['rate']['slip']}</small><br />";
		echo '</li>';
		// Let's only show 5 for the sake of the demo;
		if($c++ == 5) break;
	}
}
?>
</ul>
<input type="submit" value=" Continue ">
</form>
</div>
<div style="float: left; margin-left: 10px;">
<div style="background: #eee; width: 250px; min-height: 300px; padding: 10px; border-radius: 10px;">
<form method="get" action="create.php">
<input type="hidden" name="cart_id" value="<? echo $Booking->cart_id?>" />
<h3>Shopping Cart <?php intval(count($Booking->cart))?></h3>
<ul style="padding: 0; list-style: none;">
<?
if(count($Booking->cart)) {
	foreach($Booking->cart as $line_id => $item) {
		echo "<li style='padding: 5px'><strong>{$item['name']}</strong><br />";
		echo $item['total'];
		if($item['date_desc']) echo '<br /><span style="font-size: .9em; color: #444">' . $item['date_desc'] . '</span>';
		echo "</li>";
	}
	echo '<li><input type="submit" name="create" value=" Book Now " /></li>';
} else {
	echo '<p>EMPTY</p>';
}
?>
</div>
<a href="<?php echo $_SERVER['SCRIPT_NAME']?>">Clear session</a>
</form>
<pre style="margin-left: 10px">
<strong>Debug Information</strong>
Cart ID: <input type="text" readonly="readonly" name="cart_id" value="<?=$Booking->cart_id?>" /> 
</form>
</body>
</head>
</html>

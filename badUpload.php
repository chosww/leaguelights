<?php
	session_start();
	$eMessage = $_SESSION['uploadError'];
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<meta name="author" content="">

		<title>LeagueLights Upload Video</title>

		<!-- Bootstrap core CSS -->
		<link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

		<!-- Custom styles for this template -->
		<link href="css/shop-homepage.css" rel="stylesheet">
		<link href="css/leaguelights.css" rel="stylesheet">
	</head>
	
	<body>
		<!-- Navigation -->
		<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
			<div class="container">
				<p class="navbar-brand">LeagueLights</p>
			</div>
		</nav>	
	
	
		<div class="container py-5">
			<div class="row my-4">
				<div class="col-lg-3"></div>
				<h4 class="text-center col-lg-6"><?php echo $eMessage;?></h4>
				<div class="col-lg-3"></div>
			</div>
	</body>
</html>
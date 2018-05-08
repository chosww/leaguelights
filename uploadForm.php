<?php
	session_start();
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
				<h4 class="text-center col-lg-6">Video size is limited to 20mb, so try to extract the best part of the video!<br><a href="recordInst.php">How to extract videos</a></h4>
				<div class="col-lg-3"></div>
			</div>
			<div class="row my-4">
				<form class="col-lg-12" action="upload.php" method="post" enctype="multipart/form-data">
					<div class="row">
						<div class="col-lg-3"></div>
						<h4 class="col-lg-2 text-center">Title</h4>
						<input class="col-lg-4" type="text" name="fileName" required>
						<div class="col-lg-3"></div>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-4"></div>
						<input class="col-lg-3" type="file" name="file" required>
						<input class="col-lg-1" type="submit" value="Upload" name="submit">
						<div class="col-lg-4"></div>
					</div>
				</form>
			</div>
	</body>
</html>
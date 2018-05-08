<?php
	session_start();
	if (isset($_GET['submit'])) {
		$_SESSION['currentId'] = "";
		echo "<script>window.close();</script>";
	}
	
?>

<!DOCTYPE HTML>

<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<meta name="author" content="">

		<title>LeagueLights</title>

		<!-- Bootstrap core CSS -->
		<link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

		<!-- Custom styles for this template -->
		<link href="css/shop-homepage.css" rel="stylesheet">
		<link href="css/leaguelights.css" rel="stylesheet">
	
	</head>
	<body>
		<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
			<div class="container">
				<p class="navbar-brand">LeagueLights</p>
			</div>
		</nav>
		<div class="container py-5">
			<div class="row my-4">
				<div class="col-lg-3"></div>
				<form class="col-lg-6">
					<div class="row">
						<div class="col-lg-3"></div>
						<h4 class="text-center col-lg-6">Please sign in with new password</h4>
						<div class="col-lg-3"></div>
					</div>
					<div class="row">
						<div class="col-lg-5"></div>
						<button class="col-lg-2" type="submit" name="submit" value="submit">Ok</button>
						<div class="col-lg-5"></div>
					</div>
				</form>
				<div class="col-lg-3"></div>
			</div>
		</div>
		<script>
			window.onunload = refreshParent;
			function refreshParent() {
			window.opener.location.reload();
			}
		</script>
	</body>

</html>
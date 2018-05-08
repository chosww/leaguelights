<?php
	session_start();
	$loggedInAs = $_SESSION["currentId"];
	$serverStr = $_SESSION["currentServer"];
	
	if(isset($_POST['submit'])){
		$pswdOld = $_POST['pswdOld'];
		$pswdNew = $_POST['pswdNew'];
		$pswdConf = $_POST['pswdConf'];
			
		$dbHost = getenv('RDS_HOSTNAME');
		$dbUser = getenv('RDS_USERNAME');
		$dbPass = getenv('RDS_PASSWORD');
		$dbConn = 'mysql:host='.$dbHost.';dbname='.$serverStr.';charset=utf8mb4';
			
		try{
			$conn = new \PDO( $dbConn, 
							$dbUser, 
							$dbPass, 
							array(
								\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
								\PDO::ATTR_PERSISTENT => false
							)
						);
	
			
			$pswdHandle = $conn->prepare("SELECT Password from user where SummonerId = ?");
			$pswdHandle->bindParam(1, $loggedInAs, PDO::PARAM_STR);
			$pswdHandle->execute();
			$hashPassword = $pswdHandle->fetchColumn();
				
			if(password_verify($pswdOld, $hashPassword) && ($pswdNew === $pswdConf)){
				$updatedPswd = password_hash($pswdNew, PASSWORD_DEFAULT);
				$updateHandle = $conn->prepare("Update user SET Password = ? Where SummonerId = ?");
				$updateHandle->bindParam(1, $updatedPswd, PDO::PARAM_STR);
				$updateHandle->bindParam(2, $loggedInAs, PDO::PARAM_INT);
				$updateHandle->execute();
				header("Location:passwordUpdated.php");
				exit;
			}else{
				echo("You have entered incorrect password");
			}
			
		}catch(\PDOException $ex){
			print($ex->getMessage());
		}
	}
?>
<!DOCTYPE html>
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
	    <!-- Navigation -->
		<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
			<div class="container">
				<a class="navbar-brand" href="index.php">LeagueLights</a>
			</div>
		</nav>
		
		
		<div class="container py-5 my-5">
			<div class="row my-4">
				<form method="post" class="col-lg-12" id="userInfo">
					<div class="row">
						<div class="col-lg-3"></div>
						<h4 class="text-center col-lg-2">Password:</h4>
						<input type="password" class="col-lg-4" name="pswdOld" minlength=6 maxlength=64 required>
						<div class="col-lg-3"></div>
					</div>
					</br>
					<div class="row">
						<div class="col-lg-3"></div>
						<h4 class="text-center col-lg-2">New Password:</h4>
						<input type="password" class="col-lg-4" id="password" name="pswdNew" minlength=6 maxlength=64 required>
						<div class="col-lg-3"></div>
					</div>
					</br>
					<div class="row">
						<div class="col-lg-3"></div>
						<h4 class="text-center col-lg-2">Confirm Password:</h4>
						<input type="password" class="col-lg-4" name="pswdConf" oninput="check(this)" minlength=6 maxlength=64 required>
						<div class="col-lg-3"></div>
					</div>
					<script language='javascript' type='text/javascript'>
                        function check(input) {
                            if (input.value != document.getElementById('password').value) {
                                input.setCustomValidity('Password Must be Matching.');
                            } else {
                                // input is valid -- reset the error message
                                input.setCustomValidity('');
                            }
                        }
                    </script>
					<br>
					<div class="row">
						<div class="col-lg-5"></div>
						<button class="col-lg-2" type="submit" name="submit" value="submit">Update</button>
						<div class="col-lg-5"></div>
					</div>
					<br>
				</form>
			</div>
		</div>
		<!-- Footer -->
		<footer id="footer" class="py-5 bg-dark">
			<div class="container">
			<p class="m-0 text-center text-white">Copyright &copy; LeagueLights 2018</p>
			</div>
			<!-- /.container -->
		</footer>
	</body>
</html>
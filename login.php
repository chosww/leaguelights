<?php
	session_start();
		if(isset($_POST['submit'])){
			$userName = $_POST['ign'];
			$pswd = $_POST['pswd'];
			$server = $_POST['server'];
			$message = "";
			$serverStr = "";
			
			switch($server){
			case "NA":
				$serverStr = "na1";
			break;
			/*
			case "KR":
				$serverStr = "kr";
			break;
			
			case "EUNE":
				$serverStr = "eun1";
			break;
			
			case "EUW":
				$serverStr = "euw1";
			break;	

			case "BR":
				$serverStr = "br1";
			break;			
			
			case "JP":
				$serverStr = "jp1";
			break;
			
			case "LAN":
				$serverStr = "la1";
			break;
			
			case "LAS":
				$serverStr = "la2";
			break;
			
			case "OCE":
				$serverStr = "oc1";
			break;
			
			case "TR":
				$serverStr = "tr1";
			break;
			
			case "RU":
				$serverStr = "ru";
			break;
			*/
			}
			
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
	
			
				$idHandle = $conn->prepare("SELECT SummonerId from user where Name = ?");
				
				$idHandle->bindParam(1, $userName, PDO::PARAM_STR);
				
				$idHandle->execute();
				
				if($idHandle->rowCount()<=0){
					$message = "The user is not registered";
				}else{
				
					$pswdHandle = $conn->prepare("SELECT Password from user where Name = ?");
			
					$pswdHandle->bindParam(1, $userName, PDO::PARAM_STR);
			
					$pswdHandle->execute();
				
					$hashPassword = $pswdHandle->fetchColumn();
					
					if(password_verify($pswd, $hashPassword)){
						$id = $idHandle->fetchColumn();
						$_SESSION["currentUser"] = $userName;
						$_SESSION["currentServer"] = $serverStr;
						$_SESSION["currentId"] = $id;
						header("Location:profile.php");	
					}else{
						$message = "Incorrect password has entered, please try again";
					}
				
				
				}
				
			}catch(\PDOException $ex){
				print($ex->getMessage());
			}
			
			echo $dbConn;
		}
	?>
	<script>
		var message = '<?php echo $message;?>';
		window.onload = function() {
			var msg = document.getElementById("errorMsg");
			msg.innerHTML = message;			
		}
	</script>

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
				<a class="navbar-brand" href="/">LeagueLights</a>
			</div>
		</nav>
		
		
		<div class="container py-5 my-5">
			<div class="row my-4">
				<form method="post" class="col-lg-12" id="userInfo">
					<div class="row">
						<div class="col-lg-3"></div>
						<h4 class="text-center col-lg-2">Username:</h4>
						<input type="text" class="col-lg-4" id="ign" name="ign" required>
						<div class="col-lg-3"></div>
					</div>
					</br>
					<div class="row">
						<div class="col-lg-3"></div>
						<h4 class="text-center col-lg-2">Password:</h4>
						<input type="password" class="col-lg-4" name="pswd" required>
						<div class="col-lg-3"></div>
					</div>
					</br>
					<div class="row">
						<div class="col-lg-3"></div>
						<h4 class="text-center col-lg-2">Server:</h4>
						<select class="col-lg-4" id="region" name="server">
							<option>NA</option>
							<!--<option>KR</option>
							<option>BR</option>
							<option>JP</option>
							<option>EUNE</option>
							<option>EUW</option>
							<option>LAN</option>
							<option>LAS</option>
							<option>OCE</option>
							<option>TR</option>
							<option>RU</option>-->
						</select>
						<div class="col-lg-3"></div>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-5"></div>
						<button class="col-lg-2" type="submit" name="submit" value="submit">Sign in</button>
						<div class="col-lg-5"></div>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-3"></div>
						<div class="col-lg-6">
							<h5 class="col-lg-12 text-center">
								<a class="nav-link" href="resetPswd.php">Reset Password</a>
							</h5>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-3"></div>
						<div class="col-lg-6">
							<h3 class="text-center" Id="errorMsg" style="color:#8b0000"></h3>
						</div>
						<div class="col-lg-3"></div>
					</div>
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
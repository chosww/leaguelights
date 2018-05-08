<?php
	session_start();
		if(isset($_POST['submit'])){
			$userName = $_POST['name'];
			$email = $_POST['email'];
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
				
				$userHandle = $conn->prepare("SELECT * from user Where Name = ? AND Email = ?");
				$userHandle->bindParam(1, $userName, PDO::PARAM_STR);
				$userHandle->bindParam(2, $email, PDO::PARAM_STR);
				$userHandle->execute();
				
				if($userHandle->rowCount()>0){
					$newPswd = md5(rand(0,1000));
					$newPswdHashed = password_hash($newPswd, PASSWORD_DEFAULT);
					$updateHandle = $conn->prepare("Update user SET Password = ? Where Name = ? AND Email = ?");
					$updateHandle->bindParam(1, $newPswdHashed, PDO::PARAM_STR);
					$updateHandle->bindParam(2, $userName, PDO::PARAM_STR);
					$updateHandle->bindParam(3, $email, PDO::PARAM_STR);
					$updateHandle->execute();
				
					echo $newPswd;
				}else{
					$message = "The username is not binded with the email";
				}
			}catch(\PDOException $ex){
				print($ex->getMessage());
			}
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
						<input type="text" class="col-lg-4" id="name" name="name" required>
						<div class="col-lg-3"></div>
					</div>
					</br>
					<div class="row">
						<div class="col-lg-3"></div>
						<h4 class="text-center col-lg-2">Email:</h4>
						<input type="email" class="col-lg-4" name="email" placeholder="Enter the email you registered with" required>
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
						<button class="col-lg-2" type="submit" name="submit" value="submit">Reset</button>
						<div class="col-lg-5"></div>
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
			<p class="m-0 text-center text-white">Copyright &copy; LeagueLights 2017</p>
			</div>
			<!-- /.container -->
		</footer>
	</body>
</html>
<?php
	require 'vendor/autoload.php';
	use Aws\S3\S3Clinet;
	use Aws\Exception\AwsException;
		
	$message = "";
	$sumName = "";
	$userName = "";
	if(isset($_POST['submit'])){
		$userName = $_POST['ign'];
		$pswd = $_POST['pswd'];
		$email = $_POST['email'];
		$server = $_POST['server'];
		$sumName = str_replace(' ', '', $_POST['sumName']);
		$apiKey = getenv('RIOT_API');
	
		$summonerInfo = "https://";
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
			
		//curl request url 
		$summonerInfo .= $serverStr;
		$summonerInfo .= ".api.riotgames.com/lol/summoner/v3/summoners/by-name/".$sumName."?api_key=".$apiKey;
			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $summonerInfo);
		$result = curl_exec($ch);
		curl_close($ch);
		$obj = json_decode($result);
			
		$pswd = password_hash($pswd, PASSWORD_DEFAULT);
			
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
			//valid only if entered summoner name exist in League of Legends
			if(property_exists($obj, 'id')){
				$sumId = $obj->{'id'};
				//query to check if user with the summoner id is already registered in LeagueLights
				$idHandle = $conn->prepare("SELECT SummonerId from user where SummonerId = ?");
				$idHandle->bindParam(1, $sumId, PDO::PARAM_INT);
				$idHandle->execute();
				
				//query to check if user with the name is already registered in LeagueLights
				$nameHandle = $conn->prepare("SELECT Name from user where Name = ?");
				$nameHandle->bindParam(1, $userName, PDO::PARAM_STR);
				$nameHandle->execute();
				
				if($idHandle->rowCount()>0){
					$message = "The summoner name is already registered in LeagueLights";
				}else if($idHandle->rowCount()<=0 && $nameHandle->rowCount()>0){
					$message = "The user name is already used"; 
				}else if($idHandle->rowCount()<=0 && $nameHandle->rowCount()<=0){
					//if the user information is not registered and valid, register user with provided inputs
					$registerHandle = $conn->prepare("INSERT INTO user (SummonerId, Name, Password, Email) VALUES (?, ?, ?, ?)");
					$registerHandle->bindParam(1, $sumId, PDO::PARAM_INT);
					$registerHandle->bindParam(2, $userName, PDO::PARAM_STR);
					$registerHandle->bindParam(3, $pswd, PDO::PARAM_STR);
					$registerHandle->bindParam(4, $email, PDO::PARAM_STR);
					$registerHandle->execute();
						
					$bucketName = $serverStr.'-vid';
					$folderName = $sumId.'/';
					
					//s3 bucket access with AWS S3Client 
					$s3Client = new Aws\S3\S3Client([
						'version' => 'latest',
						'region' => 'ca-central-1'
					]);
					
					try{
						//create directory for the user 
						$s3Client->putObject([
							'Bucket' => $bucketName,
							'Key' => $folderName,
							'Body' => '',
							'ACL' => 'public-read-write',
						]);
					}catch(Aws\Exception\S3Exception $e){
						echo "There was an error creating user directory.\n";
					}
					
					//once completion of the sign up, user will be redirect to main page of LeagueLights
					header("Location:index.php");
					exit;
				}
					
			}else{
				$message = "Seems like Summoner name is not registered, please check it again";
			}
			
			
		}catch(\PDOException $ex){
			print($ex->getMessage());
		}
	}
?>
	<script language='javascript' type='text/javascript'>
		var message = '<?php echo $message;?>';
		window.onload = function() {
			//display error message 
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
		
		<div class="container py-5">
			<div class="row my-4">
				<form method="post" id="userInfo" class="col-lg-12">
					<div class="row">
						<div class="col-lg-3"></div>
						<h4 class="col-lg-2 text-center">Username:</h4>
						<!--user name must be at least 6 characters-->
						<input class="col-lg-4" type="text" name="ign" minlength=6 maxlength=16 required>
						<div class="col-lg-3"></div>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-3"></div>
						<!--password must be at least 5 characters--> 
						<h4 class="col-lg-2 text-center">Password:</h4>
						<input class="col-lg-4" type="password" id="password" name="pswd" minlength=6 maxlength=64 required>
						<div class="col-lg-3"></div>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-3"></div>
						<h4 class="col-lg-2 text-center">Confirm Password:</h4>
						<input class="col-lg-4" type="password" name="pswdConf" oninput="check(this)" required>
						<div class="col-lg-3"></div>
					</div>
					<script language='javascript' type='text/javascript'>
						// function to check if password and confirmed password are equal
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
					<div class ="row">
						<div class="col-lg-3"></div>
						<h4 class="col-lg-2 text-center">Summoner Name:</h4>
						<!--user must register to LeagueLights with vaild summoner name-->
						<input class="col-lg-4" type="text" name="sumName" placeholder="Your League of Legends' name" required>
						<div class="col-lg-3"></div>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-3"></div>
						<!-- sns will be implemented soon to confirm user-->
						<h4 class="col-lg-2 text-center">Email:</h4>
						<input class="col-lg-4" type="email" name="email" required>
						<div class="col-lg-3"></div>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-5"></div>
						<select class="col-lg-2" name="server">
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
						</select><br />
						<div class="col-lg-5"></div>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-5"></div>
						<button class="col-lg-2" type="submit" name="submit" value="submit">Register</button>
						<div class="col-lg-5"></div>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-3"></div>
						<div class="col-lg-6">
							<h3 class="text-center" id="errorMsg" style="color:#8b0000"></h3>
						</div>
						<div class="col-lg-3"></div>
					</div>
				</form>
			</div>
		</div>
		
		<!-- Footer -->
		<footer class="py-5 bg-dark">
			<div class="container">
			<p class="m-0 text-center text-white">Copyright &copy; LeagueLights 2018</p>
			</div>
			<!-- /.container -->
		</footer>		

	</body>
</html>

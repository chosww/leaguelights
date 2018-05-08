<?php
	session_start();
	require 'vendor/autoload.php';
	use Aws\S3\S3Client;
	use Aws\Exception\AwsException;
	
	$videoId = $_GET['videoId'];
	$serverStr = $_SESSION['currentServer'];
	$folderId = $_SESSION['currentId'];
	$bucket = $serverStr.'-vid';
	$userFolder = $bucket.'/'.$folderId;
	
	if (isset($_POST['yes'])) {
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
			
			$dbHandle = $conn->prepare("DELETE from video where Id = ?");
			$dbHandle->bindParam(1, $videoId, PDO::PARAM_STR);
			$dbHandle->execute();
			
			$likeHandle = $conn->prepare("DELETE from likes where videoId = ?");
			$likeHandle->bindParam(1, $videoId, PDO::PARAM_STR);
			$likeHandle->execute();
			
			$s3Client = new \Aws\S3\S3Client([
						'version' => 'latest',
						'region' => 'ca-central-1'
			]);
			$s3Client->registerStreamWrapper();
			$s3Protocol = 's3://'.$userFolder.'/'.$videoId;
			unlink($s3Protocol);
			//need to find way to delete an object 
			
		} catch(\PDOException $ex){
			print($ex->getMessage());
		}
		echo "<script>window.close();</script>";
	} else if(isset($_POST['no'])){
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

		<title>LeagueLights Delete Video</title>

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
				<form method="post" class="col-lg-6">
					<div class="row">
						<div class="col-lg-3"></div>
						<h4 class="text-center col-lg-6">Do you want to delete the video? All likes on this video will be deleted as well.</p>
						<div class="col-lg-3"></div>
					</div>
					<div class="row">
						<div class="col-lg-2"></div>
						<button class="col-lg-3" type="submit" name="yes" value="yes">Yes</button>
						<div class="col-lg-2"></div>
						<button class="col-lg-3" type="submit" name="no" value="no">No</button>
						<div class="col-lg-2"></div>
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
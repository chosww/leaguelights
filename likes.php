<?php
	session_start();
	$serverStr = $_SESSION['currentServer'];
	$loggedInAs = $_SESSION['currentId'];
	$videoId = $_POST['videoId'];
	
	if(empty($loggedInAs)){
		header("Location:login.php");
		exit;
	}
	
	$dbHost = 'leaguelights.c1armosbaqhq.ca-central-1.rds.amazonaws.com';
	$dbUser = 'daniel';
	$dbPass = 'dksk18rotorl';
	$dbConn = 'mysql:host='.$dbHost.';dbname='.$serverStr.';charset=utf8mb4';
	
	if(isset($_POST['submit'])){
		try{
			$conn = new \PDO( $dbConn, 
							$dbUser, 
							$dbPass, 
							array(
								\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
								\PDO::ATTR_PERSISTENT => false
								)
							);
					
			
			$handle = $conn->prepare("Select * from likes where videoId = ? and likedBy = ?");
			$handle->bindParam(1, $videoId);
			$handle->bindParam(2, $loggedInAs);
			$handle->execute();
			
			if($handle->rowCount()<1){
				$like=$conn->prepare("INSERT INTO likes (likedBy, videoId) VALUES (?,?)");
				$like->bindParam(1, $loggedInAs);
				$like->bindParam(2, $videoId);
				$like->execute();
			}else{
				$like=$conn->prepare("DELETE FROM likes where videoId = ? and likedBy = ?");
				$like->bindParam(1, $videoId);
				$like->bindParam(2, $loggedInAs);
				$like->execute();
			}
				
		}catch(\PDOException $ex){
			print($ex->getMessage());
		}
		
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	}
?>
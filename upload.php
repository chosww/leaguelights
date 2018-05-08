<?php
	session_start();
	require 'vendor/autoload.php';
	use Aws\S3\S3Client;
	use Aws\Exception\AwsException;
	
	$serverFolder = $_SESSION['currentServer'];
	$folderId = $_SESSION['currentId'];
	$bucket = $serverFolder.'-vid';
	$userFolder = $bucket.'/'.$folderId;
	
	
	if(isset($_POST['submit'])){
		$fileName = $_POST['fileName'];
		$file = $_FILES['file'];
		$file_name = $file['name'];
		$file_tmp = $file['tmp_name'];
		$file_size = $file['size'];
		$file_error = $file['error'];
		
		//file extension
		$file_ext = explode('.', $file_name);
		$file_ext = strtolower(end($file_ext));
		
		$allowed = 'webm';
		$fileCount = 0;
		
		if($file_ext == $allowed){
			if($file_error === 0){
				if($file_size < 20971520){
					$file_name_new = $folderId . '_' . uniqid() . '.' . $allowed;
					$newFileName = $folderId . '/' . $file_name_new;
					
					
					$s3Client = new \Aws\S3\S3Client([
						'version' => 'latest',
						'region' => 'ca-central-1'
					]);
					
					//$localFilePath = fopen($_FILES["file"]["tmp_name"], 'r');
					
					try{
						$s3Client->putObject([
							'ACL' => 'public-read',
							'Body' => file_get_contents($_FILES["file"]["tmp_name"]),
							'Bucket' => $bucket,
							'ContentType' => 'video/webm',
							'Key' => $newFileName,
						]);
					} catch (Aws\Exception\S3Exception $e) {
						echo "There was an error uploading the file.\n";
					}
					
					$dbHost = getenv('RDS_HOSTNAME');
					$dbUser = getenv('RDS_USERNAME');
					$dbPass = getenv('RDS_PASSWORD');
					$dbConn = 'mysql:host='.$dbHost.';dbname='.$serverFolder.';charset=utf8mb4';

					try{
						$conn = new \PDO( $dbConn, 
										$dbUser, 
										$dbPass, 
										array(
										\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
										\PDO::ATTR_PERSISTENT => false
										)
						);
			
						$handle = $conn->prepare("INSERT INTO video (Id, Uploader, Title) VALUES (?,?,?)");
			
						$handle->bindParam(1, $file_name_new);
						$handle->bindParam(2, $folderId);
						$handle->bindParam(3, $fileName);
		
						$handle->execute();
			
				
					}catch(\PDOException $ex){
						print($ex->getMessage());
					}
					
					header("Location:uploadSuccess.php");
					exit;
				}else{
					$_SESSION['uploadError'] = "The video is over 20MB";
                    header("Location:badUpload.php");
				}
			}
		}else{
			$_SESSION['uploadError'] = "Please check if video is WebM type";
            header("Location:badUpload.php");
		}
	}
?>
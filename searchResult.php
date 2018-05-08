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

		<title>LeagueLights</title>

		<!-- Bootstrap core CSS -->
		<link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

		<!-- Custom styles for this template -->
		<link href="css/shop-homepage.css" rel="stylesheet">
		<link href="css/leaguelights.css" rel="stylesheet">
		
		<!-- videojs style sheet -->
		<link href="http://vjs.zencdn.net/6.2.8/video-js.css" rel="stylesheet">
		
		<!-- videojs js file -->
		<script src="http://vjs.zencdn.net/6.2.8/video.js"></script>
		
		<style>
			img {
				max-width: 200px;
				max-height: 200px;
			}
			video {
				max-width: 200px;
				max-height: 160px;
			}
		</style>	
	</head>
	<body>
	
	
	<?php
		$profileDisplay = false;
	
		if(isset($_SESSION['currentId'])){
			$loggedInAs = $_SESSION['currentId'];
			$signStatus = "Sign out";
			$href = "javascript:window.open('signOut.php','mywindowtitle','width=1000,height=400')";
			$profileDisplay = true;
		}else{
			$signStatus = "Sign in";
			$href = "login.php";
		}
		$serverStr = $_GET['server'];
		$searchKey = $_GET['keyword'];
		$userKey = str_replace(' ', '', $searchKey);
		$userExist = false;
		$searchKey = "%".$searchKey."%";
		$videoSrc = array();
		$video = array();
		$title = array();
		$uploader = array();
		$uploaderName = array();
		$likes = array();
		$liked = array();
		$numVideos = "";
		$summonerLevel = "";
		$summonerId = "";
		$summonerIcon = "";
		$summonerName = "";
		$apiKey = getenv('RIOT_API');
		$version = getenv('CLIENT_VERSION');
		
		$serverInfo = "https://" . $serverStr;
		$userInfo = $serverInfo . ".api.riotgames.com/lol/summoner/v3/summoners/by-name/" .$userKey. "?api_key=" .$apiKey;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $userInfo);
		$userResult = curl_exec($ch);
		curl_close($ch);		
		
		$obj = json_decode($userResult);
		
		
		
		if (property_exists($obj, 'id')){
			$summonerId = $obj->{'id'};
			$summonerName = $obj->{'name'};
		}
		
		$dbHost = getenv('RDS_HOSTNAME');
		$dbUser = getenv('RDS_USERNAME');
		$dbPass = getenv('RDS_PASSWORD');
		$dbConn = 'mysql:host='.$dbHost.';dbname='.$serverStr.';charset=utf8mb4';

		$conn = new \PDO( $dbConn,
						$dbUser,
						$dbPass,
						array(
							\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
							\PDO::ATTR_PERSISTENT => false
							)
						
						);
		
		if (!empty($summonerId)){
			$idHandle = $conn->prepare("SELECT SummonerId from user where SummonerId=?");
			$idHandle->bindParam(1, $summonerId, PDO::PARAM_INT);
			$idHandle->execute();
			if($idHandle->rowCount()>0){
				$userExist = true;
				$summonerIcon = $obj->{'profileIconId'};
				$summonerLevel = $obj->{'summonerLevel'};
				$numVideos = $conn->prepare("select Id from video where Uploader=?");
				$numVideos->bindParam(1, $summonerId, PDO::PARAM_INT);
				$numVideos->execute();
				$numVideos = $numVideos->rowCount();
			}
		}			
						
		$srcHandle = $conn->prepare("SELECT Id, Uploader from video where Title LIKE ?");
		$srcHandle->bindParam(1, $searchKey, PDO::PARAM_STR);
		$srcHandle->execute();
		$likedHandle = $conn->prepare("SELECT * from likes where likedBy = ? AND videoId = ?");
		foreach ($srcHandle as $value){
			$src = "https://s3-ca-central-1.amazonaws.com/".$serverStr."-vid/".$value['Uploader']."/".$value['Id'];
			array_push($videoSrc, $src);
			array_push($uploader, $value['Uploader']);
			array_push($video, $value['Id']);
			$likedHandle->bindParam(1, $loggedInAs, PDO::PARAM_INT);
			$likedHandle->bindParam(2, $value['Id'], PDO::PARAM_STR);
			$likedHandle->execute();
			if ($likedHandle->rowCount() < 1){
				array_push($liked, false);
			} else {
				array_push($liked, true);
			}
		}		
		
		if (count($uploader)>0){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			foreach ($uploader as $value){
				$idToNameRequest = $serverInfo . ".api.riotgames.com/lol/summoner/v3/summoners/".$value."?api_key=".$apiKey;
				curl_setopt($ch, CURLOPT_URL, $idToNameRequest);
				$nameResult = curl_exec($ch);		
				$name = json_decode($nameResult);
				array_push($uploaderName, $name->{'name'});
			}
			curl_close($ch);
		}
		$srcJson = json_encode($videoSrc);
		$likedJson = json_encode($liked);
		$videoJson = json_encode($video);
		$uploaderJson = json_encode($uploaderName);
		
		$titleHandle = $conn->prepare("SELECT Id,Title from video where Title Like ?");
		$titleHandle->bindParam(1, $searchKey, PDO::PARAM_STR);
		$titleHandle->execute();
		
		$likeHandle = $conn->prepare("SELECT * from likes where videoId = ?");
		foreach ($titleHandle as $value){
			array_push($title, $value['Title']);
			$likeHandle->bindParam(1, $value['Id'], PDO::PARAM_STR);
			$likeHandle->execute();
			array_push($likes, $likeHandle->rowCount());
		}
		$titleJson = json_encode($title);
		$likesJson = json_encode($likes);
	?>
	
	<script>
	
		var videos = JSON.parse('<?php echo $srcJson; ?>');
		var titles = JSON.parse('<?php echo $titleJson; ?>');
		var uploaderName = JSON.parse('<?php echo $uploaderJson; ?>');
		var likes = JSON.parse('<?php echo $likesJson; ?>');
		var videoId = JSON.parse('<?php echo $videoJson; ?>');
		var likedBy = JSON.parse('<?php echo $likedJson; ?>');
		var counter = videos.length;
		
		function addUserElement(){
			var userName = "<?php echo $summonerName; ?>";
			var numVideos = "<?php echo $numVideos; ?>";
			var userLevel = "<?php echo $summonerLevel; ?>";
			var userId = "<?php echo $summonerId; ?>";
			var profileIconUrl = "http://ddragon.leagueoflegends.com/cdn/" + "<?php echo $version; ?>" + "/img/profileicon/" + "<?php echo $summonerIcon; ?>" + ".png"; 
			var profileUrl = "publicProfile.php?userId=";
			profileUrl += userId;
			
			var doubleSpacer = document.createElement("div");
			doubleSpacer.setAttribute("class", "col-lg-2");
			
			var singleSpacer = document.createElement("div");
			singleSpacer.setAttribute("class", "col-lg-1");
			
			var summonerIcon = document.createElement("img");
			summonerIcon.setAttribute("src", profileIconUrl);
			
			var summonerInfo = document.createElement("div");
			summonerInfo.setAttribute("class", "col-lg-4");
			
			
			//Summoner info section begin 
			var summonerName = document.createElement("h5");
			summonerName.setAttribute("class", "row text-center");
			summonerName.innerHTML = userName;
			
			var summonerLevel = document.createElement("h5");
			summonerLevel.setAttribute("class", "row text-center");
			summonerLevel.innerHTML = "Level " + userLevel;
			
			var videoInfo = document.createElement("h5");
			videoInfo.setAttribute("class", "row text-center");
			videoInfo.innerHTML = numVideos + " uploads";
			
			summonerInfo.appendChild(summonerName);
			summonerInfo.appendChild(summonerLevel);
			summonerInfo.appendChild(videoInfo);
			//Summoner info section end 
			
			var aTag = document.createElement("a");
			aTag.setAttribute("href", profileUrl); 
			aTag.setAttribute("class", "row");

			aTag.appendChild(summonerIcon);
			aTag.appendChild(singleSpacer);
			aTag.appendChild(summonerInfo);
			
			var user = document.getElementById("userProfile");
			user.appendChild(aTag);
		}
	
		function addVidElement(vid, vidTitle, name, like, videoId, liked){
			var divider = document.createElement("form");
			divider.setAttribute("class", "row mb-4");
			divider.setAttribute("action", "likes.php");
			divider.setAttribute("method", "post");
			
			var singleSpacer = document.createElement("div");
			singleSpacer.setAttribute("class", "col-lg-1");
		
			var title = document.createElement("h2");
			title.setAttribute("class", "row text-center");
			title.setAttribute("style", "white-space: nowrap; text-overflow: ellipsis");
			title.innerHTML = vidTitle;
			
			var uploader = document.createElement("h4");
			uploader.setAttribute("class", "row text-center");
			uploader.setAttribute("style", "white-space: nowrap; text-overflow: ellipsis");
			uploader.innerHTML = name;
			
			var likes = document.createElement("h4");
			likes.setAttribute("class", "row text-center");
			likes.innerHTML = like + " likes";
			
			var videoInfo = document.createElement("div");
			videoInfo.setAttribute("class", "col-lg-4");
			
			var likeBtn = document.createElement("button");
			likeBtn.setAttribute("type", "submit");
			likeBtn.setAttribute("name", "submit");
			likeBtn.setAttribute("value", "submit");
			likeBtn.setAttribute("class", "row text-center");
			if(liked){
				likeBtn.setAttribute("style", "background-color:#ADD8E6");
				likeBtn.innerHTML = "liked";
			}else{
				likeBtn.innerHTML = "like";
			}
			videoInfo.appendChild(title);
			videoInfo.appendChild(uploader);
			videoInfo.appendChild(likes);
			videoInfo.appendChild(likeBtn);
			
			var video = document.createElement("video");
			video.setAttribute("class", "col-lg-6 video-js vjs-icon-play-circle");
			video.setAttribute("preload", "auto");
			video.setAttribute("controls", true);
			video.src = vid;
		
			var vidId = document.createElement("input");
			vidId.setAttribute("type", "hidden");
			vidId.setAttribute("name", "videoId");
			vidId.setAttribute("value", videoId);
			vidId.setAttribute("style", "display:none");
			
			divider.appendChild(vidId);
			divider.appendChild(video);
			divider.appendChild(singleSpacer);
			divider.appendChild(videoInfo);
			
			
		
			var container = document.getElementById("videoContainer");
			container.appendChild(divider);
		}
	
		window.onload = function() {
			
			var sign = "<?php echo $signStatus; ?>";
			var signInfo = document.getElementById("sign");
			var link = document.createElement("a");
			link.setAttribute("href", "<?php echo $href; ?>");
			link.setAttribute("class", "nav-link");
			link.innerHTML = sign;
			signInfo.appendChild(link);
			
			if ("<?php echo $profileDisplay; ?>"){
				var showProfile = document.getElementById("profile");
				var profileAnchor = document.createElement("a");
				profileAnchor.setAttribute("href", "profile.php");
				profileAnchor.setAttribute("class", "nav-link");
				profileAnchor.innerHTML = "My Lights";
				showProfile.appendChild(profileAnchor);
			}

			if ("<?php echo $userExist; ?>"){
				addUserElement();
			}

			
			if	(counter>0){
				for(var i=0;i<counter;i++){
					addVidElement(videos[i], titles[i], uploaderName[i], likes[i], videoId[i], likedBy[i]);
				}
			}
			
			if (counter == 0 && !("<?php echo $userExist; ?>")){
				var text = document.createElement('h1');
				text.setAttribute("class", "text-center");
				text.innerHTML = "The summoner you are looking for is not registered on LeagueLights, or the video with the title does not exist";
				var result = document.getElementById("userProfile");
				result.appendChild(text);
			}
		}
	
	</script>
	

	<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
      <div class="container">
        <a class="navbar-brand" href="/">LeagueLights</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
		  <div class="navbar-nav ml-auto">
			<li id="profile" class="nav-item ml-auto">
			</li>
			<li id="sign" class="navbar-nav ml-auto">
			</li>
		  </div>		  
        </div>
      </div>
    </nav>

	
	
	<div class="container">
		<div class="row">
			<div class="col-lg-3"></div>
			<div class="col-lg-6 my-2 card-body" id="userProfile"></div>
			<div class="col-lg-3"></div>
		</div>

		<div class="row">
			<div class="col-lg-3"></div>
			<div class="col-lg-6 my-2 card-body" id="videoContainer"></div>
			<div class="col-lg-3"></div>
		</div>
	</div>
	
	<!-- Footer -->
    <footer class="py-5 bg-dark">
      <div class="container">
        <p class="m-0 text-center text-white">Copyright &copy; LeagueLights 2018</p>
      </div>
      <!-- /.container -->
    </footer>

    <!-- Bootstrap core JavaScript -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/popper/popper.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>


	</body>
</html>
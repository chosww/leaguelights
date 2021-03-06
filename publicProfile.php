<?php
	session_start();
?>

<!DOCTYPE html>
<html lang="en">

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
	<style>
		.card-title{
			color: white;
			font-family:cambria;
		}
	</style>	
  </head>

  <body>
	<?php
		if(isset($_SESSION['currentId'])){
			$loggedInAs = $_SESSION['currentId'];
			$signStatus = "Sign out";
			$href = "javascript:window.open('signOut.php','mywindowtitle','width=1000,height=400')";
			$profileDisplay = true;
		}else{
			$signStatus = "Sign in";
			$href = "login.php";
		}
		$serverStr = (isset($_SESSION['currentServer'])) ? $_SESSION['currentServer'] : "na1";
		$userId = $_GET['userId'];
		$videoSrc = array();
		$title = array();
		$videoStr = $userId . "_%";
		$numVid = 0;
		$numLike = 0;
		$apiKey = getenv('RIOT_API');
		$version = getenv('CLIENT_VERSION');
		
		$summonerInfo = "https://" . $serverStr;
		$summonerRank = $summonerInfo . ".api.riotgames.com/lol/league/v3/positions/by-summoner/".$userId."?api_key=".$apiKey;
		$summonerInfo .= ".api.riotgames.com/lol/summoner/v3/summoners/".$userId."?api_key=".$apiKey;
		
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $summonerInfo);
		$infoResult = curl_exec($ch);
		curl_setopt($ch, CURLOPT_URL, $summonerRank);
		$rankResult = curl_exec($ch);

		curl_close($ch);
		
		$summoner = json_decode($infoResult);
		$rank = json_decode($rankResult);
		
		if(!empty($rank)){
			$rank = $rank[0]->{'tier'}." ". $rank[0]->{'rank'}."<br>Level ".$summoner->{'summonerLevel'};
		}else{
			$rank = "Level ".$summoner->{'summonerLevel'};
		}

		
		$profileIcon = $summoner->{'profileIconId'};
		$iconLink = "http://ddragon.leagueoflegends.com/cdn/".$version."/img/profileicon/".$profileIcon.".png";
		
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
						
			$srcHandle = $conn->prepare("SELECT Id, Uploader from video where Uploader LIKE ?");
			$srcHandle->bindParam(1, $userId, PDO::PARAM_INT);
			$srcHandle->execute();
			
			$likeHandle = $conn->prepare("SELECT * from likes where videoId LIKE ?");
			$likeHandle->bindParam(1, $videoStr, PDO::PARAM_STR);
			$likeHandle->execute();
			$numLike = $likeHandle->rowCount();
			
			foreach ($srcHandle as $value){
				$src = "https://s3-ca-central-1.amazonaws.com/".$serverStr."-vid/".$value['Uploader']."/".$value['Id'];
				array_push($videoSrc, $src);
				$numVid++;
			}		
			$srcJson = json_encode($videoSrc);
		
		
			$titleHandle = $conn->prepare("SELECT Title from video where Uploader Like ?");
			$titleHandle->bindParam(1, $userId, PDO::PARAM_INT);
			$titleHandle->execute();
			foreach ($titleHandle as $value){
				array_push($title, $value['Title']);
			}
			$titleJson = json_encode($title);
		} catch(\PDOException $ex){
			print($ex->getMessage());
		}
		
		$userElo = floor(($numVid*5 + ($numLike-$numVid))/70);
		$threshold = 3;
		
		if($userElo < $threshold){
			$userRank = "Novice";
		}else if($userElo >= $threshold && $userElo < ($threshold*2)){
			$userRank = "Advanced Beginner";
		}else if($userElo >= $threshold*2 && $userElo < ($threshold*4)){
			$userRank = "Competent";
		}else if($userElo >= $threshold*4 && $userElo < ($threshold*8)){
			$userRank = "Proficient";
		}else if($userElo >= $threshold*8){
			$userRank = "Expert";
		}
		
	?>
	<script>
	
		var videos = JSON.parse('<?php echo $srcJson; ?>');
		var titles = JSON.parse('<?php echo $titleJson; ?>');
		var userRank = '<?php echo $userRank;?>';
		var counter = videos.length;
		var pageTitle = '<?php echo $summoner->{'name'}?>';
	
		function addVidElement(vid, vidTitle){
			var divider = document.createElement("div");
			divider.setAttribute("class", "col-lg-4 col-md-6 mb-4");
		
			var cardDiv = document.createElement("div");
			cardDiv.setAttribute("class", "card h-100 bg-dark");
			
			var cardBody = document.createElement("div");
			cardBody.setAttribute("class", "card-body");
			
			
			var title = document.createElement("h4");
			title.setAttribute("class", "card-title text-center");
			title.innerHTML = vidTitle;
			//title.onclick = handleVideoSelection;
		
			var video = document.createElement("video");
			video.setAttribute("class", "card-img-top video-js vjs-icon-play-circle col-xs-12");
			video.setAttribute("preload", "auto");
			video.setAttribute("controls", true);
			video.src = vid;
		
			cardDiv.appendChild(video);
			cardDiv.appendChild(title);
			divider.appendChild(cardDiv);
		
			var container = document.getElementById("videoList");
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
			
			if(document.title != pageTitle){
				document.title = pageTitle + ' | ' + document.title;
			}
			
			for(var i=0;i<counter;i++){
				addVidElement(videos[i], titles[i]);
			}
				
			var totalVid = document.getElementById("numVid");
			totalVid.innerHTML = counter;
			
			var rank = document.getElementById("leaguelightsRank");
			rank.innerHTML = userRank;
		}
	
	</script>	

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
      <div class="container">
        <a class="navbar-brand" href="/">LeagueLights</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
		  <div class="navbar-nav ml-auto">
			<li id="profile" class="navbar-nav ml-auto">
			</li>
			<li id="sign" class="navbar-nav ml-auto">
            </li>
		  </div>
          <!--
		  <ul class="navbar-nav ml-auto">
            <li class="nav-item active">
              <a class="nav-link" href="#">Home
                <span class="sr-only">(current)</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">About</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Services</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Contact</a>
            </li>
          </ul>
		  -->
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <div class="container">

      <div class="row">

        <div class="col-lg-3">

          <h1 class="my-2"><?php echo $summoner->{'name'};?></h1>
		  <h4><?php echo $rank;?></h4>
		  <img class="card-img-top" src='<?php echo $iconLink;?>'>

        </div>
        <!-- /.col-lg-3 -->

        <div class="col-lg-9">
		
            <div class="row my-4 py-5">

				<div class="col-lg-4 col-md-6 mb-4">
				<div class="card h-100 bg-dark">
					<img class="card-img-top" src="img/like.png">
					<div class="card-body">
					<h4 class="card-title text-center">
						Total Likes
					</h4>
					<h4 class="card-title text-center"><?php echo $numLike;?></h4>
					</div>
				</div>
				</div>

				<div class="col-lg-4 col-md-6 mb-4">
				<div class="card h-100 bg-dark">
					<img class="card-img-top" src="img/video.png">
					<div class="card-body">
					<h4 class="card-title text-center">
						Total Videos
					</h4>
					<h4 class="card-title text-center" id="numVid"></h4>
					</div>
				</div>
				</div>

				<div class="col-lg-4 col-md-6 mb-4">
				<div class="card h-100 bg-dark">
					<img class="card-img-top" src="img/rank.png">
					<div class="card-body">
					<h4 class="card-title text-center">
						LeagueLights Rank
					</h4>
					<h4 class="card-title text-center" id="leaguelightsRank"></h4>
					</div>
				</div>
				</div>
			</div>
		

          <div class="row py-2" id="videoList">

          </div>
          <!-- /.row -->

        </div>
        <!-- /.col-lg-9 -->

      </div>
      <!-- /.row -->

    </div>
    <!-- /.container -->

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

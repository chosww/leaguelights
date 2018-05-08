<?php
	session_start();
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="keywords" content="League of Legends, Riot Games, LOL, MOBA">
    <meta name="description" content="Share your League of Legends highlight videos or just watch other players' highlight videos!">

    <title>Show off your League of Legends highlight videos! | LeagueLights</title>

    <!-- Bootstrap core CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/shop-homepage.css" rel="stylesheet">
	<link href="css/leaguelights.css" rel="stylesheet">
</head>
<body>
	<script type="application/ld+json">
	{
		"@context": "http://schema.org",
		"@type": "WebApplication",
		"name" : "LeagueLights",
		"url" : "http://leaguelights.com",
		"applicationCategory" : "multimedia",
		"applicationSubCategory" : ["video streaming", "streaming media"],
		"countriesSupported" : "North America",
		"about" : "League of Legends",
		"creator" : "https://www.linkedin.com/in/danielswcho/",
		"isBasedOn" : "https://na.leagueoflegends.com/en/",
        "operatingSystem" : ["Windows", "macOS", "Android"]
	}
	</script>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
      <div class="container">
        <a class="navbar-brand" href="#">LeagueLights</a>
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
	<div class="container py-5">
		<div class="row">
			<div class="col d-none d-lg-block">
				<img class="d-block mx-auto" src="img/leaguelights1024.png">
			</div>
			<div class="col d-none d-md-block d-lg-none">
				<img class="d-block mx-auto" src="img/leaguelights512.png">
			</div>
			<div class="col d-md-none">
				<img class="d-block mx-auto" src="img/leaguelights256.png">
			</div>
		</div>
		<div class="row">
			<div class="col-lg-3"></div>
			<div class="col-lg-6">
				<form>
					<div class="row">
						<input class="col-lg-7" type="text" name="searchKey" required>
						<select class="col-lg-2" id="region" name="server">
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
						<button class="col-lg-3" type="submit" name="submit" value="submit">Search</button>
					</div>
					<br>
				</form>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-3"></div>
			<div class="col-lg-6">
				<h5 class="col-lg-12 text-center">
					New? <a class="nav-link" href="signUp.php">Register</a> with your summoner name!
				</h5>
			</div>
		</div>
	</div>


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
	$url = "Location:searchResult.php?server=";
	
	if (isset($_GET['submit'])) {
		$searchKey = $_GET['searchKey'];
		$server = $_GET['server'];
		
		switch($server){
			case "NA":
				$url = $url."na1";
			break;
			/*
			case "KR":
				$url = $url."kr";
			break;
			
			case "EUNE":
				$url = $url."eun1";
			break;
			
			case "EUW":
				$url = $url."euw1";
			break;	

			case "BR":
				$url = $url."br1";
			break;			
			
			case "JP":
				$url = $url."jp1";
			break;
			
			case "LAN":
				$url = $url."la1";
			break;
			
			case "LAS":
				$url = $url."la2";
			break;
			
			case "OCE":
				$url = $url."oc1";
			break;
			
			case "TR":
				$url = $url."tr1";
			break;
			
			case "RU":
				$url = $url."ru";
			break;
			*/
		}
		
		
		$url = $url."&keyword=".$searchKey;
		header($url);
		exit;
	}
	
?>
	<script>
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
		}
	</script>

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
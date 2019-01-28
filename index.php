<!doctype html5>
<html>

<?php
	//Csatlakozunk a postgresql adatbazishoz
	$db = pg_connect('dbname=katalogus user=postgres');



	if (!$db) {
	  echo "Nem sikerult az adatbazishoz csatlakozni.\n";
	  exit;
	}

	//Tartalmaz-e rendezest az URL
	if(isset($_GET["rend"])){
		$rendezes = $_GET["rend"];
	}
	else{
		$rendezes=null;
	}

	//Ha nincs rendezes akkor az alapertelmezett a nev szerint novekvo
	if(!$rendezes){
		$rendezes = "nevnov";
	}
	//Rendezes atalakitasa SQL kodda
	if($rendezes =="nevcsokk" ){
		$order = "nev desc, ar";
	}
	else if($rendezes =="arnov" ){
		$order = "ar, nev";
	}
	else if($rendezes =="arcsokk" ){
		$order = "ar desc, nev ";
	}
	else{
		$order = "nev, ar";
	}

	//Nev kivetele az URL-bol
	if(isset($_GET["termeknev"]) and $_GET["termeknev"]){
		$termeknev = $_GET["termeknev"];
	}
	else{
		$termeknev = "";
	}
	//Mettol meddig kivetele az URL-bol
	if(isset($_GET["mettol"]) and is_numeric($_GET["mettol"])){
		$mettol = $_GET["mettol"];
	}
	else{
		$mettol = 0;
	}
	if(isset($_GET["meddig"])  and is_numeric($_GET["meddig"])){
		$meddig = $_GET["meddig"];
	}
	else{
		$meddig = null;
	}

	//raktaron levok kivetele az URL-bol
	if(isset($_GET["raktaron"])){
		if($_GET["raktaron"]=="on"){
			$raktaron = "AND raktaron = 't'";
		}
	} 
	else{
		$raktaron = "";
	}

	//echo "SELECT id,nev, ar, kep, raktaron FROM cikkek WHERE ar>=$mettol AND ar<=$meddig ORDER BY $order";
	
	//SQL query vegrehajtasa
	if($meddig == null){
		$result = pg_prepare($db,"search_query", "SELECT id,nev, ar, kep, raktaron FROM cikkek WHERE ar>=$1 $raktaron AND lower(nev) LIKE $2 ORDER BY $order;");
		$result = pg_execute($db,"search_query",array($mettol,"%".strtolower($termeknev)."%"));
	}
	
	else{
	$result = pg_prepare($db,"search_query", "SELECT id,nev, ar, kep, raktaron FROM cikkek WHERE ar>=$1 AND ar<=$2 $raktaron AND lower(nev) LIKE $3 ORDER BY $order;");
	$result = pg_execute($db,"search_query",array($mettol,$meddig,"%".strtolower($termeknev)."%"));
	}
	
	if (!$result) {
	  echo "Nem sikerult a query.\n";
	  exit;
	}

?>

<head>

<title>Termékkatalógus</title>
<meta name="viewport" content="initial-scale=1.0, width=device-width"/>
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="css/main.css" rel="stylesheet" type="text/css">
</head>


<body class='container'>

<div class="header text-center jumbotron">
	<h1 class="display-4">Mobiltelefon termékkatalógus</h1>
</div>

<div class='row'>
<div class='szuro col col-lg-4 col-md-12 col-xl-3'>
	<form action='index.php' method='get'>
	<div class='card border-secondary'>
	<div class='card-header'>
		<h3>Szűrés</h3>
	</div>
	<div class='card-body'>
		<h4>Keresés</h4>
		<div class="input-group">
			<input type="text" class="form-control" name="termeknev" placeholder="Terméknév" value="<?php echo $termeknev; ?>">
	  	</div>
	  	<hr>
		<h4>Ár</h4>
		<div class="input-group">
			<input type="number" min="0" class="form-control" name="mettol" placeholder="Min" value="<?php echo $mettol; ?>">
			<div class="input-group-append">
	    		<span class="input-group-text">Ft</span>
	  		</div>
	  	</div>
  		<div class="input-group">
			<input type="number" min="0" class="form-control" name="meddig" placeholder="Max" value="<?php echo $meddig;?>">
			<div class="input-group-append">
	    		<span class="input-group-text">Ft</span>
	  		</div>
  		</div>
  		<input type="checkbox" name="raktaron" 
  		<?php 
  		//Bepipaljuk, ha a szuresben csak a raktaron levok jelennek meg
  		if(isset($_GET["raktaron"]) and $_GET["raktaron"]=="on")
  			{echo "checked";}
  		?>
  		>Csak a raktáron lévő termékek megjelenítése
  		<button class="szures btn btn-info" type="submit">Mehet</button>
	</div>
	</div>
	<div class='card border-secondary'>
	<div class='card-header'>
		<h3>Rendezés</h3>
		
	</div>
	<div class='card-body'>
		<div class="dropdown">
  			 <select id="rendez" name="rend" class="btn btn-secondary dropdown-toggle">
	    		<?php switch ($rendezes) {
	    			case 'nevcsokk':
	    				$nevcsokk='selected';
	    				break;
	    			case 'arnov':
	    				$arnov='selected';
	    				break;
	    			case 'arcsokk':
	    				$arcsokk='selected';
	    				break;
	    			default:
	    				$nevnov='selected';
	    				break;
	    		}
	    		//Az oldalon azt a modot válasszuk ki, ahogy rendezve van
	    		echo "
			  	<option class='dropdown-item' value='nevnov' $nevnov>Név szerint növekvő</option>
			    <option class='dropdown-item' value='nevcsokk' $nevcsokk>Név szerint csökkenő</option>
			    <option class='dropdown-item' value='arnov' $arnov>Ár szerint növekvő</option>
			    <option class='dropdown-item' value='arcsokk' $arcsokk>Ár szerint csökkenő</option>
		    "
	    	?>
	    	</select>
	    	<button type="submit" class="rendezes btn btn-info">Mehet</button>
		</div>


	</div>
	<div class='card-footer'>
		
	</div>
	</form>
	</div>
</div>
<div class='termekek col-xl-9 col-lg-8 col-md-12 col-sm-12 col-xs-12 col-12 text-center'>
	<div class="row">
	<?php 
	$maxar = 0;
	while ($sor = pg_fetch_row($result)) {
		if($sor[2]>$maxar){
			$maxar = $sor[2];
		}
		if($sor[4]=="t"){
			$raktaron = "<h6 class='badge badge-success'>Raktáron</h6>";
		}
		else{
			$raktaron = "<h6 class='badge badge-danger'>Nincs raktáron</h6>";
		}
		$link = "termek.php?id=$sor[0]";
	  $tetel = "<div class='termek col-xl-3 col-lg-5 col-md-5 col-sm-12 col-xs-12 card bg-light border-secondary'>
	  <div class='card-header'>
	  <img class='card-img-top' src='$sor[3]'></div>
	  <div class='card-body'>
	  	  
		  <h5 class='nev card-title text-center'>$sor[1]</h5>
		  <h6 class='ar text-center text-muted card-text'>".number_format($sor[2],0,',',' ')." Ft</h6>
		  
	  </div>
		  <div class='card-footer'>
		  $raktaron
		  <a class='btn btn-primary w-100' href='$link'>Megnézem</a>
		  </div>
	  </div>";			

		echo $tetel;
	}
	?>
	</div>
</div>
</div>
</form>
<footer class="footer-copyright text-center py-3">
	<a href="admin.php" class="text-muted">Admin felület</a>
</footer>
</body>
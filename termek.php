<!doctype html5>
<html>
<head>

<title>Termékkatalógus</title>
<meta name="viewport" content="initial-scale=1.0, width=device-width"/>
<meta charset="utf-8"/>
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="css/main.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="container bg-light border border-secondary rounded" style='padding:2rem'>
<?php

	$db = pg_connect('dbname=katalogus user=postgres');

	//echo $_GET["query"];
	if(isset($_GET["id"])){
	$id = $_GET["id"];
	}
	else{
		echo "Nincs ilyen termék!";
		exit;
	}
	if (!$db) {
	  echo "Nem sikerult az adatbazishoz csatlakozni.\n";
	  exit;
	}

	$result = pg_prepare($db,"my_query", "SELECT id,nev, ar, kep, raktaron,leiras FROM cikkek WHERE id=$1;");
	$result = pg_execute($db,"my_query", array($id));

	if (!$result) {
	  echo "Nem sikerult a query.\n";
	  exit;
	}

	$adat = pg_fetch_row($result);



	/*
	echo "<img src='$adat[3]' width='200'/><br />";
	echo "Termék: $adat[1]<br />";
	echo "Ár: $adat[2] Ft<br />";
	
	if($adat[4]=="t"){
			$raktaron = "<h6 class='badge badge-success'>Raktáron</h6>";
		}
		else{
			$raktaron = "<h6 class='badge badge-danger'>Nincs raktáron</h6>";
		}
	echo $raktaron;
	echo "Leírás: $adat[5]";
	*/

?>
<div class='row'>
<div class='col-12 col-xl-4 col-lg-4 col-md-5 col-sm-12 col-xs-12 '>
	<img src='<?php echo $adat[3];?>' class='col-12' alt='<?php echo $adat[1];?>'>
</div>
<div style='margin:1rem auto 0 auto' class='col-10 col-xl-8 col-lg-8 col-md-7 col-sm-12 col-xs-12'>
	<h3><?php echo $adat[1];?></h3>
	<?php
		if($adat[4]=="t"){
			$raktaron = "<h6 class='badge badge-success'>Raktáron</h6>";
		}
		else{
			$raktaron = "<h6 class='badge badge-danger'>Nincs raktáron</h6>";
		}
	echo $raktaron;?>
	<h4><?php echo number_format($adat[2],0,',',' ')?> Ft</h4>
	<h2 class='btn btn-primary' style='background: orange;border:darkorange'>Kosárba</h2>
</div>
<div class='col-12' style="margin:2rem;line-height: 2rem">
<?php 
	echo $adat[5];
?>
</div>
</div>
<a href="index.php" class='btn btn-primary'>Vissza</a>
</div>
</body>
</html>


<!doctype html5>
<html>
<head>
	<meta name="viewport" content="initial-scale=1.0, width=device-width"/>
	<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<link href="css/main.css" rel="stylesheet" type="text/css">
	<title>Termékkezelő</title>


</head>

<body>
	<div class="container">
	<?php 

$db = pg_connect('dbname=katalogus user=postgres');


if (!$db) {
  echo "Nem sikerult az adatbazishoz csatlakozni.\n";
  exit;
}
//Ha a username valtozo jelen van, akkor proba tortent
$proba = false;
if(isset($_POST["username"])){
	$username = $_POST["username"];
	$proba = true;
}
if(isset($_POST["password"])){
	if(isset($_POST["md5"])){
		$password = $_POST["password"];
	}
	else{
		$password = md5($_POST["password"]);	
	}
	$proba = true;
}
if($proba==true){
	$belepve = false;
	$users = pg_query($db, "select nev, jelszohash from adminok");

	while($userData = pg_fetch_row($users)){
		if($userData[0] == $username && $userData[1] == $password){
			//Ha megegyezik a felhasználonev, es a jelszohash, akkor beleptetjuk a felhasznalot
			$belepve = true;
			break;
		}
	}
	if($belepve==false){
		//Ha mar probalkoztunk ($proba=>true), es nem sikerult belepni, akkor kiirunk egy hibauzenetet
		echo "<div class='alert alert-danger text-center col-12 col-xs-12 col-sm-12 col-md-12 col-lg-10 col-xl-8'>Hibás jelszó, vagy felhasználónév!</div>";
	}
}
if($proba==false or $belepve==false){
	//Ha nem probalkoztunk meg, vagy nem sikerult belepni, akkor megjelenitjuk a belepo feluletet
	echo '
	<form method="post" style="margin:0 auto;" action="admin.php">
		<div class="card col-12 col-xs-12 col-sm-12 col-md-12 col-lg-10 col-xl-8" style="margin-top: 1rem;">
		<div class="card-header">
			<h2 class="text-center">Bejelentkezés az admin felületbe</h2>

		</div>
		<div class="loginform card-body text-center col-10 col-xs-10 col-md-10 col-lg-8 col-xl-8">
			
			<h4>Felhasználónév</h4>
			<input type="text" class="form-control w-100" required name="username"/>
			<h4>Jelszó</h4>
			<input type="password" class="form-control w-100" required name="password"/>
			<button type="submit" class="btn btn-primary w-100">Belépés</button>
		</div>
	</form>
	';
}
else{
	//Ha sikerult belepni, akkor ez a kodreszlet fut le

	//Ha kereses volt, akkor a keresett cikk nevet visszairjuk az oldal kereses mezojebes 
	if(isset($_POST['termeknev'])){
		$termeknev = strtolower($_POST['termeknev']);
	}
	else{
		$termeknev = "";
	}
	//Megjelenitjuk a felso reszt
	//Ez tartalmaz egy formot, amivel keresve ugyanide jon vissza az oldal
	echo "<div class='termekkezelo border border-secondary rounded bg-light'>
	<a href='index.php' class='text-muted' style='margin:0.5rem'> Vissza az oldalra</a>
	<h3 class='text-center'>Termékkezelő</h3>

	<hr/>
	<form action='admin.php' method='post'>
	<div class='input-group'>
	<input type='text' name='termeknev' style='margin-top:0.15rem;margin-left:2rem;' value='$termeknev' placeholder='Terméknév' class='form-control col-4'>
		<div class='hidden'>
		<input name='username' value='$username'>
		<input name='password' value='$password'>
		<input name='md5' value='1'>
		</div>
		<div class='input-group-append'>
	    		<button type='submit' class='keresesgomb btn btn-outline-primary'>Keresés</button>
	  	</div>
	</div>
	</form>";
	//Lekerdezzuk a kereses cikkeit, ha van $termeknev, ha nincs, akkor az osszes elemet megjeleniti
	$cikkek = pg_prepare($db,"lekerdezes", "select id, kep, nev from cikkek where lower(nev) like $1");
	$cikkek = pg_execute($db,"lekerdezes",array("%".$termeknev."%"));
	echo "<table class='table'>
	<thead>
		<tr>
			<th>id</th>
			<th>kép</th>
			<th>terméknév</th>
			<th>műveletek</th>
		</tr>
	</thead>
	<tbody>
	<form action='edit.php' method='post'>
			<div class='hidden'>
		<input name='username' value='$username'>
		<input name='password' value='$password'>
		<input name='md5' value='1'>
		</div>";
	//Hozzaadjuk a tablazathoz a hozzaadas sort
	echo "<tr>
		<th>+</th>
		<td></td>
		<td></td>
		<td><button type='submit' name='hozzaadas' value='1' class='btn btn-success'>Új termék hozzáadása</button> </td>
		</tr>";
	//A cikkeket egyesevel, soronkent hozzaadjuk a tablazathoz
	while ($cikk = pg_fetch_row($cikkek)) {
		echo "
		<tr>
		<th>$cikk[0]</th>
		<td><img src='$cikk[1]' alt='$cikk[2]'></td>
		<td>$cikk[2]</td>
		<td>
			<button type='submit' name='szerkesztes' value='$cikk[0]' class='btn btn-info'>Szerkesztés</button> 
			<button id='$cikk[0]t'  name='torles' onclick='function f(evt ) {if(!confirm(\"Biztosan törölni akarja: $cikk[2]?\")){evt.preventDefault();}};f(event);' value='$cikk[0]' class='btn btn-danger'>Törlés</button>
		</td></tr>";
	}
	echo "</form></tbody></table>";

	echo "</div>";
}

?>
		

	</div>

</body>

</html>
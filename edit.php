
<!doctype html5>
<html>
<head>
	<meta name="viewport" content="initial-scale=1.0, width=device-width"/>
	<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<link href="css/main.css" rel="stylesheet" type="text/css">
	<title>Termékkezelő - Szerkesztés</title>


</head>
<body>
<div id="container">
<?php 

$db = pg_connect('dbname=katalogus user=postgres');


if (!$db) {
  echo "Nem sikerult az adatbazishoz csatlakozni.\n";
  exit;
}


function uploadFile($file){
	//Ez a fuggveny ellenorzi es tolti fel a kepeket a weblapra
	if($file["size"]==0){
		//Hogyha nem adtunk meg uj kepet, akkor nem foglalkozunk a feltoltessel
		return 0;
	}

	$cel_mappa = "images/";
	$cel_file = $cel_mappa . basename($file["name"]);
	$uploadOk = 1;
	
	$kepFormatum = strtolower(pathinfo($cel_file,PATHINFO_EXTENSION));
	// Megnezzuk, hogy a feltolteni kivant fajl kep-e
	if(isset($_POST["submit"])) {
	    $kep_e = getimagesize($file["name"]);
	    if($kep_e !== false) {
	        //A fájl egy kép.
	        $uploadOk = 1;
	    } else {
	        echo "<div class='alert alert-danger'>A fájl nem egy kép</div>";
	        $uploadOk = 0;
	    }
	}
	// Megnezzuk, hogy a fajl letezik-e
	if (file_exists($cel_file)) {
	    echo "<div class='alert alert-danger'>A fájl már létezik.</div>";
	    $uploadOk = 0;
	}
	// Megnezzuk a fajlmeretet (max. 500KB)
	if ($file["size"] > 500000) {
	    echo "<div class='alert alert-danger'>A fájlméret túl nagy. Maximum 500KB lehet.</div>";
	    $uploadOk = 0;
	}
	// Csak 4 fajlformatumot engedelyezunk
	if($kepFormatum != "jpg" && $kepFormatum != "png" && $kepFormatum != "jpeg"
	&& $kepFormatum != "gif" ) {
	    echo "<div class='alert alert-danger'>Csak JPG, JPEG, PNG és GIF fájlok a megengedettek.";
	    $uploadOk = 0;
	}
	// Leellenorizzuk, hogy eddig volt-e hiba
	if ($uploadOk == 0) {
	    echo "<div class='alert alert-danger'>A kép nem lett feltöltve</div>";
	// ha nem, akkor megprobaljuk feltolteni a fajlt
	} else {
	    if (move_uploaded_file($file["tmp_name"], $cel_file)) {
	        echo "<div class='alert alert-success'>A fájl ". basename( $file["name"]). " sikeresen fel lett töltve.</div>";
	    } else {
	        echo "<div class='alert alert-danger'>Hiba történt a fájlfeltöltés közben.</div>";
	    }
	}
	//Visszaadunk 0-at, 1-et a fajlfeltoltes sikeressegetol fuggoen
	return $uploadOk;	

}

//Megnezzuk, hogy az admin.php feluletrol, vagy az edit.php-bol atjott-e a username
if(isset($_POST["username"])){
	$username = $_POST["username"];
}
else{
	die("<div class='alert alert-danger'>Nem helyes adatatvitel! 85.sor</div>");
}

//Ugyanezt a jelszoval, igy nem lehet csak kuldozgetni az edit.phpnak requesteket
//Itt a jelszo md5 kodolasu
if(isset($_POST["password"])){
	$password = $_POST["password"];
}
else{
	die("<div class='alert alert-danger'>Nem helyes adatatvitel! 94.sor</div>");
}




//Authentikacio
$belepve = false;
//Nev-jelszo parosok lekerese
$users = pg_query($db, "select nev, jelszohash from adminok");

//Masodszoros authentikacio a biztos adatvedelem miatt
//Vegigmegyunk a nev-jelszo parosokon, es ha valamelyik egyezik, akkor beleptetjuk
while($userData = pg_fetch_row($users)){
	if($userData[0] == $username && $userData[1] == $password){
		$belepve = true;
		break;
	}
}

//Ha nem sikerult beleptetni akkor nem volt megfelelo az adatatvitel 
if($belepve==false){
	die("<div class='alert alert-danger'>Nem helyes adatatvitel! 116.sor</div>");
}
else
{
	//Ha a fajl kapott szerkesztes valtozot, akkor az admin feluleten a szerkesztes gomb lett megnyomva
	//A valtozo erteke a szerkeszteni kivant cikk id-je
	if(isset($_POST["szerkesztes"]) and is_numeric($_POST["szerkesztes"])){
		//Megnezzuk, hogy az id szam-e, igy megakadalyozzuk az SQL injectiont
		$termekid = $_POST["szerkesztes"];
		//Lekerjuk a szerkeszteni kivant cikk adatait
		$cikk = pg_fetch_row(pg_query($db, "select id, nev, ar, kep, leiras, raktaron from cikkek where id=$termekid"));
		
		if($cikk[5]=="t"){
			//Ha a termek raktaron van, akkor bepipaljuk a checkboxot
			$raktaron = "checked";
		}else{
			$raktaron = "";
		}
		//Feltoltjuk a szerkesztes mezot a cikk adataival, es kiirjuk a felhasznalonak a mezot
		echo "<div class='container col-6 border border-secondary rounded' style='padding:2rem;'>
		
		<h3 class='text-center'>Termék szerkesztése</h3>
		
		<form action='edit.php' id='kuldo_form' method='post' enctype='multipart/form-data'>

		Terméknév: <input type='text' maxlength='50' class='form-control col-8' style='display:inline' name='termeknev' value='$cikk[1]'/><br />
		
		Ár: <input type='number' class='form-control col-8' style='display:inline' name='ar' value='$cikk[2]'/> Ft<br />
		
		Jelenlegi kép: <img src='$cikk[3]' style='width:20%;margin:1rem;'><br />
		
		Új kép feltöltése: <input type='file' accept='image/*' class='form-control' name='ujKep' value=''><br />
		
		Raktáron: <input type='checkbox' name='raktaron' $raktaron/><br />
		
		Leírás: <textarea name='leiras' maxlength='1000' rows='8' class='form-control' form='kuldo_form'>$cikk[4]</textarea>
		
		<br />
		<input type='reset' value='Visszaállítás' class='btn btn-warning'>
		
		<button class='btn btn-primary'  type='submit'>Módosítás</button>
			<div class='hidden'>
			<input name='frissit' value='$cikk[0]'>
			<input name='username' value='$username'>
			<input name='password' value='$password'>
			<input name='md5' value='1'>
			</div>
		</form>
		
		<form method='post' action='admin.php' class='text-right'>
			<button type='submit' class='btn btn-danger text-right'>Mégsem</button>
			<div class='hidden'>
				<input name='username' value='$username'>
				<input name='password' value='$password'>
				<input name='md5' value='1'>
			</div>
		</form>
		</div>
		";
		//A frissit rejtett input miatt a submit lenyomasakor meghivja ugyanezt az oldalt
		//A Megsem gomb megnyomasaval visszamegy az admin.php feluletre, a megfelelo jogosultsagokkal
	}

	if(isset($_POST["frissit"]) and is_numeric($_POST['frissit'])){
		//Ha az elozo(szerkesztes) form elkuldte az adatokat, akkor megprobaljuk atirni az adatbazisban
		$termekid = $_POST["frissit"];
		$termeknev = $_POST["termeknev"];
		$ar = $_POST["ar"];
		$uploadResult = uploadFile($_FILES["ujKep"]);
		$leiras = $_POST["leiras"];
		if(isset($_POST["raktaron"]) and $_POST["raktaron"]=="on"){
			$raktaron = "t";
		}
		else{
			$raktaron = "f";
		}
		if($uploadResult){
			$kep = $_FILES["ujKep"]["name"];
			$friss = pg_prepare($db,'frissit', "UPDATE cikkek SET nev = $1, ar = $2, kep= $3, leiras = $4, raktaron = $5 WHERE id='$termekid'");
			$friss = pg_execute($db, 'frissit', array($termeknev,$ar,'images/'.$kep,$leiras,$raktaron));
		}
		else{
			$friss = pg_prepare($db,'frissit', "UPDATE cikkek SET nev = $1, ar = $2, leiras = $3, raktaron = $4 WHERE id='$termekid'");
			$friss = pg_execute($db, 'frissit', array($termeknev,$ar,$leiras,$raktaron));
		
		}
		echo "<div class='container col-6'><form action='admin.php' method='post'>
			<div class='alert alert-success'>Termék sikeresen módosítva</div>
			<button type='submit' class='btn btn-primary'>Vissza a termékkezelőbe</button>
			<div class='hidden'>
			<input name='username' value='$username'>
			<input name='password' value='$password'>
			<input name='md5' value='1'>
			</div>
			<a href='index.php' class='btn btn-secondary'>Vissza a főoldalra</a>
		</form></div>";
	}


	if(isset($_POST["torles"]) and is_numeric($_POST["torles"])){
		//Ha a torles gombra kattintunk az admin.php feluleten, akkor ez az if fog lefutni
		//Ez megprobalja torolni az adatbazisbol a cikket
		$termekid = $_POST["torles"];
		pg_query($db, "DELETE from cikkek WHERE id='$termekid'");
		echo "<div class='container col-6'><form action='admin.php' method='post'>
			<div class='alert alert-success'>Termék sikeresen törölve</div>
			<button type='submit' class='btn btn-primary'>Vissza a termékkezelőbe</button>
			<div class='hidden'>
			<input name='username' value='$username'>
			<input name='password' value='$password'>
			<input name='md5' value='1'>
			</div>
			<a href='index.php' class='btn btn-secondary'>Vissza a főoldalra</a>
		</form></div>";
		exit();
	}

	if(isset($_POST["hozzaadas"])){
		//A hozzaadas gombra kattintva megjelenitunk egy feluletet
		//ahol a hozzaadni kivant cikk tulajdonsagait adhatjuk meg
		echo "<div class='container col-6 border border-secondary rounded' style='padding:2rem;'>
		<h3 class='text-center'>Termék hozzáadása</h3>
		
		<form action='edit.php' id='kuldo_form' method='post' enctype='multipart/form-data'>

		Terméknév: <input type='text' maxlength='50' class='form-control col-8' style='display:inline' name='termeknev' value=''/><br />
		
		Ár: <input type='number' class='form-control col-8' style='display:inline' name='ar' value=''/> Ft<br />
		
		Új kép feltöltése: <input type='file' accept='image/*' class='form-control' name='ujKep'><br />
		
		Raktáron: <input type='checkbox' name='raktaron'/><br />
		
		Leírás: <textarea name='leiras' maxlength='1000' rows='8' class='form-control' form='kuldo_form'></textarea>
		
		<br />
		<input type='reset' value='Újrakezdés' class='btn btn-warning'>
		
		<button class='btn btn-primary' type='submit'>Létrehozás</button>
			<div class='hidden'>
			<input name='rogzit' value='1'>
			<input name='username' value='$username'>
			<input name='password' value='$password'>
			<input name='md5' value='1'>
			</div>
		</form>
		
		<form method='post' action='admin.php' class='text-right'>
			<button type='submit' class='btn btn-danger text-right'>Mégsem</button>
			<div class='hidden'>
				
				<input name='username' value='$username'>
				<input name='password' value='$password'>
				<input name='md5' value='1'>
			</div>
		</form>
		</div>
		";
	}

	if(isset($_POST["rogzit"]) and is_numeric($_POST['rogzit'])){
		//Ha az elozo(hozzaadas) feluletet kitoltve a Letrehozas gombra kattintunk
		//akkor az alabbi kod megprobalja az adatbazishoz hozzaadni a cikket
		$termeknev = $_POST["termeknev"];
		$ar = $_POST["ar"];
		$uploadResult = uploadFile($_FILES["ujKep"]);
		$leiras = $_POST["leiras"]; 
		if(isset($_POST["raktaron"]) and $_POST["raktaron"]=="on"){
			//Ha a raktaron checkbox be van pipalva, akkor 1-est irunk az adatbazis raktaron oszlopaba
			$raktaron = "1";
		}
		else{
			$raktaron = "0";
		}
		if($uploadResult)
		{
			//Ha a kepfeltoltes fuggveny hiba nelkul lefut, akkor beleirjuk a kep nevet is az adatbazisba
			$kep = $_FILES["ujKep"]["name"];
			$hozzaad = pg_prepare($db,'rogzit', "INSERT INTO cikkek (nev,ar,kep,leiras,raktaron) VALUES($1,$2,$3,$4,$5)");
			$hozzaad = pg_execute($db, 'rogzit', array($termeknev,$ar,'images/'.$kep,$leiras,$raktaron));
		}
		else
		{
			//Ellenkezo esetben nem, visszadobva egy figyelmezteto uzenetet
			echo "<div class='alert alert-danger'>Nem adott képet a feltöltéshez</div>";
			$hozzaad = pg_prepare($db,'rogzit', "INSERT INTO cikkek (nev,ar,leiras,raktaron) VALUES($1,$2,$3,$4)");
			$hozzaad = pg_execute($db, 'rogzit', array($termeknev,$ar,$leiras,$raktaron));
		}
		echo "<div class='container col-6'><form action='admin.php' method='post'>
			<div class='alert alert-success'>Termék sikeresen hozzáadva</div>
			<button type='submit' class='btn btn-primary'>Vissza a termékkezelőbe</button>
			<div class='hidden'>
			<input name='username' value='$username'>
			<input name='password' value='$password'>
			<input name='md5' value='1'>
			</div>
			<a href='index.php' class='btn btn-secondary'>Vissza a főoldalra</a>
		</form></div>";
	}


}


?>
</div>

</body>
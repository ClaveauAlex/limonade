<?php
///////////////////////////////////////////////////////////////////////////////////
function redirect($url) {
	echo "<meta http-equiv=\"refresh\" url=".$url."\" />";
	echo "<script>location.replace('".$url."');</script>";   // To cope with Mozilla bug
	die;
}
///////////////////////////////////////////////////////////////////////////////////
function mailCheck($Email) {
	// pas de verification des mails saisis pour le TP
  return("OK");
  
  // L'adresse email doit être correctement formatée
	if(!eregi("^[[:alpha:]]{1}[[:alnum:]]*((\.|_|-)[[:alnum:]]+)*@[[:alpha:]]{1}[[:alnum:]]*((\.|-)[[:alnum:]]+)*(\.[[:alpha:]]{2,})$", $Email))
		return("Adresse ".$Email." mal formatée !");
	// On récupère le domaine
	list(,$domain ) = split('@',$Email); 
	
  // pb avec aol.com,  serveur ne répond pas aux demandes...
	if ($domain=="aol.com") return("OK");
  // idem avec laposte.net
	if ($domain=="laposte.net") return("OK");
  // pb avec free sur le return-path
	if ($domain=="free.fr") return("OK");  
  
  // On cherche des enregistrements MX dans les DNS
	if (getmxrr($domain, $MXHost)) 
		$ConnectAddress = $MXHost[0];
	else
		$ConnectAddress = $domain;
	// On créé la connexion sur le port smtp (25)
	$Connect = @fsockopen($ConnectAddress,25,$errno,$errstr); 
	if($Connect)
	{
		if(ereg("^220", $Out = fgets($Connect, 1024)))
		{
			fputs ($Connect, "HELO {$_SERVER['HTTP_HOST']}\r\n");
			$Out = fgets ( $Connect, 1024 );
			fputs ($Connect, "MAIL FROM: <{$Email}>\r\n");
			$From = fgets ( $Connect, 1024 );
			fputs ($Connect, "RCPT TO: <{$Email}>\r\n");
			$To = fgets ($Connect, 1024);
			fputs ($Connect, "QUIT\r\n");
			fclose($Connect);
			// Si le code renvoyé par la commande RCPT TO est 250 ou 251 (cf: RFC)
			// Alors l'adresse existe
			if (!ereg ("^250", $To) && !ereg ( "^251", $To ))
				// Adresse rejetée par le serveur
				return("Adresse ".$Email." rejetée par le serveur ".$domain." !");
			else
				// Adresse acceptée par le serveur
				return("OK");
		} else {
			// Le serveur n'a pas répondu
			return("Le serveur ".$domain." n'a pas répondu !");
		}
	} else {
		// Connexion au serveur de messagerie impossible
		// vous pouvez afficher le message d'erreur en décommentant la ligne suivante:
		// echo $errno."-".$errstr;
		return("Connexion au serveur ".$domain." impossible !");
	}
}
///////////////////////////////////////////////////////////////////////////////////
function dejaInscrit($courriel) {  
  global   $DbHost, $DbName, $DbUser, $DbPassword;

  $DbLink = mysqli_connect($DbHost, $DbUser, $DbPassword) or die('erreur de connexion au serveur');
  mysqli_select_db($DbLink, $DbName) or die('erreur de connexion a la base de donnees');
  mysqli_query($DbLink, "SET NAMES 'utf8'");
  $query = "SELECT count(id) FROM data WHERE identifiant='".$courriel."'";
  $result = mysqli_query($DbLink, $query) or die (mysqli_error($DbLink));
  $value = mysqli_fetch_row($result);
  mysqli_close();    
  if ($value[0]>=1) {
    return(true);
  }
  else {
    return(false);  
  }

}
///////////////////////////////////////////////////////////////////////////////////
// Générer une chaine de caractère aléatoire
function texteAleatoire($longueur) {
  $string = "";
  $chaine = "abcdefghijklmnpqrstuvwxyABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
  srand((double)microtime()*1000000);
  for($i=0; $i<$longueur; $i++) {
    $string .= $chaine[rand()%strlen($chaine)];
  }
  return $string;
  
}
///////////////////////////////////////////////////////////////////////////////////
?>

<?php


function getSQL($number){
  $servername = "localhost";
  $dbname = "CryptoBotDb";

  switch ($number) {
    case 1:
        $username = "jenkinss";
        $password = "Butt3rcup23";
        break;
    case 2:
        $username = "cryptoBotWeb1";
        $password = "UnYpH7HkgK[N";
        break;
    case 3:
        $username = "cryptoBotWeb2";
        $password = "U0I^=bBc0jkf";
        break;
    default:
        $username = "cryptoBotWeb3";
        $password = "XcE)n7GJ-Twr";
    }
    $conn = new mysqli($servername, $username, $password, $dbname);
    return $conn;
}


?>

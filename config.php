<?php                                                                                                                                                        
$dbuser  = "root";
$pass 	   = "";
$database  = "streamframe_test";
$host_name = "localhost";

try{
   require_once(__DIR__."/class/PDOHandler.php");
   $pdo_object = new PDO("mysql:host=".$host_name.";dbname=".$database, $dbuser, $pass, array(PDO::ATTR_PERSISTENT => true));
   $pdoHandlerObject = new PDOHandler();
}catch (PDOException $e){
   echo $e->getMessage();
   echo "Could not connect with database";
   die();
}
?>
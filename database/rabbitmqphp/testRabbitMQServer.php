#!/usr/bin/php
<?php
//Getting the rabbitMQ data
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function checkDatabase($username,$password)
{
    echo "Checking the credentials. . .".PHP_EOL;

    	//Applying the credential for the database
    	include("account.php");

	//Connect to the database
	$db = mysqli_connect($dbhostname, $dbusername, $dbpassword, $dbproject);

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
		exit();
	}
	echo "Connected to MySQL successfully.".PHP_EOL;
	
	//Check password
	$s = "select password FROM auth where ID = '$username'";
	($t = mysqli_query($db, $s)) or die ( mysqli_error ($db));
	$r = mysqli_fetch_array ( $t, MYSQLI_ASSOC);
	$passFromDB = $r["password"];
	
	if($password== $passFromDB){
		echo "Credential verified.".PHP_EOL;
		return true;
	}
	else{
		echo "Credential not verified.".PHP_EOL;
		return false;
	}
}

function registerToDatabase($ID,$pass,$firstName,$lastName,$email)
{
	echo "Registering the new account information".PHP_EOL;
	
	//Applying the credential for the database
	include("account.php");

	//Connect to the database
        $db = mysqli_connect($dbhostname, $dbusername, $dbpassword, $dbproject);

        if (mysqli_connect_errno())
        {
                echo "Failed to connect to MySQL: " . mysqli_connect_error();
                exit();
        }
        echo "Connected to MySQL successfully.".PHP_EOL;

	//Inserting new values
	$s = "INSERT into auth values ('$ID', '$pass', '$firstName', '$lastName', '$email')";
	($t = mysqli_query ($db, $s)) or die ( mysqli_error ($db));
	echo "New account registered successfully.".PHP_EOL;
	return true;
}

function requestProcessor($credential)
{
  echo "Database VM received request".PHP_EOL;
  var_dump($credential);
  if(!isset($credential['type']))
  {
    return "ERROR: unsupported message type";
  }
  if($credential['type']=="Login")
  {
    return checkDatabase($credential['ID'],$credential['pass']);
  }
  if($credential['type']=="Register")
  {
    return registerToDatabase($credential['ID'],$credential['pass'],$credential['firstName'],$credential['lastName'],$credential['email']);
  }
  if($credential['type']=="validate_session")
  {
    return doValidate($credential['sessionId']);
  }
  return array("returnCode" => '1', 'message'=>"Database received request and processed");
}

$dataServer = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "DatabaseRabbitMQServer BEGIN".PHP_EOL;
$dataServer->process_requests('requestProcessor');
echo "DatabaseRabbitMQServer END".PHP_EOL;
exit();
?>


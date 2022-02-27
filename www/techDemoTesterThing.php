<?php
$serviceToCreate = "DemoApp";
$newServiceName = "newUsersService";

//deleteService($newServiceName);
//try {
	if (createService($serviceToCreate, $newServiceName)) {
		echo "createdSite";
	}
//} catch (Exception $ex) {
//	echo $ex->getMessage();
//}
function deleteService($serviceName) {
	$serviceName = strtolower(sanitize($serviceName));
	deleteServiceFiles("../clientServices/".$serviceName);
	deleteServiceDB($serviceName);
}
function deleteServiceFiles($src) {
	$dir = opendir($src);
    while( $file = readdir($dir) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) && !is_link($src . '/' . $file) ) {
				deleteServiceFiles($src . '/' . $file);
            } else {
                unlink($src . '/' . $file);
            }
        }
    }
	closedir($dir);
	rmdir($src);
	return true;
}
function deleteServiceDB($serviceName) {
	$servername = "localhost";
	$username = "root";
	$password = "";
	$conn = new mysqli($servername, $username, $password);
	if ($conn->connect_error) {
		throw new Exception("Error: SQL Connection failed: ".$conn->connect_error, 100);
	}
	$dbToRemove = "CSD_".$serviceName;
	$usernameToRemove = "CSU_".$serviceName;
	$sql = "DROP USER IF EXISTS '".$usernameToRemove."'@'".$servername."';";
	$sql .= "DROP DATABASE IF EXISTS `".$dbToRemove."`;";
	if ($conn->multi_query($sql) === TRUE) {
	} else {
		throw new Exception("Error: Error deleting database: " . $conn->error, 100);
	}
	$conn->close();
	return true;
}

function createService($service, $name) {
	polyfill();
	$name = strtolower(sanitize($name));
	if (!file_exists("../clientServices/".$name)) {
		$conf = getDeployConf($service);
		if (copyDir("../serviceTemplates/".$service, "../clientServices/".$name) && createDB($name, $conf) && writeVariables($name, $service, $conf)) {
			finalRedirect($name, $conf);
			return true;
		}
	} else {
		throw new Exception("Error: Service Already Exists With Name: ".$name, 100);
	}
}
function finalRedirect($name, $conf) {
	$domain = "cloud";
	if (isset($conf["Redirect"])) {
		header("Location: http://".$name.".".$domain."/".$conf["Redirect"]);
	} else {
		header("Location: http://".$name.".".$domain);
	}
}
function sanitize($str) {
	return str_replace(Array("\\","/","'","\"","$"), "", $str);
}
function getDeployConf($service) {
	return parse_ini_file("../serviceTemplates/".$service."/rapideploy.ini");
}
function writeVariables($name, $service, $conf) {
	if (isset($conf["WriteVariables"])) {
		foreach ($conf["WriteVariables"] as $file) {
			$file = "../clientServices/".$name."/".$file;
			if (is_file ($file) && (!is_dir($file))) {
				$data = file_get_contents($file);
				$data = str_ireplace(Array("%rapideploy-servicename%","%rapideploy-clientservicename%","%rapideploy-version%","%rapideploy-serviceversion%"), Array($service, $name,"v1.0.0",((isset($conf["ServiceVersion"]))? $conf["ServiceVersion"]:"0.0.0")), $data);
				file_put_contents($file,$data);
			}
		}
	}
	return true;
}
function createDB($name, $conf) {
	if (isset($conf) && $conf["CreateDataBase"]) {
		$servername = "localhost";
		$username = "root";
		$password = "";
		$conn = new mysqli($servername, $username, $password);
		if ($conn->connect_error) {
			throw new Exception("Error: SQL Connection failed: ".$conn->connect_error, 100);
		}
		$newdbname = "CSD_".$name;
		$newusername = "CSU_".$name;
		$newpassword = generatePswrd();
		$newdbname = $conn->real_escape_string($newdbname);
		$newusername = $conn->real_escape_string($newusername);
		$sql = "CREATE USER '".$newusername."'@'".$servername."' IDENTIFIED BY '".$newpassword."';";
		$sql .= "CREATE DATABASE ".$newdbname.";";
		$sql .= "GRANT ALL PRIVILEGES ON `".$newdbname."`.* TO '".$newusername."'@'".$servername."'";
		if ($conn->multi_query($sql) === TRUE) {
			writeDBLogin($servername,$newdbname,$newusername,$newpassword,$name,$conf);
			executeSQL($servername,$newdbname,$newusername,$newpassword,$conf);
		} else {
			throw new Exception("Error: Error creating database: " . $conn->error, 100);
		}
		$conn->close();
		return true;
	} else {
		return true;
	}
}
function writeDBLogin($server, $db, $user, $pass, $name, $conf) {
	$datatemplate = "
	<?php
		\$db_server = \"".$server."\";
		\$db_user = \"".$user."\";
		\$db_password = \"".$pass."\";
		\$db_db = \"".$db."\";
	?>";
	if (isset($conf["DataBaseLogin"])) {
		foreach ($conf["DataBaseLogin"] as $file) {
			$file = "../clientServices/".$name."/".$file;
			$data = $datatemplate;
			if (is_file ($file) && (!is_dir($file))) {
				if ((filesize($file) == 0) || (trim(file_get_contents($file)) == false) || (trim(file_get_contents($file)) == "")){
					file_put_contents($file,$data);
				} else {
					$data = file_get_contents($file);
					$data = str_ireplace(Array("%rapideploy-dbserver%","%rapideploy-db%","%rapideploy-dbuser%","%rapideploy-dbpass%"), Array($server, $db, $user, $pass), $data);
					file_put_contents($file,$data);
				}
			} elseif (!is_dir($file)) {
				file_put_contents($file,$data);
			}
		}
	}
}
function executeSQL($server, $db, $user, $pass, $conf) {
	if (isset($conf["SQLFile"])) {
		foreach ($conf["SQLFile"] as $file) {
			$conn2 = new mysqli($server, $user, $pass, $db);
			if ($conn2->connect_error) {
				throw new Exception("Error: SQL Connection failed: ".$conn2->connect_error, 100);
			}
			if ($conn2->multi_query(file_get_contents($file)) === TRUE) {
				
			} else {
				throw new Exception("Error: Error creating database: " . $conn->error, 100);
			}
			$conn2->close();
		}
	}
}

function generatePswrd($length = 64, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#^&*()-_+=~:,.<>?') {
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    if ($max < 1) {
        throw new Exception('$keyspace must be at least two characters long');
    }
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}

function copyDir($src, $dst) {
    $dir = opendir($src);
    mkdir($dst);
    while( $file = readdir($dir) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
				copyDir($src . '/' . $file, $dst . '/' . $file);
            } else {
                if (!str_ends_with($file,"rapideploy.ini")) {
					if (!copy($src . '/' . $file, $dst . '/' . $file)) {
						return false; //this should be a throw
					}
				}
            }
        }
    }
    closedir($dir);
	return true;
}
function polyfill () {
	//PHP 7 Polyfill for PHP 8's string ends with
	if (! function_exists('str_ends_with')) {
		function str_ends_with(string $haystack, string $needle): bool
		{
			$needle_len = strlen($needle);
			return ($needle_len === 0 || 0 === substr_compare($haystack, $needle, - $needle_len));
		}
	}
}
?>
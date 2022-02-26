<?php
$serviceToCreate = "DemoApp";
$newServiceName = "newUsersService";
try {
	if (createService($serviceToCreate, $newServiceName)) {
		echo "createdSite";
	}
} catch (Exception $ex) {
	echo $ex->getMessage();
}

function createService($service, $name) {
	$name = strtolower(sanitize($name));
	if (!file_exists("../clientServices/".$name)) {
		if (copyDir("../serviceTemplates/".$service, "../clientServices/".$name) && createDB($name)) {
			return true;
		}
	} else {
		throw new Exception("Error: Service Already Exists With Name: ".$name, 100);
	}
}

function sanitize($str) {
	return str_replace(Array("\\","/","'","\""), "", $str);
}

function createDB($name) {
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
	$sql .= "GRANT ALL PRIVILEGES ON '".$newdbname."'.* TO '".$newusername."'@'".$servername."'";
	if ($conn->multi_query($sql) === TRUE) {
		//write to db.php here
	} else {
		throw new Exception("Error: Error creating database: " . $conn->error, 100);
	}
	$conn->close();
	return true;
}

function generatePswrd($length = 64, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_+=~:,.<>?') {
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
                if (!copy($src . '/' . $file, $dst . '/' . $file)) {
					return false;
				}
            }
        }
    }
    closedir($dir);
	return true;
}
?>
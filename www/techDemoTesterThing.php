<?php
$serviceToCreate = "DemoApp";
$newServiceName = "newUsersService";
createService($serviceToCreate, $newServiceName);

function createService($service, $name) {

	if (copyDir("../serviceTemplates/".$service, "../clientServices/".$name)) {
		echo "createdSite";
	}

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
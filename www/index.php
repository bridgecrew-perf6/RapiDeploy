<?php

require("ServiceManagerBackend.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>RapiDeploy Portal</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>

<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="#"><i>RapiD</i><b>eploy</b></a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav">
        <li class="active"><a href="#">Home</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">    
  <div class="row">
	<?php
		$dir = opendir("../serviceTemplates/");
		while( $file = readdir($dir) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir('../serviceTemplates/' . $file) && !is_link('../serviceTemplates/' . $file) ) {
					echo "
						<div class='col-sm-4'>
							<div class='panel panel-primary'>
								<div class='panel-heading'>".$file."</div>
								<div class='panel-body'><img src='placeholder.png' style='width:100%;'></img></div>
								<div class='panel-footer'>
									<center>
										<form class='form-inline' method='get'>
											<input type='text' id='service' name='service' value='".$file."' hidden></input>
											<input type='text' class='form-control' id='create' placeholder='Enter Name' name='create'>
											<button type='submit' class='btn btn-default'>Create</button>
										</form>
									</center>
								</div>
							</div>
						</div>
					";
				}
			}
		}
		closedir($dir);
	?>
  </div>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Services</th>
        <th>Options</th>
      </tr>
    </thead>
    <tbody>
		<?php
			$dir = opendir("../clientServices/");
			while( $file = readdir($dir) ) {
				if (( $file != '.' ) && ( $file != '..' )) {
					if ( is_dir('../clientServices/' . $file) && !is_link('../clientServices/' . $file) ) {
						echo "
							<tr>
								<td>".$file."</td>
								<td><a href='http://".$file.".cloud'><button type='button' class='btn btn-default'>Visit</button></a>  <a href='.?delete=".$file."'><button type='button' class='btn btn-danger'>Delete</button></a></td>
							  </tr>
						";
					}
				}
			}
			closedir($dir);
		?>
    </tbody>
  </table>
</div>

</body>
</html>

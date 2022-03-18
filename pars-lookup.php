<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<title>CA Shipment Status - Eshpre Global</title>

	<link href="css/bootstrap.css" rel="stylesheet">
	<link href="css/main.css" rel="stylesheet">
	<link href="css/hover.css" rel="stylesheet">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
 <body> 
	<?php include 'inc/header.php'; ?>
  
	<div class="container-fluid">
		<div class="col-md-3">
			<?php include 'inc/sidebar.php'; ?>
		</div>
		<div class="col-md-6">
			<div class="panel panel-default">
				<h1 style="border-bottom: 2px solid #87BC23;">Shipment Status <span class="glyphicon glyphicon-search pull-right" aria-hidden="true"></span></h1>					
				<?php
				function countdim($array)
				{
					if (is_array(reset($array)))
					{
						$return = countdim(reset($array)) + 1;
					}
					else
					{
						$return = 1;
					}
					return $return;
				}
				
				function status($id)
				{
					switch($id)
					{
						case "1":
							return "(1) Message Content Accepted";
							break;
						
						case "2":
							return "(2) Message Content Rejected";
							break;
						
						case "4":
							return "(4) Goods Released";
							break;
							
						case "5":
							return "(5) Goods Required for Examination";
							break;
							
						case "8":
							return "(8) Goods May Move Under Customs Transfer, Detain at Destination (CFIA)";
							break;
							
						case "9":
							return "(9) Delcaration Accepted, Awaiting Arrival of Goods";
							break;
							
						case "14":
							return "(14) Error message";
							break;
							
						case "23":
							return "(23) Authorised to Deliver CSA Shipment";
							break;
							
						case "34":
							return "(34) Transaction Awaiting Processing";
							break;
					}
				}
				
				if ((isset($_GET["n"])) && ($_GET["n"] != null))
				{
					$cargo = $_GET['n'];
					
					echo '<p class="lead">Looking up cargo control number '.$cargo.'</p>';
					
					$ch = curl_init();

                    curl_setopt_array($ch, array(
                      CURLOPT_URL => 'https://username.candata.com/rnsquery/cargoQuery?cargo=' . $cargo,
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => '',
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'GET',
                      CURLOPT_HTTPHEADER => array(
                        'X-Candata-Token: username:password'
                      ),
                    ));
                    
					$out = curl_exec($ch);
					curl_close($ch);
					//echo response output
					//echo json_encode($out);
					
					//echo "Output" . $out;
					
					$xml = simplexml_load_string($out);
					$json = json_encode($xml);
					$array = json_decode($json,TRUE);
					
					//print_r ($json);
					//echo $xml;
					
					$dim = countdim($array);
					
					
					
					if ($dim == 3)
					{
						echo '<table class="table">';
						echo "
						<tr><td></td><td></td></tr>
						<tr><td><b>Status: </b></td><td>" . status($array["rns"]["@attributes"]["processing_ind"]) . "</td></tr>
						<tr><td><b>Port: </b></td><td>" . $array["rns"]["@attributes"]["port"] . "</td></tr>
						<tr><td><b>Sublocation: </b></td><td>" . $array["rns"]["@attributes"]["sublocation"] . "</td></tr>
						<tr><td><b>Transaction Num: </b></td><td>" . $array["rns"]["@attributes"]["transaction_num"] . "</td></tr>";
						echo "<tr><td><b>Date: </b></td><td>" . $array["rns"]["@attributes"]["release_date"] . "</td></tr>
						<tr><td></td><td></td></tr>
						";
						echo '</table>';
						//echo "<b>Process Date: </b>" . $array["rns"]["@attributes"]["process_date"];
					}
					else if ($dim == 4)
					{
						echo '<table class="table">';
						foreach ($array["rns"] as $ar)
						{
							/*
							echo '<div class="panel panel-default">
									<p class="lead"><b>'.$ar["@attributes"]["release_date"].': </b>'.$ar["@attributes"]["processing_ind"].'</p>
								</div>';*/
							
							
							echo "
							<tr><td></td><td></td></tr>
							<tr><td><b>Status: </b></td><td>" . status($ar["@attributes"]["processing_ind"]) . "</td></tr>
							<tr><td><b>Port: </b></td><td>" . $ar["@attributes"]["port"] . "</td></tr>
							<tr><td><b>Sublocation: </b></td><td>" . $ar["@attributes"]["sublocation"] . "</td></tr>
							<tr><td><b>Transaction Num: </b></td><td>" . $ar["@attributes"]["transaction_num"] . "</td></tr>";
							echo "<tr><td><b>Date: </b></td><td>" . $ar["@attributes"]["release_date"] . "</td></tr>
							";
							
						}
						//echo "<b>Process Date: </b>" . $array["rns"]["@attributes"]["process_date"];
						echo '<tr><td></td><td></td></tr></table>';
					}
					
					echo '<h3 style="border-bottom: 2px solid #87BC23;"><b>Email & SMS Notifications</b></h3>';
					echo '<p>Register an email address to receive notifications when updates are received for a specific cargo control number.</p>';
					
					if (isset($_POST["notify"]))
					{
						$email = $_POST["email"];
						
						$ch = curl_init();

                        curl_setopt_array($ch, array(
                          CURLOPT_URL => 'https://username.candata.com/rnsquery/notify?email='.$email.'&cargo='.$cargo,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                          CURLOPT_HTTPHEADER => array(
                            'X-Candata-Token: eshpre:fvHEpxkAuIG9LGTDtd2qL8bQz'
                          ),
                        ));
						$out = curl_exec($ch);
						curl_close($ch);
						// echo response output
						echo $out;
					}
					
					if (isset($_POST["notifySMS"]))
					{
						$sms = $_POST["sms"];
						
						$ch = curl_init();

                        curl_setopt_array($ch, array(
                          CURLOPT_URL => 'https://username.candata.com/rnsquery/notify?sms='.$sms.'&cargo='.$cargo,
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'GET',
                          CURLOPT_HTTPHEADER => array(
                            'X-Candata-Token: username:password'
                          ),
                        ));
						$out = curl_exec($ch);
						curl_close($ch);
						// echo response output
						echo $out;
					}
					
					echo '<div class="row" style="margin-bottom:10px;">
							<div class="col-md-6">';
							echo'<form method="POST" class="form-inline">
								  <div class="form-group">
									<label>Email:</label>
									<input type="text" class="form-control" name="email" placeholder="name@example.com">
								  </div>
								  <button type="submit" class="btn btn-default" name="notify">Notify</button>
								</form>
							</div>';
						
							echo'<div class="col-md-6">
								<form method="POST" class="form-inline">
								  <div class="form-group">
									<label>SMS:</label>
									<input type="text" class="form-control" name="sms" placeholder="1-555-555-555">
								  </div>
								  <button type="submit" class="btn btn-default" name="notifySms">Notify</button>
								</form>
							</div>
						</div>';
				}
				else
				{
					echo '<p>Please enter a cargo control number.</p>';
				}
				?>
			</div>
		</div>
		<div class="col-md-3">
			<?php include 'inc/sidebar_right.php'; ?>
		</div>
	</div>
	
	<?php include 'inc/footer.php'; ?>	

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>

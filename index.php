<!doctype html>
<html>
<head>
	<meta charset="utf8">
	<title>Currency Rates API</title>

	<!-- Bootstrap core CSS and custom CSS -->
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="config/style.css" rel="stylesheet" type="text/css">
	<!-- Bootstrap theme -->
	<link href="bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
</head>

<body>
	<div class="col-lg-3 col-lg-offset-4">
		<form action="api_controller/hnbex_call.php">
			<label for="date">Enter date for HNBex exchange list<br></label><br>
			<div class="input-group">
				<input type="text" id="date" name="date" value="<?php echo date('d-m-Y'); ?>" class="form-control"/>
				<span class="input-group-btn">
					<button type="submit" id="submit" class="btn btn-primary">Save</button>
				</span>
			</div>
		</form>

		<br><br>

		<form action="api_controller/hnb_call.php">
			<label>HNB exchange list</label><br>
			<button type="submit" id="submit" class="btn btn-primary">Save</button>
		</form>

		<br><br>

		<form action="api_controller/pbz_call.php">
			<label>PBZ exchange list</label><br>
			<button type="submit" id="submit" class="btn btn-primary">Save</button>
		</form>
	</div>

	<!-- Modal -->
	<?php
	session_start();
	(empty($_SESSION['output_array'])) ? $session = null : $session = $_SESSION['output_array'];

	if(!empty($session))
	{
	?>
		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Exchange rates info from
					<b>
						<?php 
						echo $session[0];
						?>
					</b>
				</h4>
			</div>
			<div class="modal-body">
				<p>
					<?php
					foreach($session as $i => $value)
					{
						if($i < 1){continue;}

						echo $value;
					}
					
					session_unset();
					?>
				</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
			</div>
		</div>
		</div>
	<?php
	}
	?>

	<!-- Bootstrap core JavaScript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>

	<script>
		$(window).load(function()
		{
			$("#myModal").modal('show');
		})
	</script>
</body>
</html>
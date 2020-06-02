<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		
		<title>REST API Requester</title>
		
		<style>
			.d-block {
				display: block;
			}
		</style>
	</head>
	<body>
		<form action="api/user" method="post">
			<div class="d-block">
				<input type="text" name="user">
			</div>
			<div class="d-block">
				<input type="password" name="password">
			</div>
			<input type="submit" value="Submit">
		</form>
	</body>
</html>
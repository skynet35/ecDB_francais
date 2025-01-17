<?php
	//Start session
	session_start();
	
	//Include database connection details
	require_once('include/login/config.php');
	
	//Array to store validation errors
	$errmsg_arr = array();
	
	//Validation error flag
	$errflag = false;
	
	//Connect to mysql server
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if(!$link) {
		die('Impossible de se connecter au serveur ' . mysqli_error($link));
	}
	
	//Select database
	$db = mysqli_select_db($link, DB_DATABASE);
	if(!$db) {
		die("Impossible de se connecter a la base");
	}
	
	//Function to sanitize values received from the form. Prevents SQL injection
	function clean($str) {
		$str = @trim($str);
		if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}
		return mysqli_real_escape_string($link, $str);
	}
	
	//Sanitize the POST values
	$login = ($_POST['login']);
	$password = ($_POST['password']);

	//Input Validations
	if($login == '') {
		$errmsg_arr[] = 'Mauvais identifiant';
		$errflag = true;
	}
	if($password == '') {
		$errmsg_arr[] = 'Mauvais mot de pass';
		$errflag = true;
	}
	
	//If there are input validations, redirect back to the login form
	if($errflag) {
		$_SESSION['ERRMSG_ARR'] = $errmsg_arr;
		session_write_close();
		header("location: login.php");
		exit();
	}
	
	//Create query
	$qry="SELECT * FROM members WHERE login='$login' AND passwd='".md5($_POST['password'])."'";
	$result=mysqli_query($link, $qry);
	
	//Check whether the query was successful or not
	if($result) {
		if(mysqli_num_rows($result) == 1) {
			//Login Successful
			session_regenerate_id();
			$member = mysqli_fetch_assoc($result);
			$_SESSION['SESS_MEMBER_ID'] = $member['member_id'];
			$_SESSION['SESS_FIRST_NAME'] = $member['firstname'];
			$_SESSION['SESS_LAST_NAME'] = $member['lastname'];
			session_write_close();
			$member_id = $_SESSION['SESS_MEMBER_ID'];
			mysqli_query($link, "INSERT INTO members_stats (members_stats_member) VALUES ('$member_id')");
			header("location: index.php");
			exit();
		}else {
                        print('Je suis en ligne');
			//Login failed
			//header("location: login-failed.php");
			exit();
		}
	}else {
		die("Query failed");
	}
?>

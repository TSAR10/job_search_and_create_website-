<?php
  if(isset($_SESSION)){session_unset();session_destroy();}
  session_set_cookie_params(0);
  session_start();
  if(isset($_SESSION["applied"])){unset($_SESSION["applied"]);}
  if(isset($_SESSION["old_password"])){
    unset($_SESSION["old_password"]);
  }
  if($_SERVER["REQUEST_METHOD"] == 'POST')
  {
    $email = trim($_POST["email"]);
    $pass = trim($_POST["pass"]);
    if($email=="" || $pass=="")
    {
      header('location:../php/index.php');
    }
    // elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //   header('location:../php/index.php');
    // }
    // elseif (!preg_match('^(?=\P{Ll}*\p{Ll})(?=\P{Lu}*\p{Lu})(?=\P{N}*\p{N})(?=[\p{L}\p{N}]*[^\p{L}\p{N}])[\s\S]{8,}$',$pass)) {
    //   header('location:../php/index.php');
    // }
    else{
      $config = require '../configure.php';
      $pdo = new PDO(
        $config['database_dsn'],
        $config['database_user'],
        $config['database_pass']
      );
      $query = 'SELECT email,password,type FROM company where email = :email and password = :password union SELECT email,password,type FROM applicant where email = :email and password = :password';
      $stmt = $pdo -> prepare($query);    // validating the user
      $stmt -> bindParam('email',$email);
      $stmt -> bindParam('password',$pass);
      $stmt -> execute();
      $result = $stmt -> fetch();
      if($result){
        session_regenerate_id();
        $_SESSION['timestamp'] = time();
        $_SESSION['user'] = $result['email'];
        $_SESSION['type'] = $result['type'];

        header('location:../php/dashboard.php');
      }
      else{
        header('location:../php/index.php');
      }
    }
  }
?>

<html>
<head>
  <link href='../layout/index.css' type="text/css" rel="stylesheet">
  <title>
    JOBS-Login
  </title>
</head>
<body>
  <h1>Login</h1>
  <div id = "log">
    <form action="../php/index.php" method="post" id = "form">
      <div>
        <label for="email">Email</label><br>
        <input type="email" name="email" id="email">
      </div>
      <div>
        <label for="pass">Password</label><br>
        <input type="password" name="pass" id="pass">
      </div>
      <div>
        <input type="submit" name="submit" value="Submit"><br>
        <input type="button" name="sigup" value="Register" onclick="location.href='../php/registration.php'">
      </div>
    </form>
  </div>
</body>
</html>

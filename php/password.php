<?php
session_start();
$config = require '../configure.php';
$pdo = new PDO(
  $config['database_dsn'],
  $config['database_user'],
  $config['database_pass']
);
if(!isset($_SESSION['user'])){
  header("location: ../php/index.php");
}
elseif ($_SERVER["REQUEST_METHOD"] == 'POST') {
  if(isset($_SESSION["old_password"])){
    $password = trim($_POST["password"]);
    if($password == ""){
      header("../php/dashboard.php");
    }
    elseif(($_SESSION["old_password"]) == 1){
      $query = 'SELECT password from applicant where password =:password';
      $stmt = $pdo -> prepare($query);        //check the entered old password
      $stmt -> bindParam('password',$password);
      $stmt -> execute();
      $result = $stmt -> fetch();
      if($result)
      {
        $_SESSION["old_password"] = $result[0];
        header('location:../php/password.php');
      }
      else{
        unset($_SESSION);         //if the entered old password is wrong take to index page
        session_destroy();
        header("location: ../php/index.php");
      }
    }
    else{
      $query = 'UPDATE applicant set password = :new_password where password = :password'; //set the new password
      $stmt = $pdo -> prepare($query);
      $stmt -> bindParam('new_password',$password);
      $stmt -> bindParam('password',$_SESSION["old_password"]);
      $stmt -> execute();
      unset($_SESSION["old_password"]);
      header('location:../php/index.php');
    }
  }
}
elseif (isset($_GET["id"])) {     //making sort of hack proof
  if($_GET["id"] == "pass_change"){
    $_SESSION["old_password"] = 1;
  }
  else{
    header("../php/index.php");
  }
}
elseif(!isset($_SESSION["old_password"])) { //if anyone try to open the site externally it will take them to index page
  header("location: ../php/index.php");
}
 ?>
<html>
<head>
  <link href="../layout/index.css" type="text/css" rel="stylesheet">
  <title>
    Password Change
  </title>
</head>
<body>
  <h1>Password Change</h1>
  <div id = "log">
    <form action="../php/password.php" method="post" id = "form">
      <div>
        <label for="password" id="pass"><?php if($_SESSION["old_password"] == "1"){
          echo "Old Password";
        }
        else{
          echo "Old Password";
        }?></label><br>
        <input type="password" name="password" id="password">
      </div>
        <input type="submit" name="submit" value="Summit"><br>
      </div>
    </form>
  </div>
</body>
</html>

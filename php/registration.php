<?php
  session_start();
  if(isset($_SESSION["applied"])){unset($_SESSION["applied"]);}
  $config = require '../configure.php';
  $pdo = new PDO(
    $config['database_dsn'],
    $config['database_user'],
    $config['database_pass']
  );
  // unset($_SESSION);
  // session_destroy();
    if($_SERVER["REQUEST_METHOD"] == 'POST'){
      $_SESSION['type'] = "applicant";
      $first_name = trim($_POST["first_name"]);
      $last_name = trim($_POST["last_name"]);
      $phone = trim($_POST["phno"]);
      $email = trim($_POST["email"]);
      if(isset($_POST["password"])){
        $password = trim($_POST["password"]);
      }
      elseif(isset($_SESSION["password"])){
        $password = $_SESSION["password"];
      }
      $college = trim($_POST["college"]); //taking the posted input value
      $tenth_res = trim($_POST["tenth_res"]);
      $twelfth_res = trim($_POST["twelfth_res"]);
      $grad_res = trim($_POST["grad_res"]);
      $passing_yr = trim($_POST["passing_yr"]);
      if($first_name != "" || $last_name != "" || $phone != "" || $college != "" || $tenth_res != "" || $grad_res != "" || $passing_yr != "" || $twelfth_res != "" || $email != "" || $password != ""){
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
          $dir = "../upload";
          $fileName = $_FILES['cv']['name'];    //uploading the CV
          $fileSize = $_FILES['cv']['size'];
          $fileArray = explode(".",$fileName);
          $FileType = strtolower(end($fileArray));
          $allowed= ["pdf", "doc", "docx","jpg"];
          if(in_array($FileType,$allowed)){
            if($fileSize <= 1000000){
              $newFileName = md5($phone . $fileName) . '.' . $FileType;
              move_uploaded_file($_FILES["cv"]["tmp_name"],"$dir/$newFileName");
              $cv = $newFileName;
              if(!isset($_SESSION["action"])){
                $query = 'INSERT INTO applicant (first_name,last_name,phone,college,tenth_res,twelfth_res,cv,grad_res,passing_yr,type,email,password) VALUES (:first_name,:last_name,:phone,:college,:tenth_res,:twelfth_res,:cv,:grad_res,:passing_yr,:type,:email,:password)';
                $stmt = $pdo -> prepare($query);
                $stmt -> bindParam('first_name',$first_name); //inserting the posted data to database
                $stmt -> bindParam('last_name',$last_name);
                $stmt -> bindParam('phone',$phone);
                $stmt -> bindParam('college',$college);
                $stmt -> bindParam('tenth_res',$tenth_res);
                $stmt -> bindParam('twelfth_res',$twelfth_res);
                $stmt -> bindParam('cv',$cv);
                $stmt -> bindParam('grad_res',$grad_res);
                $stmt -> bindParam('passing_yr',$passing_yr);
                $stmt -> bindParam('type',$_SESSION["type"]);
                $stmt -> bindParam('email',$email);
                $stmt -> bindParam('password',$password);
                $stmt -> execute();
                $check ="test";
                sleep(10);
                session_unset();
                session_destroy();
                header("location:../php/index.php");

              }
              else{
                $query = 'UPDATE applicant set first_name =:first_name, last_name =:last_name,phone =:phone,college =:college,tenth_res =:tenth_res,twelfth_res =:twelfth_res,grad_res =:grad_res,passing_yr =:passing_yr,type =:type,email =:email WHERE password =:password';
                $stmt = $pdo -> prepare($query);
                $stmt -> bindParam('first_name',$first_name);     //user profile updation
                $stmt -> bindParam('last_name',$last_name);
                $stmt -> bindParam('phone',$phone);
                $stmt -> bindParam('college',$college);
                $stmt -> bindParam('tenth_res',$tenth_res);
                $stmt -> bindParam('twelfth_res',$twelfth_res);
                $stmt -> bindParam('grad_res',$grad_res);
                $stmt -> bindParam('passing_yr',$passing_yr);
                $stmt -> bindParam('type',$_SESSION["type"]);
                $stmt -> bindParam('email',$email);
                $stmt -> bindParam('password',$password);
                $stmt -> execute();
                $result = $stmt->fetch();
                sleep(5);
                unset($_SESSION["action"]);
                unset($_SESSION["password"]);
                header("location:../php/dashboard.php");
              }
            }
            else{
              header("location:../php/registration.php");
            }
          }
          else{
            header("location:../php/registration.php");
          }
        }
        else{
          header("location:../php/registration.php");
        }
      }
    }
  elseif($_SERVER["REQUEST_METHOD"] == 'GET' && isset($_SESSION["type"]) && !isset($_GET['id'])) {
    session_unset();
    session_destroy();
    header("location:../php/index.php");
  }
  else{
    $query = 'SELECT * from applicant where email = :email';
    $stmt = $pdo -> prepare($query);
    $stmt -> bindParam('email',$_SESSION["user"]);
    $stmt -> execute();
    $result = $stmt -> fetch();
    // echo "<pre>"; var_dump($result); echo "</pre>";die;
    if($result){
      $_SESSION["action"] = "edit";
      $_SESSION["password"] = $result["password"];
    }
  }
?>
<html>
<head>
  <link href="../layout/register.css" rel="stylesheet" type="text/css">
  <title>
    Registration
  </title>
</head>
<body>
  <h1>Registration</h1>
  <div id = "log">
    <form id = "form" method="post" action="../php/registration.php" enctype="multipart/form-data">
      <p>
        <label for = "first_name" >First Name</label>
        <input type="text" name="first_name" id="first_name" value="<?php if(isset($_GET["id"])){echo $result["first_name"];}?>">
      </p>
      <p>
        <label for = "last_name" >Last Name</label>
        <input type="text" name="last_name" id="last_name" value="<?php if(isset($_GET["id"])){echo $result["last_name"];}?>">
      </p>
      <p>
        <label for = "phno">Phone No.</label>
        <input type="number" name="phno" id="phno" value="<?php if(isset($_GET["id"])){echo (int)$result["phone"];}?>">
      </p>
      <p>
        <label for = "email">Email</label>
        <input type="email" name="email" id="email" value="<?php if(isset($_GET["id"])){echo $result["email"];}?>">
      </p>
      <?php if(!isset($_GET["id"])){?>
        <p>
          <label for = "password">Password</label>
          <input type="password" id ="password" name = "password">
        </p>
      <?php}
      else{?>
      <?php
      }
      ?>
      <p>
        <label for = "college" >College</label>
        <input type="text" name="college" id="college" value="<?php if(isset($_GET["id"])){echo $result["college"];}?>">
      </p>
      <div id="ed">
        <p>
          <label for = "tenth_res">10th Percentage</label>
          <input type="number" name="tenth_res" id="tenth_res" value="<?php if(isset($_GET["id"])){echo $result["tenth_res"];}?>">
        </p>
        <p>
          <label for = "twelfth_res">12th Percentage</label>
          <input type="number" name="twelfth_res" id="twelfth_res" value="<?php if(isset($_GET["id"])){echo $result["twelfth_res"];}?>">
        </p>
      </div>
      <div id="ed">
        <p>
          <label for = "grad_res">Overall CGPA</label>
          <input type="text" name="grad_res" id="grad_res" value="<?php if(isset($_GET["id"])){echo $result["grad_res"];}?>">
        </p>
        <p>
          <label for="passing_yr">Passing Year</label>
          <input type="number" name="passing_yr" id="passing_yr" value="<?php if(isset($_GET["id"])){echo $result["passing_yr"];}?>">
        </p>
      </div>
      <?php if(!isset($_GET["id"])){?>
        <p>
          <label for = "cv" >Upload CV</label>
          <input type="file" name="cv" id="cv">
        </p>
      <?php}
      else{?>
      <?php
      }
      ?>
      <input type="submit" name="submit" value="submit">
    </form>
  </div>
</body>
</html>

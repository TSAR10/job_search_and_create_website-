<?php
  session_start();
  if(isset($_SESSION["old_password"])){
    unset($_SESSION["old_password"]);
  }
  $config = require '../configure.php';
  $pdo = new PDO(
    $config['database_dsn'],
    $config['database_user'],
    $config['database_pass']
  );
  if(!isset($_SESSION['id'])){
    header("location: ../php/index.php");
  }
  elseif (isset($_GET['id'])) {
    unset($_SESSION["job_id"]);
    $id = $_GET['id'];
    $id = (int) $id;
    $query = 'SELECT * from job_applied where job_id = :job_id';
    $stmt = $pdo -> prepare($query);
    $stmt -> bindParam('job_id',$id);
    $stmt -> execute();
    $result = $stmt -> fetch();
    if($_SESSION['id'] != $result[0]){
      unset($_SESSION);
      session_destroy();
      header('location:../php/index.php');
    }
    else{
      $_SESSION["job_id"] = $_GET['id'];
      header("location:../php/application.php");
    }
  }
  elseif(isset($_SESSION["job_id"])){
    $id = (int)$_SESSION["job_id"];
    $query = 'SELECT * from applicant inner join job_applied on job_applied.applicant_id = applicant.phone where job_applied.job_id = :id';
    $stmt = $pdo -> prepare($query);
    $stmt -> bindParam('id',$id);
    $stmt -> execute();
    $result = $stmt -> fetchALL();
  }
?>
<html>
<head>
  <title>
    Application View
  </title>
  <link href="../layout/apply.css" rel="stylesheet" type="text/css">
</head>
<body>
  <?php
    foreach ($result as $key) {
  ?>
  <div id = tiles>
    <div id = "name">
      <div>
        <h3>NAME</h3>
        <span><?php echo $key["first_name"] . " " . $key["last_name"]?></span>
      </div>
    </div>
    <div id = "education">
      <div>
        <h3>10th Result</h3>
        <span><?php echo $key["tenth_res"]?></span>
      </div>
      <div>
        <h3>12th Result</h3>
        <span><?php echo $key["twelfth_res"]?></span>
      </div>
      <div>
        <h3>CGPA Result</h3>
        <span><?php echo $key["grad_res"]?></span>
      </div>
    </div>
    <div id = "contact_details">
      <div>
        <h3>E-mail</h3>
        <span><?php echo $key["email"]?></span>
      </div>
      <div>
        <h3>Phone</h3>
        <span><?php echo $key["phone"]?></span>
      </div>
    </div>
    <div>
      <div>
        <h3>College</h3>
        <span><?php echo $key["college"]?></span>
      </div>
      <div>
        <h3>Passing Year</h3>
        <span><?php echo $key["passing_yr"]?></span>
      </div>
    </div>
  </div>
<?php
}
?>
</body>
</html>

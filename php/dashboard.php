<?php
  session_start();
  if(isset($_SESSION["applied"])){unset($_SESSION["applied"]);}
  if(isset($_SESSION["old_password"])){
    unset($_SESSION["old_password"]);
  }
  if(!isset($_SESSION['user'])){
    header("location: ../php/index.php");
  }
  else{
    $config = require '../configure.php';
    $pdo = new PDO(
      $config['database_dsn'],
      $config['database_user'],
      $config['database_pass']
    );
    $query = 'SELECT phone FROM ' .$_SESSION["type"]. ' where email = :email';
    $stmt = $pdo -> prepare($query);
    $stmt -> bindParam('email',$_SESSION["user"]);
    $stmt -> execute();
    $result = $stmt -> fetch();
    $_SESSION["id"] = $result[0];
    if($_SESSION["type"] == 'company' && !isset($_GET["id"]))
    {
      $query = 'SELECT jobs_available.*,company.company_name FROM jobs_available inner join company on jobs_available.company_id = :company_id';
      $stmt = $pdo -> prepare($query);
      $stmt -> bindParam('company_id',$result[0]);
      $stmt -> execute();
      $result = $stmt -> fetchALL();
      unset($_SESSION["applied"]);

    }
    elseif($_SERVER["REQUEST_METHOD"] == 'POST'){
        $search = $_POST["job_search"];
        $query = 'SELECT jobs_available.*,company.company_name FROM jobs_available inner join company on jobs_available.company_id = company.phone WHERE job_title =:job_search';
        $stmt = $pdo -> prepare($query);
        $stmt -> bindParam('job_search',$search);
        $stmt -> execute();
        $result = $stmt -> fetchALL();
        unset($_SESSION["applied"]);
        // var_dump($result);die;


    }
    elseif(isset($_GET["id"])){
      $id = $_GET["id"];
      $query = 'SELECT company_id from jobs_available where job_id =:id';
      $stmt = $pdo -> prepare($query);
      $stmt -> bindParam('id',$id);
      $stmt -> execute();
      $company_id = $stmt -> fetch();
      $company_id = (int)$company_id[0];
      $query = 'INSERT INTO job_applied VALUES (:company_id,:id,:applicant_id)';
      $stmt = $pdo -> prepare($query);
      $id = (int)$id;
      $applicant_id = (int)$_SESSION['id'];
      $stmt -> bindParam('company_id',$company_id);
      $stmt -> bindParam('id',$id);
      $stmt -> bindParam('applicant_id',$_SESSION['id']);
      $stmt -> execute();
      sleep(10);
      unset($_SESSION["applied"]);
      header("location:../php/dashboard.php");
    }
    else{
      $query = 'SELECT job_applied.company_id,job_title,description,salary,job_applied.job_id,number_applied,status,company.company_name FROM jobs_available inner join job_applied on jobs_available.job_id = job_applied.job_id and applicant_id =:applicant_id inner join company on job_applied.company_id = company.phone';
      $stmt = $pdo -> prepare($query);
      $stmt -> bindParam('applicant_id',$result[0]);
      $stmt -> execute();
      $result = $stmt -> fetchALL();
      $_SESSION["applied"] = 1;
    }
  }
?>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../layout/dashboard.css" rel="stylesheet" type="text/css">
  <title>
    JOBS
  </title>
</head>
<body>
  <?php if($_SESSION["type"] == "applicant"){?>
  <div id = "name" class ="spacing">
    <form action="../php/dashboard.php" method="post" id="form">
      <input type="text" name = "job_search" id = "seach" placeholder="Search Jobs">
      <input type="submit" name = "search" value="search" id ="button_edit">
    </form>
    <a id ="upper" href="../php/registration.php?id=<?php echo "edit"?>">
      <button id ="button_edit">Edit</button>
    </a>
    <a id ="upper" href="../php/password.php?id=<?php echo "pass_change"?>">
      <button id ="button_edit">Password</button>
    </a>
  </div>
  <?php
  }
  ?>
  <?php
    foreach ($result as $key) {
  ?>
  <div id = 'tiles'>
    <div id = 'name'>
      <span><?php echo $key[1]?></span>
      <span><?php echo $key[7]?></span>
    </div>
    <div id = 'description'>
      <span>
        <h3>Job Description :- </h3>
        <?php echo $key[2]?>
      </span>
    </div>
    <div id = 'skills'>
      <span>
        <h3>Salary :- </h3>
        <?php echo $key[3]?>
      </span>
      <span>
        <h3>No. of applicants :- </h3>
        <?php echo $key[5]?>
      </span>
      <span>
        <h3><?php echo $key[6]."d"?></h3>
      </span>
    </div>
    <div id = "editor">
        <?php if($_SESSION['type'] == 'company'){?>
            <a href="../php/create_job.php?id=<?php echo $key[4];?>"><button id = "button_edit">Edit</button></a>
            <a href="../php/application.php?id=<?php echo $key[4];?>"><button id = "button_edit_app">View Applicactions</button></a>
        <?php
        }
        elseif(!isset($_SESSION["applied"])){
        ?>
           <a href="../php/dashboard.php?id=<?php echo $key[4];?>"><button id = "button_edit">Apply</button></a>
        <?php
        }
        else{?>
            <button id = "button_edit">Applied</button>
        <?php
        }
        ?>
    </div>
  </div>
  <?php
  }
  ?>
  <?php if($_SESSION['type'] == 'company'){?>
    <a href="../php/create_job.php">
      <div id = button>
        +
      </div>
    </a>
  <?php
  }
  ?>
</body>
</html>

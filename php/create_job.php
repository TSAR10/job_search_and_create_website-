<?php

  session_start();
  // var_dump($_SESSION);die;
  if(!isset($_SESSION['id'])){
    header("location: ../php/index.php");
  }
  if(isset($_SESSION["old_password"])){
    unset($_SESSION["old_password"]);
  }
  else{
      $config = require '../configure.php';
      $pdo = new PDO(
      $config['database_dsn'],    //database connection
      $config['database_user'],
      $config['database_pass']
      );
      if($_SERVER["REQUEST_METHOD"] == 'POST')
      {
        $job_title = trim($_POST['job_title']);
        $job_description = trim($_POST['job_description']);             // getting the posted values
        $salary = trim($_POST['salary']);
        $salary = (int)$salary;
        $status = trim($_POST['status']);
        if($job_title == "" || $job_description == "" || $salary == "" || $status == "")
        {
          // $not = 'not';
          // var_dump("$not");die;
          header("location: ../php/dashboard.php");
        }
        else{
          if(!isset($_SESSION["job_id"]))
          {
            $query = 'INSERT INTO jobs_available (company_id,job_title,description,salary,status) VALUES (:company_id,:job_title,:job_description,:salary,:status)';
            $stmt = $pdo -> prepare($query);
            $stmt -> bindParam('company_id',$_SESSION["id"]);       //creating the new job
            $stmt -> bindParam('job_title',$job_title);
            $stmt -> bindParam('job_description',$job_description);
            $stmt -> bindParam('salary',$salary);
            $stmt -> bindParam('status',$status);
            $stmt -> execute();
          }
          else{
            $id = $_SESSION["job_id"];
            $id = (int)$id;
            unset($_SESSION["job_id"]);           //editing the exixting job and their status
            $query = 'UPDATE jobs_available SET job_title = :job_title, description = :job_description, salary = :salary, status = :status WHERE job_id = :job_id';
            $stmt = $pdo -> prepare($query);
            $stmt -> bindParam('job_title',$job_title);
            $stmt -> bindParam('job_description',$job_description);
            $stmt -> bindParam('salary',$salary);
            $stmt -> bindParam('status',$status);
            $stmt -> bindParam('job_id',$id);
            $stmt -> execute();
          }

          header('location:../php/dashboard.php');
        }
      }
      elseif (isset($_GET['id'])) {                   //making the site hack proof if user type any other number in link it will logout him
        $id = $_GET['id'];
        $id = (int) $id;
        $query = 'SELECT job_title,description,salary,status,company_id from jobs_available where job_id = :job_id';
        $stmt = $pdo -> prepare($query);
        $stmt -> bindParam('job_id',$id);
        $stmt -> execute();
        $result = $stmt -> fetch();
        if($_SESSION['id'] != $result[4]){
          unset($_SESSION);
          session_destroy();
          header('location:../php/index.php');
        }
        else{
          $_SESSION["job_id"] = $_GET['id'];
        }
      }
  }
?>
<html>
<head>
  <title>
    Create Job
  </title>
  <link href="../layout/form.css" rel="stylesheet" type="text/css">
</head>
<body>
  <h1>Create Job</h1>
  <div id="log">
    <form action = "../php/create_job.php" method = "post" id = "form">
      <p>
        <label for="job_title">Job Title</label><br>
        <input type="text" name="job_title" id="job_title" value="<?php
          if(isset($_GET['id'])){
            echo $result[0];
          }
         ?>">
      </p>
      <p>
        <label for="job_description">Job Description</label><br>
        <textarea name="job_description" id="job_description" maxlength="250">
          <?php
            if(isset($_GET['id'])){
              echo $result[1];
            }
           ?>
        </textarea>
      </p>
      <p>
        <label for="salary">Salary</label><br>
        <input type="number" name="salary" id="salary" value="<?php
          if(isset($_GET['id'])){
            echo $result[2];
          }
         ?>">
      </p>
      <div>
        <div>
          <input type="radio" name="status" id="inactivate" value="inactivate"<?php
            if(isset($_GET['id'])){
              if($result[3] == 'inactivate')
              {
                echo "checked";
              }
            }
           ?>>
          <label for="inactivate">Inactivate</label>
        </div>
        <div>
          <input type="radio" name="status" id="activate" value="activate"<?php
            if(isset($_GET['id'])){
              if($result[3] == 'activate')
              {
                echo "checked";
              }
            }
           ?>>
          <label for="activate">activate</label>
        </div>
      </div>
      <input type="submit" name="submit" value="submit">
    </form>
  </div>
</body>
</html>

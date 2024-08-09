<?php 
    if(isset($_GET['id'])){
        $alumni_id = $_GET['id'];

    $serername="localhost";
    $db_username="root";
    $db_password="";
    $db_name="alumni_management_system";
    $conn=mysqli_connect($serername, $db_username, $db_password, $db_name);

    if(mysqli_connect_errno()){
        die("". mysqli_connect_error());
    }else{
        echo"Successfully Connected!";
    }
    //insert data into table alumni_archive from alumni
    $sql_archive = "INSERT INTO alumni_archive (student_id, fname, mname, lname, course, batch_startYear, batch_endYear, connected_to, contact, address, email, username, password, picture, date_created)". 
                   "SELECT student_id, fname, mname, lname, course, batch_startYear, batch_endYear, connected_to, contact, address, email, username, password, picture, date_created FROM alumni WHERE student_id=$alumni_id";
    $conn->query($sql_archive);

    //delete data in table alumni
    $sql_delete = "DELETE FROM alumni WHERE student_id=$alumni_id";
    $conn->query($sql_delete);
    }
    header("location: ../alumni.php");
    exit;
?>
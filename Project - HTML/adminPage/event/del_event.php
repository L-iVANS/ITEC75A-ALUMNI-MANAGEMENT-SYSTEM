<?php 
    if(isset($_GET['id'])){
        $coor_id = $_GET['id'];

    $serername="localhost";
    $db_username="root";
    $db_password="";
    $db_name="alumni_management_system";
    $conn=mysqli_connect($serername, $db_username, $db_password, $db_name);

    //insert data into table alumni_archive from alumni
    $sql_archive = "INSERT INTO coordinator_archive (coor_id, fname, mname, lname, contact, email, username, password, picture, date_created)" . 
                   "SELECT coor_id, fname, mname, lname, contact, email, username, password, picture, date_created FROM coordinator WHERE coor_id=$coor_id";
    $conn->query($sql_archive);

    //delete data in table alumni
    $sql_delete = "DELETE FROM coordinator WHERE coor_id=$coor_id";
    $conn->query($sql_delete);
    }
    header("location: ../coordinator.php");
    exit;
?>
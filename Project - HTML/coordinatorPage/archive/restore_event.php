<?php 
    if(isset($_GET['id'])){
        $coor_id = $_GET['id'];

    $serername="localhost";
    $db_username="root";
    $db_password="";
    $db_name="alumni_management_system";
    $conn=mysqli_connect($serername, $db_username, $db_password, $db_name);

    //insert data into table coordinator_archive from coordinator
    $sql_restore = "INSERT INTO coordinator (coor_id, fname, mname, lname, contact, email, username, password, picture, date_created)" . 
                   "SELECT coor_id, fname, mname, lname, contact, email, username, password, picture, date_created FROM coordinator_archive WHERE coor_id=$coor_id";
    $conn->query($sql_restore);

    //delete data in table coordinator_archive
    $sql_delete = "DELETE FROM coordinator_archive WHERE coor_id=$coor_id";
    $conn->query($sql_delete);
    }
    header("location: ../coordinator_archive.php");
    exit;
?>
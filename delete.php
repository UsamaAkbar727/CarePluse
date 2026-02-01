<?php
include('config/db.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM appointments WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?msg=DeletedSuccessfully");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

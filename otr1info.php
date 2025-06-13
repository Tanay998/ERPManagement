<?php 
session_start();
include 'config.php';

if(isset($_POST['submit'])){
    $stateParts = explode('|', $_POST['state']);
    $stateName = $stateParts[1] ?? $_POST['state'];

    $branch = mysqli_real_escape_string($db, $_POST['branch']);
    $applicantName = mysqli_real_escape_string($db, $_POST['applicantName']);
    $fatherName = mysqli_real_escape_string($db, $_POST['fatherName']);
    $state = mysqli_real_escape_string($db, $stateName);
    $cdistrict = mysqli_real_escape_string($db, $_POST['ccdistrict']);
    $category = mysqli_real_escape_string($db,$_POST['category']);
    $dob = mysqli_real_escape_string($db, $_POST['dob']);
    $admissionType = mysqli_real_escape_string($db, $_POST['admissionType']);
    $course = mysqli_real_escape_string($db, $_POST['course']);
    $RollNo = intval($_POST['RollNo']);
    $semester = mysqli_real_escape_string($db, $_POST['semester']);
    $registrationFee = intval($_POST['registrationFee']);
    $transactionId = mysqli_real_escape_string($db, $_POST['razorpay_payment_id']);

    // Insert into polyregis table
    $query1 = "INSERT INTO `polyregis` (`branch`,`applicantName`,`fatherName`,`state`,`cdistrict`,`category`,`dob`,`admissionType`,`course`,`RollNo`,`semester`,`RegistrationFee`,`TransactionID`) 
               VALUES ('$branch','$applicantName','$fatherName','$state','$cdistrict','$category','$dob','$admissionType','$course','$RollNo','$semester','$registrationFee','$transactionId')";

    if(mysqli_query($db, $query1)) {
        $applicationId = mysqli_insert_id($db); // Get inserted ID from polyregis

        // Set user ID from session (change as per your session variable)
        $userId = $_SESSION['user_id'] ?? 0;

        // Replace payment insert with update
        $query2 = "UPDATE payment 
        SET application_id = '$applicationId' 
        WHERE payment_id = '$transactionId' 
        AND user_id = '$userId'";

        if(mysqli_query($db, $query2)) {
            echo "<script>
                if(confirm('Your data and payment info submitted successfully.')) {
                    location.replace('new_admission.php');
                }
            </script>";
        } else {
            echo "Error updating payment record: " . mysqli_error($db);
        }

    } else {
        echo "Error inserting into polyregis table: " . mysqli_error($db);
    }
}
?>
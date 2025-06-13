<?php 
session_start();
include 'config.php'; // Use the existing connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate payment first
    if(empty($_POST['razorpay_payment_id'])) {
        die("<script>
            alert('Payment verification failed. Please complete the payment process.');
            window.history.back();
        </script>");
    }

    // Sanitize and prepare form data
    $courseType = mysqli_real_escape_string($db, $_POST['courseType']);
    $courseLevel = mysqli_real_escape_string($db,$_POST['courseLevel']);
    $courseList = mysqli_real_escape_string($db, $_POST['courseList']);
    $applicantName = mysqli_real_escape_string($db, $_POST['applicantName']);
    $fatherName = mysqli_real_escape_string($db, $_POST['fatherName']);
    $mobile = mysqli_real_escape_string($db, $_POST['mobile']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $dob = mysqli_real_escape_string($db, $_POST['dob']);
    $gender = mysqli_real_escape_string($db, $_POST['gender']);
    $aadhaar = mysqli_real_escape_string($db, $_POST['aadhaar']);
    $address = mysqli_real_escape_string($db, $_POST['address']);
    $city = mysqli_real_escape_string($db, $_POST['city']);
    $state = mysqli_real_escape_string($db, $_POST['state']);
    $category = mysqli_real_escape_string($db, $_POST['category']);
    $qualification = mysqli_real_escape_string($db, $_POST['qualification']);
    $employmentStatus = mysqli_real_escape_string($db, $_POST['status']);
    $registrationFee = 200; // Fixed amount
    $transactionId = mysqli_real_escape_string($db, $_POST['razorpay_payment_id']);

    // Handle file upload
    $photoPath = '';
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $targetDir = "uploads/";
        $fileName = basename($_FILES["photo"]["name"]);
        $targetFile = $targetDir . uniqid() . '_' . $fileName;
        
        if(move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
            $photoPath = $targetFile;
        }
    }

    $query = "INSERT INTO `estcregis` 
              (`course_type`, `courseLevel`,`course_list`, `applicant_name`, `father_name`, `mobile`, `email`, 
               `dob`, `gender`, `aadhaar`, `address`, `city`, `state`, `category`, 
               `qualification`, `employment_status`, `photo_path`, `registration_fee`, `transaction_id`) 
              VALUES 
              ('$courseType', '$courseLevel','$courseList', '$applicantName', '$fatherName', '$mobile', '$email', 
               '$dob', '$gender', '$aadhaar', '$address', '$city', '$state', '$category', 
               '$qualification', '$employmentStatus', '$photoPath', '$registrationFee', '$transactionId')";

    if(mysqli_query($db, $query)) {
        echo "<script>
            if(confirm('Your admission form has been submitted successfully with Transaction ID: $transactionId')){
                window.location.href = 'index.php?page=promote_class.php';
            }	
        </script>";
    } else {
        echo "Error: " . mysqli_error($db);
    }
} else {
    // If form not submitted properly, redirect back
    header("Location: index.php?page=promote_class.php");
    exit();
}
?>
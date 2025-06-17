<?php
$db = mysqli_connect("localhost","root","","schoolfeesys");

if(!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

if(isset($_POST['submit'])) {
    // Initialize all variables with default values to prevent undefined index warnings
    $defaults = [
        'tfw1' => '', 'hindiname' => '', 'hindiname1' => '', 'hindiname2' => '',
        'marital1' => '', 'blood1' => '', 'add11' => '', 'diva11' => '', 'div1' => '',
        'rec' => '', 'emp' => '', 'salary' => '', 'aadhaar' => '', 'ccdistrict' => '',
        'bankacc' => '', 'IFSC' => '', 'BANKNAME' => '', 'edu' => '', 'schoolName' => '',
        'Board' => '', 'subject' => '', 'yop1' => '', 'typeofmarks11' => '', 'totalmarks' => '',
        'obtainmarks' => '', 'percentage' => ''
    ];
    
    // Merge defaults with actual POST data
    $_POST = array_merge($defaults, $_POST);

    // Get category from session or POST data
    $category = $_POST['category1'] ?? '';
    $admissionDate = $_POST['dateadd'] ?? '';
    $AdDateone = $_POST['brn'] ?? '';
    $regNo = $_POST['regNo'] ?? '';
    $admissionDate1 = $_POST['admissionDate'] ?? '';
    $Rank = $_POST['jeep'] ?? '';
    $Roll = $_POST['RollNo'] ?? '';
    $Courses = $_POST['course1'] ?? '';
    $semester = $_POST['semester1'] ?? '';
    $Entry = $_POST['entry1'] ?? '';
    $TFW = $_POST['tfw1'] ?? '';
    $firstName = $_POST['firstname'] ?? '';
    $middleName = $_POST['middlename'] ?? '';
    $lastName = $_POST['lastname'] ?? '';
    $NameinHindi = $_POST['hindiname'] ?? '';
    $fathername = $_POST['fathername'] ?? '';
    $fathernameinHindi = $_POST['hindiname1'] ?? '';
    $mothername = $_POST['mothername'] ?? '';
    $mothernameinHindi = $_POST['hindiname2'] ?? '';
    $parentsMobile = $_POST['phone'] ?? '';
    $DOB = $_POST['dateone'] ?? '';
    $Email = $_POST['Email'] ?? '';
    $LandLineNumber = $_POST['phone1'] ?? '';
    $gender = $_POST['gender1'] ?? '';
    $category = $_POST['category1'] ?? '';
    $maritalStatus = $_POST['marital1'] ?? '';
    $bloodGroup = $_POST['blood1'] ?? '';
    $typeofAddress = $_POST['add11'] ?? '';
    $PhysicalDisability = $_POST['diva11'] ?? '';
    $PhysicalDisability1 = $_POST['div1'] ?? '';
    $Identification = $_POST['rec'] ?? '';
    $employmentDetail = $_POST['emp'] ?? '';
    $familyAnnualIncome = $_POST['salary'] ?? '';
    $UIDnumber = $_POST['aadhaar'] ?? '';
    $Correspondencecity = $_POST['ccity'] ?? '';
    $Correspondencestreet = $_POST['cstreet'] ?? '';
    $Correspondencestate = $_POST['cstate1'] ?? '';
    $Correspondencedistrict = $_POST['cdistrict'] ?? ''; // Changed from ccdistrict to cdistrict
    $Correspondencepincode = $_POST['cpincode'] ?? '';
    $Permanentcity = $_POST['pcity'] ?? '';
    $Permanentstreet = $_POST['pstreet'] ?? '';
    $Permanentstate = $_POST['pstate1'] ?? '';
    $Permanentdistrict = $_POST['ppdistrict'] ?? '';
    $Permanentpincode = $_POST['ppincode'] ?? '';
    $AccountNumber = $_POST['bankacc'] ?? '';
    $IfscCode = $_POST['IFSC'] ?? '';
    $Bankname = $_POST['BANKNAME'] ?? '';
    $Education = $_POST['edu'] ?? '';
    $schoolname = $_POST['schoolName'] ?? '';
    $Boardname = $_POST['Board'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $YearOfPassing = $_POST['yop1'] ?? '';
    $TypeOfMarks = $_POST['typeofmarks11'] ?? '';
    $Totalmarks = $_POST['totalmarks'] ?? '';
    $Obtainmarks = $_POST['obtainmarks'] ?? '';
    $PercentageOfObtainmarks = $_POST['percentage'] ?? '';
    
    // Get the fixed amounts from POST data
    $tuitionFixed = isset($_POST['tuitionFixed']) ? (float)$_POST['tuitionFixed'] : 0;
    $studentFundFixed = isset($_POST['studentFundFixed']) ? (float)$_POST['studentFundFixed'] : 0;
    $personalityFixed = isset($_POST['personalityFixed']) ? (float)$_POST['personalityFixed'] : 0;
    $securityFixed = isset($_POST['securityFixed']) ? (float)$_POST['securityFixed'] : 0;
    $formProcessingFixed = isset($_POST['formProcessingFixed']) ? (float)$_POST['formProcessingFixed'] : 0;
    
    // Handle tuition fee based on category
    $tuition_fee = 0;
    if (!in_array($category, ['SC', 'ST'])) {
        $tuition_fee = isset($_POST['tuition_fee']) ? (float)$_POST['tuition_fee'] : 0;
        if ($tuition_fee > 12500) {
            $tuition_fee = 12500;
        }
    }
    
    // Calculate total fixed amount
    $total_fixed = $tuitionFixed + $studentFundFixed + $personalityFixed + $securityFixed + $formProcessingFixed;

    // Get the paid amounts
    $tuition_fee = isset($_POST['tuition_fee']) ? (float)$_POST['tuition_fee'] : 0;
    $student_fund = isset($_POST['student_fund']) ? (float)$_POST['student_fund'] : 0;
    $personality_dev = isset($_POST['personality_dev']) ? (float)$_POST['personality_dev'] : 0;
    $security_deposit = isset($_POST['security_deposit']) ? (float)$_POST['security_deposit'] : 0;
    $form_processing = isset($_POST['form_processing']) ? (float)$_POST['form_processing'] : 0;
    
    // Calculate total paid and balance
    $total_paid = $tuition_fee + $student_fund + $personality_dev + $security_deposit + $form_processing;
    $balance = $total_fixed - $total_paid;

    $tuition_balance = isset($_POST['tuition_balance']) ? (float)$_POST['tuition_balance'] : 0;
    $student_fund_balance = isset($_POST['student_fund_balance']) ? (float)$_POST['student_fund_balance'] : 0;
    $personality_dev_balance = isset($_POST['personality_dev_balance']) ? (float)$_POST['personality_dev_balance'] : 0;
    $security_deposit_balance = isset($_POST['security_deposit_balance']) ? (float)$_POST['security_deposit_balance'] : 0;
    $form_processing_balance = isset($_POST['form_processing_balance']) ? (float)$_POST['form_processing_balance'] : 0;

    // Handle file upload
    $destinationfile = '';
    if(isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $ProfilePicture = $_FILES['file'];
        $filename = $ProfilePicture['name'];
        $fileerror = $ProfilePicture['error'];
        $filetmp = $ProfilePicture['tmp_name'];
        $fileext = explode('.', $filename);
        $filecheck = strtolower(end($fileext));
        $fileextstored = array('png','jpg','jpeg');
        
        if(in_array($filecheck, $fileextstored)) {
            $destinationfile = 'upload/ProfilePicture/'.$filename;
            move_uploaded_file($filetmp, $destinationfile);
        }
    }

    // First check if record with this regNo already exists
    $checkQuery = "SELECT * FROM `record` WHERE `RegNo` = '$regNo'";
    $result = mysqli_query($db, $checkQuery);

    if(mysqli_num_rows($result) > 0) {
        // Record already exists
        echo "<script>
            if(confirm('A record with this Registration Number already exists!')){
                history.back();
            }    
        </script>";
    } else {
        // Modify the query to match your actual database structure
        $Query = "INSERT INTO `record` (
            `AdmissionNo1`, `AdmissionNo2`, `RegNo`, `AdmissionDate`, `JeepRank`, `JeepRollNo`, 
            `Course`, `Semester`, `TypeofEntry`, `First Name`, `Middle Name`, `Last Name`, 
            `Father Name`, `Mother Name`, 
            `Parents Mobile No`, `Date Of Birth`, `Email`, `Land Line No`, `Gender`, `Category`, 
            `Correspondence City`, `Correspondence Street`, 
            `Correspondence State`, `Correspondence District`, `Correspondence pincode`, 
            `Permanent City`, `Permanent Street`, `Permanent State`, `Permanent District`,
            `Permanent Pincode`, `tuitionFixed`, `studentFundFixed`, 
            `personalityFixed`, `securityFixed`, `formProcessingFixed`, `Profile Picture`, 
            `tuition_fee`, `student_fund`, `personality_dev`, `security_deposit`, `form_processing`, 
            `total_fixed`, `total_paid`, `balance`
        ) VALUES (
            '$admissionDate', '$AdDateone', '$regNo', '$admissionDate1', '$Rank', '$Roll', 
            '$Courses', '$semester', '$Entry', '$firstName', '$middleName', '$lastName', 
            '$fathername', '$mothername', 
            '$parentsMobile', '$DOB', '$Email', '$LandLineNumber', '$gender', '$category', 
            '$Correspondencecity', '$Correspondencestreet', 
            '$Correspondencestate', '$Correspondencedistrict', '$Correspondencepincode', 
            '$Permanentcity', '$Permanentstreet', '$Permanentstate', '$Permanentdistrict', 
            '$Permanentpincode', '$tuitionFixed', '$studentFundFixed', 
            '$personalityFixed', '$securityFixed', '$formProcessingFixed', '$destinationfile', 
            '$tuition_fee', '$student_fund', '$personality_dev', '$security_deposit', '$form_processing', 
            '$total_fixed', '$total_paid', '$balance'
        )"; 
        
        if(mysqli_query($db, $Query)) {
            echo "<script>
                if(confirm('Your data submitted successfully')){
                    location.replace('index.php?page=studentList.php');
                }    
            </script>";
        } else {
            echo "Error: " . mysqli_error($db);
        }
    }
    mysqli_close($db);
}
?>
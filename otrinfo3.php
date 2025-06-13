<?php

$db = mysqli_connect("localhost","root","","schoolfeesys");

if(!$db)
{
    die("Connection failed: " . mysqli_connect_error());
}
	
	if(isset($_POST['submit']))
	{
    // Get category from session or POST data
	$category = $_POST['category1'] ?? '';
		$admissionDate = $_POST['dateadd'];
		$AdDateone = $_POST['brn'];
		$regNo=$_POST['regNo'];
		$admissionDate1=$_POST['admissionDate'];
		$Rank = $_POST['jeep'];
		$Roll = $_POST['RollNo'];
		$Courses = $_POST['course1'];
		$semester = $_POST['semester1'];
		$Entry = $_POST['entry1'];
		$TFW = $_POST['tfw1'];
		$firstName = $_POST['firstname'];
		$middleName = $_POST['middlename'];
		$lastName = $_POST['lastname'];
		$NameinHindi = $_POST['hindiname'];
		$fathername = $_POST['fathername'];
		$fathernameinHindi = $_POST['hindiname1'];
		$mothername = $_POST['mothername'];
		$mothernameinHindi = $_POST['hindiname2'];
		$parentsMobile = $_POST['phone'];
		$DOB= $_POST['dateone'];
		$Email= $_POST['Email'];
		$LandLineNumber = $_POST['phone1'];
		$gender= $_POST['gender1'];
		$category= $_POST['category1'];
		$maritalStatus= $_POST['marital1'];
		$bloodGroup= $_POST['blood1'];
		$typeofAddress= $_POST['add11'];
		$PhysicalDisability= $_POST['diva11'];
		$PhysicalDisability1= $_POST['div1'];
		$Identification= $_POST['rec'];
		$employmentDetail= $_POST['emp'];
		$familyAnnualIncome= $_POST['salary'];
		$UIDnumber= $_POST['aadhaar'];
		$Correspondencecity= $_POST['ccity'];
		$Correspondencestreet = $_POST['cstreet'];
		$Correspondencestate = $_POST['cstate1'];
 		$Correspondencedistrict= $_POST['ccdistrict'];
		$Correspondencepincode= $_POST['cpincode'];
		$Permanentcity = $_POST['pcity'];
		$Permanentstreet= $_POST['pstreet'];
		$Permanentstate = $_POST['pstate1'];
		$Permanentdistrict= $_POST['ppdistrict'];
		$Permanentpincode= $_POST['ppincode'];
		$AccountNumber= $_POST['bankacc'];
		$IfscCode= $_POST['IFSC'];
		$Bankname= $_POST['BANKNAME'];
		$Education= $_POST['edu'];
		$schoolname= $_POST['schoolName'];
		$Boardname= $_POST['Board'];
		$subject= $_POST['subject'];
		$YearOfPassing= $_POST['yop1'];
		$TypeOfMarks = $_POST['typeofmarks11'];
		$Totalmarks= $_POST['totalmarks'];
		$Obtainmarks= $_POST['obtainmarks'];
		$PercentageOfObtainmarks= $_POST['percentage'];
		// Get the fixed amounts from POST data
$tuitionFixed = isset($_POST['tuitionFixed']) ? (float)$_POST['tuitionFixed'] : 0;
$studentFundFixed = isset($_POST['studentFundFixed']) ? (float)$_POST['studentFundFixed'] : 0;
$personalityFixed = isset($_POST['personalityFixed']) ? (float)$_POST['personalityFixed'] : 0;
$securityFixed = isset($_POST['securityFixed']) ? (float)$_POST['securityFixed'] : 0;
$formProcessingFixed = isset($_POST['formProcessingFixed']) ? (float)$_POST['formProcessingFixed'] : 0;
		// Get fee details
        // Handle tuition fee based on category
        $tuition_fee = 0;
        if (!in_array($category, ['SC', 'ST'])) {
            $tuition_fee = (float)$_POST['tuition_fee'];
            if ($tuition_fee > 12500) {
                $tuition_fee = 12500;
            }
        }
        
// Calculate total fixed amount
$total_fixed = $tuitionFixed + $studentFundFixed + $personalityFixed + $securityFixed + $formProcessingFixed;

        // Get the paid amounts
$tuition_fee = (float)$_POST['tuition_fee'];
$student_fund = (float)$_POST['student_fund'];
$personality_dev = (float)$_POST['personality_dev'];
$security_deposit = (float)$_POST['security_deposit'];
$form_processing = (float)$_POST['form_processing'];
       // Calculate total paid and balance
$total_paid = $tuition_fee + $student_fund + $personality_dev + $security_deposit + $form_processing;
$balance = $total_fixed - $total_paid;


$tuition_balance = (float)$_POST['tuition_balance'];
$student_fund_balance = (float)$_POST['student_fund_balance'];
$personality_dev_balance = (float)$_POST['personality_dev_balance'];
$security_deposit_balance = (float)$_POST['security_deposit_balance'];
$form_processing_balance = (float)$_POST['form_processing_balance'];

		$ProfilePicture= $_FILES['file'];

		$filename = $ProfilePicture['name'];

		$fileerror = $ProfilePicture['error'];

		$filetmp = $ProfilePicture['tmp_name'];

		$fileext = explode('.',$filename);

		$filecheck = strtolower(end($fileext));

		$fileextstored = array('png','jpg','jpeg');
			$destinationfile = 'upload/ProfilePicture/'.$filename;
			
			move_uploaded_file($filetmp,$destinationfile);

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
				$Query = "INSERT INTO `record` (
					`AdmissionNo1`, `AdmissionNo2`, `RegNo`, `AdmissionDate`, `JeepRank`, `JeepRollNo`, 
					`Course`, `Semester`, `TypeofEntry`, `TFW`, `First Name`, `Middle Name`, `Last Name`, 
					`Name in Hindi`, `Father Name`, `Father Name in Hindi`, `Mother Name`, `Mother Name in Hindi`, 
					`Parents Mobile No`, `Date Of Birth`, `Email`, `Land Line No`, `Gender`, `Category`, 
					`Marital Status`, `Blood Group`, `Type of Address`, `Physical Handicapped`, 
					`Physical Handicapped Type`, `Identification Mark`, `Employment Detail`, 
					`Family Annual Income`, `Aadhaar No`, `Correspondence City`, `Correspondence Street`, 
					`Correspondence State`, `Correspondence District`, `Correspondence pincode`, 
					`Permanent City`, `Permanent Street`, `Permanent State`, `Permanent District`,
					`Permanent Pincode`, `Bank Account Number`, `IFSC Code`, `Bank Name`, `Education`, 
					`School Name`, `Board`, `Subject`, `Year of Passing`, `Type of Mark`, `Total Marks`, 
					`Obtain Marks or CGPA`, `Percentage`, `tuitionFixed`, `studentFundFixed`, 
					`personalityFixed`, `securityFixed`, `formProcessingFixed`, `Profile Picture`, 
					`tuition_fee`, `student_fund`, `personality_dev`, `security_deposit`, `form_processing`, 
					`total_fixed`, `total_paid`, `balance`,`tuition_balance`,`student_fund_balance`,`personality_dev_balance`,`security_deposit_balance`,`form_processing_balance`
				) VALUES (
					'$admissionDate', '$AdDateone', '$regNo', '$admissionDate1', '$Rank', '$Roll', 
					'$Courses', '$semester', '$Entry', '$TFW', '$firstName', '$middleName', '$lastName', 
					'$NameinHindi', '$fathername', '$fathernameinHindi', '$mothername', '$mothernameinHindi', 
					'$parentsMobile', '$DOB', '$Email', '$LandLineNumber', '$gender', '$category', 
					'$maritalStatus', '$bloodGroup', '$typeofAddress', '$PhysicalDisability', 
					'$PhysicalDisability1', '$Identification', '$employmentDetail', '$familyAnnualIncome', 
					'$UIDnumber', '$Correspondencecity', '$Correspondencestreet', '$Correspondencestate', 
					'$Correspondencedistrict', '$Correspondencepincode', '$Permanentcity', '$Permanentstreet', 
					'$Permanentstate', '$Permanentdistrict', '$Permanentpincode', '$AccountNumber', 
					'$IfscCode', '$Bankname', '$Education', '$schoolname', '$Boardname', '$subject', 
					'$YearOfPassing', '$TypeOfMarks', '$Totalmarks', '$Obtainmarks', '$PercentageOfObtainmarks', 
					'$tuitionFixed', '$studentFundFixed', '$personalityFixed', '$securityFixed', 
					'$formProcessingFixed', '$destinationfile', '$tuition_fee', '$student_fund', 
					'$personality_dev', '$security_deposit', '$form_processing', '$total_fixed', 
					'$total_paid', '$balance','$tuition_balance','$student_fund_balance','$personality_dev_balance','$security_deposit_balance','$form_processing_balance'
				)"; 
		 	
            if(mysqli_query($db,$Query)) ////////// if block start //////////
		 	{
				//header('location:form.php');
				echo "<script>
					if(confirm('Your data submitted successfully')){
						location.replace('index.php?page=new_admission.php');
					}	
				</script>";
				echo $firstname."<script type='text/javascript>alert('Your information has been successfully saved')</script>'";
		 	}
		 	else
		 	{
				 echo "Error: " . mysqli_error($db);
		 	}
		}
		mysqli_close($db);

	}
?>

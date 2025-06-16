<?php 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if transfer data exists
if (!isset($_SESSION['transfer_data'])) {
    $_SESSION['error'] = "No transfer data found. Please start the transfer process again.";
}

include 'config.php';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Administrator';

// Fetch the last ID
$sql = "SELECT MAX(id) AS last_id FROM record";
$result = $db->query($sql);
$next_id = 1;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['last_id']) {
        $next_id = $row['last_id'] + 1;
    }
}

// Get the RollNo from URL parameter
$rollNo = isset($_GET['gid']) ? $_GET['gid'] : '';
$transferData = [];

if ($rollNo) {
    $sql1 = "SELECT * FROM `polyregis` WHERE RollNo = ?";
    $stmt = $db->prepare($sql1);
    $stmt->bind_param("s", $rollNo);
    $stmt->execute();
    $result = $stmt->get_result();
    $transferData = $result->fetch_assoc();
    
    // Split applicant name into parts
    $nameParts = explode(' ', $transferData['applicantName'] ?? '');
    print_r($nameParts);
    $firstName = $nameParts[0] ?? '';
    $middleName = $nameParts[1] ?? '';
    $lastName =  $nameParts[2] ?? '';
    $fatherName = $transferData['fatherName'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Admission Form</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .form-header {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .form-section {
            margin-bottom: 40px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .section-title {
            background-color: #343a40;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
        }
        .btn-submit {
            padding: 10px 30px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .fee-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        #output {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 5px;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        .note-box {
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="form-container">
            <div class="form-header text-center">
                <h2 class="text-primary">Student Admission Form</h2>
                <p class="text-muted">Please fill all the required details carefully</p>
            </div>

            <form action="otrinfo31.php" name="form" id="form" method="post" enctype="multipart/form-data">
                
                <!-- Admission Details Section -->
                <div class="form-section">
                    <div class="section-title">Admission Details</div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="Admission" class="form-label">Admission No - ESTC / POLY /</label>
                            <div class="d-flex align-items-center">
                                <input type="text" class="form-control w-25 me-2" name="dateadd" id="dateadd" placeholder="YYY-YY" onkeyup="addHyphen(this)" />
                                <span>/</span>
                                <input type="text" class="form-control w-10 mx-2" name="brn" id="brn" readonly />
                                <span>/</span>
                                <input type="text" class="form-control w-25 ms-2" name="regNo" id="regNo" value="<?php echo $next_id; ?>" readonly />
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="admissionDate" class="form-label">Admission Date</label>
                            <input type="date" name="admissionDate" id="admissionDate" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="entry" class="form-label">प्रवेश का प्रकार (Type of admission)</label>
                            <select name="entry" id="entry" class="form-select" onchange="showt();updateAdmissionType()">
                                <option selected>Select Type of admission</option>
                                <option value="Direct Entry">Direct Entry</option>
                                <option value="Lateral Entry">Lateral Entry</option>
                                <option value="Jeep Entry">Jeep Entry</option>
                            </select>
                            <input type="hidden" name="entry1" id="entry1" value="" />
                        </div>
                    </div>

                    <div class="row mb-3" id="jeep1" style="display:none;">
                        <div class="col-md-12">
                            <label for="jeep" class="form-label">जीप रैंक (JEEP RANK - FIRST YEAR)</label>
                            <input type="text" class="form-control" name="jeep" id="jeep" placeholder="Enter JEEP RANK" />
                        </div>
                    </div>

                    <div class="row mb-3" id="RollNo" style="display:none;">
                        <div class="col-md-12">
                            <label for="RollNo1" class="form-label">जीप अनुक्रमांक (JEEP ROLL - FIRST YEAR)</label>
                            <input type="text" class="form-control" name="RollNo" id="RollNo1" placeholder="Enter JEEP Roll No" />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="course" class="form-label">पाठ्यक्रम जिसमे प्रवेश लेना है (Select Course)</label>
                            <select name="course" id="course" class="form-select" onchange="transfer();updateBranchCode()">
                                <option value="Course" selected>Select Course</option>
                                <option value="Diploma in Mechanical Engineering">Diploma in Mechanical Engineering</option>
                                <option value="Diploma in Electrical Engineering">Diploma in Electrical Engineering</option>
                                <option value="Diploma in Computer Science Engineering">Diploma in Computer Science Engineering</option>
                            </select>
                            <input type="hidden" name="course1" id="course1" value="" />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="semester" class="form-label">सेमेस्टर (SEMESTER) / वर्ष (YEAR)</label>
                            <select class="form-select" name="semester" id="semester" onchange="transfer()">
                                <option value="Semer" selected>Select Semester</option>
                                <option value="Sem1">Sem1</option>
                                <option value="Sem3">Sem3</option>
                            </select>
                            <input type="hidden" name="semester1" id="semester1" value="" />
                        </div>
                    </div>
                </div>

                <!-- Personal Details I Section -->
                <div class="form-section">
                    <div class="section-title">Personal Details - Part 1</div>
                    
                    <div class="note-box">
                        <p class="h5">NOTE: PLEASE FILL UP THE ADMISSION FORM IN CAPITAL LETTERS, FILL THE NAME AS PER 10<sup>th</sup> CERTIFICATE</p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" name="firstname" class="form-control" placeholder="Enter First Name" id="firstname" style="text-transform: uppercase;" value="<?= htmlspecialchars($firstName) ?>" />
                        </div>
                        <div class="col-md-4">
                            <label for="middlename" class="form-label">Middle Name</label>
                            <input type="text" name="middlename" class="form-control" placeholder="Enter Middle Name" id="middlename" style="text-transform: uppercase;" value="<?= htmlspecialchars($middleName) ?>" />
                        </div>
                        <div class="col-md-4">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" name="lastname" class="form-control" placeholder="Enter Last Name" id="lastname" style="text-transform: uppercase;" value="<?= htmlspecialchars($lastName) ?>" />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fathername" class="form-label">Father's Name</label>
                            <input type="text" class="form-control" placeholder="Enter Father Name" name="fathername" id="fathername" style="text-transform:uppercase;" value="<?= htmlspecialchars($fatherName) ?>" /> 
                        </div>
                        <div class="col-md-6">
                            <label for="mothername" class="form-label">Mother's Name</label>
                            <input type="text" class="form-control" name="mothername" id="mothername" placeholder="Enter Mother's Name" style="text-transform:uppercase;" />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">माता / पिता का मोबाइल नंबर (PARENTS MOBILE NUMBER)</label>
                            <input type="tel" class="form-control" maxlength="10" name="phone" id="phone" placeholder="Enter 10 digit Mobile Number" />
                        </div>
                        <div class="col-md-6">
                            <label for="dateone" class="form-label">जन्मतिथि (DOB)</label>
                            <input type="date" class="form-control" name="dateone" id="dateone" placeholder="Enter Date Of Birth" />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="Email" class="form-label">ई-मेल (E-mail)</label>
                            <input type="email" class="form-control" name="Email" id="Email" placeholder="Enter E-Mail" style="text-transform:uppercase;" />
                        </div>
                    </div>
                </div>

                <!-- Personal Details II Section -->
                <div class="form-section">
                    <div class="section-title">Personal Details - Part 2</div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone1" class="form-label">छात्र / छात्रा का मोबाइल नंबर (CANDIDATE'S MOBILE NUMBER)</label>
                            <input type="tel" id="phone1" name="phone1" maxlength="10" placeholder="Enter Mobile number" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label">लिंग (GENDER)</label>
                            <select class="form-select" name="gender" id="gender" onchange="transfer()">
                                <option selected>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <input type="hidden" name="gender1" id="gender1" value="" />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="category" class="form-label">जाती (CATEGORY)</label>
                            <select class="form-select" name="category" id="category" onchange="transfer()">
                                <option selected>Select category</option>
                                <option value="GENERAL">सामान्य (GENERAL)</option>
                                <option value="SC">अनुसूचित जाति (SC)</option>
                                <option value="ST">अनुसूचित जनजाति (ST)</option>
                                <option value="OBC">अन्य पिछड़ा वर्ग (OBC)</option>
                                <option value="EWS">आर्थिक रूप से कमजोर वर्ग (EWS)</option>
                                <option value="MINORITY">अल्पसंख्यक (MINORITY)</option>
                                <option value="EX-SERVICEMEN DEPENDENT">भूतपूर्व सैनिक EX-SERVICEMEN DEPENDENT</option>
                                <option value="DEPENDENT OF FREEDOM FIGHTER">DEPENDENT OF FREEDOM FIGHTER</option>
                            </select>
                            <input type="hidden" name="category1" id="category1" value="" />
                        </div>
                    </div>
                </div>

                <!-- Address Details Section -->
                <div class="form-section">
                    <div class="section-title">Address Details</div>
                    
                    <div class="section-title">पत्र व्यवहार का पता (CORRESPONDENCE ADDRESS)</div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ccity" class="form-label">शहर (CITY) / गांव (VILLAGE)</label>
                            <input type="text" id="ccity" class="form-control" name="ccity" placeholder="Enter City Name" style="text-transform:uppercase;" />
                        </div>
                        <div class="col-md-6">
                            <label for="cstreet" class="form-label">गली (STREET)</label>
                            <input type="text" id="cstreet" class="form-control" name="cstreet" placeholder="Enter Street Name" style="text-transform:uppercase;" />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cstate" class="form-label">राज्य (STATE)</label>
                            <select class="form-select" id="cstate" name="cstate" onchange="transfer();fetchDistricts1(this.value)">
                                <option selected>Select State</option>
                                <?php
                                    $sql = "SELECT id, name FROM states ORDER BY name ASC";
                                    $query = mysqli_query($db, $sql);
                                    if ($query) {
                                        while ($state = mysqli_fetch_assoc($query)) {
                                            echo '<option value="' . $state['id'] . '|' . $state['name'] . '">' . $state['name'] . '</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="cdistrict">District</label>
                            <select class="form-select" id="cdistrict" name="cdistrict" onchange="shownr()">
                                <option value="">Select District</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cpincode">Pincode</label>
                            <input type="text" id="cpincode" name="cpincode" class="form-control" placeholder="Enter Pincode" />
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="same As" id="sameAs" onchange="sameth();"/>
                                <label class="form-check-label" for="sameAs">Same as Correspondence Address</label>
                            </div>
                        </div>
                    </div>

                    <div class="section-title">पूर्ण स्थाई पता (PERMANENT ADDRESS)</div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="pcity">शहर (CITY) / गांव (VILLAGE)</label>
                            <input type="text" name="pcity" class="form-control" id="pcity" placeholder="Enter City Name" style="text-transform: uppercase;" />
                        </div>
                        <div class="col-md-6">
                            <label for="pstreet">गली (STREET)</label>
                            <input type="text" name="pstreet" class="form-control" id="pstreet" placeholder="Enter Street Name" style="text-transform: uppercase;" />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="pstate" class="form-label">राज्य (STATE)</label>
                            <select class="form-select" id="pstate" name="pstate" onchange="transfer();fetchDistricts(this.value)">
                                <option selected>Select State</option>
                                <?php
                                    $sql = "SELECT id, name FROM states ORDER BY name ASC";
                                    $query = mysqli_query($db, $sql);
                                    if ($query) {
                                        while ($state = mysqli_fetch_assoc($query)) {
                                            echo '<option value="' . $state['id'] . '|' . $state['name'] . '">' . $state['name'] . '</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="pdistrict">District</label>
                            <select class="form-select" id="pdistrict" name="pdistrict">
                                <option value="">Select District</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ppincode">Pincode</label>
                            <input type="text" name="ppincode" class="form-control" id="ppincode" placeholder="Enter Pincode" />
                        </div>
                    </div>
                </div>

                <!-- Fee Details Section -->
                <div class="form-section">
                    <div class="section-title">Fee Details</div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Student Information</h5>
                                    <p class="card-text">
                                        <strong>Semester:</strong> <span id="sem1"></span><br>
                                        <strong>Year:</strong> <span id="year"></span><br>
                                        <strong>Category:</strong> <span id="displayCategory"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Fee Structure</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered fee-table">
                                            <thead>
                                                <tr>
                                                    <th>Fee Component</th>
                                                    <th>Fixed Amount (₹)</th>
                                                    <th>Amount Paid (₹)</th>
                                                    <th>Balance (₹)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Tuition Fee</td>
                                                    <td>
                                                        <span id="tuitionFixed">25,000</span>
                                                        <input type="hidden" name="tuitionFixed" id="tuitionFixedValue" value="25000">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control tuition-fee" 
                                                            name="tuition_fee" id="tuition_fee_input" 
                                                            oninput="calculateTotals()">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control tuition-balance" 
                                                            name="tuition_balance" id="tuition_balance" 
                                                            style="background-color: #f0f0f0;">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Student Fund Fee</td>
                                                    <td>
                                                        <span id="studentFundFixed">7,000</span>
                                                        <input type="hidden" name="studentFundFixed" id="studentFundFixedValue" value="7000">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control student-fund" 
                                                            name="student_fund" oninput="calculateTotals()">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control student-fund-balance" 
                                                            name="student_fund_balance" id="student_fund_balance" 
                                                            style="background-color: #f0f0f0;">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Personality Development</td>
                                                    <td>
                                                        <span id="personalityFixed">2,000</span>
                                                        <input type="hidden" name="personalityFixed" id="personalityFixedValue" value="2000">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control personality-dev" 
                                                            name="personality_dev" oninput="calculateTotals()">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control personality-dev-balance" 
                                                            name="personality_dev_balance" id="personality_dev_balance" 
                                                        style="background-color: #f0f0f0;">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Security Deposit (Refundable)</td>
                                                    <td>
                                                        <span id="securityFixed">2,000</span>
                                                        <input type="hidden" name="securityFixed" id="securityFixedValue" value="2000">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control security-deposit" 
                                                            name="security_deposit" oninput="calculateTotals()">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control security-deposit-balance" 
                                                            name="security_deposit_balance" id="security_deposit_balance" 
                                                        style="background-color: #f0f0f0;">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Other's Fee</td>
                                                    <td>
                                                        <span id="formProcessingFixed">0</span>
                                                        <input type="hidden" name="formProcessingFixed" id="formProcessingFixedValue" value="0">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-processing" 
                                                            name="form_processing" id="form_processing_input" 
                                                            style="background-color: #f0f0f0;" oninput="calculateTotals()">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-processing-balance" 
                                                            name="form_processing_balance" id="form_processing_balance" 
                                                        style="background-color: #f0f0f0;">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" id="submit" name="submit" class="btn btn-primary btn-submit">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="js/displaydata.js"></script>
    <script type="text/javascript" src="js/gtransapi.js"></script>  
    <script type="text/javascript" src="js/translator.js"></script>
    
    <script>
// Keep only the first calculateTotals() function
function calculateTotals() {
    // Get fixed amounts
    const tuitionFixed = parseFloat(document.getElementById('tuitionFixedValue').value) || 0;
    const studentFundFixed = parseFloat(document.getElementById('studentFundFixedValue').value) || 0;
    const personalityFixed = parseFloat(document.getElementById('personalityFixedValue').value) || 0;
    const securityFixed = parseFloat(document.getElementById('securityFixedValue').value) || 0;
    const formProcessingFixed = parseFloat(document.getElementById('formProcessingFixedValue').value) || 0;

    // Get paid amounts
    const tuition_fee = parseFloat(document.querySelector('.tuition-fee').value) || 0;
    const student_fund = parseFloat(document.querySelector('.student-fund').value) || 0;
    const personality_dev = parseFloat(document.querySelector('.personality-dev').value) || 0;
    const security_deposit = parseFloat(document.querySelector('.security-deposit').value) || 0;
    const form_processing = parseFloat(document.getElementById('form_processing_input').value) || 0;

    // Calculate individual balances
    const tuitionBalance = tuitionFixed - tuition_fee;
    const studentFundBalance = studentFundFixed - student_fund;
    const personalityDevBalance = personalityFixed - personality_dev;
    const securityDepositBalance = securityFixed - security_deposit;
    const formProcessingBalance = formProcessingFixed - form_processing;

    // Update individual balance fields
    document.getElementById('tuition_balance').value = tuitionBalance.toLocaleString('en-IN');
    document.getElementById('student_fund_balance').value = studentFundBalance.toLocaleString('en-IN');
    document.getElementById('personality_dev_balance').value = personalityDevBalance.toLocaleString('en-IN');
    document.getElementById('security_deposit_balance').value = securityDepositBalance.toLocaleString('en-IN');
    document.getElementById('form_processing_balance').value = formProcessingBalance.toLocaleString('en-IN');

    // Calculate totals
    const total_paid = tuition_fee + student_fund + personality_dev + security_deposit + form_processing;
    const total_fixed = tuitionFixed + studentFundFixed + personalityFixed + securityFixed + formProcessingFixed;
    const total_balance = total_fixed - total_paid;

    // Update total fields
    document.getElementById('total-paid').value = total_paid.toLocaleString('en-IN');
    document.getElementById('balance').value = total_balance.toLocaleString('en-IN');
}

// Keep the rest of the code (updateFeeStructure, event listeners) unchanged
document.addEventListener('DOMContentLoaded', function() {
    updateFeeStructure();
});
</script>
    
    <script>
        function sameth() {
            if ($('#sameAs').is(':checked')) {
                // Show/hide district fields
                document.getElementById('pdistrict').style.display = 'none';
                document.getElementById('pdistrict1').style.display = 'block';
            
                // Copy correspondence address to permanent address
                $('#pcity').val($('#ccity').val());
                $('#pstreet').val($('#cstreet').val());
                $('#pstate').val($('#cstate').val());
                $('#ppincode').val($('#cpincode').val());
                
                // Handle state names (for both correspondence and permanent)
                var cstateValue = $('#cstate').val();
                if (cstateValue) {
                    var parts = cstateValue.split('|');
                    $('#cstate1').val(parts[1]); // Store correspondence state name
                    $('#pstate1').val(parts[1]); // Also store in permanent state name
                }
                
                // Handle district values - copy both the ID and name
                var districtId = $('#cdistrict').val();
                var districtName = $('#cdistrict option:selected').text();
                
                $('#pdistrict').val(districtId); // Set the ID in the hidden dropdown
                $('#pdistrict1').val(districtName); // Set the name in the visible field
                $('#ppdistrict').val(districtName); // Set the name in the hidden field
                
            } else {
                // Clear permanent address
                $('#pcity').val('');
                $('#pstreet').val('');
                $('#pstate').val('');
                $('#pdistrict').val('');
                $('#pdistrict1').val('');
                $('#ppdistrict').val('');
                $('#ppincode').val('');
                $('#pstate1').val('');
                
                // Restore district field visibility
                document.getElementById('pdistrict').style.display = 'block';
                document.getElementById('pdistrict1').style.display = 'none';
            }
        }
    </script>

    <script>
        function fetchDistricts(stateValue) {
            if (stateValue) {
                var parts = stateValue.split('|');
                var stateId = parts[0]; 
                console.log("State selected:", stateId);
                // Store the state name in the hidden field
                $('#pstate1').val(parts[1]);
                $.ajax({
                    url: 'fetch_districts.php', // PHP script to fetch districts
                    type: 'GET',
                    data: { state_id: stateId },
                    success: function(response) {
                        var districts = JSON.parse(response);
                        var districtDropdown = $('#pdistrict');
                        districtDropdown.empty(); // Clear existing options
                        districtDropdown.append('<option value="">Select District</option>'); // Add default option

                        // Populate the district dropdown
                        districts.forEach(function(district) {
                            districtDropdown.append('<option value="' + district.id + '">' + district.name + '</option>');
                        });
                        // Add change event handler to update hidden field
                        districtDropdown.change(function() {
                            var districtName = $(this).find('option:selected').text();
                            $('#ppdistrict').val(districtName);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching districts: " + error);
                    }
                });
            } else {
                $('#pdistrict').empty().append('<option value="">Select District</option>'); // Reset if no state is selected
            }
        }

        function fetchDistricts1(stateValue) {
            console.log("State selected:", stateId);
            if (stateValue) {
                var parts = stateValue.split('|');
                var stateId = parts[0]; 
                console.log("State selected:", stateId);
                // Store the state name in the hidden field
                $('#cstate1').val(parts[1]);
                $.ajax({
                    url: 'fetch_districts1.php', // PHP script to fetch districts
                    type: 'GET',
                    data: { state_id: stateId },
                    success: function(response) {
                        console.log("Raw response:", response); // Add this line
                        var districts = JSON.parse(response);
                        var districtDropdown = $('#cdistrict');
                        districtDropdown.empty(); // Clear existing options
                        districtDropdown.append('<option value="">Select District</option>'); // Add default option

                        // Populate the district dropdown
                        districts.forEach(function(district) {
                            districtDropdown.append('<option value="' + district.id + '">' + district.name + '</option>');
                        });
                        
                        // Add change event handler to update hidden field
                        districtDropdown.change(function() {
                            var districtName = $(this).find('option:selected').text();
                            $('#ccdistrict').val(districtName);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching districts: " + error);
                    }
                });
            } else {
                $('#cdistrict').empty().append('<option value="">Select District</option>'); // Reset if no state is selected
                $('#ccdistrict').val(''); // Clear hidden field when no state is selected
            }
        }
    </script>

    <script>
        function addHyphen (element) {
            let ele = document.getElementById(element.id);
            ele = ele.value.split('-').join('');    // Remove dash (-) if mistakenly entered.

            let finalVal = ele.match(/.{1,4}/g).join('-');
            document.getElementById(element.id).value = finalVal;
        }
        
        function shownr(){
            document.getElementById('ccdistrict').value;
            console.log("The value of cdisctirct is: ",document.getElementById('ccdistrict').value);
        }
    </script>

    <script>
       function showt(){
            const entryType = document.getElementById('entry').value;
            document.getElementById('entry1').value = entryType;
        
            // Get the course select element
            const courseSelect = document.getElementById('course');
        
            if(entryType == "Jeep Entry") {
                document.getElementById('tfw1').value = document.getElementById('tfw').value;
                document.getElementById('jeep1').style.display = "block";
                document.getElementById('RollNo1').style.display = "block";
                document.getElementById('tfw1').style.display = "block";
                
                // Reset course options to original
                resetCourseOptions();
            } 
            else if(entryType == "Lateral Entry") {
                document.getElementById('tfw1').value = document.getElementById('tfw').value;
                document.getElementById('jeep1').style.display = "block";
                document.getElementById('RollNo1').style.display = "block";
                document.getElementById('tfw1').style.display = "none";
                
                // Update course options with "Lateral Entry" suffix
                updateCourseOptions('Lateral Entry');
            } 
            else if(entryType == "Direct Entry"){
                document.getElementById('tfw1').value = document.getElementById('tfw').value;
                document.getElementById('jeep1').style.display = "none";
                document.getElementById('RollNo').style.display = "none";
                document.getElementById('tfw1').style.display = "none";
                
                // Reset course options to original
                resetCourseOptions();
            }
            const admissionType = document.getElementById('entry').value;
            const semesterSelect = document.getElementById('semester');
            
            // Clear existing options except the first one
            while (semesterSelect.options.length > 1) {
                semesterSelect.remove(1);
            }
            
            // Add options based on admission type
            if (admissionType === 'Lateral Entry') {
                // Add Sem3 to Sem6 for Lateral Entry
                for (let i = 3; i <= 6; i++) {
                    const option = document.createElement('option');
                    option.value = `Sem${i}`;
                    option.text = `Sem${i}`;
                    semesterSelect.add(option);
                }
            } else {
                // Add Sem1 to Sem6 for all other admission types
                for (let i = 1; i <= 6; i++) {
                    const option = document.createElement('option');
                    option.value = `Sem${i}`;
                    option.text = `Sem${i}`;
                    semesterSelect.add(option);
                }
            }
            
            // Reset to default selection
            semesterSelect.selectedIndex = 0;
        }

        // Also call this function on page load to set initial state
        window.onload = function() {
            showt();
        };

        // Function to update course options with suffix
        function updateCourseOptions(suffix) {
            const courseSelect = document.getElementById('course');
            const originalOptions = [
                "Diploma in Mechanical Engineering",
                "Diploma in Electrical Engineering",
                "Diploma in Computer Science Engineering"
            ];
            
            // Clear existing options (keep first default option)
            while(courseSelect.options.length > 1) {
                courseSelect.remove(1);
            }
            
            // Add modified options
            originalOptions.forEach(course => {
                const option = document.createElement('option');
                option.value = `${course} ${suffix}`;
                option.textContent = `${course} ${suffix}`;
                courseSelect.appendChild(option);
            });
        }

        // Function to reset course options to original
        function resetCourseOptions() {
            const courseSelect = document.getElementById('course');
            const originalOptions = [
                "Diploma in Mechanical Engineering",
                "Diploma in Electrical Engineering",
                "Diploma in Computer Science Engineering"
            ];
            
            // Clear existing options (keep first default option)
            while(courseSelect.options.length > 1) {
                courseSelect.remove(1);
            }
            
            // Add original options
            originalOptions.forEach(course => {
                const option = document.createElement('option');
                option.value = course;
                option.textContent = course;
                courseSelect.appendChild(option);
            });
        }
    </script>
    
    <script>
        function transfer(){
            document.getElementById('course1').value = document.getElementById('course').value;
            document.getElementById('semester1').value = document.getElementById('semester').value;
            document.getElementById('gender1').value = document.getElementById('gender').value;
            document.getElementById('displayCategory').innerHTML = document.getElementById('category').value;
            var stateValue = $('#cstate').val();
            if (stateValue) {
                var parts = stateValue.split('|');
                $('#cstate1').val(parts[1]); // Store the name part
            }
            var stateValue = $('#pstate').val();
            if (stateValue) {
                var parts = stateValue.split('|');
                $('#pstate1').val(parts[1]); // Store the name part
            }
            document.getElementById('pdistrict1').value = document.getElementById('ppdistrict').value;
            document.getElementById('sem1').innerHTML = document.getElementById('semester').value;
            if(document.getElementById('sem1').innerHTML == "Sem1" || document.getElementById('sem1').innerHTML == "Sem2"){
                document.getElementById('year').innerHTML = "First_Year";
            }
            else if(document.getElementById('sem1').innerHTML == "Sem3" || document.getElementById('sem1').innerHTML == "Sem4"){
                document.getElementById('year').innerHTML = "Second_Year";
            }
            else{
                document.getElementById('year').innerHTML = "Third_Year";
            }
            document.getElementById('category1').value = document.getElementById('category').value;
            updateFeeStructure(); 
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('course').addEventListener('change', updateBranchCode);
            document.getElementById('entry').addEventListener('change', updateAdmissionType);
        });

        function updateBranchCode() {
            const courseSelect = document.getElementById('course');
            const branchInput = document.getElementById('brn');
            const admissionType = document.getElementById('entry').value;
            const selectedCourse = courseSelect.value;
            
            // Determine base branch code
            let branchCode = '';
            if (selectedCourse.includes('Mechanical')) {
                branchCode = 'M';
            } else if (selectedCourse.includes('Electrical')) {
                branchCode = 'E';
            } else if (selectedCourse.includes('Computer')) {
                branchCode = 'C';
            }
            
            // Preserve -LE suffix if admission type is Lateral Entry
            if (admissionType === 'Lateral Entry') {
                branchCode += '-LE';
            }
            
            branchInput.value = branchCode;
        }

        function updateAdmissionType() {
            const admissionType = document.getElementById('entry').value;
            const branchInput = document.getElementById('brn');
            let currentValue = branchInput.value;
            
            // Remove existing -LE suffix if any
            currentValue = currentValue.replace(/-LE$/, '');
            
            // Add -LE suffix only for Lateral Entry
            if (admissionType === 'Lateral Entry') {
                // Get the base branch code (M/E/C) if course is already selected
                const courseSelect = document.getElementById('course');
                if (courseSelect.value !== 'Course') {
                    currentValue = currentValue.charAt(0); // Keep just the first character (M/E/C)
                }
                currentValue += '-LE';
            }
            
            branchInput.value = currentValue;
        }
    </script>
</body>
</html>
<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';
// Get enquiry data if exists
$enquiryData = [];
if (isset($_GET['enquiry_id'])) {
    $enquiryId = (int)$_GET['enquiry_id'];
    $stmt = $db->prepare("SELECT * FROM enquiry WHERE id = ?");
    $stmt->bind_param("i", $enquiryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $enquiryData = $result->fetch_assoc();
}

// Security check if data not found
if (empty($enquiryData) && isset($_GET['enquiry_id'])) {
    die("Invalid enquiry ID or record not found");
}

// Get the username from session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Administrator';

// Fetch the last ID
$sql = "SELECT MAX(id) AS last_id FROM record";
$result = $db->query($sql);

$next_id = 1; // Default if no records exist
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['last_id']) {
        $next_id = $row['last_id'] + 1;
    }
}

// Razorpay API Key (Note: In production, consider storing this securely)
$razorpayKeyId = 'rzp_live_5Frzcq3BYdZAWL';
$amount = 200; // Registration fee amount
$currency = 'INR';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESTC Polytechnic Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
        }
        
        .container {
            max-width: 800px;
        }
        
        .payment-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 20px;
        }
        
        .transaction-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0fff4;
            border-radius: 6px;
            border-left: 4px solid #48bb78;
            display: none;
        }
        
        .transaction-info.show {
            display: block;
        }
        
        .transaction-info h4 {
            margin-top: 0;
            color: #2f855a;
        }
        
        .transaction-details {
            margin-top: 10px;
        }
        
        .transaction-details p {
            margin: 5px 0;
        }
        
        .pay-button {
            background-color: #4299e1;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .pay-button:hover {
            background-color: #3182ce;
        }
        
        .razorpay-logo {
            text-align: center;
            margin-top: 20px;
        }
        
        .razorpay-logo img {
            height: 30px;
        }
        
        .form-title {
            color: #2a4365;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h3 class="text-center text-primary">ESTC POLYTECHNIC - RAMNAGAR</h3>
        <p class="text-center">Autonomous Organization Under Ministry of MSME, Govt. of India</p>
        <p class="text-center"><b>AICTE Approved || UBTER Affiliated</b></p>

        <form class="border p-4 rounded shadow payment-container" action="otr1info.php" method="POST" enctype="multipart/form-data" id="main-form">
            <h4 class="form-title">Registration Application Form</h4>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="branch" class="form-label">Select Branch</label>
                        <select class="form-select" id="branch" name="branch" onchange="showBranch();">
                            <option value="" selected>-Select Branch-</option>
                            <option value="Mechanical">Mechanical</option>
                            <option value="Electrical">Electrical</option>
                            <option value="Computer Science">Computer Science</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="course" class="form-label">Select Course</label>
                        <select class="form-select" id="course" name="course">
                            <option value="" selected>---Select any course---</option>
                            <option value="MechanicalEngineering">Diploma in Mechanical Engineering</option>
                            <option value="MechanicalEngineeringLateral">Diploma in Mechanical Engineering Lateral Entry</option>
                            <option value="ElectricalEngineering">Diploma in Electrical Engineering</option>
                            <option value="ElectricalEngineeringLateral">Diploma in Electrical Engineering Lateral Entry</option>
                            <option value="ComputerScienceEngineering">Diploma in Computer Science Engineering</option>
                            <option value="ComputerScienceEngineeringLateral">Diploma in Computer Science Engineering Lateral Entry</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="applicantName" class="form-label">Applicant Name *</label>
                        <input type="text" class="form-control" id="applicantName" name="applicantName" value="<?= htmlspecialchars($enquiryData['firstName'] . ' ' . ($enquiryData['middleName'] ?? '') . ' ' . $enquiryData['lastName']) ?>" placeholder="As per high school certificate" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="fatherName" class="form-label">Father's Name *</label>
                        <input type="text" class="form-control" id="fatherName" name="fatherName" value="<?= htmlspecialchars($enquiryData['fatherName'] ?? '') ?>" placeholder="As per high school certificate" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="state" class="form-label">State *</label>
                        <select class="form-select" id="state" name="state" onchange="fetchDistricts1(this.value)">
                            <option value="<?= htmlspecialchars($enquiryData['state'] ?? '') ?>"><?= htmlspecialchars($enquiryData['state_name'] ?? '') ?></option>
                            <?php
                                // Fetch states from the database
                                $sql = "SELECT id, name FROM states ORDER BY name ASC";
                                $query = mysqli_query($db, $sql);
                                if ($query) {
                                    while ($state = mysqli_fetch_assoc($query)) {
                                        echo '<option value="' . $state['id'] . '|' . $state['name'] . '">' . $state['name'] . '</option>';
                                    }
                                } else {
                                    echo '<option value="">Error fetching states</option>';
                                }
                            ?>
                        </select><input type="hidden" name="cstate1" id="cstate1" value="" />
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="district" class="form-label">District *</label>
                        <select class="form-control" id="cdistrict" name="cdistrict">
                            <option value="<?=htmlspecialchars($enquiryData['district'] ?? '') ?>"><?=htmlspecialchars($enquiryData['district'] ?? '') ?></option>
                            <option value="">Select District</option>
                        </select><input type="hidden" name="ccdistrict" id="ccdistrict" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="category" class="form-label">Category *</label>
                    <input type="radio" name="category" id="GEN" value="GEN" class="form-check-input">
                    <label for="GEN">GEN</label>
                    <input type="radio" name="category" id="Min" value="Min" class="form-check-input">
                    <label for="Min">Min</label>
                    <input type="radio" name="category" id="OBC" value="OBC" class="form-check-input">
                    <label for="OBC">OBC</label>
                    <input type="radio" name="category" id="SC" value="SC" class="form-check-input">
                    <label for="SC">SC</label>
                    <input type="radio" name="category" id="ST" value="ST" class="form-check-input">
                    <label for="ST">ST</label>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="dob" class="form-label">Date of Birth *</label>
                        <input type="date" class="form-control" id="dob" name="dob" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div id="admissionTypeDiv" class="mb-3">
                        <label for="admissionType" class="form-label">Type of Admission *</label>
                        <select class="form-select" id="admissionType" name="admissionType" onchange="updateSemester()">
                            <option value="">---Select Type of Entry ----</option>
                            <option value="Direct">Direct Entry</option>
                            <option value="Lateral">Lateral Entry</option>
                            <option value="Jeep DirectEntry">Jeep Direct Entry</option>
                            <option value="Jeep Through Council">Jeep Through Council</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="rank" class="form-label">Jeep Rank(For First Year Students)</label>
                        <input type="text" name="RollNo" id="RollNo" class="form-control">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="semester" class="form-label">Semester</label>
                        <input type="text" name="semester" id="semester" class="form-control" readonly>
                    </div>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="border-top pt-3 mt-3">
                <div class="mb-3">
                    <label class="form-label">Registration Fee</label>
                    <input type="hidden" name="registrationFee" value="200">
                    <input type="text" disabled class="form-control" value="₹200">
                </div>
                
                <input type="hidden" id="razorpay_payment_id" name="razorpay_payment_id">
                
                <div class="d-grid">
                    <button type="button" class="pay-button" id="pay-button">Pay Registration Fee</button>
                </div>
                
                <div class="transaction-info" id="transaction-info">
                    <h4>Payment Successful!</h4>
                    <div class="transaction-details">
                        <p><strong>Transaction ID:</strong> <span id="display-transaction-id"></span></p>
                        <p><strong>Amount:</strong> ₹200</p>
                    </div>
                </div>
                
                <div class="razorpay-logo mt-3">
                    <p>Secure payments powered by</p>
                    <img src="https://razorpay.com/assets/razorpay-logo.svg" alt="Razorpay">
                </div>
            </div>

            <button type="submit" name="submit" class="btn btn-primary w-100 mt-3" id="submit-btn" disabled>Submit Application</button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
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
        
        function showBranch() {
            var branch = document.getElementById('branch').value;
            var course = document.getElementById('course');

            // Clear previous options
            course.innerHTML = `<option value="" selected>---Select any course---</option>`;

            if (branch == "Mechanical") {
                course.innerHTML += `
                    <option value="Diploma in Mechanical Engineering">Diploma in Mechanical Engineering</option>
                    <option value="Diploma in Mechanical Engineering Lateral Entry">Diploma in Mechanical Engineering Lateral Entry</option>
                `;
            } else if (branch == "Electrical") {
                course.innerHTML += `
                    <option value="Diploma in Electrical Engineering">Diploma in Electrical Engineering</option>
                    <option value="Diploma in Electrical Engineering Lateral Entry">Diploma in Electrical Engineering Lateral Entry</option>
                `;
            } else if (branch == "Computer Science") {
                course.innerHTML += `
                    <option value="Diploma in Computer Science Engineering">Diploma in Computer Science Engineering</option>
                    <option value="Diploma in Computer Science">Diploma in Computer Science Engineering Lateral Entry</option>
                `;
            }
        }
        
        function updateSemester() {
            var admissionType = document.getElementById('admissionType').value;
            var semester = document.getElementById('semester');

            if (admissionType == "Direct") {
                semester.value = "Sem1";
            } else if(admissionType == "Lateral") {
                semester.value = "Sem3";
            }
        }
        
        // Razorpay Payment Integration
        document.getElementById('pay-button').onclick = function(e) {
            // Validate form
            const requiredFields = ['branch', 'applicantName', 'fatherName', 'state', 'cdistrict', 'dob', 'admissionType', 'course'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const element = document.getElementById(field);
                if (!element.value) {
                    isValid = false;
                    element.classList.add('is-invalid');
                } else {
                    element.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                alert('Please fill all required fields marked with *');
                return;
            }
            
            // Create order and open Razorpay checkout
            const options = {
                "key": "<?php echo $razorpayKeyId; ?>",
                "amount": "<?php echo $amount * 100; ?>", // Razorpay expects amount in paise
                "currency": "<?php echo $currency; ?>",
                "name": "ESTC Polytechnic",
                "description": "Registration Fee Payment",
                "image": "https://example.com/your_logo.png", // Add your college logo
                "prefill": {
                    "name": document.getElementById('applicantName').value,
                    "email": "<?php echo $_SESSION['username']; ?>", // Using session email if available
                    "contact": "" // You can add phone field if needed
                },
                "theme": {
                    "color": "#3399cc"
                },
                "handler": function (response) {
                    // On successful payment
                    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                    document.getElementById('display-transaction-id').textContent = response.razorpay_payment_id;
                    document.getElementById('transaction-info').classList.add('show');
                    document.getElementById('submit-btn').disabled = false;
                    
                    // Scroll to show the transaction info
                    document.getElementById('transaction-info').scrollIntoView({ behavior: 'smooth' });
                },
                "modal": {
                    "ondismiss": function() {
                        console.log("Payment cancelled");
                    }
                }
            };
            
            const rzp = new Razorpay(options);
            rzp.open();
            e.preventDefault();
        }
    </script>
</body>
</html>
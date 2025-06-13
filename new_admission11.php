<?php
// session_start(); // Added session_start() which was missing
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

// Get the username from session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Administrator';

// Fetch the last ID
$sql_last_id = "SELECT MAX(id) AS last_id FROM record";
$result_last_id = $db->query($sql_last_id);

// Initialize next_id with default value
$next_id = 1;

// Calculate next ID if records exist
if ($result_last_id && $result_last_id->num_rows > 0) {
    $row_last_id = $result_last_id->fetch_assoc();
    if ($row_last_id['last_id']) {
        $next_id = $row_last_id['last_id'] + 1;
    }
}

// Check payment status
$payment_status = false;
$payment_id = '';
$user_id = $_SESSION['user_id'];

// Change to include amount check
$sql_payment = "SELECT payment_id FROM payment 
                WHERE user_id = ? 
                AND status = 'success' 
                AND application_id IS NULL";
$stmt = $db->prepare($sql_payment);

// Check if prepare() succeeded
if ($stmt === false) {
    die("Database error: " . htmlspecialchars($db->error));
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$payment_result = $stmt->get_result();

if ($payment_result->num_rows > 0) {
    $row = $payment_result->fetch_assoc();
    $payment_status = true;
    $payment_id = $row['payment_id'];
}

// Razorpay API Key
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
        .payment-completed {
            background-color: #f0fff4;
            border-left: 4px solid #48bb78;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
        }
        .is-invalid {
            border-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h3 class="text-center text-primary">ESTC POLYTECHNIC - RAMNAGAR</h3>
        <p class="text-center">Autonomous Organization Under Ministry of MSME, Govt. of India</p>
        <p class="text-center"><b>AICTE Approved || UBTER Affiliated</b></p>

        <!-- <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                Import Excel
            </button>
            <a href="export_excel.php" class="btn btn-primary">Export Excel</a>
        </div> -->

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
                        <input type="text" class="form-control" id="applicantName" name="applicantName" placeholder="As per high school certificate" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="fatherName" class="form-label">Father's Name *</label>
                        <input type="text" class="form-control" id="fatherName" name="fatherName" placeholder="As per high school certificate" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category">Category</label>
                        <select name="category" id="category" class="form-control">
                            <option value="" selected>-- select category --</option>
                            <option value="SC">SC</option>
                            <option value="ST">ST</option>
                            <option value="GEN">GEN</option>
                            <option value="OBC">OBC</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="state" class="form-label">State *</label>
                        <select class="form-select" id="state" name="state" onchange="fetchDistricts1(this.value)">
                            <option value="" selected>----- Select an State</option>
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
                            <option value="">Select District</option>
                        </select><input type="hidden" name="ccdistrict" id="ccdistrict" />
                    </div>
                </div>
            </div>

            <div class="row">
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

            <!-- Paid Registration Switch -->
            <div class="form-check form-switch">
                <label class="form-check-label switch-label" for="paidSwitch">Paid Registration Fee:</label>
                <input class="form-check-input" type="checkbox" id="paidSwitch">
            </div>

            <!-- Payment Section -->
            <div class="border-top pt-3 mt-3">
                <div class="mb-3">
                    <label class="form-label">Registration Fee</label>
                    <input type="hidden" name="registrationFee" value="200">
                    <input type="text" disabled class="form-control" value="₹200">
                </div>
                
                <input type="hidden" id="razorpay_payment_id" name="razorpay_payment_id" value="<?php echo $payment_id; ?>">

                <?php if (!$payment_status): ?>
                
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
                <?php else: ?>
                    <div class="payment-completed">
                        <h4>Payment Already Completed!</h4>
                        <div class="transaction-details">
                            <p><strong>Transaction ID:</strong> <?php echo $payment_id; ?></p>
                            <p><strong>Amount:</strong> ₹200</p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="razorpay-logo mt-3">
                    <p>Secure payments powered by</p>
                    <img src="https://razorpay.com/assets/razorpay-logo.svg" id="razorpayImage" alt="Razorpay">
                </div>
            </div>

            <button type="submit" name="submit" class="btn btn-primary w-100 mt-3" id="submit-btn" <?php echo $payment_status ? '' : 'disabled'; ?>>Submit Application</button>
        </form>
        <!-- Import Modal -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Excel Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="importForm" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="excelFile" class="form-label">Select Excel File</label>
                                <input class="form-control" type="file" id="excelFile" name="excelFile" accept=".xlsx, .xls" required>
                            </div>
                            <div class="progress mb-3" style="display: none;">
                                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                            <button type="submit" class="btn btn-primary">Import Data</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
         const paidSwitch = document.getElementById('paidSwitch');
         const payBtn = document.getElementById('pay-button');
         const razorpayImage = document.getElementById('razorpayImage');
         const submitBtn = document.getElementById('submit-btn');
         paidSwitch.addEventListener('change', function () {
            if (this.checked) {
                if (confirm("Are you sure you have already paid the registration fee?")) {
                    const userId = prompt("Enter your User ID:");
                    const password = prompt("Enter your Password:");

                    // Replace with real credentials validation
                    const validUserId = "admin";
                    const validPassword = "1234";

                    if (userId === validUserId && password === validPassword) {
                        payBtn.hidden = true;
                        razorpayImage.hidden = true;
                        submitBtn.disabled = false;
                    } else {
                        alert("Invalid credentials! Please try again.");
                        paidSwitch.checked = false;
                        payBtn.hidden = false;
                        razorpayImage.style.opacity = 1;
                        submitBtn.hidden = true;
                    }
                } else {
                    paidSwitch.checked = false;
                }
            } else {
                payBtn.hidden = false;
                razorpayImage.style.opacity = 1;
                submitBtn.disabled = true;
            }
        });
        function fetchDistricts1(stateValue) {
            if (stateValue) {
                var parts = stateValue.split('|');
                var stateId = parts[0]; 
                // Store the state name in the hidden field
                $('#cstate1').val(parts[1]);
                $.ajax({
                    url: 'fetch_districts1.php',
                    type: 'GET',
                    data: { state_id: stateId },
                    success: function(response) {
                        try {
                            var districts = JSON.parse(response);
                            var districtDropdown = $('#cdistrict');
                            districtDropdown.empty();
                            districtDropdown.append('<option value="">Select District</option>');

                            districts.forEach(function(district) {
                                districtDropdown.append('<option value="' + district.id + '">' + district.name + '</option>');
                            });
                            
                            districtDropdown.change(function() {
                                var districtName = $(this).find('option:selected').text();
                                $('#ccdistrict').val(districtName);
                            });
                        } catch (e) {
                            console.error("Error parsing districts:", e);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                    }
                });
            } else {
                $('#cdistrict').empty().append('<option value="">Select District</option>');
                $('#ccdistrict').val('');
            }
        }
        
        function showBranch() {
            var branch = document.getElementById('branch').value;
            var course = document.getElementById('course');
            course.innerHTML = '<option value="" selected>---Select any course---</option>';

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
                    <option value="Diploma in Computer Science Engineering Lateral Entry">Diploma in Computer Science Engineering Lateral Entry</option>
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
            } else if(admissionType == "Jeep DirectEntry") {
                semester.value = "Sem1";
            } else if(admissionType == "Jeep Through Council") {
                semester.value = "Sem1";
            }
        }
        
        // Razorpay Payment Integration
        <?php if (!$payment_status): ?>
        function handlePayment() {
            // Validate form
            const requiredFields = ['branch', 'course', 'applicantName', 'fatherName', 'state', 'cdistrict', 'dob', 'admissionType'];
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
            
            // Create Razorpay order
            const options = {
                "key": "<?php echo $razorpayKeyId; ?>",
                "amount": "<?php echo $amount * 100; ?>",
                "currency": "<?php echo $currency; ?>",
                "name": "ESTC Polytechnic",
                "description": "Registration Fee Payment",
                "image": "https://example.com/your_logo.png",
                "prefill": {
                    "name": document.getElementById('applicantName').value,
                    "email": "<?php echo $_SESSION['username']; ?>"
                },
                "theme": {
                    "color": "#3399cc"
                },
                "handler": function (response) {
                    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                    document.getElementById('display-transaction-id').textContent = response.razorpay_payment_id;
                    document.getElementById('transaction-info').classList.add('show');
                    document.getElementById('submit-btn').disabled = false;
                    
                    // Save payment record
                    $.ajax({
                        url: 'save_payment.php',
                        type: 'POST',
                        data: {
                            payment_id: response.razorpay_payment_id,
                            amount: <?php echo $amount; ?>
                        },
                        success: function() {
                            console.log("Payment record saved");
                        }, // FIXED: Added this missing comma
                        error: function(xhr) {
                            console.error("Error saving payment:", xhr.responseText);
                            alert('Payment succeeded but record saving failed. Please contact support.');
                        }
                    });
                },
                "modal": {
                    "ondismiss": function() {
                        console.log("Payment cancelled");
                    }
                }
            };
            
            const rzp = new Razorpay(options);
            rzp.open();
        }
        document.getElementById('pay-button').addEventListener('click', handlePayment);
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Import Excel functionality
        $(document).ready(function() {
            $('#importForm').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                var progressBar = $('.progress-bar');
                var progressContainer = $('.progress');
                
                progressContainer.show();
                
                $.ajax({
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = (evt.loaded / evt.total) * 100;
                                progressBar.width(percentComplete + '%');
                                progressBar.text(Math.round(percentComplete) + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    url: 'import_excel1.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                    try {
                        if (typeof response === 'object') {
                            // Already parsed
                            var res = response;
                        } else {
                            var res = JSON.parse(response);
                        }
                        
                        if (res.status === 'success') {
                            alert(res.message);
                            if (res.errors && res.errors.length > 0) {
                                console.error("Import errors:", res.errors);
                            }
                            $('#importModal').modal('hide');
                        } else {
                            alert('Error: ' + res.message);
                        }
                    } catch (e) {
                        console.error("Raw response:", response);
                        alert('Error parsing server response. See console for details.');
                    }
                },
                    error: function() {
                        alert('Error uploading file');
                    },
                    complete: function() {
                        progressContainer.hide();
                        progressBar.width('0%');
                        progressBar.text('0%');
                    }
                });
            });
        });
    </script>
</body>
</html>
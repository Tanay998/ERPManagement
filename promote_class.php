<?php 
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


// Fetch courses from database grouped by type_of_course
$courses = [];
$course_query = "SELECT id, type_of_course, title FROM courses ORDER BY type_of_course, title";
$course_result = mysqli_query($db, $course_query);
if ($course_result) {
    while ($row = mysqli_fetch_assoc($course_result)) {
        $courses[$row['type_of_course']][] = $row;
    }
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
    <title>Admission Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

   <style>
        .container{
            border: 15px solid transparent;
            border-image-source: url('images/leatherImage.jpg');
            border-image-slice: 20%;
            border-image-repeat: round;
            padding: 20px;
            max-width: 800px;
        }
        
        /* Payment section styles */
        .payment-section {
            border-top: 1px solid #ddd;
            padding-top: 20px;
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
        
        .pay-button {
            background-color: #4299e1;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }
        
        .pay-button:hover {
            background-color: #3182ce;
        }
        
        .razorpay-logo {
            text-align: center;
            margin-top: 20px;
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875em;
        }
    </style>
</head>
<body>
    <div class="container mt-5 border p-4 rounded shadow w-75">
        <div class="bg-danger text-white p-3">
            <h2 class="text-center">Electronics Service & Training Centre - Admission Form</h2>
            <p class="text-center h6">MSME Technology Centre, Under Ministry Of MSME, Govt. of India Society, Dhela Road, Kaniya, Ramnagar, District - Nainital, Uttarakhand, PIN - 244715 INDIA Ph-05947-251530 Mobile-9411308631 E-mail-pd_estc@estcindia.com</p>
        </div>
        <form onsubmit="return validateForm()" action="otr2info.php" enctype="multipart/form-data" id="main-form">
            <div class="mb-3 mt-4">
                <label class="form-label">Select Course Type</label>
                <select class="form-select required" id="courseType" name="courseType" onchange="updateCourseList()">
                    <option value="" selected>---Select Course Type---</option>
                    <?php foreach ($courses as $type => $type_courses): ?>
                        <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3 mt-4">
                <label for="courseLevel" class="form-label">course Level</label>
                <select name="courseLevel" id="courseLevel" class="form-control">
                    <option value="" selected>---Select Course Level----</option>
                    <option value="Normal">Normal</option>
                    <option value="NSQFLevel1">NSQF Level1</option>
                    <option value="NSQFLevel2">NSQF Level2</option>
                    <option value="NSQFLevel3">NSQF Level3</option>
                    <option value="NSQFLevel4">NSQF Level4</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Course List *</label>
                <select class="form-select required" id="courseList" name="courseList" required>
                    <option value="" selected>-- Select a Course --</option>
                    <?php foreach ($courses as $type => $type_courses): ?>
                        <?php foreach ($type_courses as $course): ?>
                            <option value="<?= $course['id'] ?>" data-type="<?= htmlspecialchars($type) ?>" style="display:none;" class="course-option">
                                <?= htmlspecialchars($course['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Applicant Name *</label>
                <input type="text" class="form-control required" name="applicantName" id="applicantName" placeholder="As per high school certificate" value="<?= htmlspecialchars($enquiryData['firstName'] . ' ' . ($enquiryData['middleName'] ?? '') . ' ' . $enquiryData['lastName']) ?>" placeholder="As per high school certificate">
            </div>
            <div class="mb-3">
                <label class="form-label">Father's Name *</label>
                <input type="text" class="form-control required" name="fatherName" id="fatherName" placeholder="As per high school certificate" value="<?= htmlspecialchars($enquiryData['fatherName'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Tel/Mob *</label>
                <input type="text" class="form-control required" name="mobile" id="mobile" pattern="[0-9]{10}" placeholder="10 digit mobile number" value="<?= htmlspecialchars($enquiryData['mobile'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Email *</label>
                <input type="email" class="form-control required" name="email" id="email" placeholder="Enter email" value="<?= htmlspecialchars($enquiryData['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Date of Birth *</label>
                <input type="date" name="dob" id="dob" class="form-control required">
            </div>
            <div class="mb-3">
                <label class="form-label">Gender *</label><br>
                <input type="radio" name="gender" value="male"> Male
                <input type="radio" name="gender" value="female"> Female
            </div>
            <div class="mb-3">
                <label class="form-label">Aadhaar Card No</label>
                <input type="text" class="form-control" name="aadhaar" id="aadhaar" pattern="[0-9]{12}" placeholder="12-digit Aadhaar number">
            </div>
            <div class="mb-3">
                <label class="form-label">Address *</label>
                <input type="text" name="address" id="address" class="form-control required" placeholder="Address">
                <input type="text" name="city" id="city" class="form-control mt-2" placeholder="City">
                <input type="text" name="state" id="state" class="form-control mt-2" placeholder="State">
            </div>
            <div class="mb-3">
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
            <div class="mb-3">
                <label for="qualification">Qualification</label>
                <input type="text" name="qualification" id="qualification" class="form-control">
            </div>
            <div class="mb-3">
                <label for="photo">Upload your photo</label>
                <input type="file" name="photo" id="photo" class="form-control">
            </div>
            <div class="mb-3">
                <label for="status">Employment Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="" selected>--Select status --</option>
                    <option value="Employed">Employed</option>
                    <option value="SelfEmployed">Self-Employed</option>
                    <option value="Student">Student</option>
                    <option value="UnEmployed">Un-Employed</option>
                </select>
            </div>
           <!-- Payment Section -->
           <div class="payment-section">
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
                        <p><strong>Transaction ID:</strong> <span id="display-transaction-id" name="transactionID"></span></p>
                        <p><strong>Amount:</strong> ₹200</p>
                    </div>
                </div>
                
                <div class="razorpay-logo mt-3">
                    <p>Secure payments powered by</p>
                    <img src="https://razorpay.com/assets/razorpay-logo.svg" alt="Razorpay">
                </div>
            </div>
           <!-- In your payment section, keep the existing code but update the submit button: -->
            <button type="submit" name="submit" class="btn btn-primary w-100 mt-3" id="submit-btn" disabled>Submit Application</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        function validateForm() {
            let isValid = true;
            const requiredFields = document.querySelectorAll('.required');
            
            requiredFields.forEach(field => {
                if (field.value.trim() === "") {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            // Check if payment is completed
            if (!document.getElementById('razorpay_payment_id').value) {
                alert('Please complete the payment process before submitting.');
                return false;
            }
            
            return isValid;
        }

        // Function to update course list based on selected type
        function updateCourseList() {
            const courseType = document.getElementById('courseType').value;
            const courseList = document.getElementById('courseList');
            
            // Reset to default option
            courseList.selectedIndex = 0;
            
            // Show/hide options based on selected type
            const options = courseList.querySelectorAll('option');
            options.forEach(option => {
                if (option.value === "") {
                    return; // Skip the default option
                }
                
                if (!courseType || option.getAttribute('data-type') === courseType) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
        }
        
        // Razorpay Payment Integration
        document.getElementById('pay-button').onclick = function(e) {
            // Validate required fields
            const requiredFields = document.querySelectorAll('.required');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (field.value.trim() === "") {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
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
                "name": "Electronics Service & Training Centre",
                "description": "Registration Fee Payment",
                "image": "https://example.com/your_logo.png", // Add your college logo
                "prefill": {
                    "name": document.querySelector('[name="applicantName"]').value,
                    "email": document.querySelector('[name="email"]').value,
                    "contact": document.querySelector('[name="mobile"]').value
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

                    // Enable and style the submit button
                    const submitBtn = document.getElementById('submit-btn');
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('btn-primary', 'bg-danger');
                    submitBtn.classList.add('bg-success');
                    submitBtn.textContent = 'Submit Application (Payment Verified)';
                    
                    // Scroll to show the transaction info
                    document.getElementById('transaction-info').scrollIntoView({ behavior: 'smooth' });
                },
                "modal": {
                    "ondismiss": function() {
                        const submitBtn = document.getElementById('submit-btn');
                        submitBtn.disabled = true;
                        submitBtn.classList.remove('btn-primary', 'bg-success');
                        submitBtn.classList.add('bg-danger');
                        submitBtn.textContent = 'Complete Payment First';
                        
                        console.log("Payment cancelled");
                    }
                }
            };
            
            const rzp = new Razorpay(options);
            rzp.open();
            e.preventDefault();
        }
        // Initialize button state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.classList.add('bg-danger');
            submitBtn.textContent = 'Complete Payment First';
        });
    </script>
</body>
</html>

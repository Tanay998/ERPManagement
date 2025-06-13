<?php
// Database connection
$servername = "localhost"; // Database server
$username = "root";        // Database username
$password = "";            // Database password
$dbname = "schoolfeesys";  // Database name

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$enquiryData = null;

// If we are editing, get the enquiry details
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $enquiryId = $_GET['id'];
    $sql = "SELECT * FROM enquiries WHERE id = $enquiryId";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $enquiryData = $result->fetch_assoc();
    }
}

// Handle form submission (both for add and edit)
if (isset($_POST['submitEnquiry'])) {
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $fatherName = $_POST['fatherName'];
    $motherName = $_POST['motherName'];
    $address = $_POST['address'];
    $dob = $_POST['dob'];
    $state = $_POST['state'];
    $district = $_POST['district'];
    $city = $_POST['city'];

    // Photo upload
    $photo = $_FILES['studentPhoto']['name'];
    $photo_tmp = $_FILES['studentPhoto']['tmp_name'];
    $photo_path = 'uploads/' . $photo;
    move_uploaded_file($photo_tmp, $photo_path);

    // Insert or update data into the database
    if ($enquiryData) {
        // Edit existing enquiry
        $sql = "UPDATE enquiries SET firstName='$firstName', middleName='$middleName', lastName='$lastName', fatherName='$fatherName', 
                motherName='$motherName', address='$address', dob='$dob', state='$state', district='$district', city='$city', photo='$photo_path'
                WHERE id=$enquiryId";
    } else {
        // Add new enquiry
        $sql = "INSERT INTO enquiries (firstName, middleName, lastName, fatherName, motherName, address, dob, state, district, city, photo) 
                VALUES ('$firstName', '$middleName', '$lastName', '$fatherName', '$motherName', '$address', '$dob', '$state', '$district', '$city', '$photo_path')";
    }

    if ($conn->query($sql) === TRUE) {
        header("Location: plugins.php"); // Redirect to the plugins page after submit
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $enquiryData ? "Edit Enquiry" : "Add New Enquiry"; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> <!-- Add your custom CSS if needed -->
</head>
<body>
    <div id="wrapper">

    <!-- Sidebar -->
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <h2>Logo</h2>
        </div>
        <ul class="sidebar-nav">
            <li>
                <a href="index.php" data-target="home.php"><i class="fa fa-home"></i>Dashboard</a>
            </li>
            <li>
                <a href="plugins.php" data-target="plugins.php"><i class="fa fa-plug"></i>Enquiry</a>
            </li>
            <li>
                <a href="users.php" data-target="users.php"><i class="fa fa-user"></i>Users</a>
            </li>
        </ul>
    </aside>

    <!-- Navbar -->
    <div id="navbar-wrapper">
        <nav class="navbar navbar-inverse">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a href="#" class="navbar-brand" id="sidebar-toggle"><i class="fa fa-bars"></i></a>
                </div>
            </div>
        </nav>
    </div>

    <!-- Content Wrapper -->
    <section id="content-wrapper">
        <div class="container mt-5">
            <h2><?php echo $enquiryData ? "Edit Enquiry" : "Add New Enquiry"; ?></h2>

            <!-- Enquiry Form -->
            <form action="enquiry_form.php<?php echo $enquiryData ? '?id=' . $enquiryData['id'] : ''; ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="firstName" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo $enquiryData['firstName'] ?? ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="middleName" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" id="middleName" name="middleName" value="<?php echo $enquiryData['middleName'] ?? ''; ?>">
                </div>
                <div class="mb-3">
                    <label for="lastName" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo $enquiryData['lastName'] ?? ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="fatherName" class="form-label">Father's Name</label>
                    <input type="text" class="form-control" id="fatherName" name="fatherName" value="<?php echo $enquiryData['fatherName'] ?? ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="motherName" class="form-label">Mother's Name</label>
                    <input type="text" class="form-control" id="motherName" name="motherName" value="<?php echo $enquiryData['motherName'] ?? ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required><?php echo $enquiryData['address'] ?? ''; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="dob" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" id="dob" name="dob" value="<?php echo $enquiryData['dob'] ?? ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="state" class="form-label">State</label>
                    <input type="text" class="form-control" id="state" name="state" value="<?php echo $enquiryData['state'] ?? ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="district" class="form-label">District</label>
                    <input type="text" class="form-control" id="district" name="district" value="<?php echo $enquiryData['district'] ?? ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="city" class="form-label">City</label>
                    <input type="text" class="form-control" id="city" name="city" value="<?php echo $enquiryData['city'] ?? ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="studentPhoto" class="form-label">Upload Photo</label>
                    <input type="file" class="form-control" id="studentPhoto" name="studentPhoto" <?php echo !$enquiryData ? 'required' : ''; ?>>
                    <?php if ($enquiryData && $enquiryData['photo']) { ?>
                        <img src="<?php echo $enquiryData['photo']; ?>" alt="Current Photo" width="100" class="mt-2">
                    <?php } ?>
                </div>
                <button type="submit" class="btn btn-primary" name="submitEnquiry">Submit</button>
            </form>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>

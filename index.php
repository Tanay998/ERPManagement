<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Define common menu items for all users
$menu_items = [
    'dashboard.php' => 'Dashboard',
    'enquiry.php' => 'Enquiry',
    'courses.php' => 'Add Course',
    'studentDetails.php' => 'Student Details',
    'new_admission.php' => 'Admission',
    'staff.php' => 'Staff',
    'fee.php' => 'Fee',
    'account.php' => 'Finance',
    'hostel.php' => 'Hostel',
    'hr.php'=> 'HR',
    'Library.php'=> 'Library',
    'Franchise.php'=> 'Franchise',
    'examinations.php' => 'Examinations',
    'manage_users.php' => 'Manage Users',
    'contact.php' => 'Contact',
];

// List of allowed pages
$allowed_pages = array_keys($menu_items);
$allowed_pages = array_merge($allowed_pages, [
    'add_user.php', 
    'remove_user.php',
    'edit_user.php',
    'new_admission.php',
    'promote_class.php',
    'new_admission1.php',
    'new_admission2.php',
    'new_admission11.php',
    'promote_class1.php',
    'transfer_student.php',
    'student_records.php',
    'view_staff.php',
    'view_student11.php',
    'add_staff.php',
    'backup_students.php',
    'edit_fee_form.php',
    'edit_student11.php',
    'staff_id_cards.php',
    'staff_attendance.php',
    'attendance_report.php',
    'fee_deposit.php',
    'fee_receipt_view.php',
    'fee_report.php',
    'fee_due_report.php',
    'studentList.php',
    'manageStudent.php',
    'certificates.php',
    'promote_class1.php'
]);

// Parse URL parameters
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard.php';
$action = isset($_GET['action']) ? $_GET['action'] : null;
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Security check - only allow alphanumeric and underscore in page names
$page = preg_replace('/[^a-zA-Z0-9_\.]/', '', $page);

// Validate page
if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard.php';
    error_log("Attempted to access unauthorized page: " . $page);
}

// Verify file exists
$page_path = __DIR__ . '/' . $page;
if (!file_exists($page_path)) {
    $page = 'dashboard.php';
    error_log("Requested page not found: " . $page_path);
}

// Create a global state object for the application
$app_state = [
    'current_page' => $page,
    'current_action' => $action,
    'current_id' => $id,
    'menu_items' => $menu_items,
    'user_id' => $_SESSION['user_id']
];

// Function to generate URL with parameters
function generateUrl($page, $action = null, $id = null) {
    $params = ['page' => $page];
    if ($action) $params['action'] = $action;
    if ($id) $params['id'] = $id;
    return 'index.php?' . http_build_query($params);
}

// Function to check if a menu item is active
function isMenuItemActive($current_page, $menu_page, $action = null) {
    if ($current_page === $menu_page) {
        if ($action) {
            return isset($_GET['action']) && $_GET['action'] === $action;
        }
        return true;
    }
    return false;
}

// Check if this is a transfer request
if (isset($_GET['transfer']) && isset($_GET['table']) && isset($_GET['id'])) {
    $table = $_GET['table'];
    $id = $_GET['id'];
    
    try {
        if ($table === 'polyregis') {
            $sql = "SELECT * FROM polyregis WHERE RollNo = ?";
        } elseif ($table === 'estcregis') {
            $sql = "SELECT * FROM estcregis WHERE id = ?";
        } else {
            throw new Exception("Invalid table specified");
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Student record not found");
        }
        
        $student = $result->fetch_assoc();
        
        // Split applicant name into parts
        $nameParts = explode(' ', $student[$table === 'polyregis' ? 'applicantName' : 'applicant_name']);
        $firstName = $nameParts[0] ?? '';
        $middleName = $nameParts[1] ?? '';
        $lastName = implode(' ', array_slice($nameParts, 2)) ?? '';
        
        // Store transfer data in session
        $_SESSION['transfer_data'] = [
            'firstName' => $firstName,
            'middleName' => $middleName,
            'lastName' => $lastName,
            'original_id' => $id,
            'source_table' => $table,
            // Add other fields as needed...
        ];
        
        // Redirect to new admission page
        header("Location: new_admission1.php");
        exit();
        
    } catch (Exception $e) {
        // Handle errors by showing them on the index page
        $_SESSION['error'] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .user-info {
            position: absolute;
            top: 15px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #333;
            z-index: 1000;
        }
        .sidebar {
            background-color: teal;
            min-height: 100vh;
            overflow-y: scroll; 
            z-index: 1000 !important;
            position: relative;
        }
        #sidebarToggle {
            display: none;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1050;
        }
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                left: -300px;
                top: 0;
                bottom: 0;
                width: 250px;
                transition: all 0.3s;
                z-index: 1000;
            }
            .sidebar.active {
                left: 0;
            }
            #sidebarToggle {
                display: block;
            }
            .col-md-9.col-lg-10.ms-auto {
                margin-left: 0 !important;
                width: 100%;
            }
            .user-info {
                right: 10px;
            }
            .logout-btn {
                right: 10px;
            }
        }
        .sidebar-item {
            color: white;
            padding: 15px;
            cursor: pointer;
            transition: 0.3s;
        }
        .sidebar-item:hover, .sidebar-item.active {
            background-color: rgba(0, 0, 0, 0.2);
        }
        #content-container {
            position: relative;
            padding: 50px 20px 20px;
        }
        .logout-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            z-index: 1001;
        }
        .sidebar-item.dropdown .dropdown-toggle {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 100%;
        }
        .sidebar-item.dropdown .dropdown-menu {
            background-color:rgb(8, 63, 63);
            border: none;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        .sidebar-item.dropdown .dropdown-item {
            color: white;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }
        .sidebar-item.dropdown .dropdown-item:hover {
            background-color: rgba(0, 0, 0, 0.2);
        }
        .sidebar-item i {
            width: 20px;
            text-align: center;
        }
        .card:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }
        .dataTables_wrapper {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <button id="sidebarToggle" class="btn btn-primary">
        <i class="fas fa-bars"></i>
    </button>
    <div class="user-info">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
            <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
        </svg>
        <span><?= htmlspecialchars($_SESSION['user_id']) ?></span>
    </div>
    <a href="logout.php" class="btn btn-danger logout-btn mt-5">Logout</a>

    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            <div class="col-md-9 col-lg-10 ms-auto">
                <div id="content-container">
                    <?php include($page); ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Replace the existing JavaScript with this:
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        // Initialize sidebar state - check if we're on StudentList page
        const isStudentList = window.location.href.includes('page=StudentList.php');
        const sidebarOpen = localStorage.getItem('sidebarOpen') === 'true' || isStudentList;
        
        if (sidebarOpen) {
            sidebar.classList.add('active');
        }
        
        // Toggle button handler
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            localStorage.setItem('sidebarOpen', sidebar.classList.contains('active'));
        });

        // Click outside handler
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 992 && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('active');
                localStorage.setItem('sidebarOpen', false);
            }
        });

        // Window resize handler - less aggressive
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) {
                // Only auto-close if not on StudentList page
                if (!window.location.href.includes('page=StudentList.php')) {
                    sidebar.classList.remove('active');
                    localStorage.setItem('sidebarOpen', false);
                }
            }
        });
    });
    </script>
</body>
</html>
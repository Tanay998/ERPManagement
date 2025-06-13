<?php
function getIconClass($title) {
    switch ($title) {
        case 'Dashboard': return 'fas fa-tachometer-alt';
        case 'Enquiry': return 'fas fa-question-circle';
        case 'Add Course': return 'fas fa-plus-circle';
        case 'Student Details': return 'fas fa-list';
        case 'Admission': return 'fas fa-users';
        case 'Staff': return 'fas fa-user-tie';
        case 'Fee': return 'fas fa-money-bill-wave';
        case 'Finance': return 'fas fa-wallet';
        case 'Hostel': return 'fas fa-bed';
        case 'HR': return 'fas fa-users-cog';
        case 'Library': return 'fas fa-book-open';
        case 'Franchise': return 'fas fa-store';
        case 'Examinations': return 'fas fa-clipboard-list';
        case 'Manage Users': return 'fas fa-user-cog';
        case 'Contact': return 'fas fa-address-book';
        default: return 'fas fa-circle';
    }
}

// Get current page and action from app state
$current_page = $app_state['current_page'];
$current_action = $app_state['current_action'];
?>
<div class="col-md-3 col-lg-2 sidebar p-0">
    <div class="d-flex flex-column">
        <button class="btn btn-link text-white d-block d-md-none text-end pe-3" 
                onclick="document.querySelector('.sidebar').classList.remove('active');localStorage.setItem('sidebarOpen', false);">
            <i class="fas fa-times"></i>
        </button>
        
        <?php foreach($menu_items as $url => $title): ?>
            <?php if (in_array($title, ['Admission', 'Staff', 'Fee', 'Student Details', 'Manage Users'])): ?>
                <div class="sidebar-item dropdown <?= isMenuItemActive($current_page, $url) ? 'active' : '' ?>">
                    <div class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" 
                         data-bs-toggle="dropdown" role="button" style="justify-content: flex-start;">
                        <i class="<?= getIconClass($title) ?> me-2"></i>
                        <?= $title ?>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <?php if ($title === 'Admission'): ?>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'new_admission11.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('new_admission11.php') ?>">
                                <i class="fas fa-user-plus me-2"></i>Poly Registration
                            </a></li>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'promote_class.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('promote_class.php') ?>">
                                <i class="fas fa-arrow-up me-2"></i>Professional Course<br>Registration
                            </a></li>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'new_admission2.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('new_admission2.php') ?>">
                                <i class="fas fa-user-plus me-2"></i>Poly admission
                            </a></li>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'promote_class1.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('promote_class1.php') ?>">
                                <i class="fas fa-arrow-up me-2"></i>Professional Course<br>Admission
                            </a></li>
                        
                        <?php elseif ($title === 'Staff'): ?>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'view_staff.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('view_staff.php') ?>">
                                <i class="fas fa-eye me-2"></i>View Staff
                            </a></li>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'add_staff.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('add_staff.php') ?>">
                                <i class="fas fa-user-plus me-2"></i>Add New Staff
                            </a></li>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'staff_id_cards.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('staff_id_cards.php') ?>">
                                <i class="fas fa-id-card me-2"></i>Staff ID Cards
                            </a></li>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'staff_attendance.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('staff_attendance.php') ?>">
                                <i class="fas fa-calendar-check me-2"></i>Staff Attendance
                            </a></li>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'attendance_report.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('attendance_report.php') ?>">
                                <i class="fas fa-file-alt me-2"></i>Attendance Report
                            </a></li>
                        
                        <?php elseif ($title === 'Fee'): ?>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'fee_deposit.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('fee_deposit.php') ?>">
                                <i class="fas fa-coins me-2"></i>Fee Deposit
                            </a></li>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'fee_receipt_view.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('fee_receipt_view.php') ?>">
                                <i class="fas fa-receipt me-2"></i>Fee Receipt View
                            </a></li>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'fee_report.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('fee_report.php') ?>">
                                <i class="fas fa-file-invoice me-2"></i>Fee Report
                            </a></li>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'fee_due_report.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('fee_due_report.php') ?>">
                                <i class="fas fa-exclamation-circle me-2"></i>Fee Due Report
                            </a></li>
                        
                        <?php elseif ($title === 'Student Details'): ?>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'studentList.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('studentList.php') ?>">
                                <i class="fas fa-list me-2"></i>Student List
                            </a></li>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'manageStudent.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('manageStudent.php') ?>">
                                <i class="fas fa-user-edit me-2"></i>Manage Students
                            </a></li>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'certificates.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('certificates.php') ?>">
                                <i class="fas fa-certificate me-2"></i>Student Certificates
                            </a></li>
                        
                        <?php elseif ($title === 'Manage Users'): ?>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'add_user.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('add_user.php') ?>">
                                <i class="fas fa-user-plus me-2"></i>Add New User
                            </a></li>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'remove_user.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('remove_user.php') ?>">
                                <i class="fas fa-user-minus me-2"></i>Remove User
                            </a></li>
                            <li><a class="dropdown-item <?= isMenuItemActive($current_page, 'edit_user.php') ? 'active' : '' ?>" 
                                  href="<?= generateUrl('edit_user.php') ?>">
                                <i class="fas fa-user-edit me-2"></i>Edit User
                            </a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php else: ?>
                <div class="sidebar-item <?= isMenuItemActive($current_page, $url) ? 'active' : '' ?>" 
                     onclick="window.location.href='<?= generateUrl($url) ?>'">
                    <i class="<?= getIconClass($title) ?> me-2"></i>
                    <?= $title ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
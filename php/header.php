
<body>
    <div id="wrapper">
        <nav class="navbar navbar-default navbar-cls-top " role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php">Admission Management</a>
            </div>

        </nav>
        <!-- /. NAV TOP  -->
        <nav class="navbar-default navbar-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav" id="main-menu">
                    <li>
                        <div class="user-img-div text-center">
                            <img src="img/admin-p.png" class="img" />
                            <h5 style="color:white;"><?php echo $_SESSION['rainbow_name'];?></h5>
                        </div>

                    </li>


                    <li>
                        <a class="<?php if($page=='dashboard'){ echo 'active-menu';}?>" href="index.php"><i class="fa fa-dashboard "></i>Dashboard</a>
                    </li>
					
					 <li>
                        <a class="<?php if($page=='student'){ echo 'active-menu';}?>" href="student.php"><i class="fa fa-users "></i>Student Management</a>
                    </li>

                    <li>
                        <a class="<?php if($page=='inact'){ echo 'active-menu';}?>" href="inactivestd.php"><i class="fa fa-toggle-off "></i>In-Active Students</a>
                    </li>

                    <li>
                        <a class="<?php if($page=='grade'){ echo 'active-menu';}?>" href="grade.php"><i class="fa fa-th-large"></i>Courses</a>
                    </li>

                    <li>
                        <a class="<?php if($page=='enquiry'){ echo 'active-menu';}?>" href="enquiry.php"><i class="fa fa-th-large"></i>Enquiry</a>
                    </li>
                    
					<li>
                        <a class="<?php if($page=='fees'){ echo 'active-menu';}?>" href="fees.php"><i class="fa fa-money "></i>Fees Section</a>
                    </li>
					 <li>
                        <a class="<?php if($page=='report'){ echo 'active-menu';}?>" href="report.php"><i class="fa fa-file-pdf-o "></i>Report Section</a>
                    </li>
					
					 
					
					<li>
                        <a class="<?php if($page=='setting'){ echo 'active-menu';}?>" href="setting.php"><i class="fa fa-cogs "></i>Account Setting</a>
                    </li>
					
					 <li>
                        <a href="logout.php"><i class="fa fa-power-off "></i>Logout</a>
                    </li>
					
			
                </ul>

            </div>

        </nav>
        <!-- /. NAV SIDE  -->
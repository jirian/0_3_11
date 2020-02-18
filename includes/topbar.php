<!-- Navigation Bar-->
<header id="topnav">
    <div class="topbar-main">
        <div class="container">

            <!-- LOGO -->
            <div class="topbar-left">
                <a href="index.php" class="logo">
                    <i class="zmdi zmdi-group-work icon-c-logo"></i>
                    <span>PatchCableMgr </span>
					<span><small id="orgName"><?php echo $qls->org_info['name']; ?></small></span>
                </a>
				
            </div>
            <!-- End Logo container-->


            <div class="menu-extras">
				<small><span class="label label-warning" title="PatchCableMgr is currently in beta">Beta</span></small>
                <ul class="nav navbar-nav pull-right">

                    <li class="nav-item">
                        <!-- Mobile menu toggle-->
                        <a class="navbar-toggle">
                            <div class="lines">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </a>
                        <!-- End mobile menu toggle-->
                    </li>
					
					<li class="nav-item hidden-sm-down">
                        <form id="searchForm" role="search" class="navbar-left app-search pull-left hidden-xs">
                            <input id="autocomplete" type="text" placeholder="Search..." class="form-control">
                        </form>
                    </li>
					
					<li class="nav-item dropdown notification-list">
                        <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" title="User Guide" href="/userGuide/">
                            <i class="zmdi zmdi-help-outline noti-icon"></i>
                        </a>
					</li>
					<!--
                    <li class="nav-item dropdown notification-list">
                        <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"
                           aria-haspopup="false" aria-expanded="false">
                            <i class="zmdi zmdi-notifications-none noti-icon"></i>
                            <span class="noti-icon-badge"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-arrow dropdown-lg" aria-labelledby="Preview">
                            <div class="dropdown-item noti-title">
                                <h5><small><span class="label label-danger pull-xs-right">7</span>Notification</small></h5>
                            </div>
							
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon bg-success"><i class="icon-bubble"></i></div>
                                <p class="notify-details">Robert S. Taylor commented on Admin<small class="text-muted">1min ago</small></p>
                            </a>

                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon bg-info"><i class="icon-user"></i></div>
                                <p class="notify-details">New user registered.<small class="text-muted">1min ago</small></p>
                            </a>

                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon bg-danger"><i class="icon-like"></i></div>
                                <p class="notify-details">Carlos Crouch liked <b>Admin</b><small class="text-muted">1min ago</small></p>
                            </a>

                            <a href="javascript:void(0);" class="dropdown-item notify-item notify-all">
                                View All
                            </a>

                        </div>
                    </li>
					-->
					<!--
                    <li class="nav-item dropdown notification-list">
                        <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"
                           aria-haspopup="false" aria-expanded="false">
                            <i class="zmdi zmdi-email noti-icon"></i>
							<?php
								$query = $qls->SQL->select('*', 'shared_user_messages', 'to_id = '.$qls->user_info['id'].' AND viewed = 0');
								if($qls->SQL->num_rows($query)) {
							?>
                            		<span class="noti-icon-badge"></span>
							<?php
								}
							?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-arrow dropdown-arrow-success dropdown-lg" aria-labelledby="Preview">
                            <!-- item->
                            <div class="dropdown-item noti-title bg-success">
								<?php
									$query = $qls->SQL->select('*', 'shared_user_messages', array('to_id' => array('=', $qls->user_info['id'])));
									$messageNumber = $qls->SQL->num_rows($query);
									$messageString = $messageNumber ? 'Messages' : 'Message';
								?>
                                <h5><small>Messages<span class="label label-danger pull-xs-right"><?php echo $messageNumber; ?></span></small></h5>
                            </div>
							<?php
								$dateNow = new DateTime('now', $qls->user_info['timezoneObject']);
								$query = $qls->SQL->select('*', 'shared_user_messages', array('to_id' => array('=', $qls->user_info['id'])));
								while($row = $qls->SQL->fetch_assoc($query)) {
									$dateSent = new DateTime($row['date'], new DateTimeZone('UTC'));
									$dateSent->setTimezone($qls->user_info['timezoneObject']);
									$difference = $dateNow->diff($dateSent);
									$differenceFormated = $difference->format('%H:%i:%s ago');
							?>
									<a href="javascript:void(0);" class="dropdown-item notify-item">
										<div class="notify-icon bg-faded">
											<img src="assets/images/users/avatar-2.jpg" alt="img" class="img-circle img-fluid">
										</div>
										<p class="notify-details">
											<b><?php echo $qls->User->id_to_username($row['from_id']); ?></b>
											<span><?php echo $row['subject']; ?></span>
											<small class="text-muted"><?php echo $differenceFormated; ?></small>
										</p>
									</a>
							<?php
								}
							?>

                            <!-- All->
                            <a id="messagesViewAll" href="javascript:void(0);" class="dropdown-item notify-item notify-all">
                                View All
                            </a>

                        </div>
                    </li>
					-->

                    <li class="nav-item dropdown notification-list">
                        <a class="nav-link dropdown-toggle arrow-none waves-effect waves-light nav-user" data-toggle="dropdown" href="#" role="button"
                           aria-haspopup="false" aria-expanded="false">
                            <img src="assets/images/users/user_icon.png" alt="user" class="img-circle">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-arrow profile-dropdown " aria-labelledby="Preview">
                            <!-- item-->
                            <div class="dropdown-item noti-title">
                                <h5 class="text-overflow"><small><?php echo $qls->user_info['username']; ?></small> </h5>
                            </div>

                            <!-- item-->
                            <a href="profile.php" class="dropdown-item notify-item">
                                <i class="zmdi zmdi-account-circle"></i> <span>Profile</span>
                            </a>

                            <!-- item-->
                            <a href="settings.php" class="dropdown-item notify-item">
                                <i class="zmdi zmdi-settings"></i> <span>Settings</span>
                            </a>
							
							<!-- item-->
                            <a id="btnAbout" href="#" class="dropdown-item notify-item">
                                <i class="zmdi zmdi-help"></i> <span>About</span>
                            </a>

                            <!-- item-->
                            <a href="logout.php" class="dropdown-item notify-item">
                                <i class="zmdi zmdi-power"></i> <span>Logout</span>
                            </a>

                        </div>
                    </li>

                </ul>

            </div> <!-- end menu-extras -->
            <div class="clearfix"></div>

        </div> <!-- end container -->
    </div>
    <!-- end topbar-main -->


    <div class="navbar-custom">
        <div class="container">
            <div id="navigation">
                <!-- Navigation Menu-->
                <ul class="navigation-menu">
					<?php
						$userGroup = $qls->Group->fetch_group_info($qls->user_info['group_id'])['name'];
					?>
                    <li>
                        <a href="index.php"><i class="zmdi zmdi-view-dashboard"></i> <span> Dashboard </span> </a>
                    </li>
					
					<?php
						if($userGroup == 'Administrator' or $userGroup == 'Admin' or $userGroup == 'Operator') {
					?>
					<li class="has-submenu">
						<a href="#"><i class="zmdi zmdi-wrench"></i><span> Build </span> </a>
						<ul class="submenu">
							<li><a href="templates.php">Templates</a></li>
							<li><a href="environment.php">Environment</a></li>
						</ul>
					</li>
					<?php } ?>
					
					<li>
                        <a href="explore.php"><i class="zmdi zmdi-globe-alt"></i> <span> Explore </span> </a>
                    </li>
					
					<li>
                        <a href="diagram.php"><i class="fa fa-map-o"></i> <span> Diagram </span> </a>
                    </li>

					<?php
						if($userGroup == 'Administrator' or $userGroup == 'Admin' or $userGroup == 'Operator') {
					?>
                    <li>
                        <a href="scan.php"><i class="fa fa-barcode"></i> <span> Scan </span> </a>
                    </li>
					<?php } ?>
					
					<?php
						if($userGroup == 'Administrator' or $userGroup == 'Admin' or $userGroup == 'Operator') {
					?>
					<li>
                        <a href="cableInventory.php"><i class="fa fa-book"></i> <span> Cable Inventory </span> </a>
                    </li>
					<?php } ?>
					
					<?php
						if($userGroup == 'Administrator' or $userGroup == 'Admin') {
					?>
					<li class="has-submenu">
						<a href="#"><i class="zmdi zmdi-key"></i><span> Admin </span> </a>
						<ul class="submenu">
							<li><a href="admin.php">General</a></li>
							<li><a href="admin-integration.php">Integration</a></li>
						</ul>
                    </li>
					<?php } ?>
					
                </ul>
                <!-- End navigation menu  -->
            </div>
        </div>
    </div>
</header>
<!-- End Navigation Bar-->


<!-- ============================================================== -->
<!-- Start right Content here -->
<!-- ============================================================== -->

<div class="wrapper">
    <div class="container">

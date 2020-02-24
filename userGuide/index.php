<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html lang="en">
<!--<![endif]-->

<head>
	
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keywords" content="">

    <title>PatchCableMgr User Guide</title>

    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/stroke.css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="css/animate.css">
    <link rel="stylesheet" type="text/css" href="css/prettyPhoto.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">

    <link rel="stylesheet" type="text/css" href="js/syntax-highlighter/styles/shCore.css" media="all">
    <link rel="stylesheet" type="text/css" href="js/syntax-highlighter/styles/shThemeRDark.css" media="all">

    <!-- CUSTOM -->
    <link rel="stylesheet" type="text/css" href="css/custom.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>

    <div id="wrapper">

        <div class="container">

            <section id="top" class="section docs-heading">

                <div class="row">
                    <div class="col-md-12">
                        <div class="big-title text-center">
                            <h1>PatchCableMgr User Guide</h1>
                            <p class="lead">Tame Your Patch Cable Nightmare</p>
                        </div>
                        <!-- end title -->
                    </div>
                    <!-- end 12 -->
                </div>
                <!-- end row -->

                <hr>

            </section>
            <!-- end section -->

            <div class="row">

                <div class="col-md-3">
                    <nav class="docs-sidebar" data-spy="affix" data-offset-top="300" data-offset-bottom="200" role="navigation">
                        <ul class="nav">
							<li><a href="/">Home</a></li>
                            <li><a href="#gettingStarted">Getting Started</a></li>
							<li><a href="#dashboard">Dashboard</a></li>
							<li><a href="#build">Build</a>
								<ul class="nav">
									<li><a href="#templates">Templates</a></li>
									<li><a href="#cabinets">Environment</a></li>
								</ul>
							</li>
							<li><a href="#explore">Explore</a></li>
							<li><a href="#scan">Scan</a></li>
							<li><a href="#cableInventory">Cable Inventory</a></li>
							<li><a href="#admin">Admin</a></li>
                        </ul>
                    </nav >
                </div>
                <div class="col-md-9">
                    <section class="welcome">

                        <div class="row">
                            <div class="col-md-12 left-align">
                                <h2 class="dark-text">Introduction<hr></h2>
                                <div class="row">

                                    <div class="col-md-12 full">

                                        <hr>
                                        <div>
                                            <p>Thank you for your interest in PatchCableMgr, a web based patch cable management application designed with simplicity and usability in mind.</p>

                                            <p>This documentation will help you setup, maintain, and get the most out of your account.  If you have any questions, please contact <a href="mailto:support@patchcablemgr.com">support@patchcablemgr.com</a>.</p>

                                        </div>
                                    </div>

                                </div>
                                <!-- end row -->
                            </div>
                        </div>
                    </section>

                    <section id="gettingStarted" class="section">

                        <div class="row">
                            <div class="col-md-12 left-align">
                                <h2 class="dark-text">Getting Started <a href="#top">#back to top</a><hr></h2>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->

                        <div class="row">
                            <div class="col-md-12">
                                <p>There are 3 different ways to get started with an PatchCableMgr account:</p>
								<ol>
									<li><strong>Demo Account</strong>
										<p>The demo account is intended to provide you with a sandbox environment which allows you to immediately start working with the application to determine if it is right for your organization.</p>
										<p>To access the demo account, enter a valid email address on the <a href="https://patchcablemgr.com">landing page</a> and click the "Instant Demo" button.  You will be presented with a login page.  The username is 'demo@patchcablemgr.com' and the password is 'demo'.</p>
										<div class="intro2 clearfix">
											<p><i class="fa fa-exclamation-triangle"></i> The demo account is a shared environment.  Any information entered in the demo account will be visible to the public.  Do not enter sensitive information while using the demo account.
											</p>
										</div>
									</li>
									<li><strong>Hosted Account</strong>
										<p>A hosted account is hosted on PCM servers.  Everything related to server maintenance and support (server configuration, OS & app upgrades, SSL certificates, etc.) is handled by PCM.  Upon registration, a tenant is created with a unique domain name (ie. acme.patchcablemgr.com) and individual database to create an isolated environment.</p>
										<p>To create a hosted account, navigate to the "<a href="https://patchcablemgr.com/#pricing">Plans</a>" section of the landing page and click the "Register" button at the bottom of the "Hosted" box, or click <a href="https://patchcablemgr.com/register.php">here</a> to go directly to the registration form.  Fill out the form and click "Join Now".  Your tenant environment will be created and you will be redirected to the login page.  An email will be sent to the address provided confirming your account.</p>
										<div class="intro2 clearfix">
											<p><i class="fa fa-exclamation-triangle"></i> A hosted account stores information on servers not owned by you or your organization.  This may violate the security policies of your organization.  Consult your organization before entering any information in a hosted account.  Consider using a self-hosted account if you are unsure.
											</p>
										</div>
									</li>
									<li><strong>Self-Hosted Account</strong>
										<p>A self-hosted account is hosted on your server.  Everything related to server maintenance and support is your responsibility.</p>
										<p>To deploy a self-hosted PCM, follow the installation instructions <a href="https://patchcablemgr.com/#installation">here</a>.</p>
									</li>
								</ol>
								<p>PatchCableMgr supports role-based multiple user access.  From administrator account you can send an invitation to members of your team allowing them to work in your PCM environment.</p>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->

                    </section>
                    <!-- end section -->
					
					<section id="dashboard" class="section">

                        <div class="row">
                            <div class="col-md-12 left-align">
                                <h2 class="dark-text">
									<img src="images/dashboard.png">
									Dashboard <a href="#top">#back to top</a><hr>
								</h2>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->

                        <div class="row">
                            <div class="col-md-12">
                                <p>The Dashboard provides a quick overview of your organization's environment.</p>
								<p>The <strong>Cable Inventory</strong> donut chart displays a count of your organization's current patch cable inventory as well as the status which is defined below.</p>
								<ul>
                                    <li><strong>In-Use</strong> - Cables that are in-use have both ends connected to an object port.</li>
                                    <li><strong>Not In-Use</strong> - Cables that are not in-use do not have either end connected to an object port.</li>
									<li><strong>Dead Wood</strong> - Cables that only have one end connected to an object port.</li>
                                    <li><strong>Pending Delivery</strong> - Cables that have been purchased through PatchCableMgr and have not yet been delivered.</li>
                                </ul>
								<p>The <strong>Port Utilization</strong> table displays all devices deployed in your PCM environment and can help identify patch panel availability issues before they become a problem.</p>
								<p>The <strong>History</strong> table displays and add/change/delete actions carried out by users.</p>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->

                    </section>
                    <!-- end section -->
					
					<section id="build" class="section">

                        <div class="row">
                            <div class="col-md-12 left-align">
                                <h2 class="dark-text">
									<img src="images/build.png">
									Build <a href="#top">#back to top</a><hr>
								</h2>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->

                        <div class="row">
                            <div class="col-md-12">
								<p>The Build menu group contains pages where your environment is modeled by creating object templates and racking those templates into cabinets.</p>
                                <h4 id="templates">Templates - <a href="#top">#back to top</a></h4>
                                <p>Use the Template page to create and edit custom templates which represent objects in your environment.  The template page is separated into 3 columns:</p>
								<ol>
                                    <li><strong>Properties</strong> - Sets the properties of the custom template.</li>
                                    <li><strong>Preview</strong> - Displays the custom template as you build it.</li>
									<li><strong>Template Details</strong> - Displays details about a template selected from the list of available templates.</li>
                                </ol>
								<ul>
									<li>
										<h4>Properties:</h4>
										<ul>
											<li><strong>Name</strong> - Defines the name of the template.</li>
											<li><strong>Category</strong> - A category is user created and applies a color to the template as well as the ability to group similar templates together for easier access.</li>
											<li><strong>Template Type</strong> - A stanadard template is one that can be installed in a cabinet by itself.  An insert is a template is installed in an existing object's enclosure partition.</li>
											<li><strong>Template Size</strong> - Defines the Rack Unit size of the template.  Max is 25.</li>
											<li><strong>Template Function</strong> - Defines the template as an endpoint or passive.
												<ol>
													<li>An endpoint template will always terminate a cable path (switch, router, server, etc.).</li>
													<li>A passive template is part of the physical cable infrastructure (patch panel, fiber insert, etc.).</li>
												</ol>
											</li>
											<li><strong>Mounting Configuration</strong> - A 2-post template will only be visible on one side of the installed cabinet and can have another 2-post template installed behind it.  A 4-post template will have a front and a back which occupy both sides of the cabinet it is installed in.</li>
											<li><strong>Add/Remove Partition</strong> - Partitions allow for the template layout to accurately reflect the object it is modeling.  A horizontal partition spans the entire width of the partition it is created in and can grow vertically.  A vertical partition spance the full height of the partition it is created in and can grow horizontally.</li>
											<li><strong>Partition Size</strong> - Horizontal partitions grow vertically in 1/2 RU increments.  Vertical partitions grow horizontally in increments equal to 1/24 of the entire template width.</li>
											<li><strong>Partition Type</strong> - Generic partitions have no properties and can be used as spacers or containers for other partitions.  Connectable partitions contain ports or interfaces.  Enclosure partitions can contain insert templates.</li>
											<li><strong>Port ID</strong> - The format describing how the template ports will be identified.  Clicking the "Configure" button will open a window allowing you to add/change/delete fields that will be used to compile each port ID (ie "Port-1a").  You can configure up to 5 fields of 3 possible field types.
												<ol>
													<li>A <strong>"Static"</strong> field will be compiled into the port ID as it is defined by the user.</li>
													<li>An <strong>"Incremental"</strong> field accepts a single alphanumeric character and will increment with the port numbers.</li>
													<li>A <strong>"Series"</strong> field accepts a comma separated list of strings that will be cycled through when compiling the port ID.</li>
												</ol>
											</li>
											<li><strong>Port Layout</strong> - Number of port columns and rows in the selected connectable partition.</li>
											<li><strong>Port Orientation</strong> - Determines the direction in which port numbers are incremented.  Switches are typically ordered top to bottom while most RJ45 patch panels are ordered left to right.</li>
											<li><strong>Port Type</strong> - The type of port for the selected connectable partition.  When connecting a cable end to an object port, the cable end type and port type must match.  The exception to this rule are SFP ports, which can accept any cable end type.</li>
											<li><strong>Media Type</strong> - This configuration is exclusive to passive templates.  This refers to the cabling behind the passive object.  When trunking two passive objects, the media type must match.</li>
											<li><strong>Enclosure Layout</strong> - This configuration is exclusive to enclosure partition types.  Enclosure layout columns and rows determine the slots available to install insert objects.</li>
											<li><strong>Enclosure Tolerance</strong> - This configuration is exclusive to enclosure partition types.  A "Strict" enclosure will only accept inserts that have been created using an enclosure with the same partition and enclosure dimensions.  A "Loose" enclosure accepts inserts of any size, but some distortion may occur as the insert fills the enclosure space.  An insert must be of the same function (endpoint or passive) as the enclosure it is being installed in.</li>
										</ul>
									</li>
									<li>
										<h4>Preview:</h4>
										<p class="columnContent">The preview card displays the template as it is created.  The "Lock" checkbox determines if the preview box scrolls with the page keeping the template visible even when working at the end of the long list of properties.  The "Front" and "Back" radio buttons toggle the face of the template being displayed.  A yellow highlight appears around the selected partition and indicates that any partition specific configuration will be applied to it.</p>
									</li>
									<li>
										<h4>Template Details:</h4>
										<p class="columnContent">The Selected Template card displays information about the template selected in the Available Templates card.  It also allows for some configuration as well as the ability to delete a selected template.</p>
										<p class="columnContent">
											The Available Templates card lists all templates grouped by category.  Front and Back radio buttons toggle the face of all available templates.  Clicking on an object will highlight the selected partition and display information in the Selected Template card.
											<br><br>
											The "Filter" field allows you to display only templates containing one or more strings of text.
											<br><br>
											The Import button opens a window that allows you to browse a catalog of templates published by PatchCableMgr.  Select a template and click "Import" to add it to your organization's list of available templates.
										</p>
									</li>
								</ul>
							</div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
						
						<div class="row">
                            <div class="col-md-12">
                                <h4 id="cabinets">Environment - <a href="#top">#back to top</a></h4>
                                <p>The Environment page allows you to create a hierarchical representation of your environment's locations, cabinets, and objects as well as their relationship to each other.  The Environment page is separated into 3 sections:</p>
								<ol>
                                    <li><strong>Locations and Cabinets</strong> - Create locations and cabinets.  Define relationships between cabinets.</li>
                                    <li><strong>Cabinet</strong> - Displays the selected cabinet and objects installed.</li>
									<li><strong>Object Details</strong> - Display details about selected objects and their partitions.</li>
                                </ol>
								<h4>Locations and Cabinets:</h4>
								<p class="columnContent">The Location Tree card contains an editable tree of locations and cabinets.  Right click on a location to rename, delete, or create a new location, pod, or cabinet nested within it.
									<br>
									A <strong>location</strong> can represent a physical region, building, floor, or room.  Locations can only be nested under other locations.
									<br>
									A <strong>pod</strong> represents a group of cabinets within a location.  Cabinets within the same pod can have left/right relationships with other cabinets.  Pods can only be nested under locations.
									<br>
									A <strong>cabinet</strong> represents a physical rack or cabinet that can contain objects.  Cabinets can be nested under locations or pods.
									<br>
									A <strong>floorplan</strong> represents the floor of a building.  Floor plans can be nested under locations.
								</p>
								<p class="columnContent">The Cabinet card allows for cabinet properties to be edited.
									<br>
									<strong>RU size</strong> can grow up to 50 RU and shrink as long as the top RU is not occupied by an object.
									<br>
									<strong>RU orientation</strong> determines the direction of RU numbering as well as whether RUs are added/removed from the top (Bottom-Up) or bottom (Top-Down).
									<br>
									<strong>Cable Paths</strong> can be added to represent usable cable paths between cabinets.  Consider a cable path as overhead cabletray, raised floor space, conduit, or any other path that patch cables can be ran.  A cable path can only be created between two cabinets in the same location.  Cable paths and their configured distances are considered when calculating possible cable paths with the path finder function.
									<br>
									<strong>Cabinet adjacencies</strong> can be configured to tell PatchCableMgr which cabinets neighbor the selected one.  A cabinet adjacency can only be created between two cabinets in the same pod.  Cabinet adjacencies are considered when calculating possible cable paths with the path finder function.
								</p>
								<h4>Cabinet:</h4>
								<p class="columnContent">
									The Cabinet card displays the selected cabinet and all of the objects it contains.  Toggling the Front and Back radio buttons switches the cabinet view.  To install an object in the cabinet displayed, drag and drop a template from the Available Templates section.
								</p>
								<h4>Object Details:</h4>
								<p class="columnContent">
									The Object Details card displays information about the selected object and partition as well as templates available to be installed.
									<br>
									The Selected Object section displays information about the object.  The Object Name and Trunked To properties are editable.  Connectable object partitions can be trunked to other connectable object partitions.  When trunking a passive object partition to another passive object partition, the number of ports must be equal and the media type must match.  When trunking a passive object partition to an endpoint object partition, the number of ports must be equal and the endpoint object partition port type must be RJ45.  An endpoint object partition cannot be trunked to another endpoint object partition.
									<br>
									The "Delete" button in the "Actions" dropdown will remove the selected object from the cabinet.
									<br>
									The Available Templates section displays a list of templates available to install in the cabinet grouped by category.  Drag and drop a template into the cabinet to install it as an object.  An insert template can only be installed in an enclosure partition of a standard object.
								</p>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
					</section>

					<section id="explore" class="section">

                        <div class="row">
                            <div class="col-md-12 left-align">
                                <h2 class="dark-text">
									<img src="images/explore.png">
									Explore <a href="#top">#back to top</a><hr>
								</h2>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
						
                        <div class="row">
                            <div class="col-md-12">
                                <p>The Explore page allows you to navigate the environment and display information about objects and how they are connected.  The Explore page is separated into 3 sections:</p>
								<ul>
                                    <li><strong>Locations and Cabinets</strong> - Navigate environment locations and select cabinets.  Locations and cabinets are not editable from the Explore page.</li>
                                    <li><strong>Cabinet</strong> - Displays the selected cabinet and objects installed.  Objects cannot be added, moved, or deleted from the Explore page.</li>
									<li><strong>Object Details</strong> - Display details about selected objects and their partitions.  Object details are not editable from the Explore page.</li>
                                </ul>
								<h4>Locations and Cabinets:</h4>
								<p class="columnContent">
								The Location Tree card contains a tree of locations and cabinets.
								<br>
								A location can represent a physical region, building, floor, or room.
								<br>
								A pod represents a group of cabinets within a location.
								<br>
								A cabinet represents a physical rack or cabinet that can contain objects.
								</p>
								<h4>Cabinet:</h4>
								<p class="columnContent">
								The Cabinet card displays the selected cabinet and all of the objects it contains.  Toggling the Front and Back radio buttons switches the cabinet view.
								</p>
								<h4>Object Details:</h4>
								<p class="columnContent">
								The Object Details section displays information about the selected object and partition as well as cable path details for a specific port.
								<br>
								The Selected Object card displays information about the object and partition.
								<br>
								The Path card displays cable path information about the selected port.  Select a port by clicking on an individual port in a connectable object partition.  The port drop down provides an alternative way to select a port after a connectable object partition has been selected.
								<br>
								The Populated checkbox allows you to flag a port as populated even though an PatchCableMgr managed patch cable has not been connected to it.  This is useful for when you have existing patch cables that have not yet been scanned into your PatchCableMgr inventory, or when it is not necessary for a patch cable to be managed by PatchCableMgr.  Flagging a port as populated will take it out of consideration when calculating possible cable paths with the Path Finder function.
								<br>
								When an individual port is selected, its cable path will be displayed.  The cable path is represented by boxes containing the full name of the object and colored according to the object's category.  A double-ended arrow represents a trunk connection between two objects.  A curved line represents a patch cable.  The currently selected object port is identified by a pin icon.  Endpoint objects are identified by a target icon.
								<br><br>
								Clicking the Path Finder button opens the path finder modal.  The path finder modal allows you to calculate all possible paths between two ports (Endpoint A and Endpoint B).  Endpoint A is determined by the currently selected port.  Use the navigation dropdowns to indicate Endpoint B, the far port you wish to find available paths to.  Once you've narrowed down the path selection dropdowns to an individual port, the Run Path Finder button becomes enabled.  Clicking the Run Path Funder button tells PatchCableMgr to begin calculating all available paths between the two endpoints.  Results are returned in a table indicating the number of patch cables required for each unique path.  The results table indicates the number of local (between objects in the same cabinet), adjacent (between objects in neighboring cabinets), path (between objects reachable via cable path), and total patch cables required to connect the endpoints.  Clicking on a path in the result table will display the full cable path.
								</p>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
						
					</section>
					
					<section id="scan" class="section">
						<div class="row">
                            <div class="col-md-12 left-align">
                                <h2 class="dark-text">
									<img src="images/scan.png">
									Scan <a href="#top">#back to top</a><hr>
								</h2>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
						
                        <div class="row">
                            <div class="col-md-12">
                                <p class="columnContent">
									The Scan page allows you to request and edit data about a Unique Cable End Identifier (UCEI).  Clicking the Scan button displays the scan modal.  Either scan the barcode, or switch to manual input to enter the UCEI.
									<br><br>
									When a valid UCEI has been scanned and has already been initialized, details about the cable and its local and remote ends will be populated.  To connect a cable end to an object port select the location path, object, and port using the navigation dropdowns.
									<br><br>
									When a valid UCEI has been scanned but has not already been initialized, you must scan the remote UCEI and define the cable length, media type, and local/remote connector types.  Once all cable properties have been defined, the Finalize button will be enabled.  Clicking the Finalize button disables the cable property inputs preventing the cable from being edited.
									<br><br>
									A UCEI is invalid when it does not exist in either the initialized cable table or Available Userspace table of the Cable Inventory page.
									<br><br>
									UCEIs are specific to an organization.  UCEIs must be unique with an organization.  UCEIs can be initialized in any order as long as they are listed in the Available Userspace table of the Cable Inventory page.
								</p>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
					</section>
					
					<section id="cableInventory" class="section">
						<div class="row">
                            <div class="col-md-12 left-align">
                                <h2 class="dark-text">
									<img src="images/cable-inventory.png">
									Cable Inventory <a href="#top">#back to top</a><hr>
								</h2>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
						
                        <div class="row">
                            <div class="col-md-12">
                                <p class="columnContent">
									The Cable Inventory page displays all unique cable end identifiers (UCEI) that have been applied to a cable end and scanned into the application as well as unused UCEIs which are available.
									<br><br>
									The Initialized Cables table is separated into 3 column groups.  Each cable end column group contains details specific to that cable end.  The ID column which displays the base36 encoded UCEI as well as a barcode button.  Clicking the UCEI in the ID column will navigate to the scan page and display its properties as if it was scanned.  The barcode button displays the UCEI represented as a code39 barcode.  The Connector column displays the connector type.  The connected column indicates whether the cable end is connected to a port.
									<br><br>
									The Cable Properties columns display details specific to the entire cable.  The Finalized column indicates whether or not the cable is editable (it is suggested that all cables be finalized to prevent unwanted editing).  The Media column displays the cable media type.  The Length column displays the cable length (fiber length is displayed in meters, UTP length is displayed in feet).
									<br><br>
									The Available Userspace table lists all UCEIs that have not been initialized and are available to be attached to a cable end and scanned into the database for use in the application.  UCEIs are uppercase base36 encoded strings.  UCEIs are specific to each organization and must be unique within that organization.  Be cautious not to introduce duplicate UCEIs into your environment as this may cause unwanted behavior.  Each organization is allocated 100 userspace UCEIs upon creation.  Once the number of available UCEIs falls below 100, you may request additional UCEIs by clicking the Add More button.  UCEIs are allocated in sequence.
								</p>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
					</section>
					
					<section id="admin" class="section">
						<div class="row">
                            <div class="col-md-12 left-align">
                                <h2 class="dark-text">
									<img src="images/admin.png">
									Admin <a href="#top">#back to top</a><hr>
								</h2>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
						
                        <div class="row">
                            <div class="col-md-12">
                                <p class="columnContent">
									The Admin page allows organization administrators to perform privileged functions.
									<br><br>
									The Invite User section allows you to send an invitation to an individual to join your organization.  Recipients will receive an email with a link to join.  Existing users can also accept the invitation from their user Profile and can revert back to their original organization at any time.  Invited users are added to your organization with "User" level privileges by default.
									<br><br>
									The Email Settings section allows you to configure how PCM will send emails for the invitation and password reset functions.  There are three methods of sending emails:
									<ul>
										<li><strong>PCM Proxy</strong> - This is the simplest method and uses PCM's email service to send eamils.</li>
										<li><strong>SMTP</strong> - Using an anonymous SMTP relay server is an easy way to deliver emails assuming your organization allows for unauthenticated SMTP relay.</li>
										<li><strong>SMTP (authenticated)</strong> - This method is the most successful way for PCM to deliver emails, but requires an account on an SMTP server.  Google allows for SMTP relay using gmail account.</li>
									</ul>
									<br><br>
									The Organization Name section allows an Administrator to change the organization name which is displayed a the top of the page and is visible by all users.
									<br><br>
									The Manage Users section allows you to remove or change roles of a user.  You cannot delete your own account, you cannot downgrade your role if you are the only administrator.
								</p>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </section>
                    <!-- end section -->

                </div>
                <!-- // end .col -->

            </div>
            <!-- // end .row -->

        </div>
        <!-- // end container -->

    </div>
    <!-- end wrapper -->

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/retina.js"></script>
    <script src="js/jquery.fitvids.js"></script>
    <script src="js/wow.js"></script>
    <script src="js/jquery.prettyPhoto.js"></script>

    <!-- CUSTOM PLUGINS -->
    <script src="js/custom.js"></script>
    <script src="js/main.js"></script>

    <script src="js/syntax-highlighter/scripts/shCore.js"></script>
    <script src="js/syntax-highlighter/scripts/shBrushXml.js"></script>
    <script src="js/syntax-highlighter/scripts/shBrushCss.js"></script>
    <script src="js/syntax-highlighter/scripts/shBrushJScript.js"></script>

</body>

</html>

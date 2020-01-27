<?php
if (!defined('QUADODO_IN_SYSTEM')){
	define('QUADODO_IN_SYSTEM', true);
	require_once('header.php');
}
$results = $qls->SQL->select('*', 'app_object_category');
while ($row = $qls->SQL->fetch_assoc($results)){
	?>
	.category<?php echo $row['name']; ?> {
		background-color: <?php echo $row['color']; ?>;
		color: <?php echo color_inverse($row['color']); ?>;
	}
	<?php
}

function color_inverse($color){
    $color = str_replace('#', '', $color);
    if (strlen($color) != 6){ return '000000'; }
    $rgb = '';
    for ($x=0;$x<3;$x++){
        $c = 255 - hexdec(substr($color,(2*$x),2));
        $c = ($c < 0) ? 0 : dechex($c);
        $rgb .= (strlen($c) < 2) ? '0'.$c : $c;
    }
    return '#'.$rgb;
}
?>

.noCategory {
	background-color: lightgray;
}

#cablePathTable tbody tr {
	cursor: pointer;
}

.objectBox {
	margin-top: 5px;
	border-radius: 3px;
	padding: 3px;
	border: 1px solid gray;
}

.cursorPointer {
	cursor: pointer;
}

.cursorGrab {
    cursor: move; /* fallback if grab cursor is unsupported */
    cursor: grab;
    cursor: -moz-grab;
    cursor: -webkit-grab;
}

.tableRowHighlight {
	background-color: #039cfd36 !important;
}

.inputBlock {
	display: block;
	position: relative;
	left: 20px;
}

.dependantField {
	display: none;
}

.rackObjSelected {
	//border: 2px solid yellow;
	box-shadow: inset 0 0 2px 2px yellow;
}

.floorplanObjSelected {
	//border: 2px solid yellow;
	//box-shadow: inset 0 0 2px 2px yellow;
	background-color: yellow !important;
}

.floorplanObject {
	color: #039cfd;
	background-color: white;
}

.objBaseline {
	text-align: center;
	width: 100%;
}

.port.RJ45 {
	background-image: url("/assets/images/portIcons/RJ45-black.png");
}

.port.RJ45.populated {
	background-image: url("/assets/images/portIcons/RJ45-red.png") !important;
}

.port.RJ45.endpointTrunked {
	background-image: url("/assets/images/portIcons/RJ45-gray.png");
}

.port.LC {
	background-image: url("/assets/images/portIcons/LC-black.png");
}

.port.LC.populated {
	background-image: url("/assets/images/portIcons/LC-red.png") !important;
}

.port.LC.endpointTrunked {
	background-image: url("/assets/images/portIcons/LC-gray.png");
}

.port.SC {
	background-image: url("/assets/images/portIcons/SC-black.png");
}

.port.SC.populated {
	background-image: url("/assets/images/portIcons/SC-red.png") !important;
}

.port.SC.endpointTrunked {
	background-image: url("/assets/images/portIcons/SC-gray.png");
}

.port.SFP {
	background-image: url("/assets/images/portIcons/SFP-black.png");
}

.port.SFP.populated {
	background-image: url("/assets/images/portIcons/SFP-red.png") !important;
}

.port.SFP.endpointTrunked {
	background-image: url("/assets/images/portIcons/SFP-gray.png");
}

.port {
	height: 8px;
	width: 8px;
	margin: auto;
	background-size: 8px 8px;
}

.populated {
	//background-color: red;
}

.endpointTrunked {
	//background-color: gray;
}

.RU1 {
	height: 25px;
}

.RU2 {
	height: 50px;
}

.RU3 {
	height: 75px;
}

.RU4 {
	height: 100px;
}

.RU5 {
	height: 125px;
}

.RU6 {
	height: 150px;
}

.RU7 {
	height: 175px;
}

.RU8 {
	height: 200px;
}

.RU9 {
	height: 225px;
}

.RU10 {
	height: 250px;
}

.RU11 {
	height: 275px;
}

.RU12 {
	height: 300px;
}

.RU13 {
	height: 325px;
}

.RU14 {
	height: 350px;
}

.RU15 {
	height: 375px;
}

.RU16 {
	height: 400px;
}

.RU17 {
	height: 425px;
}

.RU18 {
	height: 450px;
}

.RU19 {
	height: 475px;
}

.RU20 {
	height: 500px;
}

.RU21 {
	height: 525px;
}

.RU22 {
	height: 550px;
}

.RU23 {
	height: 575px;
}

.RU24 {
	height: 600px;
}

.RU25 {
	height: 625px;
}

.flex-container {
	display: flex;
	height: 100%;
}

.border-black {
	box-sizing: border-box;
	border: 1px solid black;
}

.transparency-20 {
	background-color: rgba(255, 255, 255, .2);
}

.flex-container-parent {
	display: flex;
	height: 100%;
}

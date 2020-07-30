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

.navTree {
	<?php echo ($qls->user_info['treeSize']) ? '' : 'max-height: 200px;'; ?>
	overflow: auto;
}

.dataTables_wrapper .dataTables_filter {
	float: right;
	text-align: left;
}

.dataTables_scrollBody thead {visibility: hidden;}

.printImage {
	position: absolute;
}

.m-l-10 {
	margin-left: 10px;
}

.m-r-10 {
	margin-right: 10px;
}

.diagramLocationBox {
	box-shadow: -2px -2px 10px grey;
	padding-left: 20px;
	margin-bottom: 20px;
}

.diagramLocationBoxTitle {
	padding-bottom: 10px;
}

.diagramLocationSubBox {
	display:flex;
}

.diagramCabinetContainer {
	width: 350px;
	padding-left: 20px;
}

.noCategory {
	background-color: lightgray;
}

#cablePathTable tbody tr {
	cursor: pointer;
}

.objectBox {
	display: inline-block;
	margin-top: 5px;
	margin-bottom: 5px;
	border-radius: 3px;
	padding: 3px;
	border: 1px solid gray;
	-webkit-print-color-adjust: exact;
}

.noSelect {
	-webkit-touch-callout: none; /* iOS Safari */
	-webkit-user-select: none; /* Safari */
	-khtml-user-select: none; /* Konqueror HTML */
	-moz-user-select: none; /* Old versions of Firefox */
	-ms-user-select: none; /* Internet Explorer/Edge */
	user-select: none; /* Non-prefixed version, currently supported by Chrome, Opera and Firefox */
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
	background-color: yellow !important;
}

.floorplanObject {
	color: #039cfd;
}

.objBaseline {
	text-align: center;
	width: 100%;
}

.trunk.stacked {
	background-image: url("/assets/images/cableIcons/stacked-trunk.png");
	height: 50px;
	width: 30px;
	margin: auto;
	background-size: 30px 100%;
	background-repeat: no-repeat;
	background-position: center center;
	-webkit-print-color-adjust: exact;
}

.trunk.adjacent {
	background-image: url("/assets/images/cableIcons/adjacent-trunk.png");
	height: 30px;
	margin: auto;
	background-size: 15px 30px;
	background-repeat: no-repeat;
	background-position: center center;
	-webkit-print-color-adjust: exact;
}

.cable.Unk.stacked {
	background-image: url("/assets/images/cableIcons/stacked-Unk-black.png");
	background-position: center center;
}

.cable.SM-OS1.stacked {
	background-image: url("/assets/images/cableIcons/stacked-SM-yellow.png");
	background-position: center center;
}

.cable.MM-OM3.stacked, .cable.MM-OM4.stacked {
	background-image: url("/assets/images/cableIcons/stacked-MM-aqua.png");
	background-position: center center;
}

.cable.Cat5e.stacked, .cable.Cat6.stacked, .cable.Cat6a.stacked {
	background-image: url("/assets/images/cableIcons/stacked-Eth-green.png");
	background-position: center center;
}

.cable.Unk.adjacent {
	background-image: url("/assets/images/cableIcons/adjacent-Unk-black.png");
	background-position: left;
	width: 150px;
}

.cable.SM-OS1.adjacent {
	background-image: url("/assets/images/cableIcons/adjacent-SM-yellow.png");
	background-position: left;
	width: 150px;
}

.cable.MM-OM3.adjacent, .cable.MM-OM4.adjacent {
	background-image: url("/assets/images/cableIcons/adjacent-MM-aqua.png");
	background-position: left;
	width: 150px;
}

.cable.Cat5e.adjacent, .cable.Cat6.adjacent, .cable.Cat6a.adjacent {
	background-image: url("/assets/images/cableIcons/adjacent-Eth-green.png");
	background-position: left;
	width: 150px;
}

.port.Unk {
	background-image: url("/assets/images/portIcons/Unk-black.png");
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

.port.MPO-12 {
	background-image: url("/assets/images/portIcons/MPO-black.png");
}

.port.MPO-12.populated {
	background-image: url("/assets/images/portIcons/MPO-red.png") !important;
}

.port.MPO-12.endpointTrunked {
	background-image: url("/assets/images/portIcons/MPO-gray.png");
}

.port.MPO-24 {
	background-image: url("/assets/images/portIcons/MPO-black.png");
}

.port.MPO-24.populated {
	background-image: url("/assets/images/portIcons/MPO-red.png") !important;
}

.port.MPO-24.endpointTrunked {
	background-image: url("/assets/images/portIcons/MPO-gray.png");
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

.port.QSFP {
	background-image: url("/assets/images/portIcons/SFP-black.png");
}

.port.QSFP.populated {
	background-image: url("/assets/images/portIcons/SFP-red.png") !important;
}

.port.QSFP.endpointTrunked {
	background-image: url("/assets/images/portIcons/SFP-gray.png");
}

.port {
	height: 8px;
	width: 8px;
	margin: auto;
	background-size: 8px 8px;
	background-repeat: no-repeat;
	-webkit-print-color-adjust: exact;
}

.cable {
	height: 40px;
	width: 100%;
	margin: auto;
	background-size: 20px 100%;
	background-repeat: no-repeat;
	-webkit-print-color-adjust: exact;
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

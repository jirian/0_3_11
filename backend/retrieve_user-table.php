<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('administrator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once '../includes/Validate.class.php';
	
	$validate = new Validate($qls);
	$validate->returnData['success'] = array();
	
	if ($validate->returnData['active'] == 'inactive') {
		$html .= json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		$html = '';
		$groupArray = array();
		$query = $qls->SQL->select('*', 'groups');
		while($groupRow = $qls->SQL->fetch_assoc($query)) {
			$groupArray[$groupRow['id']] = $groupRow;
		}
		
		$query = $qls->SQL->select('*', 'users');
		while($row = $qls->SQL->fetch_assoc($query)) {
			$html .= '<tr>';
			$html .= '<td>'.$row['username'].'</td>';
			if($row['id'] != $qls->user_info['id']) {
				$html .= '<td><a class="editableUserStatus" href="#" data-type="select" data-pk="'.$row['id'].'" data-value="'.$row['blocked'].'"></a></td>';
			} else {
				$html .= '<td>Unblocked</td>';
			}
			if($row['id'] != $qls->user_info['id']) {
				$html .= '<td><a class="editableUserMFA" href="#" data-type="select" data-pk="'.$row['id'].'" data-value="'.$row['mfa'].'"></a></td>';
			} else {
				$html .= ($row['mfa']) ? '<td>Yes</td>' : '<td>No</td>';
			}
			if($row['id'] == $qls->user_info['id']) {
				$html .= '<td>'.$groupArray[$row['group_id']]['name'].'</td>';
			} else {
				$html .= '<td><a class="editableUserRole" href="#" data-type="select" data-pk="'.$row['id'].'" data-value="'.$row['group_id'].'" data-userType="active"></a></td>';
			}
			
			$html .= '<td>';
			if($row['id'] != $qls->user_info['id']) {
				$html .= '<button class="buttonRemoveUser btn btn-sm waves-effect waves-light btn-danger" data-userType="active" data-userID="'.$row['id'].'" type="button" title="Remove user">';
				$html .= '<i class="fa fa-remove"></i>';
				$html .= '</button>';
			}
			$html .= '</td>';
			$html .= '</tr>';
		}
		
		$query = $qls->SQL->select('*', 'invitations', array('used' => array('=', 0)));
		while($row = $qls->SQL->fetch_assoc($query)) {
			$html .= '<tr>';
			$html .= '<td>'.$row['email'].'</td>';
			$html .= '<td>Pending</td>';
			$html .= '<td>N/A</td>';
			$html .= '<td><a class="editableUserRole" href="#" data-type="select" data-pk="'.$row['id'].'" data-value="'.$row['group_id'].'" data-userType="invitation"></a></td>';
			$html .= '<td>';
				$html .= '<button class="buttonRemoveUser btn btn-sm waves-effect waves-light btn-danger" data-userType="invitation" data-userID="'.$row['id'].'" type="button" title="Remove user">';
				$html .= '<i class="fa fa-remove"></i>';
				$html .= '</button>';
			$html .= '</td>';
			$html .= '</tr>';
		}
		$validate->returnData['success'] = $html;
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	
}
?>

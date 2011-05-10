<?php
/**
 * Sticky Notes
 * Made for MODx Evolution 1.x
 * By Amir Hossein Hodjati Pour (AHHP) ~ Boplo.ir
 * May 2011
 * Version 1.0.2
 */

include './includes/config.php';

$action = isset($_POST['stk_action']) ? $_POST['stk_action'] : 'get';
$key = isset($_POST['stk_key']) ? $modx->db->escape($_POST['stk_key']) : 'main';
$user = $_SESSION['mgrInternalKey'];

Switch($action) {

	Case 'archive':
	Case 'delete':
	Case 'finish':
		if($action == 'delete') {
			$fields = array('deleted'=>1);
			$target = 'stk-trash';
			$where = "(user=$user)";
			$countSql = "SELECT COUNT(id) FROM $table WHERE deleted=1 AND user=$user";
			$isDeleted = $modx->db->getValue("SELECT deleted FROM $table WHERE id=$key");
			if($isDeleted == 1) {
				$modx->db->delete($table, "id=$key");
				$count = $modx->db->getValue($countSql);
				$response = '{"error":false,"target":"' .$target. '","count":'.$count.',"row":""}';
				Break;
			}
		}
		if($action == 'archive') {
			$fields = array('archived'=>1);
			$target = 'stk-archive';
			$where = "((user=$user AND to_user IS NULL) OR (user!=$user AND to_user=$user))";
			$countSql = "SELECT COUNT(id) FROM $table WHERE deleted=0 AND archived=1 AND (user=$user OR to_user=$user)";
		}
		if($action == 'finish') {
			$fields = array('finished'=>1, 'finishedon'=>time());
			$target = 'stk-finished';
			$where = "((user=$user AND to_user IS NULL) OR (user!=$user AND to_user=$user))";
			$countSql = "SELECT COUNT(id) FROM $table WHERE deleted=0 AND archived=0 AND finished=1";
		}
		$modx->db->update($fields, $table, "id=$key AND $where");
		
		$data = $modx->db->getRow(
			$modx->db->query("
				SELECT 
					sn.*, 
					IF(ua1.fullname='',mu1.username,ua1.fullname) AS myName,
					IF(ua2.fullname='',mu2.username,ua2.fullname) AS otherName
				FROM $table sn 
				LEFT JOIN {$modx->db->config['table_prefix']}manager_users mu1 ON sn.user = mu1.id
				LEFT JOIN {$modx->db->config['table_prefix']}manager_users mu2 ON sn.to_user = mu2.id
				LEFT JOIN {$modx->db->config['table_prefix']}user_attributes ua1 ON mu1.id=ua1.internalKey
				LEFT JOIN {$modx->db->config['table_prefix']}user_attributes ua2 ON mu2.id=ua2.internalKey
				WHERE sn.id=$key
			")
		);
		$data['panel'] = $key;
		$data['isOrder'] = $data['user']==$user && !empty($data['to_user']) ? 1 : 0;
		$data['isTodo'] = $data['to_user'] == $user ? 1 : 0;
		$data['canDelete'] = $data['user'] == $user ? 1 : 0;
		$data['isNew'] = ($key == 'todo' && $data['isOrder'] == 0 && $data['createdon'] < $_SESSION['mgrLastlogin']) ? 1 : 0;
		$data['user'] = $data['isOrder'] == 1 ? "For $data[otherName]" : "From $data[myName]";
		$data['comment'] = nl2br($data['comment']);
		$data['createdon'] = date('d M Y H:i', $data['createdon']);
		$data['finishedon'] = date('d M Y H:i', $data['finishedon']);
		$data['must_be_finishedon'] = $data['must_be_finishedon']==0 ? 0 : date('d M Y H:i', $data['must_be_finishedon']);
		$item = include STICKY_BASE_PATH . 'item.php';
		
		$count = $modx->db->getValue($countSql);
		$response = '{"error":false,"target":"' .$target. '","count":'.$count.',"row":' .sticky_json($item). '}';
		Break;
	
	
	
	Case 'refresh':
		$where = 'FALSE';
		if($key == 'main')
			$where = "sn.user=$user AND sn.deleted=0 AND sn.archived=0 AND sn.finished=0 AND sn.to_user IS NULL";
		if($key == 'todo')
			$where = "sn.deleted=0 AND sn.archived=0 AND sn.finished=0 AND ((sn.user=$user AND sn.to_user IS NOT NULL) OR sn.to_user=$user)";
		if($key == 'finished')
			$where = "sn.deleted=0 AND sn.archived=0 AND sn.finished=1 AND (sn.user=$user OR sn.to_user=$user)";
		if($key == 'archive')
			$where = "sn.deleted=0 AND sn.archived=1 AND (sn.user=$user OR sn.to_user=$user)";
		if($key == 'trash')
			$where = "sn.deleted=1 AND sn.user=$user";
		
		$select = $modx->db->query("
			SELECT 
				sn.*, 
				IF(ua1.fullname='',mu1.username,ua1.fullname) AS myName,
				IF(ua2.fullname='',mu2.username,ua2.fullname) AS otherName
			FROM $table sn 
			LEFT JOIN {$modx->db->config['table_prefix']}manager_users mu1 ON sn.user = mu1.id
			LEFT JOIN {$modx->db->config['table_prefix']}manager_users mu2 ON sn.to_user = mu2.id
			LEFT JOIN {$modx->db->config['table_prefix']}user_attributes ua1 ON mu1.id=ua1.internalKey
			LEFT JOIN {$modx->db->config['table_prefix']}user_attributes ua2 ON mu2.id=ua2.internalKey
			WHERE $where
		");
		$count = $modx->db->getRecordCount($select);
		$items = '';
		while($data = $modx->db->getRow($select)) {
			$data['panel'] = $key;
			$data['isOrder'] = $data['user']==$user && !empty($data['to_user']) ? 1 : 0;
			$data['isTodo'] = $data['to_user'] == $user ? 1 : 0;
			$data['canDelete'] = $data['user'] == $user ? 1 : 0;
			$data['isNew'] = ($key == 'todo' && $data['isOrder'] == 0 && $data['createdon'] < $_SESSION['mgrLastlogin']) ? 1 : 0;
			$data['user'] = $data['isOrder'] == 1 ? "For $data[otherName]" : "From $data[myName]";
			$data['comment'] = nl2br($data['comment']);
			$data['createdon'] = date('d M Y H:i', $data['createdon']);
			$data['finishedon'] = date('d M Y H:i', $data['finishedon']);
			$data['must_be_finishedon'] = $data['must_be_finishedon']==0 ? 0 : date('d M Y H:i', $data['must_be_finishedon']);
			$items .= include STICKY_BASE_PATH . 'item.php';
		}
		$response = '{"error":false,"result":{"count":' .$count. ',"content":' .sticky_json($items). '}}';
		Break;
	
	
	
	Case 'managers':
		$options = '<option value=""></option>';
		$select = $modx->db->query("
			SELECT mu.id,mu.username,ua.fullname FROM {$modx->db->config['table_prefix']}manager_users mu
			LEFT JOIN {$modx->db->config['table_prefix']}user_attributes ua ON mu.id=ua.internalKey
			WHERE mu.id != $user
		");
		while($row = $modx->db->getRow($select))
			$options .= '<option value="' .$row['id']. '">' .($row['fullname']==''?$row['username']:$row['fullname']). '</option>';
		$options .= '<option value="" disabled="disabled">-------------------------</option>';
		$options .= '<option value="0">All users</option>';
		$response = '{"error":false,"result":' .sticky_json($options). '}';
		Break;
	
	
	
	Case 'form':
		if(empty($_POST['title']) ) { $response = '{"error":"true","result":"Title can not be empty!"}'; Break; }
		$data = array(
			'title' => $modx->db->escape($_POST['title']),
			'comment' => (!empty($_POST['comment']) ? $modx->db->escape($_POST['comment']) : ''),
			'progress' => (!empty($_POST['progress']) ? ($_POST['progress']>100?100:intval($_POST['progress'])) : 1),
			'finished' => (!empty($_POST['finished']) ? 1 : 0),
			'archived' => (!empty($_POST['archived']) ? 1 : 0)
		);
		if(!empty($_POST['must_be_finishedon'])) $data['must_be_finishedon'] = strtotime($_POST['must_be_finishedon']);
		if(!empty($_POST['archived']) || !empty($_POST['restore'])) $data['deleted'] = 0;
		if(!empty($_POST['finished'])) $data['finishedon'] = time();
		
		
		if(!empty($_POST['note'])) {
			$note = intval($_POST['note']);
			$panelData = $modx->db->getRow( $modx->db->select('*',$table,"id=$note") );
			$oldPanel = sticky_getPanel($panelData);
			$result = $modx->db->update($data, $table, 'id='.intval($_POST['note']));
		} else {
			$oldPanel = null;
			$data['createdon'] = time();
			$data['user'] = $user;
			if(!empty($_POST['to_user'])) {
				if($_POST['to_user'] == 0) {
					$select = $modx->db->query("SELECT id FROM manager_users WHERE id != $user");
					while($row = $modx->db->getRow($select)) {
						$data['to_user'] = $row['id'];
						$result = $modx->db->insert($data, $table);
					}
				} else {
					$data['to_user'] = intval($_POST['to_user']);
					$result = $modx->db->insert($data, $table);
				}
			} else {
				$result = $modx->db->insert($data, $table);
			}
		}
		if($result==false) {
			$response = '{"error":true,"result":"There is a problem!"}';
		} else {
			$newPanel = sticky_getPanel($data);
			$panels = "[\"$newPanel\",\"" . (isset($oldPanel) ? $oldPanel : $newPanel) . "\"]";
			$response = '{"error":false,"result":"OK","panels":' .$panels. '}';
		}
		Break;
	
	
	
	Case 'fillForm':	
		$row = $modx->db->getRow($modx->db->select('*',$table,"id=$key"));
		$obj = new stdClass;
		$obj->error = false;
		$obj->row = new stdClass;
		$obj->row->title = $row['title'];
		$obj->row->progress = $row['progress'];
		$obj->row->comment = $row['comment'];
		$obj->row->deleted = $row['deleted'];
		$obj->row->archived = $row['archived'];
		$obj->row->finished = $row['finished'];
		$obj->row->to_user = ($row['to_user'] ? $row['to_user'] : 0);
		$obj->row->must_be_finishedon = ($row['must_be_finishedon']>0 ? date('Y-m-d H:i:00', $row['must_be_finishedon']) : '');
		$response = sticky_json($obj);
		Break;
	
	
	
	Case 'emptyTrash':
		$modx->db->delete($table, "deleted=1 AND user=$user");
		$response = '{"error":false,"result":"OK"}';
		Break;
	
	
	
	Case 'help':
		$response = file_get_contents('./help.html');
		Break;
	
	
	
	Case 'search':
		$ids = array();
		$select = $modx->db->query("SELECT id FROM $table WHERE title LIKE '%$key%' OR comment LIKE '%$key%'");
		while($id = $modx->db->getValue($select)) $ids[] = $id;
		$response = '{"error":false,"result":' .sticky_json($ids). '}';
		Break;
	
}





die($response);
?>

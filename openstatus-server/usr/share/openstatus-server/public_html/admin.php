<?php
$requirelogin = true;
require('../header.php');

if ($auth === true) {

	echo '<div id="stats" class="stats_container">';

	if (isset($_GET['editnote'])) {
		if (isset($_POST['noteedit'])) {
			$update = $db->prepare('UPDATE servers SET note = ? WHERE uid = ?');
			$update->execute(array($_POST['note'], intval($_GET['editnote'])));
			header('Location: admin.php');
		}
		echo '<table style="width: 600px">';
		$query = $db->prepare('SELECT * FROM servers WHERE uid = ?');
		$query->execute(array(intval($_GET['editnote'])));
		$server = $query->fetch(PDO::FETCH_ASSOC);
		echo '<tr><th>Edit Note: '.$server['hostname'].'</th>';
		echo '<tr><td><form action="admin.php?editnote='.$server['uid'].'" method="post"><textarea name="note" style="width: 90%; height: 100px;">'.$server['note'].'</textarea></td></tr>';
		echo '<tr><td><input type="submit" name="noteedit" value="Save" /></td></tr>';
		echo '</table>';
	} else {

		if (isset($_POST['addservice'])) {
			$query = $db->prepare('INSERT INTO `processes` (`uid`, `process`, `name`, `disabled`, `status`) VALUES (?, ?, ?, ?, ?)');
			$q = $query->execute(array($_POST['uid'], $_POST['servicename'], $_POST['servicename'], 0, 1));
			if ($q === FALSE) {
				print_r($db->errorInfo());
			}
		}
		if (isset($_POST['addserver'])) {
			$query = $db->prepare('INSERT INTO `servers` (`hostname`, `ip`, `disabled`) VALUES (?, ?, ?)');
			$q = $query->execute(array($_POST['hostname'], $_POST['ip'], 0));
		}


	        $dbs = $db->prepare('SELECT * FROM servers WHERE disabled = 0 ORDER BY hostname ASC');
	        $result = $dbs->execute();
	        $i = 0;
	        $provider = '';
		echo '
			<table>
			<thead>
				<tr><th colspan="5">Servers</th></tr>
				<tr>
					<th style="width: 25px">UID</th>
					<th style="width: 100px">Hostname/IP</th>
					<th style="width: 150px">Services</th>
					<th>Notes</th>
					<th style="width: 100px">Actions</th>
				</tr>
			</thead>';
	        while ($row = $dbs->fetch(PDO::FETCH_ASSOC)) {
			echo '
				<tr>
					<td>' .$row['uid']. '</td>
					<td>' .$row['hostname']. '</td><td>';
	                $dbq = $db->prepare('SELECT * FROM processes WHERE uid = ? ORDER BY name ASC');
	                $dbr = $dbq->execute(array($row['uid']));
	                echo '<table class="services">';
	                while ($service = $dbq->fetch(PDO::FETCH_ASSOC)) {
	                        echo '<tr><td>'. $service['name'] .'</td></tr>';
	                }
			echo '</table>';
			echo '</td>
					<td>'.$row['note'].'</td>
					<td><a href="admin.php?editnote='. $row['uid']. '">Edit Notes</a></td>
				</tr>';
		}
		echo '
			</table>';
		echo '
			<form action="admin.php?addserver" method="post">
			<table>
			<thead>
				<tr><th colspan="2">Add Server</th></tr>
				<tr>
					<th>Hostname</th>
					<th>IP</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><input type="name" name="hostname" style="width: 150px" /></td>
					<td><input type="text" name="ip" style="width: 150px" /></td>
				</tr>
				<tr>
					<td colspan="2"><input type="submit" name="addserver" value="Add Server" /></td>
				</tr>
			</tbody>
			</table>
			</form>';

		echo '
			<form action="admin.php?addservice" method="post">
			<table>
			<thead>
				<tr><th colspan="2">Add Service</th></tr>
				<tr>
					<th>Host</th>
					<th>Service Name</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<select name="uid">
							<option value="">---</option>';
		$dbq = $db->prepare('SELECT * FROM servers ORDER BY hostname ASC');
		$dbr = $dbq->execute();
		while ($host = $dbq->fetch(PDO::FETCH_ASSOC)) {
			echo '<option value="'.$host['uid'].'">'.$host['hostname'].'</option>';
		}
		echo '				</select>
					</td>
					<td><input type="text" name="servicename" style="width: 150px" /></td>
				</tr>
				<tr>
					<td colspan="2"><input type="submit" name="addservice" value="Add Service" /></td>
				</tr>
			</tbody>
			</table>
			</form>';
	}
	echo '</div>';
}

require('../footer.php');
?>
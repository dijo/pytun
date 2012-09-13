<?
// DB Configuration
$db_name = '';
$db_host = '';
$db_user = '';
$db_pass = '';

// Global variables
$timestamp = time();
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
$client_name = -1;
$client_id = -1;

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}


// Install database, if an install parameter is given
if (isset($_REQUEST['install'])) {
    $mysqli->query('CREATE TABLE httpvpn_packets (id INT(11) auto_increment, packet_id INT(11) NOT NULL, packet VARCHAR(4600), client_id INT(11), PRIMARY KEY(id));');
    $mysqli->query('CREATE TABLE httpvpn_clients (id INT(11) NOT NULL auto_increment, name VARCHAR(2000), last_activity INT(11) NOT NULL, PRIMARY KEY(id));');
}

// Get user information from DB or create new one
if (!isset($_REQUEST['client_id'])) {
    print("Give me client id!");
    exit();
} else {
    // We have client_id, so fetch it
    $res = $mysqli->query('SELECT id, name, last_activity FROM httpvpn_clients WHERE name="'.$mysqli->real_escape_string($_REQUEST['client_id']).'"');
    if ($res->num_rows == 0) {
        // Or create, if he is not known
        $mysqli->query('INSERT INTO httpvpn_clients(name, last_activity) VALUES("'.$mysqli->real_escape_string($_REQUEST['client_id']).'", '.$timestamp.')');
        
        // Query again, to fetch client id from db
        $res = $mysqli->query('SELECT id, name, last_activity FROM httpvpn_clients WHERE name="'.$mysqli->real_escape_string($_REQUEST['client_id']).'"');
    }
    $row = $res->fetch_assoc();
    $client_id = $row['id'];
    $client_name = $row['name'];
    
    $mysqli->query('UPDATE httpvpn_clients SET last_activity='.$timestamp.' WHERE id='.$row['id']);
}

// Delete old packets (timeout is 60s)
$mysqli->query('DELETE FROM httpvpn_packets WHERE timestamp<'.$timestamp - 60);

// Insert new packages
if (isset($_REQUEST['data'])) {
    $res = $mysqli->query('SELECT id FROM httpvpn_clients WHERE id != '.$client_id);
    print $res->num_rows;
    while ($row = $res->fetch_assoc()) {
        $mysqli->query('INSERT INTO httpvpn_packets(packet_id, packet, client_id) VALUES ('.$timestamp.', "'.$mysqli->real_escape_string($_REQUEST['data']).'", '.$row['id'].')');
    }
}

// Send waiting packages
$res = $mysqli->query('SELECT id, packet FROM httpvpn_packets WHERE client_id='.$client_id.'');
while ($row = $res->fetch_assoc()) {
    print($row['packet'] . "\n");
    $mysqli->query('DELETE FROM httpvpn_packets WHERE id='.$row['id']);
}
?>

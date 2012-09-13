<?
// Read client id (name)
if (!isset($_REQUEST['client_id'])) {
    print("Give me client id!");
    exit();
}

// Create client's data directory
$client_id = $_REQUEST['client_id'];
if (!is_dir('vpn-data/' . $client_id)) {
        mkdir('vpn-data/' . $client_id);
}

// Save packets for other clients, if given
if (isset($_REQUEST['data'])) {
    $timestamp = microtime(True);
    $d = opendir('vpn-data/');
    while ($entry = readdir($d)) {
        if ($entry != "." and $entry != ".." and is_dir('vpn-data/' . $entry) and $entry != $client_id) {        
            $fp = fopen('vpn-data/' . $entry . '/' . $timestamp, 'wb');
            print $_REQUEST['data'];
            fwrite($fp, $_REQUEST['data']);
            fclose($fp);
        }
    }
}

// Read data for this client name
$d = opendir('vpn-data/' . $client_id);
while ($entry = readdir($d)) {
    if (is_file('vpn-data/' . $client_id . '/' . $entry)) {
        $fp = fopen('vpn-data/' . $client_id . '/' . $entry, 'r');
        $data = fread($fp, 2000);        
        print $data;
        fclose($fp);
        unlink('vpn-data/' . $client_id . '/' . $entry);
    }
}
?>

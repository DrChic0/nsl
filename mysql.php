<?php
$servername = "localhost";
$username = "SECRET";
$password = "SECRET";
$dbname = "nsl";

try
{
    $db = new PDO("mysql:host=".$servername.";dbname=".$dbname.";charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
}
catch(PDOException $e)
{
    die("An error occured connecting to the database: ".$e->getMessage());
}

session_start();

$availablePorts = array(24411, 24417, 24418, 24419);
/*
$stmt = $db->prepare("SELECT * FROM servers WHERE online = 1 AND hosting = 2 ORDER BY RAND() LIMIT 1");
$stmt->execute();

while($server = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if(((new DateTime())->getTimestamp() - (new DateTime($server['lastping']))->getTimestamp()) >= (10 * 60)) {
        $response = file_get_contents("https://api.novetusserverlist.com/get_players?port=" . $server['port']);
        $response = json_decode($response, true);
        $players = [];
        if($response[0]['code'] != 403) {
            foreach($response[0]['message'] as $player) {
                $stmt1 = $db->prepare("SELECT username FROM users WHERE username LIKE :username");
                $stmt1->execute([':username' => substr($player, 0, strlen($player) / 2) . "%"]);

                if($stmt1->rowCount() or $player == "[SERVER]") {
                    $players[] = $player;
                }
            }

            $stmt1 = $db->prepare("UPDATE servers SET players = :players, lastping = CURRENT_TIMESTAMP(), online = 1 WHERE id = :id");
            $stmt1->execute([
                ':players' => json_encode($players),
                ':id' => $server['id']
            ]);
        } else {
            $stmt1 = $db->prepare("UPDATE servers SET players = '[]', online = 0 WHERE id = :id");
            $stmt1->execute([
                ':id' => $server['id']
            ]);
        }
    }
}

$stmt = $db->prepare("SELECT * FROM servers WHERE online = 1 AND hosting = 1 ORDER BY RAND()");
$stmt->execute();

while($server = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if(((new DateTime())->getTimestamp() - (new DateTime($server['lastping']))->getTimestamp()) >= (10 * 60)) {
        // ping player hosted servers
    }
}
*/

if(isset($_SESSION['siteipban'])) {
	$stmt = $db->prepare("SELECT * FROM bans WHERE username = :username");
	$stmt->execute([':username' => $_SESSION['siteipban']]);
	
	if(!$stmt->rowCount()) {
		$reason = "tried to switch to different IP";
		$stmt = $db->prepare("INSERT INTO bans (username, reason) VALUES (:username, :reason)");
		$stmt->execute([
			':username' => $_SESSION['siteipban'],
			':reason' => $reason
		]);
		
		$_SESSION['siteipban'] = hash_hmac("sha256", $_SERVER["HTTP_CF_CONNECTING_IP"], "ip");
		
		if($_SERVER['REQUEST_URI'] != "/moderation") {
			header("Location: /moderation");
			exit();
		}
	}
}

$stmt = $db->prepare("SELECT * FROM bans WHERE username = :username OR username = :ip");
$stmt->execute([
	':username' => (isset($_SESSION['siteusername'])) ? $_SESSION['siteusername'] : "",
	':ip' => hash_hmac("sha256", $_SERVER["HTTP_CF_CONNECTING_IP"], "ip")
]);

if($stmt->rowCount()) {
	$_SESSION['siteipban'] = hash_hmac("sha256", $_SERVER["HTTP_CF_CONNECTING_IP"], "ip");
	if($_SERVER['REQUEST_URI'] != "/moderation") {
		header("Location: /moderation");
		exit();
	}
}

/*
if(isset($_POST['password'])) {
    if($_POST['password'] == "SECRET") {
        $_SESSION['access'] = "yes";
    }
}

if(!isset($_SESSION['access'])) { ?>
<form method="POST">
    <input type="password" name="password">
    <input type="submit">
</form>
<?php exit();}
*/

if(new DateTime() < new DateTime('2024-06-02 12:00:00')) {
	include($_SERVER['DOCUMENT_ROOT'] . "/countdown.php");
	exit();
}

if(isset($_SESSION['siteusername'])) {
    $ip = hash_hmac("sha256", $_SERVER["HTTP_CF_CONNECTING_IP"], "ip");
    $stmt = $db->prepare("UPDATE users SET ip = :ip, lastlogin = CURRENT_TIMESTAMP() WHERE username = :username");
    $stmt->bindParam(":username", $_SESSION['siteusername']);
    $stmt->bindParam(":ip", $ip);
    $stmt->execute();
	
	$stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
	$stmt->execute([':username' => $_SESSION['siteusername']]);
	
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

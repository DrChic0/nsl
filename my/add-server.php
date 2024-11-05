<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/mysql.php";

if(!isset($_SESSION['siteusername'])) {
    header("Location: /login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $allowedVersions = array("2006S", "2007E", "2007M", "2008M", "2009E", "2010L", "2011E", "2011M");
    //allowedports

    if(!isset($_POST['name']) or !isset($_POST['maxplayers']) or !isset($_POST['version']) or !isset($_POST['hosting']) or !in_array($_POST['version'], $allowedVersions)) {
        $_SESSION['error'] = "Invalid request!";
        header("Location: /my/add-server");
        exit();
    }
	
	if(empty(trim($_POST['name']))) {
		$_SESSION['error'] = "Name cannot be empty!";
        header("Location: /my/add-server");
        exit();
	}

    if(strlen($_POST['name']) > 25 || strpos($_POST['name'], ':') != false || strpos($_POST['name'], 'radmin') != false) {
        $_SESSION['error'] = "Server name is too long";
        header("Location: /my/add-server");
        exit();
    }

    $stmt = $db->prepare("SELECT * FROM servers WHERE hosting = 2 AND online = 1");
    $stmt->execute();

    while($server = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if(in_array($server['port'], $availablePorts)) {
            $index = array_search($server['port'], $availablePorts);
            if ($index !== false) {
                unset($availablePorts[$index]);
            }
        }
    }

    if($_POST['hosting'] == "2") {
        if(count($availablePorts) == 0) {
            $_SESSION['error'] = "There are too many servers been hosted by novetusserverlist.com";
            header("Location: /my/add-server");
            exit();
        }

        $allowedVersions = array("2010L", "2011E", "2011M");

        if(!in_array($_POST['version'], $allowedVersions)) {
            $_SESSION['error'] = "This version is unsupported";
            header("Location: /my/add-server");
            exit();
        }

        $port = $availablePorts[array_rand($availablePorts)];
        $filename = uniqid();
        $fileextension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		
		if($fileextension != "rbxl" && $fileextension != "rbxlx") {
			$_SESSION['error'] = "Invalid file";
            header("Location: /my/add-server");
            exit();
		}

        if(move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . "/assets/places/" . $filename . "." . $fileextension)) {
            $stmt = $db->prepare("INSERT INTO servers (author, filename, port, maxplayers, hosting, title, client) VALUES (:author, :filename, :port, :maxplayers, :hosting, :title, :client)");
            $stmt->execute([
                ':author' => $_SESSION['siteusername'],
                ':filename' => $filename . "." . $fileextension,
                ':port' => $port,
                ':maxplayers' => $_POST['maxplayers'],
                ':hosting' => 2,
                ':title' => $_POST['name'],
                ':client' => $_POST['version']
            ]);
        } else {
            $_SESSION['error'] = "Error uploading file";
            header("Location: /my/add-server");
            exit();
        }
    } else {
        $allowedVersions = array("2006S", "2007E", "2007M", "2008M", "2009E", "2010L", "2011E", "2011M");

        if(!in_array($_POST['version'], $allowedVersions)) {
            $_SESSION['error'] = "This version is unsupported";
            header("Location: /my/add-server");
            exit();
        }
		
		if(!(substr($_POST['uri'], 0, strlen("novetus://")) === "novetus://")) {
			$_SESSION['error'] = "Invalid uri";
            header("Location: /my/add-server");
            exit();
		}
		
		if(!filter_var($_POST['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$_SESSION['error'] = "Invalid IP";
            header("Location: /my/add-server");
            exit();
		}
		
		$stmt = $db->prepare("INSERT INTO servers (author, filename, port, maxplayers, hosting, title, client, ip) VALUES (:author, :filename, :port, :maxplayers, :hosting, :title, :client, :ip)");
        $stmt->execute([
            ':author' => $_SESSION['siteusername'],
            ':filename' => "",
            ':port' => $_POST['port'],
            ':maxplayers' => $_POST['maxplayers'],
            ':hosting' => 1,
            ':title' => $_POST['name'],
            ':client' => $_POST['version'],
			':ip' => $_POST['ip']
        ]);
    }

    $_SESSION['success'] = "Successfully created server.";
    header("Location: /my/servers");
    exit();
}
?>
<html>
    <head>
        <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/head.php"; ?>
    </head>
    <body>
        <div id="particles-js"></div>
        <div class="container">
            <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/header.php"; ?>
            <div id="app" class="mt-5">
                <div class="card">
                    <div class="card-header">
                        Add Server
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-center" style="margin-left: auto;margin-right: auto;">
                            <div class="col-md-8">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="name" class="col-form-label text-md-right"><small>Server name</small></label>
                                        <input type="text" name="name" placeholder="Server name" class="form-control" required>
                                        <label for="maxplayers" class="col-form-label text-md-right"><small>Max players</small></label>
                                        <input type="number" name="maxplayers" placeholder="Max players" class="form-control" required>
                                        <label for="version" class="col-form-label text-md-right"><small>Version</small></label>
                                        <select name="version" placeholder="Version" class="form-control text-success" id="version" required>
                                            <option class="text-success" value="2006S">2006S</option>
                                            <option class="text-success" value="2007E">2007E</option>
                                            <option class="text-success" value="2007M">2007M</option>
                                            <option class="text-success" value="2008M">2008M</option>
                                            <option class="text-success" value="2009E">2009E</option>
                                            <option class="text-success" value="2010L">2010L</option>
                                            <option class="text-success" value="2011E">2011E</option>
                                            <option class="text-success" value="2011M">2011M</option>
                                        </select>
                                        <label for="hosting" class="col-form-label text-md-right"><small>Hosting provider</small></label>
                                        <select name="hosting" id="hosting" class="form-control" required onchange="toggleInputs()">
                                            <option value="1">Self-host server</option>
                                            <option value="2">novetusserverlist.com hosts this server</option>
                                        </select>
                                        <hr>
                                        <label for="ip" class="col-form-label text-md-right"><small>IP address</small></label>
                                        <input type="text" name="ip" id="ip" placeholder="IP address" class="form-control">
                                        <label for="port" class="col-form-label text-md-right"><small>Port</small></label>
                                        <input type="text" name="port" id="port" placeholder="Port" class="form-control">
                                        <label for="uri" class="col-form-label text-md-right"><small>Online URI (provided in server information)</small></label>
                                        <input type="text" name="uri" id="uri" placeholder="Online URI" class="form-control">
                                        <label for="file" class="col-form-label text-md-right" style="display: none;"><small>Place file</small></label><br>
                                        <input type="file" name="file" placeholder="Place file" style="display: none;">
                                        <small id="subFile" style="display: none;">Needs to be under 100mb</small><br>
                                        <script>
                                            function toggleInputs() {
                                                var hostingSelect = document.getElementById("hosting");
                                                var ipInput = document.getElementById("ip");
                                                var portInput = document.getElementById("port");
                                                var uriInput = document.getElementById("uri");
                                                var fileInput = document.querySelector('[name="file"]');
                                                var label1 = document.querySelector('[for="ip"]');
                                                var label2 = document.querySelector('[for="port"]');
                                                var label3 = document.querySelector('[for="file"]');
                                                var label4 = document.getElementById("subFile");
                                                var label5 = document.querySelector('[for="uri"]');
                                                var v2006s = document.querySelector('[value="2006S"]');
                                                var v2007e = document.querySelector('[value="2007E"]');
                                                var v2007m = document.querySelector('[value="2007M"]');
                                                var v2008m = document.querySelector('[value="2008M"]');
                                                var v2009e = document.querySelector('[value="2009E"]');
                                                var version = document.getElementById("version");

                                                if (hostingSelect.value === "1") {
                                                    ipInput.style.display = "";
                                                    portInput.style.display = "";
                                                    fileInput.style.display = "none";
                                                    uriInput.style.display = "";
                                                    label1.style.display = "";
                                                    label2.style.display = "";
                                                    label3.style.display = "none";
                                                    label4.style.display = "none";
                                                    label5.style.display = "";
                                                    v2006s.disabled = false;
                                                    v2007e.disabled = false;
                                                    v2007m.disabled = false;
                                                    v2008m.disabled = false;
                                                    v2009e.disabled = false;
                                                    v2006s.innerHTML = "2006S";
                                                    v2007e.innerHTML = "2007E";
                                                    v2007m.innerHTML = "2007M";
                                                    v2008m.innerHTML = "2008M";
                                                    v2009e.innerHTML = "2009E";
                                                    v2006s.classList.add('text-success');
                                                    v2006s.classList.remove('text-danger');
                                                    v2007e.classList.add('text-success');
                                                    v2007e.classList.remove('text-danger');
                                                    v2007m.classList.add('text-success');
                                                    v2007m.classList.remove('text-danger');
                                                    v2008m.classList.add('text-success');
                                                    v2008m.classList.remove('text-danger');
                                                    v2009e.classList.add('text-success');
                                                    v2009e.classList.remove('text-danger');
                                                } else {
                                                    ipInput.style.display = "none";
                                                    portInput.style.display = "none";
                                                    fileInput.style.display = "";
                                                    uriInput.style.display = "none";
                                                    label1.style.display = "none";
                                                    label2.style.display = "none";
                                                    label3.style.display = "";
                                                    label4.style.display = "";
                                                    label5.style.display = "none";
                                                    v2006s.disabled = true;
                                                    v2007e.disabled = true;
                                                    v2007m.disabled = true;
                                                    v2008m.disabled = true;
                                                    v2009e.disabled = true;
                                                    v2006s.innerHTML = "2006S (broken)";
                                                    v2007e.innerHTML = "2007E (broken)";
                                                    v2007m.innerHTML = "2007M (broken)";
                                                    v2008m.innerHTML = "2008M (broken)";
                                                    v2009e.innerHTML = "2009E (broken)";
                                                    v2006s.classList.remove('text-success');
                                                    v2006s.classList.add('text-danger');
                                                    v2007e.classList.remove('text-success');
                                                    v2007e.classList.add('text-danger');
                                                    v2007m.classList.remove('text-success');
                                                    v2007m.classList.add('text-danger');
                                                    v2008m.classList.remove('text-success');
                                                    v2008m.classList.add('text-danger');
                                                    v2009e.classList.remove('text-success');
                                                    v2009e.classList.add('text-danger');
                                                    version.value = "2010L";
                                                }
                                            }
                                        </script>
                                        <div class="d-flex flex-column align-items-center">
                                            <button type="submit" class="btn btn-primary mt-3" style="background-color: #510985;border-color: #510985;">Add Server</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/footer.php"; ?>
        </div>
    </body>
</html>
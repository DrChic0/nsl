<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/mysql.php";

if (!isset($_SESSION['siteusername'])) {
    header("Location: /login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $allowedVersions = array("2006S", "2007E", "2007M", "2008M", "2009E", "2010L", "2011E", "2011M");
    //allowedports

    if(!isset($_POST['name']) or !isset($_POST['maxplayers']) or !isset($_POST['version']) or !isset($_POST['hosting']) or !in_array($_POST['version'], $allowedVersions)) {
        $_SESSION['error'] = "Invalid request!";
        header("Location: /my/servers");
        exit();
    }
	
	if(empty(trim($_POST['name']))) {
		$_SESSION['error'] = "Name cannot be empty!";
        header("Location: /my/servers");
        exit();
	}

    if(strlen($_POST['name']) > 25) {
        $_SESSION['error'] = "Server name is too long";
        header("Location: /my/servers");
        exit();
    }
	
	$stmt = $db->prepare("SELECT * FROM servers WHERE id = :id");
	$stmt->execute([
		':id' => $_POST['id']
	]);
	
	if(!$stmt->rowCount()) {
		$_SESSION['error'] = "This server does not exist";
        header("Location: /my/servers");
        exit();
	}

    $server = $stmt->fetch(PDO::FETCH_ASSOC);

    if($server['author'] != $_SESSION['siteusername']) {
        $_SESSION['error'] = "You are not allowed to edit this server.";
        header("Location: /my/servers");
        exit();
    }
	
	if($stmt->fetch(PDO::FETCH_ASSOC)['online'] == 1) {
		$_SESSION['error'] = "You must stop your server before updating it";
        header("Location: /my/servers");
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
        $allowedVersions = array("2010L", "2011E", "2011M");

        if(!in_array($_POST['version'], $allowedVersions)) {
            $_SESSION['error'] = "This version is unsupported";
            header("Location: /my/servers");
            exit();
        }

        $port = 0;
        $filename = uniqid();
        $fileextension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		
		if($fileextension != "rbxl" && $fileextension != "rbxlx") {
			$_SESSION['error'] = "Invalid file";
            header("Location: /my/add-server");
            exit();
		}

        if(move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . "/assets/places/" . $filename . "." . $fileextension)) {
            $stmt = $db->prepare("UPDATE servers SET author = :author, filename = :filename, port = :port, maxplayers = :maxplayers, hosting = :hosting, title = :title, client = :client WHERE id = :id");
            $stmt->execute([
                ':author' => $_SESSION['siteusername'],
                ':filename' => $filename . "." . $fileextension,
                ':port' => $port,
                ':maxplayers' => $_POST['maxplayers'],
                ':hosting' => 2,
                ':title' => $_POST['name'],
                ':client' => $_POST['version'],
				':id' => $_POST['id']
            ]);
        } else {
            $_SESSION['error'] = "Error uploading file";
            header("Location: /my/servers");
            exit();
        }
    } else {
        $allowedVersions = array("2006S", "2007E", "2007M", "2008M", "2009E", "2010L", "2011E", "2011M");

        if(!in_array($_POST['version'], $allowedVersions)) {
            $_SESSION['error'] = "This version is unsupported";
            header("Location: /my/servers");
            exit();
        }
		
		if(strlen($_POST['name']) > 25) {
			$_SESSION['error'] = "Server name is too long";
			header("Location: /my/servers");
			exit();
		}
		
		if(!(substr($_POST['uri'], 0, strlen("novetus://")) === "novetus://")) {
			$_SESSION['error'] = "Invalid uri";
            header("Location: /my/servers");
            exit();
		}

        if(empty(trim($_POST['ip']))) {
            $_SESSION['error'] = "Invalid IP";
            header("Location: /my/add-server");
            exit();
        }
		
		if(!filter_var($_POST['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$_SESSION['error'] = "Invalid IP";
            header("Location: /my/add-server");
            exit();
		}
		
		if($_POST['ip'] == "147.185.221.16") {
			$_SESSION['error'] = "This IP is invalid";
            header("Location: /my/servers");
            exit();
		}
		
		$stmt = $db->prepare("UPDATE servers SET author = :author, filename = :filename, port = :port, maxplayers = :maxplayers, hosting = :hosting, title = :title, client = :client, :ip WHERE id = :id");
        $stmt->execute([
            ':author' => $_SESSION['siteusername'],
            ':filename' => "",
            ':port' => $_POST['port'],
            ':maxplayers' => $_POST['maxplayers'],
            ':hosting' => 1,
            ':title' => $_POST['name'],
            ':client' => $_POST['version'],
			':id' => $_POST['id'],
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
        <style>
            @keyframes fadeInUp2 {
                0% {
                    opacity: 0;
                    -webkit-transform: translate3d(0, 25%, 0);
                    transform: translate3d(0, 25%, 0)
                }

                to {
                    opacity: 0.6;
                    -webkit-transform: none;
                    transform: none
                }
            }

            @keyframes fadeOutDown2 {
                0% {
                    opacity: 0.6
                }

                to {
                    opacity: 0;
                    -webkit-transform: translate3d(0, 25%, 0);
                    transform: translate3d(0, 25%, 0)
                }
            }

            .console {
                position: absolute;
                top: 0;
                z-index: 999999;
                width: 100%;
                height: 100%;
            }

            .consoleIn {
                animation: fadeInUp2 0.25s ease;
            }

            .consoleDown {
                animation: fadeOutDown2 0.25s ease;
            }

            .console .card {
                height: fit-content;
				min-height: 500px;
            }

            .consoleBackground {
                position: absolute;
                pointer-events: none;
                width: 100%;
                height: 100%;
                background-color: black;
                opacity: 0.6;
            }

            .console-window {
                width: 100%;
                height: 100%;
                background-color: black;
                color: white;
                font-family: monospace;
                padding: 10px;
                overflow-y: auto;
                border: 2px solid var(--gray);
            }
        </style>
    </head>
    <body>
        <div id="particles-js"></div>
        <div class="container">
            <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/header.php"; ?>
            <div id="app" class="mt-5">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-center text-center flex-column">
                            <?php
                            $stmt = $db->prepare("SELECT * FROM servers WHERE author = :author ORDER BY created DESC");
                            $stmt->execute([':author' => $_SESSION['siteusername']]);

                            while($server = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $server['players'] = json_decode($server['players'], true); ?>
                            <div class="card mt-3" style="width: 75%;margin-left: auto;margin-right: auto;">
                                <div class="card-body">
                                    <div class="d-flex flex-row">
                                        <div>
                                            <img src="/img/66134779.png" style="width: 75%;height: auto;">
                                        </div>
                                        <div style="min-width: 30%;">
                                            <h2 class="m-0"><?php echo htmlspecialchars($server['title']) ?></h2>
                                            <p class="m-0">Client: <?php echo $server['client'] ?></p>
                                            <p class="m-0"><?php echo ($server['hosting'] == 2) ? "Hosted by novetusserverlist.com" : "Self-hosted"; ?></p>
                                            <p class="m-0"><?php echo ($server['hosting'] == 2) ? count($server['players']) : "?"; ?>/<?php echo $server['maxplayers'] ?> <span class="text-<?php echo ($server['online'] == 1) ? "success" : "danger"; ?>" id="status<?php echo $server['id'] ?>"><?php echo ($server['online'] == 1) ? "Online" : "Offline"; ?></span></p>
                                            <?php if($server['hosting'] == 2) { ?>
                                                <button class="btn btn-success btn-sm mt-auto" id="open<?php echo $server['id'] ?>" onclick="startServer(<?php echo $server['id'] ?>);"<?php echo ($server['online'] == 1) ? " disabled" : "" ?>>Start</button>
                                                <button class="btn btn-danger btn-sm mt-auto" id="close<?php echo $server['id'] ?>" onclick="stopServer(<?php echo $server['id'] ?>);"<?php echo ($server['online'] == 0) ? " disabled" : "" ?>>Stop</button>
                                                <button class="btn btn-warning btn-sm mt-auto" onclick="openConsole();toggleCMD('edit', '<?php echo htmlspecialchars($server['title']) ?>', <?php echo $server['maxplayers'] ?>, '<?php echo $server['client'] ?>', <?php echo $server['hosting'] ?>, '<?php echo $server['ip'] ?>', <?php echo $server['port'] ?>, '<?php echo $server['uri'] ?>', <?php echo $server['id'] ?>);">Edit</button><br>
                                                <button class="btn btn-primary btn-sm mt-1" onclick="openConsole();updateCMD(<?php echo $server['id'] ?>);toggleCMD('console')">Open console</button>
                                            <?php } ?>
                                        </div>
                                        <div class="mr-5" style="margin-left: auto;">
                                            <p class="m-0">Created: <?php echo date("m/d/Y g:i A", strtotime($server['created'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if(!$stmt->rowCount()) { ?>
                                No servers found.
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/footer.php"; ?>
        </div>
        <div class="d-flex justify-content-center console" id="consoleCard" style="display: none;">
            <div class="consoleBackground"></div>
            <div class="card mt-5">
                <div class="card-header">
                    Console <button class="btn btn-danger float-right" onclick="closeConsole();">X</button>
                </div>
                <div class="card-body">
                    <div id="consoleCMD" class="console-window"></div>
                    <div id="editForm" style="display: none;">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="name" class="col-form-label text-md-right"><small>Server name</small></label>
                                <input type="text" name="name" placeholder="Server name" class="form-control" id="name" required>
                                <label for="maxplayers" class="col-form-label text-md-right"><small>Max players</small></label>
                                <input type="number" name="maxplayers" placeholder="Max players" class="form-control" id="players" required>
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
								<input type="hidden" name="id" id="id">
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
                                    var sname = document.getElementById("name");
                                    var mplayers = document.getElementById("players")
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
                                    
                                    function toggleInputs() {
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
                                    <button type="submit" class="btn btn-primary mt-3" style="background-color: #510985;border-color: #510985;">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
            const console = document.getElementById("consoleCard");
            const consoleWindow = document.getElementById("consoleCMD");
            const editWindow = document.getElementById("editForm");

            function openConsole() {
                console.style.display = "";
                console.style.zIndex = 999999;
                console.classList.add("consoleIn");
                $(window).scrollTop(0);
                document.querySelector('body').style.overflow = "hidden";

                console.querySelectorAll("*").forEach(element => {
                    if(element.id != "editForm") {
                        element.style.display = "";
                    }
                });
            }

            function closeConsole() {
                console.classList.remove("consoleIn");
                console.classList.add("consoleDown");
                document.querySelector('body').style.overflow = "";

                setTimeout(function() {
                    console.style.display = "none";
                    console.style.zIndex = -999;
                    console.querySelectorAll("*").forEach(element => {
                        element.style.display = "none";
                    });
                    console.classList.remove("consoleDown");
                }, 250)
                
            }

            function toggleCMD(type, name = "", maxplayers = 0, version = "", hosting = 0, ip = "", port = 0, uri = "", id = 0) {
                if(type == "edit") {
                    consoleWindow.style.display = "none";
                    console.querySelector(".card").querySelector(".card-header").innerHTML = 'Edit <button class="btn btn-danger float-right" onclick="closeConsole();">X</button>';
                    editWindow.style.display = "";
                    sname.value = name;
                    mplayers.value = maxplayers;
                    hostingSelect.value = hosting;
                    ipInput.value = ip;
                    portInput.value = port;
                    uriInput.value = uri;
					document.getElementById('id').value = id;
                    toggleInputs();
                } else {
                    consoleWindow.style.display = "";
                    console.querySelector(".card").querySelector(".card-header").innerHTML = 'Console <button class="btn btn-danger float-right" onclick="closeConsole();">X</button>';
                    editWindow.style.display = "none";
                }
            }

            function updateCMD(id) {
                const xhr = new XMLHttpRequest();

                xhr.open('GET', "/api/get_console?id=" + id, true);
                xhr.setRequestHeader('Content-Type', 'application/json');

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        logToConsole(JSON.parse(xhr.responseText).message);
                    } else {
                        logToConsole('Error: ' + JSON.parse(xhr.responseText).message + ' ' + JSON.parse(xhr.responseText).status);
                    }
                };

                xhr.onerror = function() {
                    logToConsole('Network Error');
                };

                xhr.send();
            }

            function startServer(id) {
                const xhr = new XMLHttpRequest();
                openConsole();
                logToConsole('<img src="/img/loading.gif" width="16" height="16">');
                document.getElementById("open" + id).disabled = true;

                xhr.open('GET', "/api/start_server?id=" + id, true);
                xhr.setRequestHeader('Content-Type', 'application/json');

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        var msg = JSON.parse(xhr.responseText).message;
                        var msg = JSON.parse(msg);
                        var log = msg.stdout + msg.stderr;
                        logToConsole(nl2br(log));
                        document.getElementById("close" + id).disabled = false;
                        document.getElementById("status" + id).classList.remove("text-danger");
                        document.getElementById("status" + id).classList.add("text-success");
                        document.getElementById("status" + id).innerText = "Online";
                    } else {
                        document.getElementById("open" + id).disabled = false;
                        logToConsole('Error: ' + JSON.parse(xhr.responseText).message + ' ' + JSON.parse(xhr.responseText).status);
                    }
                };

                xhr.onerror = function() {
                    document.getElementById("open" + id).disabled = false;
                    logToConsole('Network Error');
                };

                xhr.send();
            }

            function stopServer(id) {
                const xhr = new XMLHttpRequest();
                openConsole();
                logToConsole('<img src="/img/loading.gif" width="16" height="16">');
                document.getElementById("close" + id).disabled = true;

                xhr.open('GET', "/api/end_server?id=" + id, true);
                xhr.setRequestHeader('Content-Type', 'application/json');

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        logToConsole(JSON.parse(xhr.responseText).message);
                        document.getElementById("close" + id).disabled = true;
                        document.getElementById("open" + id).disabled = false;
                        document.getElementById("status" + id).classList.remove("text-success");
                        document.getElementById("status" + id).classList.add("text-danger");
                        document.getElementById("status" + id).innerText = "Offline";
                    } else {
                        document.getElementById("close" + id).disabled = false;
                        logToConsole('Error: ' + JSON.parse(xhr.responseText).message + ' ' + JSON.parse(xhr.responseText).status);
                    }
                };

                xhr.onerror = function() {
                    document.getElementById("close" + id).disabled = false;
                    logToConsole('Network Error');
                };

                xhr.send();
            }

            function nl2br(str) {
                return str.replace(/(?:\r\n|\r|\n)/g, '<br>');
            }

            function logToConsole(text) {
                consoleWindow.innerHTML = text;
                consoleWindow.scrollTop = consoleWindow.scrollHeight;
            }

            console.style.zIndex = -999;
            console.querySelectorAll("*").forEach(element => {
                element.style.display = "none";
            });
        </script>
    </body>
</html>
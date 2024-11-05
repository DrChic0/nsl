<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/mysql.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['close'])) {
        $port = $_POST['port'];

        exec("netstat -ano | findstr :$port", $output);

        if (!empty($output)) {
            $parts = explode(" ", $output[0]);
            $pid = trim(end($parts));

            exec("taskkill /F /PID $pid", $kill_output, $kill_status);

            if ($kill_status === 0) {
                echo "RobloxApp_server.exe process using port $port has been killed.\n";
            } else {
                echo "Failed to kill the RobloxApp_server.exe process.\n";
            }
        } else {
            echo "RobloxApp_server.exe process using port $port was not found.\n";
        }
    } else {
        function executeCommandWithTimeout($command, $timeout)
        {
            $process = proc_open($command, array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("pipe", "w")
            ), $pipes);

            if (is_resource($process)) {
                stream_set_blocking($pipes[1], false);
                stream_set_blocking($pipes[2], false);

                stream_set_timeout($pipes[1], $timeout);
                stream_set_timeout($pipes[2], $timeout);

                $stdout = stream_get_contents($pipes[1]);
                $stderr = stream_get_contents($pipes[2]);

                fclose($pipes[0]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);

                return array(
                    'stdout' => nl2br($stdout),
                    'stderr' => nl2br($stderr)
                );
            }

            return false;
        }

        $command = "cd C:/novetus-windows/bin && NovetusCMD.exe --no3d --port " . $_POST['port'] . " --map \"" . $_POST['file'] . "\" --client " . $_POST['client'];

        $timeout = 10;

        $result = executeCommandWithTimeout($command, $timeout);

        if ($result === false) {
            echo "Process took longer than {$timeout} seconds and was killed.";
        } else {
            echo "Command output:\n";
            echo "STDOUT:\n{$result['stdout']}\n";
            echo "STDERR:\n{$result['stderr']}\n";
        }
    }
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

            .notification {
                position: absolute;
                top: 0;
                z-index: 999999;
                width: 100%;
                height: 100%;
            }

            .notificationIn {
                animation: fadeInUp2 0.25s ease;
            }

            .notificationDown {
                animation: fadeOutDown2 0.25s ease;
            }

            .notification .card {
                height: fit-content;
                width: 55%;
            }

            .notificationBackground {
                position: absolute;
                pointer-events: none;
                width: 100%;
                height: 100%;
                background-color: black;
                opacity: 0.6;
            }
			
			#welcome {
				display: flex;
			}

            .ml-3 ol li {
                list-style-type: circle;
            }
        </style>
    </head>
    <body>
        <div id="particles-js"></div>
        <div class="container">
            <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/header.php"; ?>
            <div id="app" class="mt-5">
                <a href="/my/add-server" class="btn btn-success btn-sm"><i class="far fa-plus"></i> Add Server</a>
                <?php
                $stmt = $db->prepare("
					SELECT * 
					FROM servers 
					ORDER BY 
						JSON_LENGTH(players) DESC, 
						online DESC, 
						started DESC 
					LIMIT 10
				");
                $stmt->execute();

                $serverCount = 0;

                echo '<div class="row mt-3">';

                while($server = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $server['players'] = json_decode($server['players'], true);
                    echo '<div class="col-md-6">'; ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex flex-row">
                                <div>
                                    <img src="/img/66134779.png" style="width: 75%;height: auto;">
                                </div>
                                <div class="ml-3">
                                    <h2 class="m-0"><?php echo htmlspecialchars($server['title']) ?></h2>
                                    <p class="m-0">by <?php echo htmlspecialchars($server['author']) ?></p>
                                    <p class="m-0">Client: <?php echo $server['client'] ?></p>
                                    <p class="m-0"><?php echo ($server['hosting'] == 2) ? "Hosted by novetusserverlist.com" : "Self-hosted"; ?></p>
                                    <p class="m-0"><?php echo ($server['hosting'] == 2) ? count($server['players']) : "?"; ?>/<?php echo $server['maxplayers'] ?> <?php if($server['hosting'] == 2) { ?><span class="text-<?php echo ($server['online'] == 1) ? "success" : "danger"; ?>"><?php echo ($server['online'] == 1) ? "Online" : "Offline"; ?></span><?php } ?></p>
                                </div>
                            </div>
                            <button class="btn btn-success mt-3" id="join<?php echo $server['id'] ?>" onclick="openCard('Joining <?php echo str_replace("'", "", htmlspecialchars($server['title'])) ?>', false, '<?php echo htmlspecialchars($server['ip']) ?>', '<?php echo htmlspecialchars($server['port']) ?>', '<?php echo $server['client'] ?>', '<?php echo $server['id'] ?>');join('<?php echo $server['uri'] ?>');" style="width: 100%;"<?php echo ($server['online'] == 0 && $server['hosting'] == 2) ? " disabled" : ""; ?>><i class="fas fa-play"></i><span style="display: none"><?php echo json_encode($server['players']) ?></span></button>
                        </div>
                    </div>
                    <?php
                    echo '</div>';

                    $serverCount++;

                    if ($serverCount % 2 == 0 && $serverCount < 10) {
                        echo '</div><div class="row">';
                    }
                }

                echo '</div>';
                ?>
            </div>
            <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/footer.php"; ?>
        </div>
        <div class="d-flex justify-content-center notification" id="notificationCard" style="display: none;">
            <div class="notificationBackground"></div>
            <div class="card mt-5">
                <div class="card-header">
                    Welcome to novetusserverlist.com! <button class="btn btn-danger float-right" onclick="closeCard();">X</button>
                </div>
                <div class="card-body">
                    <div class="justify-content-center" id="join" style="display: none;">
                        <div class="row">
                            <div class="d-flex justify-content-center" style="width: 55%">
                                <img src="/img/66134779.png" style="width: auto;max-height: 226px;">
                            </div>
                            <div style="width: 45%;">
                                <h2 class="m-0">Server Info:</h2>
                                <p class="m-0 mb-1">If you didn't get a pop-up to join the server from your web browser you can use the server information below to join manually.</p>
                                <div class="flex-row">
                                    <div>
                                        <p class="m-0" id="ip">IP:</p>
                                        <p class="m-0" id="port">Port:</p>
                                        <p class="m-0" id="client">Version:</p>
                                    </div>
                                    <div class="ml-3">
                                        <h5 class="m-0">Players:</h5>
                                        <ol id="players">
                                            <li>[SERVER]</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h2 class="text-warning m-0">Warnings:</h2>
                        <ol class="m-0 pb-5">
                            <li>Play these servers at your own risk!</li>
                            <li>Don't play self-hosted servers by people you don't trust! Your IP is exposed to self-hosted servers and their creators.</li>
                            <li>If you are playing on servers hosted by novetusserverlist.com, a player can use cheats to gain a higher level of access.</li>
                            <li>Use a VPN to hide your IP while playing <strong>any</strong> servers that are listed.</li>
                        </ol>
                    </div>
                    <div class="text-center d-flex align-items-center flex-column" id="welcome" style="display: none;">
						<img src="/img/nsl.png" style="width: 75%;height: auto;">
						<p>Hello!</p>
						<p>We host Novetus servers and list user generated servers, we would like to notify you that our service is in early development and if there is a bug in our service, you may send an e-mail to <a href="mailto:support@aesthetiful.com">support@aesthetiful.com</a> or report it on our <a href="https://discord.gg/invite/wgwb6z8krC">discord server</a>.</p>
						<h2>Rules</h2>
						<ol class="text-left">
							<li>Do not list or host any games containing offensive, sexual, or abusive content.</li>
                            <li>Do not use cheat/exploit sofetware.</li>
							<li>Do not purposely create servers or accounts to break the rules.</li>
							<li><strong>Not required</strong> but heavily encouraged to use a VPN while joining servers.</li>
						</ol>
					</div>
                </div>
            </div>
        </div>
        <script>
            const card = document.getElementById("notificationCard");
            const header = card.querySelector(".card").querySelector('.card-header');
            const body = card.querySelector(".card").querySelector('.card-card');
            const players = document.getElementById('players');
			
			function htmlspecialchars(str) {
				if (typeof str !== "string") {
					return str;
				}
				
				return str.replace(/&/g, '&amp;')
						  .replace(/</g, '&lt;')
						  .replace(/>/g, '&gt;')
						  .replace(/"/g, '&quot;')
						  .replace(/'/g, '&#039;');
			}

            function openCard(header, isWelcome = true, ip = "", port = "", client = "", id="") {
                card.style.display = "";
                card.style.zIndex = 999999;
                card.classList.add("notificationIn");
				header = htmlspecialchars(header);
                $(window).scrollTop(0);
                document.querySelector('body').style.overflow = "hidden";

                card.querySelectorAll("*").forEach(element => {
                    if(!isWelcome && element.id == "welcome") {
                        element.style.display = "none";
                        changeHeader(header);
                        document.getElementById("ip").innerHTML = "IP: " + ip;
                        document.getElementById("port").innerHTML = "Port: " + port;
						document.getElementById("client").innerHTML = "Version: " + client;
                        players.innerHTML = "";
                        playerList = JSON.parse(document.getElementById("join" + id).querySelector('span').innerHTML);
                        for(let index = 0; index < playerList.length; index++) {
                            players.innerHTML += "<li>" + htmlspecialchars(playerList[index]) + "</li>";
                        }
						
						if(!playerList.length) {
							players.innerHTML = "No players";
						}
                    } else if(isWelcome && element.id == "join") {
                        element.style.display = "none";
                    } else {
						if(element.classList.contains("d-flex") || element.classList.contains("flex-row")) {
							element.style.display = "flex";
                            if(element.classList.contains('align-items-center')) {
                                element.classList.remove('d-flex');
                            }
						} else {
							element.style.display = "";
						}
                    }
                });
            }

            function closeCard() {
                card.classList.remove("notificationIn");
                card.classList.add("notificationDown");
                document.querySelector('body').style.overflow = "";

                setTimeout(function() {
                    card.style.display = "none";
                    card.style.zIndex = -999;
                    card.querySelectorAll("*").forEach(element => {
                        element.style.display = "none";
                    });
                    card.classList.remove("notificationDown");
                }, 250);
            }

            function changeHeader(message) {
                header.innerHTML = message + ' <button class="btn btn-danger float-right" onclick="closeCard();">X</button>';
            }

            function join(uri) {
				if(uri != "") {
					setTimeout(() => {
						window.location.href = uri;
					}, 1000);
				}
            }

            card.style.zIndex = -999;
            card.querySelectorAll("*").forEach(element => {
                element.style.display = "none";
            });

            <?php if(!isset($_SESSION['siteusername'])) { ?>
                openCard("", true);
            <?php } ?>
        </script>
    </body>
</html>
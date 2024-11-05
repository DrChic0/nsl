<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/mysql.php"; ?>
<?php
if(!isset($_SESSION['siteusername'])) {
    header("Location: /");
    exit();
}

if($user['status'] != "admin") {
    header("Location: /");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == "POST") {
    if($_POST['type'] == "ban") {
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $_POST['username']]);

        if($stmt->rowCount()) {
            $stmt = $db->prepare("INSERT INTO bans (username, reason) VALUES (:username, :reason)");
            $stmt->execute([
                ':username' => $_POST['username'],
                ':reason' => $_POST['reason']
            ]);

            $_SESSION['success'] = "Successfully banned";
            header('Location: /admin/');
            exit();
        } else {
            $_SESSION['error'] = "User not found";
            header('Location: /admin/');
            exit();
        }
    } else if($_POST['type'] == "ipban") {
        $stmt = $db->prepare("SELECT ip FROM users WHERE username = :username");
        $stmt->execute([':username' => $_POST['username']]);

        if(!$stmt->rowCount()) {
            $_SESSION['error'] = "User not found";
            header('Location: /admin/');
            exit();
        }
		
		$ip = $stmt->fetch(PDO::FETCH_ASSOC)['ip'];

        $stmt = $db->prepare("INSERT INTO bans (username, reason) VALUES (:username, :reason)");
        $stmt->execute([
            ':username' => $ip,
            ':reason' => $_POST['reason']
        ]);

        $_SESSION['success'] = "Successfully banned";
        header('Location: /admin/');
        exit();
    } else if($_POST['type'] == "unban") {
		$stmt = $db->prepare("SELECT ip FROM users WHERE username = :username");
        $stmt->execute([':username' => $_POST['username']]);

        if(!$stmt->rowCount()) {
            $_SESSION['error'] = "User not found";
            header('Location: /admin/');
            exit();
        }

        $stmt = $db->prepare("DELETE FROM bans WHERE username = :username");
        $stmt->execute([
            ':username' => $_POST['username']
        ]);

        $_SESSION['success'] = "Successfully banned";
        header('Location: /admin/');
        exit();
	} else if ($_POST['type'] == "delbyusr") {
		$stmt = $db->prepare("SELECT * FROM servers WHERE author = :username AND online = 1");
		$stmt->execute([':username' => $_POST['username']]);
		
		while($server = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$response = file_get_contents("https://api.novetusserverlist.com/stop?port=" . $server['port'] . "&key=verysecretomgomgskibiti");
		}
		
		$stmt = $db->prepare("SELECT COUNT(*) FROM servers WHERE author = :username");
		$stmt->execute([':username' => $_POST['username']]);
		
		$servers = $stmt->fetchColumn();
		$stmt = $db->prepare("DELETE FROM servers WHERE author = :username");
		$stmt->execute([':username' => $_POST['username']]);
		
		$_SESSION['success'] = "Successfully deleted " . $servers . " servers";
        header('Location: /admin/');
        exit();
	}
}
?>
<html>
    <head>
        <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/head.php"; ?>
		<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                height: 648px;
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
            <div id="app" class="card mt-5">
                <div class="card-header">Admin Panel</div>
                <div class="card-body">
					<h2>Dashboard</h2>
					<div class="d-flex flex-row">
						<div class="col-md-6 d-flex flex-column">
							<div class="card w-100">
								<div class="card-body">
									<p>CPU load: <span id="cpu">0</span>/100%</p>
									<p>RAM Usage: <span id="ram">0</span>/16 GB</p>
									<div>
										<canvas id="resourceChart" width="400" height="300"></canvas>
									</div>
									<script>
										async function fetchData() {
											const response = await fetch('stats');
											const data = await response.json();
											return data;
										}

										async function updateChart() {
											const data = await fetchData();

											const usedMemoryGB = (data.memory_usage.used / (1024 * 1024 * 1024)).toFixed(1);
											const freeMemoryGB = (data.memory_usage.free / (1024 * 1024 * 1024)).toFixed(1);

											resourceChart.data.labels.push('');
											resourceChart.data.datasets[0].data.push(data.cpu_load);
											resourceChart.data.datasets[1].data.push(usedMemoryGB);
											document.getElementById('cpu').innerHTML = data.cpu_load;
											document.getElementById('ram').innerHTML = usedMemoryGB;

											if (resourceChart.data.labels.length > 100) {
												resourceChart.data.labels.shift();
												resourceChart.data.datasets[0].data.shift();
												resourceChart.data.datasets[1].data.shift();
											}

											resourceChart.update();
										}

										const ctx = document.getElementById('resourceChart').getContext('2d');
										const resourceChart = new Chart(ctx, {
											type: 'line',
											data: {
												labels: [],
												datasets: [
													{
														label: 'CPU Load (%)',
														data: [],
														borderColor: 'rgba(75, 192, 192, 1)',
														borderWidth: 1,
														yAxisID: 'y-axis-cpu',
														fill: false
													},
													{
														label: 'Used Memory (GB)',
														data: [],
														borderColor: 'rgba(255, 99, 132, 1)',
														borderWidth: 1,
														yAxisID: 'y-axis-memory',
														fill: false
													}
												]
											},
											options: {
												scales: {
													x: {
														display: true,
														title: {
															display: true,
															text: 'Time (s)',
															color: 'white'
														},
														ticks: {
															color: 'white'
														}
													},
													'y-axis-cpu': {
														type: 'linear',
														position: 'left',
														title: {
															display: true,
															text: 'CPU Load (%)',
															color: 'white'
														},
														ticks: {
															color: 'white'
														},
														min: 0,
														max: 100
													},
													'y-axis-memory': {
														type: 'linear',
														position: 'right',
														title: {
															display: true,
															text: 'Used Memory (GB)',
															color: 'white'
														},
														ticks: {
															color: 'white'
														},
														min: 0,
														max: 16
													}
												},
												plugins: {
													legend: {
														labels: {
															color: 'white'
														}
													}
												},
												layout: {
													padding: {
														top: 10,
														bottom: 10,
														left: 10,
														right: 10
													}
												},
												responsive: true,
												maintainAspectRatio: false
											}
										});

										setInterval(updateChart, 5000);
									</script>
								</div>
							</div>
							<div class="card w-100 mt-4">
								<div class="card-body">
									<form method="POST">
										<div class="form-group">
                                            <input type="hidden" name="type" value="ban">
											<label for="username" class="col-form-label text-md-right"><small>Username</small></label>
											<input type="text" name="username" placeholder="Username" class="form-control" required>
											<label for="reason" class="col-form-label text-md-right"><small>Reason</small></label>
											<input type="reason" name="reason" placeholder="Reason" class="form-control" required>
											<div class="d-flex flex-column align-items-center">
												<button type="submit" class="btn btn-danger mt-3">Ban</button>
											</div>
										</div>
									</form>
								</div>
							</div>
                            <div class="card w-100 mt-4">
								<div class="card-body">
									<form method="POST">
										<div class="form-group">
                                            <input type="hidden" name="type" value="ipban">
											<label for="username" class="col-form-label text-md-right"><small>Username</small></label>
											<input type="text" name="username" placeholder="Username" class="form-control" required>
											<label for="reason" class="col-form-label text-md-right"><small>Reason</small></label>
											<input type="reason" name="reason" placeholder="Reason" class="form-control" required>
											<div class="d-flex flex-column align-items-center">
												<button type="submit" class="btn btn-danger mt-3">IP Ban</button>
											</div>
										</div>
									</form>
								</div>
							</div>
                            <div class="card w-100 mt-4">
								<div class="card-body">
									<form method="POST">
										<div class="form-group">
                                            <input type="hidden" name="type" value="unban">
											<label for="username" class="col-form-label text-md-right"><small>Username</small></label>
											<input type="text" name="username" placeholder="Username" class="form-control" required>
											<label for="reason" class="col-form-label text-md-right"><small>Reason</small></label>
											<input type="reason" name="reason" placeholder="Reason" class="form-control" required>
											<div class="d-flex flex-column align-items-center">
												<button type="submit" class="btn btn-danger mt-3">Unban</button>
											</div>
										</div>
									</form>
								</div>
							</div>
							<div class="card w-100 mt-4">
								<div class="card-body">
									<form method="POST">
										<div class="form-group">
                                            <input type="hidden" name="type" value="delbyusr">
											<label for="username" class="col-form-label text-md-right"><small>Username</small></label>
											<input type="text" name="username" placeholder="Username" class="form-control" required>
											<div class="d-flex flex-column align-items-center">
												<button type="submit" class="btn btn-danger mt-3">Delete Servers</button>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
						<div class="col-md-6 d-flex flex-column">
							<div class="card w-100">
								<div class="card-body">
									<div class="d-flex justify-content-center text-center flex-column">
										<?php
										$stmt = $db->prepare("SELECT * FROM servers WHERE online = 1 ORDER BY created DESC");
										$stmt->execute();

										while($server = $stmt->fetch(PDO::FETCH_ASSOC)) {
											$server['players'] = json_decode($server['players'], true); ?>
										<div class="card mt-3 w-100" style="margin-left: auto;margin-right: auto;">
											<div class="card-body">
												<div class="d-flex flex-row">
													<div>
														<img src="/img/66134779.png" style="width: 75%;height: auto;">
													</div>
													<div style="min-width: 30%;">
														<p class="m-0"><strong><?php echo htmlspecialchars($server['title']) ?></strong></p>
														<p class="m-0">Client: <?php echo $server['client'] ?></p>
														<p class="m-0"><?php echo ($server['hosting'] == 2) ? "Hosted by novetusserverlist.com" : "Self-hosted"; ?></p>
														<p class="m-0"><?php echo ($server['hosting'] == 2) ? count($server['players']) : "?"; ?>/<?php echo $server['maxplayers'] ?> <span class="text-<?php echo ($server['online'] == 1) ? "success" : "danger"; ?>" id="status<?php echo $server['id'] ?>"><?php echo ($server['online'] == 1) ? "Online" : "Offline"; ?></span></p>
														<?php if($server['hosting'] == 2) { ?>
															<button class="btn btn-danger btn-sm mt-auto" id="close<?php echo $server['id'] ?>" onclick="stopServer(<?php echo $server['id'] ?>);"<?php echo ($server['online'] == 0) ? " disabled" : "" ?>>Stop</button><br>
															<button class="btn btn-primary btn-sm mt-1" onclick="openConsole();updateCMD(<?php echo $server['id'] ?>);toggleCMD('console')">Open console</button>
														<?php } ?>
													</div>
													<div class="mr-5" style="margin-left: auto;">
														<p class="m-0">Created: <?php echo date("m/d/Y g:i A", strtotime($server['created'])) ?></p>
														<p class="m-0">By: <?php echo htmlspecialchars($server['author']) ?></p>
													</div>
												</div>
											</div>
										</div>
										<?php } ?>
										<?php if(!$stmt->rowCount()) { ?>
											No online servers.
										<?php } ?>
									</div>
								</div>
							</div>
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
                </div>
            </div>
        </div>
        <script>
            const console = document.getElementById("consoleCard");
            const consoleWindow = document.getElementById("consoleCMD");

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
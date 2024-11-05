<html>
    <head>
        <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/head.php"; ?>
    </head>
    <body>
        <div id="particles-js"></div>
        <div class="container">
            <div id="app" class="card mt-5">
                <div class="card-body text-center align-items-center">
                    <img src="/img/nsl.png" style="width: 50%;height: auto;">
					<h1>Novetus Server List countdown release!</h1>
					<p>Novetus Server List will release in:</p>
					<h2 id="countdown"></h2>
					<script>
						const targetDate = new Date('2024-06-02T12:00:00-07:00');

						function updateCountdown() {
							const now = new Date();
							const timeDifference = targetDate - now;

							if (timeDifference < 0) {
								window.location.href = "/";
								clearInterval(countdownInterval);
								return;
							}

							const days = Math.floor(timeDifference / (1000 * 60 * 60 * 24));
							const hours = Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
							const minutes = Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60));
							const seconds = Math.floor((timeDifference % (1000 * 60)) / 1000);

							document.getElementById('countdown').innerText = 
								`${days}d ${hours}h ${minutes}m ${seconds}s`;
						}

						// Update the countdown every second
						const countdownInterval = setInterval(updateCountdown, 1000);

						// Initial call to display the countdown immediately
						updateCountdown();
					</script>
                </div>
            </div>
            <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/footer.php"; ?>
        </div>
    </body>
</html>
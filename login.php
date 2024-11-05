<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/mysql.php"; ?>
<?php
if(isset($_SESSION['siteusername'])) {
    header("Location: /");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == "POST") {
    $request = (object) [
        "username" => htmlspecialchars($_POST['username']),
        "password" => $_POST['password'],
        "password_hash" => password_hash($_POST['password'], PASSWORD_DEFAULT),
        "returned_password" => "",

        "error" => (object) [
            "message" => "",
            "status" => "OK"
        ]
    ];

    $stmt = $db->prepare("SELECT password FROM users WHERE username = :username");
    $stmt->bindParam(":username", $request->username);
    $stmt->execute();

    if (!$stmt->rowCount()) {
        $request->error->message = "Incorrect username or password!";
        $request->error->status = "";
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!isset($row['password'])) {
        $request->error->message = "Incorrect username or password!";
        $request->error->status = "";
    } else {
        $request->returned_password = $row['password'];
    }

    if (!password_verify($request->password, $request->returned_password)) {
        $request->error->message = "Incorrect username or password!";
        $request->error->status = "";
    }

    if ($request->error->status == "OK") {
        $stmt = $db->prepare("SELECT username FROM users WHERE username = :username");
        $stmt->bindParam(":username", $request->username);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['siteusername'] = $result['username'];
        $_SESSION['success'] = "Successfully logged in.";

        header("Location: /");
        exit();
    } else {
        $_SESSION['error'] = $request->error->message;
        header("Location: /login");
        exit();
    }
} ?>
<html>
    <head>
        <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/head.php"; ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    </head>
    <body>
        <div id="particles-js"></div>
        <div class="container">
            <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/header.php"; ?>
            <div id="app" class="card mt-5">
                <div class="card-header">Login</div>
                <div class="card-body">
                    <div class="col-md-6" style="margin-left: auto;margin-right: auto;">
						<form method="POST">
							<div class="form-group">
								<label for="username" class="col-form-label text-md-right"><small>Username</small></label>
								<input type="text" name="username" placeholder="Username" class="form-control" required>
								<label for="password" class="col-form-label text-md-right"><small>Password</small></label>
								<input type="password" name="password" placeholder="Password" class="form-control" required>
								<div class="d-flex flex-column align-items-center">
									<button type="submit" class="btn btn-primary mt-3" style="background-color: #510985;border-color: #510985;">Login</button>
								</div>
							</div>
						</form>
                    </div>
                </div>
            </div>
            <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/footer.php"; ?>
        </div>
    </body>
</html>
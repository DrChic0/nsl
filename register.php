<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/mysql.php"; ?>
<?php 
if(isset($_SESSION['siteusername'])) {
    header("Location: /");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == "POST") {
    function remove_emoji($text) {
        $clean_text = "";
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $text);
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);
        return $clean_text;
    }

    $request = (object) [
        "username" => htmlspecialchars(remove_emoji($_POST['username'])),
        "password" => $_POST['password'],
        "email" => htmlspecialchars($_POST['email']),
        "password_hash" => password_hash($_POST['password'], PASSWORD_BCRYPT),

        "error" => (object) [
            "message" => "",
            "status" => "OK"
        ],
    ];

    $recaptcha = (object) [
        "secret" => "SECRET",
        "response" => $_POST['g-recaptcha-response'],
    ];

    $options = [
        'http' => [
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'method' => 'POST',
            'content' => http_build_query($recaptcha),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    $json = json_decode($result);

    if (!$json->success) {
        $request->error->message = "reCAPTCHA failed.";
        $request->error->status = "";
    }
    if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
        $request->error->message = "Your email is invalid!";
        $request->error->status = "";
    }
    if (strlen($request->username) > 21) {
        $request->error->message = "Your username must be shorter than 20 characters.";
        $request->error->status = "";
    }
    if (strlen($request->password) < 8) {
        $request->error->message = "Your password must at least be 8 characters long.";
        $request->error->status = "";
    }
    if (!preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $request->password)) {
        $request->error->message = "Include numbers and letters in your password!";
        $request->error->status = "";
    }
    /*
    if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+"-]\./', $request->username) or preg_match('/[\'^£$%&*()}{@#~?><>,|=_+"-]\./', $_POST['username'])) {
       $request->error->message = "Your username cannot contain any special characters!";
       $request->error->status = "";
    }
    */
    if (preg_match('/[^a-zA-Z0-9_]/', $request->username) || preg_match('/[^a-zA-Z0-9_]/', $_POST['name'])) {
        $request->error->message = "Your username cannot contain any special characters!";
        $request->error->status = "";
    }
    if (!preg_match('/^\S+\w\S{1,}/', $request->username) or strpos($request->username, '.') != false) {
        $request->error->message = "Your username cannot contain any special characters!";
        $request->error->status = "";
    }
    if (empty(trim($request->username))) {
        $request->error->message = "Your username cannot be empty!";
        $request->error->status = "";
    }
    if (preg_match('/\s/', $request->username)) {
        $request->error->message = "Username cannot contain spaces!";
        $request->error->status = "";
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE email = lower(:email)");
    $stmt->bindParam(":email", $request->email);
    $stmt->execute();

    if ($stmt->rowCount()) {
        $request->error->message = "An account has already registered with this email";
        $request->error->status = "";
    }

    $stmt = $db->prepare("SELECT username FROM users WHERE username = :username");
    $stmt->bindParam(":username", $request->username);
    $stmt->execute();

    if ($stmt->rowCount()) {
        $request->error->message = "There's already a user with the same username!";
        $request->error->status = "";
    }

    if ($request->error->status == "OK") {
        $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->bindParam(":username", $request->username);
        $stmt->bindParam(":email", $request->email);
        $stmt->bindParam(":password", $request->password_hash);
        $stmt->execute();

        $_SESSION['siteusername'] = $request->username;
        $_SESSION['success'] = "Successfully registered!";

        header("Location: /");
        exit();
    } else {
        $_SESSION['error'] = $request->error->message;
        header("Location: /register");
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
                <div class="card-header">Register</div>
                <div class="card-body">
                    <div class="col-md-6" style="margin-left: auto;margin-right: auto;">
                    <form method="POST">
                        <div class="form-group">
                            <label for="username" class="col-form-label text-md-right"><small>Username</small></label>
                            <input type="text" name="username" placeholder="Username" class="form-control" required>
                            <label for="email" class="col-form-label text-md-right"><small>Email</small></label>
                            <input type="email" name="email" placeholder="Email" class="form-control" required>
                            <label for="password" class="col-form-label text-md-right"><small>Password</small></label>
                            <input type="password" name="password" placeholder="Password" class="form-control" required>
                            <div class="d-flex flex-column align-items-center">
                                <div id="_g-recaptcha" class="mt-3">
                                    <div class="g-recaptcha" data-sitekey="6LepitcpAAAAAOFz4v_pnlQFl9pH9PLXmslbdusD"></div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3" style="background-color: #510985;border-color: #510985;">Register</button>
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
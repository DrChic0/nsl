<nav class="navbar navbar-expand-lg navbar-light header">
                <a href="/" class="navbar-brand"><img src="/img/nsl-min.png" alt="novestusserverlist"
        class="navbar-brandimg d-inline-block mr-2" style="width: auto;height: 40px;"></a>
                <div id="navbar-collapse" class="collapse navbar-collapse">
                    <ul class="nav navbar-nav mr-auto">
                        <li class="nav-item"><a href="/" class="nav-link">Home</a></li>
                    </ul>
                    <div style="float: right;">
                        <?php if(!isset($_SESSION['siteusername'])) { ?>
                        <ul class="nav navbar-nav my-2 my-lg-0">
                            <li class="nav-item"><a href="/login" class="nav-link">Login</a></li>
                            <li class="nav-item"><a href="/register" class="nav-link">Register</a></li>
                        </ul>
                        <?php } else { ?>
                        <ul class="nav navbar-nav my-2 my-lg-0">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false"><i class="far align-middle fa-user mr-1"></i> <?php echo $_SESSION['siteusername'] ?>
                                    <span class="caret"></span>
                                </a>
                                <div class="navbar-dropdown dropdown-menu dropdown-menu-right">
									<?php if($user['status'] == "admin") { ?>
									<a class="dropdown-item" href="/admin/"><i class="fas fa-users-cog"></i>
                                        Admin Panel</a>
									<?php } ?>
                                    <a class="dropdown-item" href="/my/add-server"><i class="far align-middle fa-fw fa-plus mr-1"></i>
                                        Add Server</a>
                                    <a class="dropdown-item" href="/my/servers"><i
                                            class="fa-solid align-middle fa-fw fa-server mr-1"></i> My Servers</a>
                                    <a class="dropdown-item" href="/logout"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                            class="fa-solid align-middle fa-fw fa-right-from-bracket mr-1"></i> Logout</a>
                                    <form id="logout-form" action="/logout" method="POST" style="display: none;">
                                        <input type="hidden" name="_token" value="gma4YIw8AEaStI6KmB89iAdZ6El7Q4dNMuroxs7m">
                                    </form>
                                </div>
                            </li>
                        </ul>
                        <?php } ?>
                    </div>
                </div>
            </nav>
            <div class="alert alert-warning mt-3 mb-0">
                We currently only support hosting 4 servers at the same time due to limited resources.
            </div>
			<div class="alert alert-info alert-bar mt-3" role="alert">
				<strong>Notice:</strong> NSL isn't a solution for hosting Novetus 24/7 and you could be banned if you try to use our service as such.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
            <?php /*
			<div class="alert alert-info alert-bar mt-3" role="alert">
				25 characters for a title, no eye-candy allowed.
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
            */ ?>
			<?php /*
			<div class="alert alert-warning mt-3 mb-0">
                Servers will automatically shutdown after 5 minutes of inactivity
            </div>
			*/ ?>
            <?php if(isset($_SESSION['success'])) { ?>
            <div class="alert alert-success mt-3 mb-0">
                <?php echo $_SESSION['success']; ?>
            </div>
            <?php
            unset($_SESSION['success']);
            } ?>
            <?php if(isset($_SESSION['error'])) { ?>
            <div class="alert alert-danger mt-3 mb-0">
                <?php echo $_SESSION['error']; ?>
            </div>
            <?php
            unset($_SESSION['error']);
            } ?>
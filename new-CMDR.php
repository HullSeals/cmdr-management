<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../users/init.php';  //make sure this path is correct!
if (!securePage($_SERVER['PHP_SELF'])){die();}
$logged_in = $user->data();

$db = include 'db.php';
$mysqli = new mysqli($db['server'], $db['user'], $db['pass'], $db['db'], $db['port']);
$platformList = [];
$res = $mysqli->query('SELECT * FROM lookups.platform_lu ORDER BY platform_id');
while ($burgerking = $res->fetch_assoc()) {
    if ($burgerking['platform_name'] == 'ERR') {
        continue;
    }
    $platformList[$burgerking['platform_id']] = $burgerking['platform_name'];
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = include 'db.php';
$mysqli = new mysqli($db['server'], $db['user'], $db['pass'], $db['db'], $db['port']);
$stmt = $mysqli->prepare("SELECT seal_ID FROM staff WHERE seal_ID =?");
$stmt->bind_param("i", $user->data()->id);
$stmt->execute();
$numAlias = $stmt->get_result();
$stmt->close();
$validationErrors = [];
$lore = [];
if (isset($_GET['send'])) {
    foreach ($_REQUEST as $key => $value) {
        $lore[$key] = strip_tags(stripslashes(str_replace(["'", '"'], '', $value)));
    }
    if (!isset($platformList[$lore['platform']])) {
        $validationErrors[] = 'invalid platform';
    }
    if ($numAlias->num_rows+1 > 15) {
      $validationErrors[] = 'You have Too Many Registered CMDRs. Remove some first!';
    }
    if (!count($validationErrors)) {
      $stmt = $mysqli->prepare('CALL spCreateAliasCleaner(?,?,?)');
      $stmt->bind_param('sii', $lore['new_alias'], $lore['platform'], $user->data()->id);
      $stmt->execute();
      foreach ($stmt->error_list as $error) {
          $validationErrors[] = 'DB: ' . $error['error'];
      }
      $stmt->close();
  header("Location: .");
    }
}
 ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link href="../favicon.ico" rel="icon" type="image/x-icon">
        <link href="../favicon.ico" rel="shortcut icon" type="image/x-icon">
        <meta charset="UTF-8">
        <meta content="Wolfii Namakura" name="author">
        <meta content="hull seals, elite dangerous, distant worlds, seal team fix, mechanics, dw2" name="keywords">
        <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0" name="viewport">
        <meta content="Welcome to the Hull Seals, Elite Dangerous's Premier Hull Repair Specialists!" name="description">
        <title>New CMDR | The Hull Seals</title>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
        <link rel="stylesheet" type="text/css" href="https://hullseals.space/assets/css/allPages.css" />
      	<script src="https://hullseals.space/assets/javascript/allPages.js" integrity="sha384-PsQdnKGi+BdHoxLI6v+pi6WowfGtnraU6GlDD4Uh5Qw2ZFiDD4eWNTNG9+bHL3kf" crossorigin="anonymous"></script>
      	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
      	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js" integrity="sha384-1CmrxMRARb6aLqgBO7yyAxTOQE2AKb9GfXnEo760AUcUmFx3ibVJJAzGytlQcNXd" crossorigin="anonymous"></script>
        <style>
            .input-group-prepend input[type="checkbox"] {
                margin-right: 5px;
            }
            label {
                user-select: none;
            }
        </style>
    </head>

    <body>
        <div id="home">
            <header>
                <nav class="navbar container navbar-expand-lg navbar-expand-md navbar-dark" role="navigation">
                    <a class="navbar-brand" href="../"><img src="../images/emblem_scaled.png" height="30" class="d-inline-block align-top" alt="Logo"> Hull Seals</a>

                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="../">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../knowledge">Knowledge Base</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../journal">Journal Reader</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../about">About</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../contact">Contact</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="https://hullseals.space/users/">Login/Register</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <section class="introduction">
                <article>
                    <h1>Register a New CMDR</h1>
                    <br />
					          <hr />
                    <?php
                    if (count($validationErrors)) {
                        foreach ($validationErrors as $error) {
                            echo '<div class="alert alert-danger">' . $error . '</div>';
                        }
                        echo '<br>';
                    }
                    ?>
                    <form action="?send" method="post">
                        <div class="input-group mb-3">
                            <input type="text" name="new_alias" value="<?= $lore['new_alias'] ?? '' ?>" class="form-control" placeholder="New CMDR Name" aria-label="New CMDR Name" required>
                        </div>
                        <div class="input-group mb-3">
                                                  <div class="input-group-prepend">
                                                      <span class="input-group-text">Platform</span>
                                                  </div>
                                                  <select name="platform" class="custom-select" id="inputGroupSelect01" placeholder="Test" required>
                                                    <option value="4" selected disabled>Choose...</option>
                                                      <?php
                                                      foreach ($platformList as $platformId => $platformName) {
                                                          echo '<option value="' . $platformId . '"' . ($burgerking['platform'] == $platformId ? ' checked' : '') . '>' . $platformName . '</option>';
                                                      }
                                                      ?>
                                                  </select>
      </div>
                        <button type="submit" class="btn btn-primary">Submit</button> <a href="." class="btn btn-warning">Go Back</a>
                    </form>
                    <?php
    if($numAlias->num_rows === 0) exit('This will be your First CMDR.</article>
      <div class="clearfix"></div>
      </section>
      </div>
      <footer class="page-footer font-small">
      <div class="container">
      <div class="row">
      <div class="col-md-9 mt-md-0 mt-3">
      <h5 class="text-uppercase">Hull Seals</h5>
      <p><em>The Hull Seals</em> were established in January of 3305, and have begun plans to roll out galaxy-wide!</p>
      <a href="https://fuelrats.com/i-need-fuel" class="btn btn-sm btn-secondary">Need Fuel? Call the Rats!</a>
      </div>
      <hr class="clearfix w-100 d-md-none pb-3">
      <div class="col-md-3 mb-md-0 mb-3">
      <h5 class="text-uppercase">Links</h5>
      <ul class="list-unstyled">
      <li><a href="https://twitter.com/HullSeals" target="_blank"><img alt="Twitter" height="20" src="../images/twitter_loss.png" width="20"></a> <a href="https://reddit.com/r/HullSeals" target="_blank"><img alt="Reddit" height="20" src="../images/reddit.png" width="20"></a> <a href="https://www.youtube.com/channel/UCwKysCkGU_C6V8F2inD8wGQ" target="_blank"><img alt="Youtube" height="20" src="../images/youtube.png" width="20"></a> <a href="https://www.twitch.tv/hullseals" target="_blank"><img alt="Twitch" height="20" src="../images/twitch.png" width="20"></a> <a href="https://gitlab.com/hull-seals-cyberseals" target="_blank"><img alt="GitLab" height="20" src="../images/gitlab.png" width="20"></a></li>
      <li><a href="../donate">Donate</a></li>
      <li><a href="https://hullseals.space/knowledge/books/important-information/page/privacy-policy">Privacy & Cookies Policy</a></li>
      </ul>
      </div>
      </div>
      </div>
      <div class="footer-copyright">
      Site content copyright © 2019, The Hull Seals. All Rights Reserved. Elite Dangerous and all related marks are trademarks of Frontier Developments Inc.
      </div>
      </footer></body>
      </html>
');
    echo "<br />";
    echo "<h4>This is CMDR # ";
    echo $numAlias->num_rows+1;
	  echo " under the username ";
    echo echousername($user->data()->id);
    echo nl2br (" out of a maximum of 15</h4>");
    ?>
                </article>
            </section>
        </div>
        <footer class="page-footer font-small">
            <div class="container">
                <div class="row">
                    <div class="col-md-9 mt-md-0 mt-3">
                        <h5 class="text-uppercase">Hull Seals</h5>
                        <p><em>The Hull Seals</em> were established in January of 3305, and have begun plans to roll out galaxy-wide!</p>
                        <a href="https://fuelrats.com/i-need-fuel" class="btn btn-sm btn-secondary">Need Fuel? Call the Rats!</a>
                    </div>
                    <hr class="clearfix w-100 d-md-none pb-3">
                    <div class="col-md-3 mb-md-0 mb-3">
                        <h5 class="text-uppercase">Links</h5>

                        <ul class="list-unstyled">
                            <li><a href="https://twitter.com/HullSeals" target="_blank"><img alt="Twitter" height="20" src="../images/twitter_loss.png" width="20"></a> <a href="https://reddit.com/r/HullSeals" target="_blank"><img alt="Reddit" height="20" src="../images/reddit.png" width="20"></a> <a href="https://www.youtube.com/channel/UCwKysCkGU_C6V8F2inD8wGQ" target="_blank"><img alt="Youtube" height="20" src="../images/youtube.png" width="20"></a> <a href="https://www.twitch.tv/hullseals" target="_blank"><img alt="Twitch" height="20" src="../images/twitch.png" width="20"></a> <a href="https://gitlab.com/hull-seals-cyberseals" target="_blank"><img alt="GitLab" height="20" src="../images/gitlab.png" width="20"></a></li>
                            <li><a href="../donate">Donate</a></li>
                            <li><a href="https://hullseals.space/knowledge/books/important-information/page/privacy-policy">Privacy & Cookies Policy</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="footer-copyright">
                Site content copyright © 2019, The Hull Seals. All Rights Reserved. Elite Dangerous and all related marks are trademarks of Frontier Developments Inc.
            </div>
        </footer>
    </body>
</html>

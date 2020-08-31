<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//UserSpice Required
require_once '../users/init.php';  //make sure this path is correct!
if (!securePage($_SERVER['PHP_SELF'])){die();}

//IP Tracking Stuff
require '../assets/includes/ipinfo.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = include 'db.php';
$mysqli = new mysqli($db['server'], $db['user'], $db['pass'], $db['db'], $db['port']);
$stmt = $mysqli->prepare("SELECT * FROM staff WHERE seal_ID = ? AND seal_name = ?");
    $stmt->bind_param("is", $user->data()->id, $_GET['cne']);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!isset($_SESSION['2ndrun'])){
    if($result->num_rows === 0) {
      Redirect::to('index.php');
    }
  }
  $_SESSION['2ndrun'] = true;
$chickennugget = $result->fetch_assoc();
$fluffernutter = $chickennugget['seal_name'];
$wendys = $chickennugget['platform'];
$salsa = $chickennugget['ID'];
$stmt->close();
$platformList = [];
$res = $mysqli->query('SELECT * FROM lookups.platform_lu ORDER BY platform_id');
while ($burgerking = $res->fetch_assoc()) {
    if ($burgerking['platform_name'] == 'ERR') {
        continue;
    }
    $platformList[$burgerking['platform_id']] = $burgerking['platform_name'];
}
$validationErrors = [];
$lore = [];
if (isset($_GET['send'])) {
    foreach ($_REQUEST as $key => $value) {
        $lore[$key] = strip_tags(stripslashes(str_replace(["'", '"'], '', $value)));
    }
    if (!isset($platformList[$lore['platform']])) {
        $validationErrors[] = 'invalid platform';
    }
    if (!count($validationErrors)) {
      $stmt = $mysqli->prepare('CALL spEditAliasCleaner(?,?,?,?)');
      $stmt->bind_param('siis', $lore['edt_alias'], $lore['platform'], $lore['numberedt'], $lgd_ip);
      $stmt->execute();
      foreach ($stmt->error_list as $error) {
          $validationErrors[] = 'DB: ' . $error['error'];
      }
      $stmt->close();
          unset($_SESSION['2ndrun']);
      header("Location: .");
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta content="Edit My CMDRs" name="description">
    <title>Edit My CMDRs | The Hull Seals</title>
    <?php include '../assets/includes/headerCenter.php'; ?>
  </head>
<body>
  <div id="home">
    <?php include '../assets/includes/menuCode.php';?>
      <section class="introduction container">
    <article id="intro3">

      <h1>Edit CMDR</h1>
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
                  <div class="input-group-prepend">
                      <span class="input-group-text">Edited Name:</span>
                  </div>
                  <input type="text" name="edt_alias" value="<?php echo $fluffernutter; ?>" class="form-control" placeholder="Edited CMDR Name" aria-label="Edited CMDR Name" required>
                  <input type="hidden" name="numberedt" value="<?php echo $salsa; ?>" required>
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
            </article>
            <div class="clearfix"></div>
        </section>
      </div>
      <?php include '../assets/includes/footer.php'; ?>
  </body>
  </html>

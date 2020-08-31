<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//UserSpice Required
require_once '../../users/init.php';  //make sure this path is correct!
if (!securePage($_SERVER['PHP_SELF'])){die();}

//IP Tracking Stuff
require '../../assets/includes/ipinfo.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = include '../db.php';
$mysqli = new mysqli($db['server'], $db['user'], $db['pass'], $db['db'], $db['port']);
$stmt = $mysqli->prepare("SELECT * FROM irc WHERE seal_ID = ? AND irc_name = ? AND del_flag <> 1");
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
  $salsa = $chickennugget['ID'];
  $fluffernutter = $chickennugget['irc_name'];
  $stmt->close();
  $validationErrors = [];
  $lore = [];
  if (isset($_GET['send'])) {
      foreach ($_REQUEST as $key => $value) {
          $lore[$key] = strip_tags(stripslashes(str_replace(["'", '"'], '', $value)));
      }
      if (!count($validationErrors)) {
          $stmt = $mysqli->prepare('CALL spRemIRC(?,?)');
          $stmt->bind_param('is',$lore['numberedt'], $lgd_ip);
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
      <meta content="Remove IRC Alias" name="description">
      <title>Remove IRC Alias | The Hull Seals</title>
      <?php include '../../assets/includes/headerCenter.php'; ?>
    </head>
    <body>
        <div id="home">
          <?php include '../../assets/includes/menuCode.php';?>
            <section class="introduction container">
    	    <article id="intro3">
        <h1>Remove Alias</h1>
        <?php
        if (count($validationErrors)) {
            foreach ($validationErrors as $error) {
                echo '<div class="alert alert-danger">' . $error . '</div>';
            }
            echo '<br>';
        }
        ?>
        <h5>Are you Sure you want to remove the Alias <?php echo $fluffernutter; ?>?</h5>
        <form action="?send" method="post">
          <div class="input-group mb-3">
            <input type="hidden" name="numberedt" value="<?php echo $salsa; ?>" required>
          </div>
          <button type="submit" class="btn btn-danger">Yes, Remove.</button> <a href="." class="btn btn-warning">Go Back</a>
          </form>
        </article>
        <div class="clearfix"></div>
    </section>
  </div>
  <?php include '../../assets/includes/footer.php'; ?>
</body>
</html>

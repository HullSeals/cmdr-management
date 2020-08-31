<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//UserSpice Required
require_once '../../users/init.php';  //make sure this path is correct!
if (!securePage($_SERVER['PHP_SELF'])){die();}

$counter = 0;
if (isset($_SESSION['2ndrun'])) {
  unset($_SESSION['2ndrun']);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta content="IRC Aliases" name="description">
    <title>IRC Aliases | The Hull Seals</title>
    <?php include '../../assets/includes/headerCenter.php'; ?>
  </head>
  <body>
      <div id="home">
        <?php include '../../assets/includes/menuCode.php';?>
          <section class="introduction container">
  	    <article id="intro3">
    <h1>IRC Name Reservation</h1>
    <p>You may reserve up to 15 different Aliases. These are the names you will use in IRC. These do not affect your login username.</p>
    <?php
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = include '../db.php';
    $mysqli = new mysqli($db['server'], $db['user'], $db['pass'], $db['db'], $db['port']);
    $stmt = $mysqli->prepare("SELECT seal_ID, irc_name FROM irc WHERE seal_ID =? AND del_flag <> 1");
    $stmt->bind_param("i", $user->data()->id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows === 0) exit('<a href="new-irc.php" class="btn btn-success btn-lg active" >Register a New IRC Alias</a> or <a href="../" class="btn btn-secondary btn-lg active">Go to My CMDRs</a>
    </article>
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
    <li><a href="https://twitter.com/HullSeals" target="_blank"><img alt="Twitter" height="20" src="https://hullseals.space/images/twitter_loss.png" width="20"></a> <a href="https://reddit.com/r/HullSeals" target="_blank"><img alt="Reddit" height="20" src="https://hullseals.space/images/reddit.png" width="20"></a> <a href="https://www.youtube.com/channel/UCwKysCkGU_C6V8F2inD8wGQ" target="_blank"><img alt="Youtube" height="20" src="https://hullseals.space/images/youtube.png" width="20"></a> <a href="https://www.twitch.tv/hullseals" target="_blank"><img alt="Twitch" height="20" src="https://hullseals.space/images/twitch.png" width="20"></a> <a href="https://gitlab.com/hull-seals" target="_blank"><img alt="GitLab" height="20" src="https://hullseals.space/images/gitlab.png" width="20"></a></li>
    <li><a href="https://hullseals.space/donate">Donate</a></li>
    <li><a href="https://hullseals.space/knowledge/books/important-information/page/privacy-policy">Privacy & Cookies Policy</a></li>
    </ul>
    </div>
    </div>
    </div>
    <div class="footer-copyright">
    Site content copyright © 2020, The Hull Seals. All Rights Reserved. Elite Dangerous and all related marks are trademarks of Frontier Developments Inc.
    </div>
    </footer></body>
    </html>
');
    echo '<table class="table table-dark table-striped table-bordered table-hover table-responsive-md">
          <tr>
              <td>#</td>
              <td>Alias</td>
              <td colspan="2">Options</td>
          </tr>';
        while ($row = $result->fetch_assoc()) {
            $field1name = $counter+1;
            $field2name = $row["irc_name"];
            echo '<tr>
                      <td>'.$field1name.'</td>
                      <td>'.$field2name.'</td>
                      <td><a href="edit-irc.php?cne='.$field2name.'" class="btn btn-warning active">Edit</a></td>
                      <td><a href="rem-irc.php?cne='.$field2name.'" class="btn btn-danger active">Delete</a></td>
                  </tr>';
              $counter++;
        }
        echo '</table>';
        $result->free();
        echo "Number of Aliases: ";
        echo $counter;
        echo nl2br ("/15\n");
    ?>
    <br />
    <a href="new-irc.php" class="btn btn-success btn-lg active" >Register a New IRC Alias</a> or <a href="../" class="btn btn-secondary btn-lg active">Go to My CMDRs</a>
            </article>
            <div class="clearfix"></div>
        </section>
      </div>
      <?php include '../../assets/includes/footer.php'; ?>
  </body>
  </html>

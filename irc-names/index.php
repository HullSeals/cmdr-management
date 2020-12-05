<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//UserSpice Required
require_once '../../users/init.php';  //make sure this path is correct!
if (!securePage($_SERVER['PHP_SELF'])){die();}
$myUname = echousername($user->data()->id);

//IP Tracking Stuff
require '../../assets/includes/ipinfo.php';

$counter = 0;

//DB Info
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = include '../db.php';
$mysqli = new mysqli($db['server'], $db['user'], $db['pass'], 'ircDB', $db['port']);

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
        header("Location: .");
  }
}
if (isset($_GET['new'])) {
    foreach ($_REQUEST as $key => $value) {
        $lore[$key] = strip_tags(stripslashes(str_replace(["'", '"'], '', $value)));
    }
    if ($numAlias->num_rows+1 > 15) {
      $validationErrors[] = 'You have Too Many Registered Aliases. Remove some first!';
    }
    if (!count($validationErrors)) {
      $stmt = $mysqli->prepare('CALL spCreateIRCCleaner(?,?,?,?)');
      $stmt->bind_param('siss', $lore['new_alias'], $user->data()->id, $lgd_ip, echousername($user->data()->id));
      $stmt->execute();
      foreach ($stmt->error_list as $error) {
          $validationErrors[] = 'DB: ' . $error['error'];
      }
      $stmt->close();
  header("Location: .");
    }
}
if (isset($_GET['edit'])) {
    foreach ($_REQUEST as $key => $value) {
        $lore[$key] = strip_tags(stripslashes(str_replace(["'", '"'], '', $value)));
    }
    if (!count($validationErrors)) {
      $stmt = $mysqli->prepare('CALL spEditIRCCleaner(?,?,?,?)');
      $stmt->bind_param('siss', $lore['edt_alias'], $lore['numberedt'], $lgd_ip, $myUname);
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
	<meta content="IRC Aliases" name="description">
	<title>IRC Aliases | The Hull Seals</title><?php include '../../assets/includes/headerCenter.php'; ?>
	<script>
	   $('#myModal').on('shown.bs.modal', function () {
	 $('#myInput').trigger('focus')
	})
	</script>
</head>
<body>
	<div id="home">
		<?php include '../../assets/includes/menuCode.php';?>
		<section class="introduction container">
			<article id="intro3">
				<h1>IRC Name Reservation</h1>
				<p>You may reserve up to 15 different Aliases. These are the names you will use in IRC. These do not affect your login username. Deleted names are purged on a monthly basis, and may take up to 30 days to be processed.</p><?php
				    $stmt = $mysqli->prepare("SELECT nick, id FROM anope_db_NickAlias WHERE nc = ? AND del_flag <> 1");
				    $stmt->bind_param("s", $myUname);
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
				            $field2name = $row["nick"];
				            $field3name = $row["id"];
				            echo '<tr>
				                      <td>'.$field1name.'</td>
				                      <td>'.$field2name.'</td>
				                      <td><button type="button" class="btn btn-warning active" data-toggle="modal" data-target="#moE'.$field1name.'">Edit</button></td>
				                      <td><button type="button" class="btn btn-danger active" data-toggle="modal" data-target="#mo'.$field1name.'">Delete</button>
				                      </td>
				                  </tr>';
				                  echo '<div class="modal fade" id="mo'.$field1name.'" tabindex="-1" aria-hidden="true">
				  <div class="modal-dialog modal-dialog-centered">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="exampleModalLabel" style="color:black;">Delete Alias?</h5>
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body" style="color:black;">
				        Are you sure you want to delete the alias name "'.$field2name.'"?
				      </div>
				      <div class="modal-footer">
				        <form action="?send" method="post">
				            <input type="hidden" name="numberedt" value="'.$field3name.'" required>
				          <button type="submit" class="btn btn-danger">Yes, Remove.</button><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				          </form>
				      </div>
				    </div>
				  </div>
				</div>';
				                  echo '<div class="modal fade" id="moE'.$field1name.'" tabindex="-1" aria-hidden="true">
				  <div class="modal-dialog modal-dialog-centered">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="exampleModalLabel" style="color:black;">Edit IRC Alias<h5>
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body" style="color:black;">
				      <p>Your IRC Nickname must not have any special characters or spaces. Please replace all spaces with underscores.</p>
				      <form action="?edit" method="post">
				        <div class="input-group mb-3">
				                  <div class="input-group-prepend">
				                      <span class="input-group-text">Edited Alias:</span>
				                  </div>
				                  <input type="text" name="edt_alias" value="';
				                   echo $field2name;
				                   echo '" class="form-control" placeholder="Edited Alias Name" aria-label="Edited Alias Name" pattern="[a-zA-Z0-9-_.`|\[\]\{\}]{1,45}" maxlength="30" required>
				      </div>
				      <div class="modal-footer">
				            <input type="hidden" name="numberedt" value="'.$field3name.'" required>
				          <button type="submit" class="btn btn-primary">Submit</button><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				          </form>
				      </div>
				    </div>
				  </div>
				</div>';
				              $counter++;
				        }
				        echo '</table>';
				        $result->free();
				        echo "Number of Aliases: ";
				        echo $counter;
				        echo nl2br ("/15\n");
				    ?><br>
				<button class="btn btn-success btn-lg active" data-target="#moNew" data-toggle="modal" type="button">Register a New IRC Alias</button> or <a class="btn btn-secondary btn-lg active" href="../">Go to My CMDRs</a>
				<div aria-hidden="true" class="modal fade" id="moNew" tabindex="-1">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="exampleModalLabel" style="color:black;">New IRC Alias</h5><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
							</div>
							<div class="modal-body" style="color:black;">
								<p>Your IRC Nickname must not have any special characters or spaces. Please replace all spaces with underscores.</p>
								<hr>
								<form action="?new" method="post">
									<div class="input-group mb-3">
										<input aria-label="New Alias" class="form-control" maxlength="30" name="new_alias" pattern="[a-zA-Z0-9-_.`|\[\]\{\}]{1,45}" placeholder="New Alias" required="" type="text" value="&lt;?= $lore['new_alias'] ?? '' ?&gt;">
									</div>
									<div class="modal-footer">
										<button class="btn btn-primary" type="submit">Submit</button><button class="btn btn-secondary" data-dismiss="modal" type="button">Close</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</article>
		</section>
	</div>
	<div class="clearfix"></div><?php include '../../assets/includes/footer.php'; ?>
</body>
</html>

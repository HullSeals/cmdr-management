<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//UserSpice Required
require_once '../users/init.php';  //make sure this path is correct!
if (!securePage($_SERVER['PHP_SELF'])){die();}

//IP Tracking Stuff
require '../assets/includes/ipinfo.php';

$counter = 0;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
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

$validationErrors = [];
$lore = [];
if (isset($_GET['send'])) {
    foreach ($_REQUEST as $key => $value) {
        $lore[$key] = strip_tags(stripslashes(str_replace(["'", '"'], '', $value)));
    }
    if (!count($validationErrors)) {
        $stmt = $mysqli->prepare('CALL spRemAlias(?,?)');
        $stmt->bind_param('is',$lore['numberedt'], $lgd_ip);
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
      header("Location: .");
    }
}
if (isset($_GET['new'])) {
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
      $stmt = $mysqli->prepare('CALL spCreateAliasCleaner(?,?,?,?)');
      $stmt->bind_param('siis', $lore['new_alias'], $lore['platform'], $user->data()->id, $lgd_ip);
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
	<meta content="CMDR Management" name="description">
	<title>CMDR Management | The Hull Seals</title><?php include '../assets/includes/headerCenter.php'; ?>
</head>
<body>
	<div id="home">
		<?php include '../assets/includes/menuCode.php';?>
		<section class="introduction container">
			<article id="intro3">
				<h1>CMDR Management</h1>
				<p>You may register up to 15 different CMDR Names/Accounts. These are the names used on paperwork and records. These do not affect your login username.</p><?php
				    $stmt = $mysqli->prepare("SELECT seal_ID, seal_name, platform, ID FROM staff WHERE seal_ID =? AND del_flag <>1");
				    $stmt->bind_param("i", $user->data()->id);
				    $stmt->execute();
				    $result = $stmt->get_result();
				    if($result->num_rows === 0) {
              }
            else {
				    echo '<table class="table table-dark table-striped table-bordered table-hover table-responsive-md">
				          <tr>
				              <td>#</td>
				              <td>CMDR</td>
				              <td>Platform</td>
				              <td colspan="2">Options</td>
				          </tr>';
				        while ($row = $result->fetch_assoc()) {
				            $field1name = $counter+1;
				            $field2name = $row["seal_name"];
				            $field3name = $row["platform"];
				            $field4name = $row["ID"];
				            echo '<tr>
				                      <td>'.$field1name.'</td>
				                      <td>'.$field2name.'</td>
				                      <td>';
				                      if ($field3name == "1") {
				                        echo "PC";
				                      } elseif ($field3name=="2") {
				                        echo "Xbox";
				                      } elseif ($field3name=="3") {
				                        echo "PlayStation";
				                      } elseif ($field3name=="4") {
				                        echo "Needs Updating";
				                      }
				                      echo '</td>
				                      <td><button type="button" class="btn btn-warning active" data-toggle="modal" data-target="#moE'.$field1name.'">Edit</button></td>
				                      <td><button type="button" class="btn btn-danger active" data-toggle="modal" data-target="#mo'.$field1name.'">Delete</button></td>
				                  </tr>';
				                  echo '<div class="modal fade" id="mo'.$field1name.'" tabindex="-1" aria-hidden="true">
				  <div class="modal-dialog modal-dialog-centered">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="exampleModalLabel" style="color:black;">Delete CMDR?</h5>
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body" style="color:black;">
				        Are you sure you want to delete CMDR "'.$field2name.'"?
				      </div>
				      <div class="modal-footer">
				        <form action="?send" method="post">
				            <input type="hidden" name="numberedt" value="'.$field4name.'" required>
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
				        <h5 class="modal-title" id="exampleModalLabel" style="color:black;">Edit CMDR<h5>
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body" style="color:black;">
				      <form action="?edit" method="post">
              <div class="input-group mb-3">
    						<div class="input-group-prepend">
    							<span class="input-group-text">Edited Name:</span>
    						</div><input aria-label="Edited CMDR Name" class="form-control" name="edt_alias" placeholder="Edited CMDR Name" required="" type="text" value="'.$field2name.'"> <input name="numberedt" required="" type="hidden" value="'.$field4name.'">
    					</div>
    					<div class="input-group mb-3">
    						<div class="input-group-prepend">
    							<span class="input-group-text">Platform</span>
    						</div><select class="custom-select" id="inputGroupSelect01" name="platform" required="">
    							<option disabled selected value="4">
    								Choose...
    							</option>';
    				 foreach ($platformList as $platformId => $platformName) {
               if (!is_array($burgerking['platform'] ?? false)) continue;
               else {
    				     echo '<option value="' . $platformId . '"' . ($burgerking['platform'] == $platformId ? ' checked' : '') . '>' . $platformName . '</option>';
               }
    				 };
    						echo '</select>
    					</div>				      <div class="modal-footer">
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
				        echo "Number of CMDRs: ";
				        echo $counter;
				        echo nl2br ("/15\n");
              }
				    ?><br>
				<button class="btn btn-success btn-lg active" data-target="#moNew" data-toggle="modal" type="button">Register a New CMDR</button> or <a class="btn btn-secondary btn-lg active" href="irc-names">Go to IRC Names</a>
        <div aria-hidden="true" class="modal fade" id="moNew" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel" style="color:black;">New CMDR</h5><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
              </div>
              <div class="modal-body" style="color:black;">
                <form action="?new" method="post">
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
                  <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Submit</button><button class="btn btn-secondary" data-dismiss="modal" type="button">Close</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
			</article>
			<div class="clearfix"></div>
		</section>
	</div><?php include '../assets/includes/footer.php'; ?>
</body>
</html>

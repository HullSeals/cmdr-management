<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Declare Title, Content, Author
$pgAuthor = "";
$pgContent = "";
$useIP = 1; //1 if Yes, 0 if No.

//If you have any custom scripts, CSS, etc, you MUST declare them here.
//They will be inserted at the bottom of the <head> section.
$customContent = '<script>
   $(\'#myModal\').on(\'shown.bs.modal\', function () {
 $(\'#myInput\').trigger(\'focus\')
})
</script>
';

//UserSpice Required
require_once '../../users/init.php';  //make sure this path is correct!
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])){die();}

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
      $stmt = $mysqli->prepare('CALL spCreateIRC(?,?,?,?)');
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
      $stmt = $mysqli->prepare('CALL spEditIRC(?,?,?)');
      $stmt->bind_param('sis', $lore['edt_alias'], $lore['numberedt'], $lgd_ip);
      $stmt->execute();
      foreach ($stmt->error_list as $error) {
          $validationErrors[] = 'DB: ' . $error['error'];
      }
      $stmt->close();
      header("Location: .");
    }
}
?>
				<h1>IRC Name Reservation</h1>
				<p>You may reserve up to 15 different Aliases. These are the names you will use in IRC. These do not affect your login username. Deleted names are purged on a monthly basis, and may take up to 30 days to be processed.</p><?php
				    $stmt = $mysqli->prepare("SELECT nick, na.id FROM anope_db_NickAlias AS na JOIN anope_db_NickCore AS nc ON nc.display = na.nc WHERE nc.id = ? AND (del_flag <> 1 OR del_flag IS NULL)");
				    $stmt->bind_param("i", $user->data()->id);
				    $stmt->execute();
				    $result = $stmt->get_result();
				    if($result->num_rows === 0) {
              }
            else {
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
				                      <td><button type="button" class="btn btn-warning active" data-toggle="modal" data-target="#moE'.$field1name.'">Edit</button></td>';
                              if($result->num_rows === 1) {
                                echo '<td>You cannot delete the last record in the table.
                                </td>';
                                }
                                else {
				                      echo '<td><button type="button" class="btn btn-danger active" data-toggle="modal" data-target="#mo'.$field1name.'">Delete</button>
				                      </td>';
                            }
				                  echo '</tr>';
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
              }
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
										<input aria-label="New Alias" class="form-control" maxlength="30" name="new_alias" pattern="[a-zA-Z0-9-_.`|\[\]\{\}]{1,45}" placeholder="New Alias" required="" type="text" value="<?= $lore['new_alias'] ?? '' ?>">
									</div>
									<div class="modal-footer">
										<button class="btn btn-primary" type="submit">Submit</button><button class="btn btn-secondary" data-dismiss="modal" type="button">Close</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>

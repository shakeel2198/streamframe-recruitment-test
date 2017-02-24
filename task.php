<?php
require_once(__DIR__.'/class/task.php');
$objTask = new Task();

$selectedParentId = "";
$title = "";
$excludeId = "";

// Form submitted to create new task
if(isset($_POST['submit']) && trim($_POST['title']) != ""){
	$resp = $objTask->addNewTask($_POST['parentId'], $_POST['title']);
}

// Editted task ID
if(isset($_GET['id']) && $_GET['id']!= ""){
	$task = $objTask->getTaskById($_GET['id']);
	$selectedParentId = $task->parent_id;
	$title = $task->title;
	$excludeId = $_GET['id'];
}

// Form submitted to update a task
if(isset($_POST['update']) && trim($_POST['title']) != "" && trim($_POST['taskId']) != ""){
	$resp = $objTask->updateTask($_POST['taskId'], $_POST['parentId'], $_POST['title']);
	$selectedParentId = $_POST['parentId'];
	$title = $_POST['title'];
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Streamframe Test</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="container">
      <h2><?php echo (isset($_GET['id']))? 'Update Task' : 'Add New Task' ?></h2>
      <div class="clearfix"><p>&nbsp;</p></div>
      <!--Start Alert Box-->
      <?php if(isset($resp)){ echo $resp['message']; }?>
      <!--End Alert Box-->
      <div class="pull-right"><a type="button" href="index.php" class="btn btn-default"> << Back</a></div>
      <div class="clearfix"></div>
        <form name="addNewTask" id="addNewTask" action="" method="post">
          <div class="form-group">
            <label for="exampleInputEmail1">Parent Task</label>
            <?php echo $objTask->getTasksForSelect($selectedParentId, $excludeId); ?>
          </div>
          <div class="form-group">
            <label for="exampleInputPassword1">Title</label>
            <input type="text" name="title" class="form-control" id="title" placeholder="Title" value="<?php echo $title; ?>" required>
          </div>
          <?php if(isset($_GET['id'])){ ?>
          <input type="text" hidden="hidden" name="taskId" value="<?php echo $_GET['id']; ?>">
          <button type="submit" name="update" class="btn btn-primary">Update</button>
          <?php }else{ ?>
          <button type="submit" name="submit" class="btn btn-primary">Submit</button>
          <?php } ?>
        </form>
    </div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="js/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
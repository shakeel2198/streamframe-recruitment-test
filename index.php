<?php
require_once(__DIR__.'/class/task.php');
$objTask = new Task();
$filter = "all";
$currentPage = 1;

// If task have been filtered
if(isset($_GET['status']) && $_GET['status'] != ''){
	$filter = trim($_GET['status']);
}

// If navigated to any page
if(isset($_GET['page']) && $_GET['page'] != ''){
	$currentPage = (int)($_GET['page']);
}

// Getting all tasks
$tasks = $objTask->getAllTask($filter, $currentPage);
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
<link href="css/custome.css" rel="stylesheet">
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="container">
  <h2>Task Management System</h2>
  <div class="clearfix">
    <p>&nbsp;</p>
  </div>
  <!--Modal for task dependancies-->
  <div class="modal fade" id="addNewTask" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Task Dependancies</h4>
        </div>
        <div class="modal-body">
          <p style="text-align:center"><img src="images/ajax-loader.gif"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      <!-- /.modal-content --> 
    </div>
    <!-- /.modal-dialog --> 
  </div>
   <!--End modal for task dependancies-->
  <div class="panel panel-default">
    <div class="panel-body">
      <form class="form-inline" name="frm_filter" action="" method="get">
        <div class="form-group">
          <label for="exampleInputName2">Status</label>
          <select name="status" class="form-control">
            <option value="all" <?php if($filter == 'all') echo 'selected'; ?>>All</option>
            <option value="in-progress" <?php if($filter == 'in-progress') echo 'selected'; ?>>In Progress</option>
            <option value="done" <?php if($filter == 'done') echo 'selected'; ?>>Done</option>
            <option value="complete" <?php if($filter == 'complete') echo 'selected'; ?>>Complete</option>
          </select>
        </div>
        <button type="submit" class="btn btn-default">Filter</button>
      </form>
    </div>
  </div>
  <div class="pull-right"><a type="button" href="task.php" class="btn btn-primary">Add New Task</a></div>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Description</th>
        <th>Total Dependencies</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php
		  if(count($tasks)){ 
			  foreach($tasks as $task){ 
				$status = "In Progress";
				if($task->status == 1){
					$status = "Done";
				}elseif($task->status == 2){
					$status = "Complete";
				}
				
				$dependant = $objTask->countDependancies($task->id);
			  ?>
      <tr>
        <td><?php echo $task->id; ?></td>
        <td><?php echo $task->title; ?></td>
        <td><button type="button" class="btn btn-default" data-childs="<?php echo $dependant['childIds']?>" data-toggle="modal" data-target="#addNewTask"><span class="badge"><?php echo $dependant['total']?></span> View</button></td>
        <td><?php echo $status; ?></td>
        <td><input type="checkbox" value="<?php echo $task->id; ?>" <?php if($task->status) echo 'checked="checked"'; ?> onChange="changeStatus(this);">
          &nbsp;&nbsp;<a type="button" href="task.php?id=<?php echo $task->id; ?>" class="btn btn-default">Edit</a></td>
      </tr>
      <?php } 
		  }else{ ?>
      <tr>
        <td colspan="5">No task available</td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <!-- Pagination -->
  <div class="pull-left">
    <?php if($objTask->pagination){ echo $objTask->pagination; }?>
  </div>
  <!-- End pagination-->
</div>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) --> 
<script src="js/jquery.min.js"></script> 
<!-- Include all compiled plugins (below), or include individual files as needed --> 
<script src="js/bootstrap.min.js"></script> 
<script src="js/custome.js"></script>
</body>
<!--Overlay to show on whole body while changing state of a task-->
<div class="overlay"><img src="images/ajax-loader.gif" style="padding-top:20%"></div>
</html>
<?php
require_once(__DIR__.'/../config.php');

class Task{
	const TASK_TABLE = 'tasks';
	const STATUS_IN_PROGRESS = 0;
	const STATUS_DONE = 1;
	const STATUS_COMPLETE = 2;
	const RECORDS_PER_PAGE = 10;
	
	// Initiall pagination will be false
	public $pagination = false;
	
	/**
	* This function ruturns total number of records
	*
	@param $filter      string
	@param $currentPage number
	$return array	
	*/
	public function getAllTask($filter, $currentPage = 1){
		global $pdoHandlerObject;
		
		$where = " WHERE 1";
		if($filter == "complete"){
			$where = " WHERE status=".self::STATUS_COMPLETE;
		}elseif($filter == "in-progress"){
			$where = " WHERE status=".self::STATUS_IN_PROGRESS;
		}elseif($filter == "done"){
			$where = " WHERE status=".self::STATUS_DONE;
		}
		
		$startFrom = ($currentPage-1)* self::RECORDS_PER_PAGE;
		$rsTasks = $pdoHandlerObject->executeQuery("SELECT * FROM ".self::TASK_TABLE. $where ." ORDER BY id LIMIT ".$startFrom.",".self::RECORDS_PER_PAGE);
		$task = array();
		while($tasksObject = $rsTasks->fetch(PDO::FETCH_OBJ)){
			$task[] = $tasksObject;
		}
		
		// Creating pagination
		$this->createPagination($filter, $where, $currentPage);
		
		return $task;
	}
	
	
	/**
	* This function creates HTML for pagination if total number of records exceeds number of records per page
	*
	@param $filter      string
	@param $where       string
	@param $currentPage number
	$return void
	*/
	private function createPagination($filter, $where, $currentPage){
		global $pdoHandlerObject;
		$total = $pdoHandlerObject->executeQuery("SELECT COUNT(id) as total FROM ".self::TASK_TABLE.$where)->fetch(PDO::FETCH_OBJ)->total;
		
		if($total > self::RECORDS_PER_PAGE){
			$paginationHtml = '<nav aria-label="...">
      						   <ul class="pagination">';
			$numberOfPages = ceil($total/self::RECORDS_PER_PAGE);
			
			$prevLink = "#";
			$prevClass = "disabled";
			$nextLink = "#";
			$nextClass = "disabled";
			
			if($currentPage > 1){
				$prevLink = "?status=".$filter."&page=".($currentPage-1);
				$prevClass = "";
			}
			if($currentPage < $numberOfPages){
				$nextLink = "?status=".$filter."&page=".($currentPage+1);
				$nextClass = "";
			}
			
			$paginationHtml .=  '<li class="'.$prevClass.'"><a href="'.$prevLink.'" aria-label="Previous"><span aria-hidden="true">«</span></a></li>';
								
			for($i=1; $i <= $numberOfPages; $i++){
				$activeClass = "";
				if($i == $currentPage){
					$activeClass = "active";
				}
				$link = "?status=".$filter."&page=".$i;
				$paginationHtml .=  '<li class="'.$activeClass.'"><a href="'.$link.'">'.$i.' <span class="sr-only">(current)</span></a></li>';
			}
			
			$paginationHtml .=  '<li class="'.$nextClass.'"><a href="'.$nextLink.'" aria-label="Next"><span aria-hidden="true">»</span></a></li>';
			$paginationHtml .=   '</ul>.
								</nav>';
			$this->pagination = $paginationHtml;
		}
	}
	
	
	/**
	* This function add new task
	*
	@param $parentId number
	@param $title    string
	@return array
	*/
	public function addNewTask($parentId, $title){
		global $pdoHandlerObject;
		
		// Validating for blank title
		if($title == ""){
			$msg = '<div class="alert alert-danger" role="alert">A title is required for the task.</div>';
			return array('error'=>true, "parentId"=>$parentId, "title"=>$title, 'message'=>$msg);
		}
	  
		// Adding new task
		$input_array = array("parent_id"=>$parentId, "title"=>$title);
		$taskId = $pdoHandlerObject->insertQuery($input_array, self::TASK_TABLE);
		
		// Reverting complete parent to done if parent exists
		if($parentId){
			$task = $this->getTaskById($taskId);
			$this->revertCompleteParentsToDone($task);
		}
		
		$msg = '<div class="alert alert-success" role="alert">Task has been added successfuly!</div>';
		return array('error'=>false, 'message'=>$msg);
	}
	
	
	/**
	* This function updates an existing task
	*
	@param $taskId    number
	@param $parentId  number
	@param $title     string
	@return array
	*/
	public function updateTask($taskId, $newParentId, $title){
		global $pdoHandlerObject;
		
		// Checking if there any circular dependancy
		if($this->isCircularDependancy($taskId, $newParentId)){
			$msg = '<div class="alert alert-danger" role="alert">Cannot assign parent, a circular dependancy occured.</div>';
			return array('error'=>true, "parentId"=>$newParentId, "title"=>$title, 'message'=>$msg);
		}
		
		// Task befor edit
	    $task = $this->getTaskById($taskId);
		
		// Before edit marking done parents to complete
		if($task->status != self::STATUS_COMPLETE){
			$this->markDoneParentsToComplete($task);
		}
		
		// Updating task
		$update_array = array("parent_id"=>$newParentId, "title"=>$title);
		$pdoHandlerObject->updateQuery($update_array, self::TASK_TABLE, " WHERE id=".$taskId);
		
		// Task after edit
		$task = $this->getTaskById($taskId);
		
		// After edit reverting completed parents to done 
		if($task->status != self::STATUS_COMPLETE){
			$this->revertCompleteParentsToDone($task);
		}
		
		$msg = '<div class="alert alert-success" role="alert">Task has been updated successfuly!</div>';
		return array('error'=>false, 'message'=>$msg);
	}
	
	
	/**
	* This function validate for circular dependancy while updating task
	*
	@param $taskId       number
	@param $newParentId  number
	@return boolian
	*/
	public function isCircularDependancy($taskId, $newParentId){
		global $pdoHandlerObject;
		$task = $this->getTaskById($taskId);
		if($newParentId == 0){ // This is root task there is no parent
			return false;
		}
		
		if($task->parent_id == $newParentId){ // Parent is not changed
			return false;
		}
		
		$currentParentId = $newParentId;
		while($currentParentId != 0){
			if($currentParentId == $task->id){
				return true;
			}
			$currentParent = $this->getTaskById($currentParentId);
			$currentParentId = $currentParent->parent_id;
		}
		return false;
	}
	
	
  	/**
	* This function returns task by its ID
	*
	@param $taskId       number
	@return object
	*/
    public function getTaskById($taskId){
		global $pdoHandlerObject;
        $rsTask = $pdoHandlerObject->executeQuery("SELECT * FROM ".self::TASK_TABLE." WHERE id=".$taskId);
		$taskObject = $rsTask->fetch(PDO::FETCH_OBJ);
		return $taskObject;
    }
	
	
	/**
	* This function returns all task in the form of select box
	*
	@return string
	*/
	public function getTasksForSelect($selectedId = "", $excludeId = ""){
		global $pdoHandlerObject;
		$select = '<select name="parentId" class="form-control">'.
              '<option value="0">Select Parant Task</option>';
			  
		$rsTasks = $pdoHandlerObject->executeQuery("SELECT * FROM ".self::TASK_TABLE." ORDER BY title");
		while($tasksObject = $rsTasks->fetch(PDO::FETCH_OBJ)){
			$selected = "";
			if($selectedId == $tasksObject->id){
				$selected = "selected";
			}
			// Excluding editted task from parent task list
			if($excludeId !== $tasksObject->id){
				$select .= '<option value="'.$tasksObject->id.'" '.$selected.'>'.$tasksObject->title.'</option>';
			}
		}
		
        $select .= '</select>';
		return $select;
	}
	
	
	/**
	* This function counts total dependancies of a task
	*
	@param $taskId number
	@return array
	*/
	public function countDependancies($taskId){
		global $pdoHandlerObject;
		$total = 0;
		$childIds = array();
		$rsChilds = $pdoHandlerObject->executeQuery("SELECT * FROM ".self::TASK_TABLE." WHERE parent_id=".$taskId);
		while($child = $rsChilds->fetch(PDO::FETCH_OBJ)){
			$total++;
			$childIds[] = $child->id;
		}
		return array("total"=>$total, "childIds"=>implode($childIds,','));
	}
	
	
	/**
	* This function total dependancies of a task in the form of table
	*
	@param $taskIds string
	@return string
	*/
	public function getDependancies($taskIds){
		global $pdoHandlerObject;
		$rsDependants = $pdoHandlerObject->executeQuery("SELECT * FROM ".self::TASK_TABLE." WHERE id IN(".$taskIds.")");
		$table =  '<table class="table table-striped"> 
					<thead> 
					  <tr> 
					   <th>ID</th> 
					   <th>Description</th> 
					   <th>Status</th>
					   <th>Action</th>  
					  </tr> 
					</thead>
					<tbody>';
		while($dependant = $rsDependants->fetch(PDO::FETCH_OBJ)){
			$status = "In Progress";
			$checked = "";
			if($dependant->status == 1){
				$status = "Done";
				$checked = "checked";
			}elseif($dependant->status == 2){
				$status = "Complete";
				$checked = "checked";
			}
			$table .= '<tr>
						<td>'.$dependant->id.'</td>'.
						'<td>'.$dependant->title.'</td>'.
						'<td>'.$status.'</td>'.
						'<td><input type="checkbox" value="'.$dependant->id.'"'.$checked.'onChange="changeStatus(this);">'.
          '&nbsp;&nbsp;<a type="button" href="task.php?id='.$dependant->id.'" class="btn btn-default">Edit</a></td>'.
					   '</tr>';
		}
		$table .= '</tbody> </table>';
		return $table;
	}
	
	
	/**
	* This function change status of a task based on its dependancies and its previous status
	*
	@param $taskId number
	@return void
	*/
	public function markDoneOrComplete($taskId){
		global $pdoHandlerObject;
		$task = $this->getTaskById($taskId);
		if($this->areChildsCompleted($task)){
			$this->markComplete($task);
		}else{
			$this->markDone($task);
		}
	}
	
	
	/**
	* This function checks if childs of a task are completed
	*
	@param $task object
	@return boolian
	*/
	public function areChildsCompleted($task){
		global $pdoHandlerObject;
		$rsChilds = $pdoHandlerObject->executeQuery("SELECT * FROM ".self::TASK_TABLE." WHERE parent_id=".$task->id);
		while($child = $rsChilds->fetch(PDO::FETCH_OBJ)){
			if($child->status != self::STATUS_COMPLETE){
				return false;
				$this->areChildsCompleted($child);
			}
		}
		return true;
	}
	
	
	/**
	* This function marks a task complete
	*
	@param $task object
	@return void
	*/
	public function markComplete($task){
		global $pdoHandlerObject;
		$pdoHandlerObject->updateQuery(array("status"=>self::STATUS_COMPLETE), self::TASK_TABLE, " WHERE id=".$task->id);
		if($task->parent_id){
			$this->markDoneParentsToComplete($task);
		}
	}
	
	/**
	* This function marks a task done
	*
	@param $task object
	@return void
	*/
	public function markDone($task){
		global $pdoHandlerObject;
		$pdoHandlerObject->updateQuery(array("status"=>self::STATUS_DONE), self::TASK_TABLE, " WHERE id=".$task->id);
	}
	
	
	/**
	* This function reverts a task to in-progress state
	*
	@param $taskId number
	@return void
	*/
	public function markInProgress($taskId){
		global $pdoHandlerObject;
		$task = $this->getTaskById($taskId);
		$pdoHandlerObject->updateQuery(array("status"=>self::STATUS_IN_PROGRESS), self::TASK_TABLE, " WHERE id=".$taskId);
		
		if($task->parent_id){
			$this->revertCompleteParentsToDone($task);
		}
	}
	
	
	/**
	* This function marks all parent to complete which are in done state when a child is completed
	*
	@param $task object
	@return void
	*/
	public function markDoneParentsToComplete($task){
		global $pdoHandlerObject;
		$currentParentId = $task->parent_id;
		
		while($currentParentId != 0){
			$currentParent = $this->getTaskById($currentParentId);
			$currentParentId = $currentParent->parent_id;
			
			if($this->areSiblingCompleted($task)){
				if($currentParent->status == self::STATUS_DONE){
					$pdoHandlerObject->updateQuery(array("status"=>2), self::TASK_TABLE, " WHERE id=".$currentParent->id);
				}
			}
			$task = $currentParent;
		}
	}
	
	
	/**
	* This function marks all parent to done whenever a child reverted to in-progress state
	*
	@param $task object
	@return void
	*/
	public function revertCompleteParentsToDone($task){
		global $pdoHandlerObject;
		$parentId = $task->parent_id;
		while($parentId != 0){
			$parentTask = $this->getTaskById($parentId);
			if($parentTask->status == self::STATUS_COMPLETE){
				$pdoHandlerObject->updateQuery(array("status"=>self::STATUS_DONE), self::TASK_TABLE, " WHERE id=".$parentId);
			}
			$parentId = $parentTask->parent_id;	
		}
	}
	
	
	/**
	* This function checks if siblings of a task are completed
	*
	@param $task object
	@return boolian
	*/
	public function areSiblingCompleted($task){
		global $pdoHandlerObject;
		$rsSibling = $pdoHandlerObject->executeQuery("SELECT * FROM ".self::TASK_TABLE." WHERE parent_id=".$task->parent_id);
		
		while($sibling = $rsSibling->fetch(PDO::FETCH_OBJ)){
			if($sibling->status != self::STATUS_COMPLETE && $sibling->id != $task->id){
                return false;
            }
		}
		return true;
	}
}
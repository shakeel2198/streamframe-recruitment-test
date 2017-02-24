<?php
class PDOHandler{

	function executeQuery($sql){
		
		global $pdo_object;
		
		$stmt = $pdo_object->prepare($sql, array(PDO::ATTR_CURSOR, PDO::CURSOR_SCROLL));
	    
		$stmt->execute();
		
		return $stmt;
	}
	
	function insertQuery($input_array,$tableName){
	
		try{
			global $pdo_object;
		
			$insert_query = "INSERT INTO ".$tableName." SET ";
			
			foreach($input_array as $column_name => $value){
				
				$insert_query .= $column_name ." = :".$column_name.",";
			}
			
			$pdo_object->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		    $pdo_object->beginTransaction();

			$insert_query = substr($insert_query,0,strlen($insert_query)-1);
			
			$sth = $pdo_object->prepare($insert_query);
		
			$sth->execute($input_array);
			
			$last_insert_id = $pdo_object->lastInsertId();
			
			$pdo_object->commit();
			
			return $last_insert_id;
			
		}catch(PDOException $e){
			
			print $e->getMessage();
		}
	}
	
	function updateQuery($input_array,$tableName,$condition){
		
	
		try{
			global $pdo_object;
		
			$update_query = "UPDATE ".$tableName." SET ";
			
			foreach($input_array as $column_name => $value){
				
				$update_query .= $column_name ." = :".$column_name.",";
			}
			
			$update_query = substr($update_query,0,strlen($update_query)-1);
			
			$pdo_object->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		    $pdo_object->beginTransaction();

			$update_query =  $update_query.$condition;
			 
			$sth = $pdo_object->prepare($update_query);
		
			$sth->execute($input_array);
			
			$pdo_object->commit();
			 
			return true;
			
		}catch(PDOException $e){
			
			print $e->getMessage();
		}
	}
	
	
}
?>
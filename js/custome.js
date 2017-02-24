$('#addNewTask').on('show.bs.modal', function (event) {
	var button = $(event.relatedTarget) // Button that triggered the modal
	var childIds = button.data('childs')
	var modal = $(this);
	if(childIds == ""){ 
		modal.find('.modal-body').html('There is no dependancy of this task');
	}else{
		$.ajax({
			url:'ajax.php',
			data:{childIds:childIds},
			type:'POST',
			dataType:"json",
			success:function(response){
				modal.find('.modal-body').html(response.dependancies)
			}
		});
	}
})

function changeStatus(obj){
	var taskId = $(obj).val();
	if($(obj).is(':checked')){
			var action = 'markDoneOrComplete';
	}else{
		var action = 'markInProgress';
	}
	if(confirm('Are you sure?')){
		$.ajax({
			url:'ajax.php',
			data:{taskId:taskId,action:action},
			type:'POST',
			dataType:"json",
			beforeSend: function(){
				$(".overlay").modal('show');
			},
			success:function(response){
				if(response.success){
					location.reload();
				}
			}
		});
	}else{ // If not confirmed, changing checkbox status to previous state
		if($(obj).is(':checked')){
			$(obj).prop('checked', false);
		}else{
			$(obj).prop('checked', true);
		}
	}
}
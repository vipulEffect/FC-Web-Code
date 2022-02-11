@extends('layouts.subscriptionapp')

@section('content')
<style>
.error{color:red;}
#result {
	border: 1px solid #888;
	background: #f7f7f7;
	padding: 1em;
	margin-bottom: 1em;
}
</style>		
<div class="pageContainer">
	<div class="pageBox">
		<h2 class="pageHead">User Listing</h2>
		<div class="contentHolder">
			<div id="results" class="alert alert-info" style='display:none'></div>
			<div id='loadingmessage' style='display:none; height: 25%;position: absolute; width: 100%; text-align:center; padding-top:6%; '>
				<img style="width:120px;" src="{{ asset('images/download.gif') }}" alt="">
			</div>
			
			<div class="ImageUploadForm">
				<div class="UploadedImageTable" style="margin-top:0px;">
					<div class="tableRow">
						<table class="data-table1 mdl-data-table dataTable wallpapersTable table table-bordered" cellspacing="0" width="100%" role="grid" style="width: 100%;">
							<thead>
							<tr>
								<th>Id</th>
								<!--<th>Device</th>-->
								<th>Email</th>
								<!--<th>Current Wallpaper</th>-->
								<th>Subscription Status</th>
								<!--<th>Current Status</th>-->
							</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>
		
		<!-- boostrap subscription at device  model -->
		<div class="modal fade" id="subscription-modal" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="SubscriptionModal"></h4>
					</div>
					<div class="modal-body">
						<table class="table table-striped" id="tblDeviceInfo">
							<thead class="thead-dark">
								<tr>
								  <th scope="col">Email</th>
								  <th scope="col">Device</th>
								  <th scope="col">Current Wallpaper</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
					<div class="modal-footer"></div>
				</div>
			</div>
		</div>
		<!-- end bootstrap model -->
		<script type="text/javascript">
		//Function is used to unlock access to selected wallpaper
		function unlockAccess(id){//alert('====');
			if (confirm("Do you want to unlock access for user to select wallpaper?") == true) {
				var id = id; //alert(id);
				$.ajax({
					type:"POST",
					url: "{{ url('delete-user-subscription') }}",
					data: { id: id},
					dataType: 'json',
					success: function(res){
						$('.data-table1').DataTable().ajax.reload();
					}
				});
			}
		}
		
		function viewFunc(id){ //alert('===='+id);
			$.ajax({
				type:"POST",
				url: "{{ url('view-subscription') }}",
				data: { id: id },
				dataType: 'json',
				success: function(data){ //alert('####'+data);
					$('#SubscriptionModal').html("User Wallpaper");
					$('#subscription-modal').modal('show');
					
					var rec = "";
					$.each(data, function (key, val) {
						if(val.selFileName != ""){
							var filename = '<img src="'+val.selFileName+'" width="78px" height="78px" />';
						} else {
							var filename = 'Not selected';
						}
						rec += '<tr><td>'+val.userEmail+'</td><td>'+val.device+'</td><td>'+filename+'</td></tr>';
					});
					$("#tblDeviceInfo tbody").html(rec);
				}
			});
		} 
		
		$(document).ready( function () {
			$.ajaxSetup({
				headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			
			var table = $('.data-table1').DataTable({
				order: [ 0, 'desc' ],
				ajax: "{{ route('listUserSubscription') }}",
				columns: [
					{ 	"data": "id","visible":false},
					//{ 	"data": "deviceName"},
					{
						"data": "userEmail",
						"render": function(data, type, row) {
							return '<a href="javascript:void(0)" class="editLine" data-toggle="tooltip" onClick="viewFunc('+row.id+')">'+row.userEmail+'</a>';
						}
					},
					/*{
						"data": "selFileName",
						"render": function(data, type, row) {
							if(row.selFileName){
								if(row.selWallpaperType == 1){ //phone
									var imgPath = "https://fractalchaos.s3.ap-south-1.amazonaws.com/phoneWallpaper/"+row.selFileName;
								} else if(row.selWallpaperType == 2){ //tablet
									var imgPath = "https://fractalchaos.s3.ap-south-1.amazonaws.com/tabletWallpaper/"+row.selFileName;
								}
								return '<img src="'+imgPath+'" width="78px" height="78px" />';
							} else {
								return 'Not selected';
							}
						},"orderable": false, "searchable": false
					},*/
					{ 
						"data": "userSubscriptionType",
						"render": function(data, type, row) {
							if(row.userSubscriptionType == 0 || row.userSubscriptionType ==1){ //Weekly-trail or Weekly Plan
								var date = new Date(row.subscriptionEndDate);
								//date.setDate(date.getDate() + 7);
								
								var todayDate = new Date(); //Today Date  
								var todayDateFormated = moment(todayDate).format('MM-DD-YYYY');		

								var subsExpDate = new Date(date);
								var subsExpDateFormated = moment(subsExpDate).format('MM-DD-YYYY');	
								//console.log('todayDateFormated='+todayDateFormated);
								//console.log('subsExpDateFormated='+subsExpDateFormated);

								if(subsExpDateFormated >= todayDateFormated){
									var UserSubStatus = "Trial Period";
								} else {
									var UserSubStatus = "Expired";
								}
							} else if(data == 2) { //Monthly
								var date = new Date(row.subscriptionEndDate);
								//date.setDate(date.getDate() + 30);
								
								var todayDate = new Date(); //Today Date  
								var todayDateFormated = moment(todayDate).format('MM-DD-YYYY');		

								var subsExpDate = new Date(date);
								var subsExpDateFormated = moment(subsExpDate).format('MM-DD-YYYY');	
								//console.log('todayDateFormated='+todayDateFormated);
								//console.log('subsExpDateFormated='+subsExpDateFormated);

								if(subsExpDateFormated >= todayDateFormated){
									var UserSubStatus = "Subscribed";
								} else {
									var UserSubStatus = "Expired";
								}
							}
							return UserSubStatus + '&nbsp; <span class="expiry">Expiry date ' + moment(date).format('MMMM-DD-YYYY') +'</span>';
						}
					},
					//{ "data": "action","name": "action","orderable": false, "searchable": false},
				]
			});
		});
		</script>
	</div>
</div>
@endsection	
@extends('layouts.innerapp')

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
        <h2 class="pageHead">Wallpaper</h2>
        <div class="contentHolder" style="position:relative;">
			@if (session()->has('message'))
				<div class="alert alert-info">
					{{ session('message') }}
				</div>
			@endif
			
			<div id="results" class="alert alert-info" style='display:none'></div>
			<div id='loadingmessage' style='display:none; height: 25%;position: absolute; width: 100%; text-align:center; padding-top:6%; '>
				<img style="width:120px;" src="{{ asset('images/download.gif') }}" alt="">
			</div>
			
			<div class="ImageUploadForm">
				<form action="" method="post" name="frmAdd" id="frmAdd" class="form-horizontal" enctype="multipart/form-data">
				{{ csrf_field() }}
				<div class="form50">
					<div class="pageRow">
						<div class="RowLabel"><label for="">Wallpaper Name</label></div>
						<div class="rowInput">
							<input type="text" name="wallpaperName" id="wallpaperName" />
							<span class="tooltip">Error</span>
						</div>
					</div>
					
					<div class="pageRow">
						<div class="RowLabel"><label for="">Phone Wallpaper</label></div>
						<div class="rowInput">
							<div class="browse" style="padding-left:0px;">
								<label class="button file" for="phoneWallpaper">Browse</label>
								<input style="display:block; border:none; padding:0px; visibility:hidden; height:1px;" type="file" name="phoneWallpaper" id="phoneWallpaper">
							</div>
						</div>
					</div>
					
					<div class="pageRow">
						<div class="RowLabel"><label for="">Tablet Wallpaper</label></div>
						<div class="rowInput">
							<div class="browse"  style="padding-left:0px;">
								<label class="button file" for="tabletWallpaper">Browse</label>
								<input style="display:block; border:none; padding:0px; visibility:hidden; height:1px;" type="file" name="tabletWallpaper" id="tabletWallpaper">
							</div>	
						</div>
					</div>
				</div>
           
				<div class="imageFormSubmitRow">
					<input type="submit" class="button uploadImage" value="Submit" />
				</div>
				</form>
				
				<div id="imgContainer"></div><div id="imgContainer1"></div>
				
				<div class="UploadedImageTable">
					<div id="result">
						Event result:
					</div>
					<div class="tableRow">
						<table class="data-table mdl-data-table dataTable wallpapersTable table table-bordered" cellspacing="0" width="100%" role="grid" style="width: 100%;">
                            <thead>
                                <tr>
									<th>id</th>
                                    <th>Order No</th>
									<th>Wallpaper Name</th>
									<th>Type</th>
									<th>Phone Wallpaper</th>
									<th>Tablet Wallpaper</th>
									<th>Action</th>
                                </tr>
                            </thead>
                        </table>
					</div>
				</div>
			</div>
			
			<!-- boostrap wallpaper  model -->
			<div class="modal fade" id="wallpaper-modal" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="WallpaperModal"></h4>
						</div>
						<div class="modal-body">
							<form action="javascript:void(0)" id="frmAdd1" name="frmAdd1" class="form-horizontal" method="POST" enctype="multipart/form-data">
								{{ csrf_field() }}
								<input type="hidden" name="id" id="id">
								<div class="form-group">
									<label for="name" class="col-sm-12 control-label">Wallpaper Name</label>
									<div class="col-sm-12">
										<input type="text" class="form-control" id="wallpaperName1" name="wallpaperName" required="">
									</div>
								</div> 
								
								<div class="form-group">
									<label for="name" class="col-sm-12 control-label">Phone Wallpaper</label>
									<div class="col-sm-12">
										<img src="" id="phoneWallpaper11" width="78px" height="78px" />
										<div class="browseimg">
											<input type="file" name="phoneWallpaper1" id="phoneWallpaper1">
											<label class="button file-upld" for="phoneWallpaper1"><img src="{{asset('images/uplod.png')}}"></label>
										</div>
									</div>
								</div>
								
								<div class="form-group">
									<label class="col-sm-12 control-label">Tablet Wallpaper</label>
									<div class="col-sm-12">
										<img src="" id="tabletWallpaper11" width="78px" height="78px" />
										<div class="browseimg">
											<input type="file" name="tabletWallpaper1" id="tabletWallpaper1">
											<label class="button file-upld" for="tabletWallpaper1"><img src="{{asset('images/uplod.png')}}"></label>
										</div>
									</div>
								</div>
								
								<div class="col-sm-offset-2 col-sm-10">
									<input type="submit" id="btn-save" value="Save changes" />
								</div>
							</form>
						</div>
						<div class="modal-footer">
						<div id="imgContainer11"></div><div id="imgContainer111"></div>
						</div>
					</div>
				</div>
			</div>
			<!-- end bootstrap model -->
			
			<script type="text/javascript">
			$(document).ready( function () {
				$.ajaxSetup({
					headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					}
				});
			});
			
			function editFunc(id){ //alert('===='+id);
				$.ajax({
					type:"POST",
					url: "{{ url('edit-wallpaper') }}",
					data: { id: id },
					dataType: 'json',
					success: function(res){ 
						$('#WallpaperModal').html("Edit Wallpaper");
						$('#wallpaper-modal').modal('show');
						
						$('#id').val(res.id);
						$('#wallpaperName1').val(res.wallpaperName);
						
						var phoneImg11 = "https://fractalchaos.s3.ap-south-1.amazonaws.com/phoneWallpaper/"+res.phoneFilename;
						$("#phoneWallpaper11").attr("src",phoneImg11);
						
						var tabletImg11 = "https://fractalchaos.s3.ap-south-1.amazonaws.com/tabletWallpaper/"+res.tabletFilename;
						$("#tabletWallpaper11").attr("src",tabletImg11);
					}
				});
			} 
			
			function deleteFunc(id,prefOrder){
				if (confirm("Do you want to delete wallpaper?") == true) {
					var id = id; //alert(id);
					var prefOrder = prefOrder;
					// ajax
					$.ajax({
						type:"POST",
						url: "{{ url('delete-wallpaper') }}",
						data: { id: id, prefOrder:prefOrder},
						dataType: 'json',
						success: function(res){
							$('.data-table').DataTable().ajax.reload();
							//var oTable = $('.data-table').dataTable();
							//oTable.fnDraw(false);
						}
					});
				}
			}
			
			<!-- jQuery Form Validation code -->
			$.validator.addMethod('minImageWidth1', function(value, element, minWidth) {
				return ($(element).data('imageWidth') || 0) >= minWidth;
			}, function(minWidth, element) {
				var imageWidth = $(element).data('imageWidth');
				return (imageWidth)
					? ("Your image's width must be greater than or equal to " + minWidth + "px")
					: "Selected file is not an image.";
			});
			
			$.validator.addMethod('minImageHeight1', function(value, element, minHeight) {
				return ($(element).data('imageHeight') || 0) >= minHeight;
			}, function(minHeight, element) {
				var imageHeight = $(element).data('imageHeight');
				return (imageHeight)
					? ("Your image's height must be greater than or equal to " + minHeight + "px")
					: "Selected file is not an image.";
			});
			
			var validator1 = $('#frmAdd1').validate({
				rules: {
					wallpaperName: {required: true},  
					phoneWallpaper1: {required: true,minImageWidth1: 1280,minImageHeight1: 800},
					tabletWallpaper1: {required: true,minImageWidth1: 1280,minImageHeight1: 1920}
				},
				messages: {
					wallpaperName: {required: "Enter Wallpaper Name." },  
					phoneWallpaper1: { required: "Min. size of Phone Wallpaper is 1280*800px" },
					tabletWallpaper1: { required: "Min. size of Tablet Wallpaper is 1280*1920px" },
				}
			});
			
			$('#frmAdd1').submit(function(e) { //alert('--');
				e.preventDefault();
				var $form = $(this);
				// check if the input is valid using a 'valid' property
				if (!$form.valid) return false;
				
				if(this.elements['phoneWallpaper1'].files[0]){
					the_file = this.elements['phoneWallpaper1'].files[0]; //get the file element
					var filename = Date.now() + '.' + the_file.name.split('.').pop(); //make file name unique using current time (milliseconds)
					//alert(filename)
				}
				
				if(this.elements['tabletWallpaper1'].files[0]){
					the_file1 = this.elements['tabletWallpaper1'].files[0]; //get the file element
					var filename1 = Date.now() + '.' + the_file1.name.split('.').pop(); 
					//alert(filename1)
				}
				
				var form_data = new FormData(this);
				$.ajax({
					type:'POST',
					url: "{{ url('store-wallpaper')}}",
					data: form_data,
					datatype: 'json',
					contentType: false,
					processData:false,
					/*beforeSend: function(){
						// Show image container
						$("#loadingmessage").show();
					},*/
					success: function(data){
						//Hide image container
						//$("#loadingmessage").hide();
						$("#wallpaper-modal").modal('hide');
						//var oTable = $('.data-table').dataTable();
						//oTable.fnDraw(false);
						$('.data-table').DataTable().ajax.reload();
						$("#btn-save").html('Submit');
						$("#btn-save"). attr("disabled", false);
						$("#results").css("display","block"); 
						$("#results").html(data.msg); 
						
						setTimeout(function(){ 
							$("#results").css("display","none"); 
							$("#results").html(""); 
						}, 4000);
					},
					error: function(data){
						console.log(data);
					}
				});
			});
			
			$(document).ready(function() {
				$.validator.addMethod('minImageWidth', function(value, element, minWidth) {
					return ($(element).data('imageWidth') || 0) >= minWidth;
				}, function(minWidth, element) {
					var imageWidth = $(element).data('imageWidth');
					return (imageWidth)
						? ("Your image's width must be greater than or equal to " + minWidth + "px")
						: "Selected file is not an image.";
				});
				
				$.validator.addMethod('minImageHeight', function(value, element, minHeight) {
					return ($(element).data('imageHeight') || 0) >= minHeight;
				}, function(minHeight, element) {
					var imageHeight = $(element).data('imageHeight');
					return (imageHeight)
						? ("Your image's height must be greater than or equal to " + minHeight + "px")
						: "Selected file is not an image.";
				});
						
				<!-- jQuery Form Validation code -->
				var validator = $('#frmAdd').validate({
					rules: {
						wallpaperName: {required: true},  
						phoneWallpaper: {required: true,minImageWidth: 1280,minImageHeight: 800},
						tabletWallpaper: {required: true,minImageWidth: 1280,minImageHeight: 1920}
					},
					messages: {
						wallpaperName: {required: "Enter Wallpaper Name." },  
						phoneWallpaper: { required: "Min. size of Phone Wallpaper is 1280*800px" },
						tabletWallpaper: { required: "Min. size of Tablet Wallpaper is 1280*1920px" },
					}
				});
						
				$("#frmAdd").submit(function(e) {
					e.preventDefault();
					var $form = $(this);

					// check if the input is valid using a 'valid' property
					if (!$form.valid) return false;
					//if(this.elements['phoneWallpaper'].files[0]){
						the_file = this.elements['phoneWallpaper'].files[0]; //get the file element
						var filename = Date.now() + '.' + the_file.name.split('.').pop(); //make file name unique using current time (milliseconds)
					//}
					//if(this.elements['tabletWallpaper'].files[0]){
						the_file1 = this.elements['tabletWallpaper'].files[0]; //get the file element
						var filename1 = Date.now() + '.' + the_file1.name.split('.').pop(); 
					//}
					var form_data = new FormData(this); //Creates new FormData object
					$.ajax({
						url : "{{ url('store-wallpaper')}}",
						type: 'post',
						datatype: 'json',
						data : form_data,
						contentType: false,
						processData:false,
						beforeSend: function(){
							// Show image container
							if ($form.valid){ $("#loadingmessage").show();}
						},
						success: (data) => { //alert('success='+data.msg);
							//Hide image container
							$("#loadingmessage").hide();
							//var oTable = $('.data-table').dataTable();
							//oTable.fnDraw(false);
							$("#results").css("display","block"); 
							$("#results").html(data.msg); 
							
							setTimeout(function(){ 
								$("#results").css("display","none"); 
								$("#results").html(""); 
							}, 4000);
							$('.data-table').DataTable().ajax.reload();
						},
						error: function(data){
							console.log('error='+data);
						}
					});
				});

				var $submitBtn = $('#frmAdd').find('input:submit');
				var	$photoInput = $('#phoneWallpaper');
				var $imgContainer = $('#imgContainer');
				var phoneImageWidth = 1280;
				var phoneImageHeight = 800;
				$('#phoneWallpaper').change(function() {
					$photoInput.removeData('imageWidth');
					$photoInput.removeData('imageHeight');
					$imgContainer.hide().empty();

					var file = this.files[0];
					if (file.type.match(/image\/.*/)) {
						$submitBtn.attr('disabled', true);
						var reader = new FileReader();
						reader.onload = function() {
							var $img = $('<img />').attr({ src: reader.result });
							$img.on('load', function() {
								$imgContainer.append($img).show();
								var imageWidth = $img.width(); 
								var imageHeight = $img.height(); 
								
								//alert('imageWidth='+imageWidth);
								//alert('imageHeight='+imageHeight);
								
								$photoInput.data('imageWidth', imageWidth);
								$photoInput.data('imageHeight', imageHeight);
								if (imageWidth <= phoneImageWidth && imageHeight <= phoneImageHeight) {
									$imgContainer.hide();
								} else {
									$img.css({ width: '400px', height: '200px' });
									$img.css('display', 'none');
								}
								$submitBtn.attr('disabled', false);
								validator.element($photoInput);
							});
						}
						reader.readAsDataURL(file);
					} else {
						validator.element($photoInput);
					}
				});
				
				var	$tabletInput = $('#tabletWallpaper');
				var $imgContainer1 = $('#imgContainer1');
				var tabletImageWidth = 1280;
				var tabletImageHeight = 1920;
				$('#tabletWallpaper').change(function() {
					$tabletInput.removeData('imageWidth');
					$tabletInput.removeData('imageHeight');
					$imgContainer1.hide().empty();
					
					var file = this.files[0];
					if (file.type.match(/image\/.*/)) {
						$submitBtn.attr('disabled', true);
						var reader = new FileReader();
						reader.onload = function() {
							var $img = $('<img />').attr({ src: reader.result });
							$img.on('load', function() {
								$imgContainer1.append($img).show();
								var imageWidth = $img.width(); 
								var imageHeight = $img.height(); 
								
								//alert('tablet-image-Width='+imageWidth);
								//alert('tablet-image-Height='+imageHeight);
								$tabletInput.data('imageWidth', imageWidth);
								$tabletInput.data('imageHeight', imageHeight);
								if (imageWidth <= tabletImageWidth && imageHeight <= tabletImageHeight) {
									$imgContainer1.hide();
								} else {
									$img.css({ width: '400px', height: '200px' });
									$img.css('display', 'none');
								}
								$submitBtn.attr('disabled', false);
								validator.element($tabletInput);
							});
						}
						reader.readAsDataURL(file);
					} else {
						validator.element($tabletInput);
					}
				});
				
				//For Edit section
				var $submitBtn1 = $('#frmAdd1').find('input:submit');
				var	$photoInput1 = $('#phoneWallpaper1');
				var $imgContainer11 = $('#imgContainer11');
				var phoneImageWidth = 1280;
				var phoneImageHeight = 800;
				//$('#phoneWallpaper1').change(function() {alert('==');
				$('body').on('click', '#phoneWallpaper1', function(){ //alert('photo');
					$photoInput1.removeData('imageWidth');
					$photoInput1.removeData('imageHeight');
					$imgContainer11.hide().empty();

					var file = this.files[0];
					if (file.type.match(/image\/.*/)) {
						$submitBtn1.attr('disabled', true);
						var reader = new FileReader();
						reader.onload = function() {
							var $img = $('<img />').attr({ src: reader.result });
							$img.on('load', function() {
								$imgContainer11.append($img).show();
								var imageWidth = $img.width(); 
								var imageHeight = $img.height(); 
								
								//alert('imageWidth='+imageWidth);
								//alert('imageHeight='+imageHeight);
								
								$photoInput1.data('imageWidth', imageWidth);
								$photoInput1.data('imageHeight', imageHeight);
								if (imageWidth <= phoneImageWidth && imageHeight <= phoneImageHeight) {
									$imgContainer11.hide();
								} else {
									$img.css({ width: '400px', height: '200px' });
									$img.css('display', 'none');
								}
								$submitBtn1.attr('disabled', false);
								validator1.element($photoInput1);
							});
						}
						reader.readAsDataURL(file);
					} else {
						validator1.element($photoInput1);
					}
				});
				
				var	$tabletInput1 = $('#tabletWallpaper1');
				var $imgContainer111 = $('#imgContainer111');
				var tabletImageWidth = 1280;
				var tabletImageHeight = 1920;
				//$('#tabletWallpaper1').change(function() {
				$('body').on('click', '#tabletWallpaper1', function(){ //alert('ciccc');
					$tabletInput1.removeData('imageWidth');
					$tabletInput1.removeData('imageHeight');
					$imgContainer111.hide().empty();
					
					var file = this.files[0];
					if (file.type.match(/image\/.*/)) {
						$submitBtn1.attr('disabled', true);
						var reader = new FileReader();
						reader.onload = function() {
							var $img = $('<img />').attr({ src: reader.result });
							$img.on('load', function() {
								$imgContainer111.append($img).show();
								var imageWidth = $img.width(); 
								var imageHeight = $img.height(); 
								
								//alert('tablet-image-Width='+imageWidth);
								//alert('tablet-image-Height='+imageHeight);
								$tabletInput.data('imageWidth', imageWidth);
								$tabletInput.data('imageHeight', imageHeight);
								if (imageWidth <= tabletImageWidth && imageHeight <= tabletImageHeight) {
									$imgContainer111.hide();
								} else {
									$img.css({ width: '400px', height: '200px' });
									$img.css('display', 'none');
								}
								$submitBtn1.attr('disabled', false);
								validator1.element($tabletInput1);
							});
						}
						reader.readAsDataURL(file);
					} else {
						validator1.element($tabletInput1);
					}
				});
				//End
				
				

				var table = $('.data-table').DataTable({
					//rowReorder: true,
					order: [ 1, 'desc' ],
					//pageLength: 5,
					//processing: true,
					//serverSide: true,
					ajax: "{{ route('list') }}",
					columns: [
						{ "data": "id","visible":false},
						{ "data": "prefOrder"},
						{ "data": "wallpaperName" },
						{ "data": "prefType" },
						{
							"data": "phoneFilename",
							"render": function(data, type, row) {
								var phoneImg ="https://fractalchaos.s3.ap-south-1.amazonaws.com/phoneWallpaper/"+data;
								return '<img src="'+phoneImg+'" width="78px" height="78px" />';
							},"orderable": false, "searchable": false
						},
						{
							"data": "tabletFilename",
							"render": function(data, type, row) {
								var tabletImg ="https://fractalchaos.s3.ap-south-1.amazonaws.com/tabletWallpaper/"+data;
								return '<img src="'+tabletImg+'" width="78px" height="78px" />';
							},"orderable": false, "searchable": false
						},
						//{ "data": "reorder","name": "reorder","orderable": false, "searchable": false},
						{ "data": "action","name": "action","orderable": false, "searchable": false},
					],
					rowReorder: {
						dataSrc: 'prefOrder'
					}
				});
				
				table.on("row-reorder", function (e, diff, edit) {
					var finalRes = [];
					var temp = edit.triggerRow.data();
					var result = "Reorder started on row: " + edit.triggerRow.data()["wallpaperName"] + "<br>";

					for (var i = 0, ien = diff.length; i < ien; i++) {
						var rowData = table.row(diff[i].node).data();
						result += rowData["wallpaperName"] + " updated to be in position " + diff[i].newData + " (was " + diff[i].oldData + ")<br>";
						
						finalRes.push({wallpaperId: rowData["id"],wallpaperName: rowData["wallpaperName"],oldPosition: diff[i].oldData, NewPosition: diff[i].newData, dbPrefType: rowData["prefType"]});
					}
					$("#result").html("Event result:<br>" + result);
					
					var elements = Object.keys(finalRes).map(function (k) { return finalRes[k];})
					//console.log('elements='+elements.length);
					getVal(elements);
				});
				
				//Function is used to save reorder sequence  of wallpaper
				function getVal(res) { //alert(res.length);
					$.ajax({
						type:"POST",
						url: "{{ url('reorder-wallpaper-sequence') }}",
						data: { res: res },
						dataType: 'json',
						success: function(data){ 
							console.log(data);
							$('.data-table').DataTable().ajax.reload();
						},
						error:function(error){
							console.log(error);
						}
					});
				}
			});
			</script>
		</div>
    </div>
</div>
@endsection
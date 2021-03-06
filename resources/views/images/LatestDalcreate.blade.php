@extends('layouts.innerapp')

@section('content')

<style>
.error{color:red;}
</style>
<div class="pageContainer">
    <div class="pageBox">
        <h2 class="pageHead">Wallpaper</h2>
        <div class="contentHolder">
			@if (session()->has('message'))
				<div class="alert alert-info">
					{{ session('message') }}
				</div>
			@endif
			
			<div class="ImageUploadForm">
				 
				<form action="{{ route('store') }}" method="post" name="frmAdd" id="frmAdd" class="form-horizontal" enctype="multipart/form-data">
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
						
							<!--<span class="tooltip">Error</span>-->
						</div>
						<!--<div class="rowInput" ><input style="border:none;" type="file" name="phoneWallpaper" id="phoneWallpaper"></div>-->
						
					</div>
					
					<div class="pageRow">
						<div class="RowLabel"><label for="">Tablet Wallpaper</label></div>
						<div class="rowInput">
						<div class="browse"  style="padding-left:0px;">
						<label class="button file" for="tabletWallpaper">Browse</label>
							<input style="display:block; border:none; padding:0px; visibility:hidden; height:1px;" type="file" name="tabletWallpaper" id="tabletWallpaper">
							
						</div>	
						</div>
						
							
						
						<!--<div class="rowInput"><input style="border:none;" type="file" name="tabletWallpaper" id="tabletWallpaper"></div>-->
					
					</div>
				</div>
           
				<div class="imageFormSubmitRow">
					<input type="submit" class="button uploadImage" value="Submit" />
				</div>
				</form>
				
				<div id="imgContainer"></div><div id="imgContainer1"></div>
				
				<div class="UploadedImageTable">
					<div class="tableRow">
						<table class="data-table mdl-data-table dataTable wallpapersTable table table-bordered" cellspacing="0" width="100%" role="grid" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Order No</th>
									<th>Wallpaper Name</th>
									<th>Reorder</th>
									<th>Phone Wallpaper</th>
									<th>Tablet Wallpaper</th>
									<th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
					</div>
				</div>
				
				<!--<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
				<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>-->
				
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
										<!--<input type="file" name="phoneWallpaper" id="phoneWallpaper1" >-->
										
											<div class="browseimg">
									<input type="file" name="phoneWallpaper" id="phoneWallpaper1">
									<label class="button file-upld" for="tabletWallpaper"><img src="{{asset('images/uplod.png')}}"></label>
									</div>
										
									</div>
								</div>
								
								<div class="form-group">
									<label class="col-sm-12 control-label">Tablet Wallpaper</label>
									<div class="col-sm-12">
										<img src="" id="tabletWallpaper11" width="78px" height="78px" />
										<!--<input type="file" name="tabletWallpaper" id="tabletWallpaper1">-->
										
										
											
							
					
						<div class="browseimg">
							<input type="file" name="tabletWallpaper" id="tabletWallpaper1">
							<label class="button file-upld" for="tabletWallpaper"><img src="{{asset('images/uplod.png')}}"></label>
						</div>
										
										
										
									</div>
								</div>
								
								<div class="col-sm-offset-2 col-sm-10">
									<button type="submit" class="" id="btn-save">Save changes
								</button>
								</div>
							</form>
						</div>
						<div class="modal-footer">
						</div>
					</div>
				</div>
			</div>
			<!-- end bootstrap model -->
			
			
			<script type="text/javascript">
			$(document).ready( function () {
				/*$("#phoneWallpaper11").click(function(){
					$("#phoneWallpaper1").click();
				});*/
				
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
			
			function deleteFunc(id){
				if (confirm("Do you want to delete wallpaper?") == true) {
					var id = id; //alert(id);
					// ajax
					$.ajax({
						type:"POST",
						url: "{{ url('delete-wallpaper') }}",
						data: { id: id },
						dataType: 'json',
						success: function(res){
							var oTable = $('.data-table').dataTable();
							oTable.fnDraw(false);
						}
					});
				}
			}
			
			$('#frmAdd1').submit(function(e) {
				e.preventDefault();
				var formData = new FormData(this);
				$.ajax({
					type:'POST',
					url: "{{ url('store-wallpaper')}}",
					data: formData,
					cache:false,
					contentType: false,
					processData: false,
					success: (data) => {
						$("#wallpaper-modal").modal('hide');
						var oTable = $('.data-table').dataTable();
						oTable.fnDraw(false);
						$("#btn-save").html('Submit');
						$("#btn-save"). attr("disabled", false);
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

						
						$('.data-table').DataTable({
							order: [ 0, 'desc' ],
							pageLength: 3,
							processing: true,
							serverSide: true,
							ajax: "{{ route('list') }}",
							columns: [
								{ "data": "id" },
								{ "data": "wallpaperName" },
								
								
								{ "data": "reorder","name": "reorder","orderable": false, "searchable": false},
								
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
								
								
								{ "data": "action","name": "action","orderable": false, "searchable": false},
							],
						});
						
						 
    
   
						
					});
				</script>
			
        </div>
    </div>
</div>
@endsection
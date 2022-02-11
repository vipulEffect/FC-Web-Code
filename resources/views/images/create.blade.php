@extends('layouts.innerapp')

@section('content')

<style>
.error{color:red;}
#result {
	border: 1px solid #888;
	background: #f7f7f7;
	padding: 1em;
	margin-bottom: 1em;
	display:none;
}
.browseimg .error{float: left;
margin-left: 65px;
position: absolute;
top: 0;}
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
			<div id='loadingmessage' style='display:none; height: 25%;position: absolute; width: 100%; text-align:center; padding-top:6%; z-index:9999; '>
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
								<span style="margin-left:20px;" id="selectedPhoneWallpaperName"></span>
								<input style="display:block; border:none; padding:0px; visibility:hidden; height:1px;" type="file" name="phoneWallpaper" id="phoneWallpaper">
							</div>
						</div>
					</div>
					
					<div class="pageRow">
						<div class="RowLabel"><label for="">Tablet Wallpaper</label></div>
						<div class="rowInput">
							<div class="browse"  style="padding-left:0px;">
								<label class="button file" for="tabletWallpaper">Browse</label>
								<span style="margin-left:20px;" id="selectedTabletWallpaperName"></span>
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
									<th>Phone Wallpaper</th>
									<th>Tablet Wallpaper</th>
									<th>Status</th>
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
							<div id='loadingmessage1' style='display:none; height: 100%;position: absolute; width: 100%; text-align:center; padding-top:15%; z-index:9999; '>
								<img style="width:120px;" src="{{ asset('images/download.gif') }}" alt="">
							</div>
							
							<form action="" id="frmAdd1" name="frmAdd1" class="form-horizontal" method="POST" enctype="multipart/form-data">
								{{ csrf_field() }}
								<input type="hidden" name="id" id="id">
								<div class="form-group">
									<label for="wallpaperName1" class="col-sm-12 control-label">Wallpaper Name</label>
									<div class="col-sm-12">
										<input type="text" class="form-control" id="wallpaperName1" name="wallpaperName1">
									</div>
								</div> 
								
								<div class="form-group">
									<label for="" class="col-sm-12 control-label">Phone Wallpaper</label>
									<div class="col-sm-12">
										<img src="" id="phoneWallpaper11" width="78px" height="78px" />
										<!--<div class="browseimg">
											<input type="file" name="phoneWallpaper1" id="phoneWallpaper1">
											<label class="button file-upld button file" for="phoneWallpaper1"><img src="{{asset('images/uplod.png')}}"></label>
										</div>-->
										
										<div class="browseimg" >
										<label class="button file-upld button file" for="phoneWallpaper1"><img src="{{asset('images/uplod.png')}}"></label>
											<!--<label class="button file" for="phoneWallpaper1">Browse</label>-->
											<input style="display:block; border:none; padding:0px; visibility:hidden; height:1px;" type="file" name="phoneWallpaper1" id="phoneWallpaper1">
										</div>
									</div>
									
								</div>
								
								<div class="form-group">
									<label for="" class="col-sm-12 control-label">Tablet Wallpaper</label>
									<div class="col-sm-12">
										<img src="" id="tabletWallpaper11" width="78px" height="78px" />
										<div class="browseimg">
											<!--<label class="button file" for="tabletWallpaper1">Browse</label>-->
											<label class="button file-upld button file" for="tabletWallpaper1"><img src="{{asset('images/uplod.png')}}"></label>
											<input style="display:block; border:none; padding:0px; visibility:hidden; height:1px;" type="file" name="tabletWallpaper1" id="tabletWallpaper1">
										</div>
									</div>
								</div>
								
								<div class="col-sm-offset-2 col-sm-10">
									<input type="submit" id="btn-save" value="Save changes" />
								</div>
							</form>
						</div>
						<div class="modal-footer">
						</div>
						<div id="imgContainer11"></div><div id="imgContainer111"></div>
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
						
						if(res.phoneFilename != "" || res.phoneFilename !== null){
							var phoneImg11 = "https://fractalchaos.s3.ap-south-1.amazonaws.com/phoneWallpaper/"+res.phoneFilename;
						} else {
							var phoneImg11 = "";
						}
						$("#phoneWallpaper11").attr("src",phoneImg11);
						
						if(res.tabletFilename != "" || res.tabletFilename !== null){
							var tabletImg11 = "https://fractalchaos.s3.ap-south-1.amazonaws.com/tabletWallpaper/"+res.tabletFilename;
						} else {
							var tabletImg11 = ""
						}
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
			
			
			$(document).ready(function() {
				<!-- jQuery Form Validation code -->
				$.validator.addMethod('minImageWidth', function(value, element, minWidth) {
					var imageWidth = $(element).data('imageWidth')
					//alert('imageWidth@@@@='+imageWidth);
					if (typeof imageWidth === "undefined") { 
						return true;
					} else { //alert('elseee');
						return ($(element).data('imageWidth') || 0) >= minWidth;
					}
				}, function(minWidth, element) {
					var imageWidth = $(element).data('imageWidth'); 
					//alert('imageWidth='+imageWidth);
					if (typeof imageWidth === "undefined") { // alert('undefined');
						return true;
					} else { //alert('elseee');
						return (imageWidth)
						? ("Your image's width must be greater than or equal to " + minWidth + "px")
						: "Selected file is not an image.";
					}	
				});
				
				$.validator.addMethod('minImageHeight', function(value, element, minHeight) {
					var imageHeight = $(element).data('imageHeight')
					//alert('imageHeight@@@@='+imageHeight);
					if (typeof imageHeight === "undefined") { 
						return true;
					} else { //alert('elseee');
						return ($(element).data('imageHeight') || 0 ) >= minHeight;
					}
					
				}, function(minHeight, element) { //alert('######');
					var imageHeight = $(element).data('imageHeight');
					//alert('imageHeight='+imageHeight);
					if (typeof imageHeight === "undefined") { // alert('undefined');
						return true;
					} else { //alert('elseee');
						return (imageHeight)
						? ("Your image's height must be greater than or equal to " + minHeight + "px")
						: "Selected file is not an image.";
					}
				});
				
				var validator1 = $('#frmAdd1').validate({
					rules: {
						wallpaperName1: {required: true},  
						phoneWallpaper1: {
							required: false,
							extension: "jpg,jpeg,png",
							//maxsize: 10485760,
							//minImageWidth: 1080,
							//minImageHeight: 1920
						},
						tabletWallpaper1: {
							required: false,
							extension: "jpg,jpeg,png",
							//maxsize: 10485760,
							//minImageWidth: 1024,
							//minImageHeight: 720
						}
					},
					messages: {
						wallpaperName1: {required: "Enter Wallpaper Name." },  
						phoneWallpaper1: { 
							extension: "Please use jpg,jpeg,png extension", 
							//maxsize: "File size must not exceed 10 MB"
						},
						tabletWallpaper1: { 
							extension: "Please use jpg,jpeg,png extension", 
							//maxsize: "File size must not exceed 10 MB"
						},
					},
					submitHandler: function(form){
						var formData = new FormData(form);
						//alert(formData);
						$.ajax({
							type:'POST',
							url: "{{ url('store-wallpaper')}}",
							data: formData,
							datatype: 'json',
							contentType: false,
							processData:false,
							beforeSend: function(){
								// Show image container
								$("#loadingmessage1").show();
							},
							success: function(data){ //alert('==='+data);
								//Hide image container
								$("#loadingmessage1").hide();
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
								//console.log(data);
								//console.log('error='+data.message);
								$("#loadingmessage1").hide();//Hide image container
								alert("Something is wrong with image.Please try again later");
								location.reload();
							}
						})
					}
				});
			
				<!-- jQuery Form Validation code -->
				var validator = $('#frmAdd').validate({
					rules: {
						wallpaperName: {required: true},  
						phoneWallpaper: {
							required: true,
							extension: "jpg,jpeg,png",
							//maxsize: 10485760,
							//minImageWidth: 1080,
							//minImageHeight: 1920
						},
						tabletWallpaper: {
							required: true,
							extension: "jpg,jpeg,png",
							//maxsize: 10485760,
							//minImageWidth: 1024,
							//minImageHeight: 720
						}
					},
					messages: {
						wallpaperName: {required: "Enter Wallpaper Name" },  
						phoneWallpaper: { 
							required: "Please upload Phone Wallpaper" , //Min. size of Phone Wallpaper is 1080*1920px
							extension: "Please use jpg,jpeg,png extension", 
							//maxsize: "File size must not exceed 10 MB"
						},
						tabletWallpaper: { 
							required: "Please upload Tablet Wallpaper", //Min. size of Tablet Wallpaper is 1024*720px
							extension: "Please use jpg,jpeg,png extension", 
							//maxsize: "File size must not exceed 10 MB"
						},
					},
					submitHandler: function(form1){
						var $form = $(this);
						var formData1 = new FormData(form1);//alert(formData);
						$.ajax({
							url : "{{ url('store-wallpaper')}}",
							type: 'post',
							datatype: 'json',
							data : formData1,
							contentType: false,
							processData:false,
							beforeSend: function(){
								if ($form.valid){ $("#loadingmessage").show();}//Show image container
							},
							success: function(data) { //alert('success='+data.msg);
								$("#loadingmessage").hide();//Hide image container
								$("#results").css("display","block"); 
								$("#results").html(data.msg); 
								$('.data-table').DataTable().ajax.reload();
								location.reload();
								setTimeout(function(){ 
									$("#results").css("display","none"); 
									$("#results").html(""); 
								}, 4000);
							},
							error: function(data){
								//console.log('error='+data.message);
								$("#loadingmessage").hide();//Hide image container
								alert("Something is wrong with image.Please try again later");
								location.reload();
							}
						})
					}
				});
						
				var $submitBtn = $('#frmAdd').find('input:submit');
				var	$photoInput = $('#phoneWallpaper');
				var $imgContainer = $('#imgContainer');
				//var phoneImageWidth = 1080;
				//var phoneImageHeight = 1920;
				$('#phoneWallpaper').change(function() {
					$photoInput.removeData('imageWidth');
					$photoInput.removeData('imageHeight');
					$imgContainer.hide().empty();

					var file = this.files[0];
					
					if (file.type.match(/image\/.*/)) {
					
						$("#selectedPhoneWallpaperName").text(file.name);
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
								/*if (imageWidth <= phoneImageWidth && imageHeight <= phoneImageHeight) {
									$imgContainer.hide();
								} else {*/
									$img.css({ width: '400px', height: '200px' });
									$img.css('display', 'none');
								//}
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
				var tabletImageWidth = 1024;
				var tabletImageHeight = 720;
				$('#tabletWallpaper').change(function() {
					$tabletInput.removeData('imageWidth');
					$tabletInput.removeData('imageHeight');
					$imgContainer1.hide().empty();
					
					var file = this.files[0];
					
					if (file.type.match(/image\/.*/)) { 
					
						$("#selectedTabletWallpaperName").text(file.name);
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
								/*if (imageWidth <= tabletImageWidth && imageHeight <= tabletImageHeight) { 
									//alert('ifff');
									$imgContainer1.hide();
								} else { 
									//alert('elseee');
									*/
									$img.css({ width: '400px', height: '200px' });
									$img.css('display', 'none');
								//}
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
				//var phoneImageWidth = 1080;
				//var phoneImageHeight = 1920;
				//$('#phoneWallpaper1').change(function() {alert('==');
				$('body').on('change', '#phoneWallpaper1', function(){ //alert('photo');
					$photoInput1.removeData('imageWidth');
					$photoInput1.removeData('imageHeight');
					$imgContainer11.hide().empty();

					var file = this.files[0];
					if (file.type.match(/image\/.*/ )) {
						
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
								/*if (imageWidth <= phoneImageWidth && imageHeight <= phoneImageHeight) {
									$imgContainer11.hide();
								} else {*/
									$img.css({ width: '400px', height: '200px' });
									$img.css('display', 'none');
								//}
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
				//var tabletImageWidth = 1024;
				//var tabletImageHeight = 720;
				//$('#tabletWallpaper1').change(function() {
				$('body').on('change', '#tabletWallpaper1', function(){ //alert('ciccc');
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
								$tabletInput1.data('imageWidth', imageWidth);
								$tabletInput1.data('imageHeight', imageHeight);
								/*if (imageWidth <= tabletImageWidth && imageHeight <= tabletImageHeight) {
									$imgContainer111.hide();
								} else {*/
									$img.css({ width: '400px', height: '200px' });
									$img.css('display', 'none');
								//}
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
						{ 
							"data": "prefType",
							"render": function(data, type, row) {
								if(row.prefType == 'Future'){
									var prefType = "Upcoming Wallpaper";
								} 
								else if(row.prefType == 'Past'){
									var prefType = "Previous Wallpaper";
								} else {
									var prefType = '';
								}
								return prefType;
							}
						},
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
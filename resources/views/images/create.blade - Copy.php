@extends('layouts.innerapp')

@section('content')
<div class="pageContainer">
    <div class="pageBox">
        <h2 class="pageHead">Wallpaper</h2>
        <div class="contentHolder">
			<div class="ImageUploadForm">
				 
				<form action="/fractalchaos/wallpaperlisting" method="post" class="form-horizontal" enctype="multipart/form-data">
				{{ csrf_field() }}
				<div class="form50">
					<div class="pageRow">
						<div class="RowLabel"><label for="">Wallpaper Name</label></div>
						<div class="rowInput">
							<input type="text" name="wallpaperName" />
							<span class="tooltip">Error</span>
						</div>
					</div>
					<div class="pageRow">
						<div class="RowLabel"><label for="">Wallpaper Date</label></div>
						<div class="rowInput">
							<input type="text" name="wallpaperDate" />
							<span class="tooltip">Error</span>
						</div>
					</div>
					
					<div class="pageRow">
						<div class="RowLabel"><label for="">Phone Wallpaper</label></div>
						<div class="rowInput">
							<input type="text" />
							<span class="tooltip">Error</span>
						</div>
						<div class="browse">
							<input type="file" name="image" id="iPhone">
							<label class="button file" for="iPhone">Browse</label>
						</div>
					</div>
					
					<div class="pageRow">
						<div class="RowLabel"><label for="">Android Tablet</label></div>
						<div class="rowInput">
							<input type="text" />
							<span class="tooltip">Error</span>
						</div>
						<div class="browse">
							<input type="file" name="image1" id="iPad">
							<label class="button file" for="iPad">Browse</label>
						</div>
					</div>
				</div>
           
				<div class="imageFormSubmitRow">
					<input type="submit" class="button uploadImage" value="Submit" />
				</div>
				</form>
				
				<div class="UploadedImageTable">
					<div class="tableControlRow">
						<div class="rowBreakOne">
							<p>
							<span>Show</span>
							<select>
								<option value="">10</option>
							</select>
							<span>entries</span>
							</p>
						</div>
						<div class="rowBreakTwo">
							<div class="rbtinput">
								<i class="fa fa-calendar"></i>
								<input type="text" class="filterFrom" />
							</div>
							<div class="rbtinput">
								<i class="fa fa-calendar"></i>
								<input type="text" class="filterTo" />
							</div>
							<a href="" class="button FilterTable">Filter</a>
						</div>
						<div class="rowBreakThree">
							<label for="tableSearch">Search:
							<input type="text" placeholder="Search" id="tableSearch"/></label>
						</div>
					</div>
					
					
					<div class="tableRow">
						<table class="wallpapersTable table table-bordered">
							<thead>
							<tr>
								<th>Wallpaper Name</th>
								<th>Created Date</th>
								<th>Android Phone</th>
								<th>Android Tablet</th>
								<th>Action</th>
							</tr>
							</thead>
							
							<tbody>
							@foreach ($images as $image)
							<tr>
								<td>{{ $image->wallpaperName }}</td>
								<td>{{ $image->wallpaperDate }}</td>
								<td class="wallpaperThumb">
									<!--<div class="thumbnailBox">
										<div class="thumbnailprev">
											<a class="removeImage" href=""><i class="fa fa-times-circle" aria-hidden="true"></i></a>
											<img src="images/Thumbnail.png" alt="" />
										</div>
									</div>
									<input class="tableUpload" type="file" id="ColNoRowNo" />
									<label style="display: none" class="file button uploadImg" for="ColNoRowNo">Upload</label>-->
									<a href="{{ $image->phoneUrl }}" target="_blank">Click to view</a>
								</td>
								<td class="wallpaperThumb">
									<!--<div class="thumbnailBox" style="display: none">
										<div class="thumbnailprev">
											<a class="removeImage" href=""><i class="fa fa-times-circle" aria-hidden="true"></i></a>
											<img src="images/Thumbnail.png" alt="" />
										</div>
									</div>
									<input class="tableUpload" type="file" id="ColNoRowNo" />
									<label class="file button uploadImg" for="ColNoRowNo"
									>Upload</label>-->
									<a href="{{ $image->tabletUrl }}" target="_blank">Click to view</a>
								</td>
								<td class="actionTd">
									<a href="" class="editLine"><i class="fa fa-edit"></i></a>
									<a href="" class="trash"><i class="fa fa-trash-o"></i></a>
								</td>
							</tr>
							@endforeach
							</tbody>
						</table>
					</div>
					
					<div class="tablePaginationRow">
						<div class="paginationleft">
						  <p>
							Showing <span>1</span> to <span>10</span> of
							<span>65</span> entries
						  </p>
						</div>
						<div class="paginationRight">
							<ul class="paginationUl">
								<!--use      buttonDisabled     Class to show disabled-->
								<li class="paginationPrevious"><span>Previous</span></li>
								<li class="active"><a href="">1</a></li>
								<li><a href="">2</a></li>
								<li><a href="">3</a></li>
								<li><a href="">4</a></li>
								<li><a href="">5</a></li>
								<li><a href="">6</a></li>
								<li><a href="">7</a></li>
								<li class="paginationNext"><a href="">Next</a></li>
							</ul>
						</div>
					</div>
				</div>
				
				<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
				<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
			</div>
        </div>
    </div>
</div>
@endsection


<?php
namespace App\Http\Controllers;
use DB;
use App\Image;
use App\Wallpapertype;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Redirect;

class ImageController extends Controller
{
	//privacy policy page
	public function privacypolicy()
    {
		return view('images.privacypolicy');
	}
    //wallpaper listing
	public function index(Request $request)
    { 	
		if ($request->ajax()) { 
			$data = Image::select('id','prefOrder','wallpaperName','prefType','phoneFilename','tabletFilename');
			return Datatables::of($data)
			->addIndexColumn()
			->addColumn('action', function($row){ 
				$btn = '<a href="javascript:void(0)" class="editLine" data-toggle="tooltip" onClick="editFunc('.$row->id.')"><i class="fa fa-edit"></i></a>';
				
				/*if($row->prefType == "" || $row->prefType == "Null"){
					$btn .= '<a href="javascript:void(0)" class="trash" id="delete-compnay" onClick="deleteFunc('.$row->id.','.$row->prefOrder.')" data-toggle="tooltip" data-original-title="Delete"><i class="fa fa-trash-o"></i></a>';
				}*/
				
				return $btn;
			})
			->rawColumns(['action'])
			->make(true);
		}
        return view('images.create');
	}
	
	//wallpaper Save or update
	public function store(Request $request)
    {  
		$wallpaperId = $request->id;
		if(!empty($wallpaperId)){ //edit case
			$where = array('id' => $wallpaperId);
			$wallpaper  = Image::where($where)->first();
			//echo "<pre>wallpaper=";print_r($wallpaper->phoneFilename);die;
			if ($request->hasFile('phoneWallpaper1')){ //die('Yes');
				$path = $request->file('phoneWallpaper1')->store('phoneWallpaper', 's3');
				Storage::disk('s3')->setVisibility($path, 'public');
				$phoneFilename = basename($path);
				$phoneUrl = Storage::disk('s3')->url($path);
			} else {//die('No');
				$phoneFilename = $wallpaper->phoneFilename;
				$phoneUrl = $wallpaper->phoneUrl;
			}
			
			if ($request->hasFile('tabletWallpaper1')){
				$path1 = $request->file('tabletWallpaper1')->store('tabletWallpaper', 's3');
				Storage::disk('s3')->setVisibility($path1, 'public');
				$tabletFilename = basename($path1);
				$tabletUrl = Storage::disk('s3')->url($path1);
			} else {
				$tabletFilename = $wallpaper->tabletFilename;
				$tabletUrl = $wallpaper->tabletUrl;
			}
			
			if ($request->wallpaperName1 != ""){
				$wallpaperName = $request->wallpaperName1;
			} else {
				$wallpaperName = $wallpaper->wallpaperName;
			}
			$wallpaper = Image::updateOrCreate(
			['id' => $wallpaperId],
			[
			'wallpaperName' => $wallpaperName,
			'wallpaperDate' => date('Y-m-d H:i:s'),
			'phoneFilename' => $phoneFilename,
			'phoneUrl' => $phoneUrl,
			'tabletFilename' => $tabletFilename,
			'tabletUrl' => $tabletUrl
			]);  
			if($wallpaper){ 
				$arr = array('msg' => 'Wallpaper is successfully updated', 'status' => true);
			} else {
				$arr = array('msg' => 'Something goes to wrong. Please try again later', 'status' => false);
			}
			return Response()->json($arr);
		} 
		else { #die('add-case');
			$path = $request->file('phoneWallpaper')->store('phoneWallpaper', 's3');
			Storage::disk('s3')->setVisibility($path, 'public');
			
			$path1 = $request->file('tabletWallpaper')->store('tabletWallpaper', 's3');
			Storage::disk('s3')->setVisibility($path1, 'public');
			$check = Image::create([
				'wallpaperName' =>$request->wallpaperName,
				'wallpaperDate' =>date('Y-m-d H:i:s'),
				'phoneFilename' => basename($path),
				'phoneUrl' => Storage::disk('s3')->url($path),
				'tabletFilename' => basename($path1),
				'tabletUrl' => Storage::disk('s3')->url($path1)
			]);
			$lastInsertedId = $check->id; //get last inserted record id
			#echo "lastInsertedId=".$lastInsertedId;
			
			if($lastInsertedId){
				//Using query builder
				#$countRec = Image::count();#echo "countRec=".$countRec;die;
				$countRec = Image::orderBy('id', 'desc')->get();
				#echo "<pre>countRec=";print_r($countRec);die;
				if(!empty($countRec) && (count($countRec) >= 1 && count($countRec) <= 3) ){
				#if($countRec >= 1 && $countRec <= 3){//if wallpaper count is (1 to 3)in db	
					//if wallpaper count is (1 to 3)in db
					$prefType = "Future";
					//update prefType
					Image::updateOrCreate(['id' => $lastInsertedId],['prefType' => $prefType]);  
					
					//fetch wallpapertypes table record for latest week
					$data_1stweek = Wallpapertype::orderBy('week_num', 'desc')->first();
					#echo "<pre>data_1stweek=";print_r($data_1stweek);die;
					if(!empty($data_1stweek)){//die('iff');
						//echo $data_1stweek['upcoming_wallpaper_ids'];
						$updVal = $data_1stweek['upcoming_wallpaper_ids'].",".$lastInsertedId;
						//update
						Wallpapertype::where('week_num', $data_1stweek['week_num']) //For 1st week
						->update(['upcoming_wallpaper_ids' => $updVal]);
					} else {#die('elsee'); 
						//insert
						Wallpapertype::create(['week_num' =>1,'upcoming_wallpaper_ids' => $lastInsertedId
						]);
					}
				} 
				//update prefOrder order
				$totRecord = count($countRec);
				Image::updateOrCreate(['id' => $lastInsertedId],['prefOrder' => $totRecord ]); 
			}
			$arr = array('msg' => 'Something goes to wrong. Please try again later', 'status' => false);
			if($check){ 
			$arr = array('msg' => 'Wallpaper is successfully uploaded', 'status' => true);
			}
			return Response()->json($arr);
		}
	}
	
	//wallpaper View
    public function show(Image $image)
    {
        return Storage::disk('s3')->response('images/' . $image->filename);
        //return $image->url;
    }
	
	/**
     * Show the form for editing the specified resource.
     *
     * @param  \App\image  $image
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {   
        $where = array('id' => $request->id);
        $wallpaper  = Image::where($where)->first();
		return Response()->json($wallpaper);
    }
      
    //Get and update larger prefOrder Db for order reset
	public function resetLargerPerforderValuesFromDb($wallpaperId,$prefOrder){
		#echo $wallpaperId.",".$prefOrder;#die;
		
		//Get Future pref order no 
		$futurePrefNoArr = DB::table('images')
		->select('id','prefOrder','prefType')
        ->where('prefType','=', 'Future')
		->orderBy('prefOrder', 'asc')
        ->get();
		#echo "<pre>futurePrefNoArr=";print_r($futurePrefNoArr);die;
		if(count($futurePrefNoArr)>0){ //die('iff');
			$futureSerialNoArr = array();
			foreach($futurePrefNoArr as $fKey=>$fVal){
				array_push($futureSerialNoArr,$fVal->prefOrder);
			}
			#echo "<pre>futureSerialNoArr=";print_r($futureSerialNoArr);#die;
		}
		
		//Get Past pref order no
		$pastPrefNoArr = DB::table('images')
		->select('id','prefOrder','prefType')
        ->where('prefType','=', 'Past')
		->orderBy('prefOrder', 'asc')
        ->get();
		if(count($pastPrefNoArr)>0){ //die('iff');
			$pastSerialNoArr = array();
			foreach($pastPrefNoArr as $pKey=>$pVal){
				array_push($pastSerialNoArr,$pVal->prefOrder);
			}
			#echo "<pre>pastSerialNoArr=";print_r($pastSerialNoArr);die;
		}
		
		#echo "<pre>futureSerialNoArr@@@=";print_r($futureSerialNoArr);
		#echo "<pre>pastSerialNoArr@@@=";print_r($pastSerialNoArr);
		
		//Get larger prefOrder Db records from delete entry
		$largerPerfOrder = Image::select('id','prefOrder','prefType')
					->where('prefOrder','>', $prefOrder)
					->orderBy('prefOrder', 'asc')
					->get();
		//echo count($largerPerfOrder);echo "<pre>largerPerfOrder=";print_r($largerPerfOrder);#die;
		if(count($largerPerfOrder)>=1){ //die('iff');
			foreach($largerPerfOrder as $largerKey=>$largerVal){
				//updated image perference order
				$resetPrefOrder = $largerVal->prefOrder - 1;
				Image::where('id', $largerVal->id)->update(['prefOrder' => $resetPrefOrder]);
			} //end foreach
		}
		
		//update image PrefType="" from all 
		$updArr = array('prefType'=>'') ;
		Image::where('prefType', 'Future')->orwhere('prefType','Past')->update($updArr);
		
		#echo "<pre>futureSerialNoArr=";print_r($futureSerialNoArr);
		#echo "<pre>pastSerialNoArr=";print_r($pastSerialNoArr);die;
		//update future prefernce with new values
		if(!empty($futureSerialNoArr)){
			foreach($futureSerialNoArr as $fSk=>$fSv){
				//check sequences are exist in table or not
				$isExist = "SELECT id,prefOrder FROM images where prefOrder='".$fSv."'";
				$isExistR = DB::select($isExist);
				//echo "<pre>isExistR=";print_r($isExistR);die;
				if(count($isExistR)>=1){
					//update wallpaper id with Future
					Image::where('id', $isExistR[0]->id)->update(['prefType'=>"Future"]);
				}
			}
		}
		
		//update past prefernce with new values
		if(!empty($pastSerialNoArr)){
			foreach($pastSerialNoArr as $pSk=>$pSv){
				//check sequences are exist in table or not
				$isExist1 = "SELECT id,prefOrder FROM images where prefOrder='".$pSv."'";
				$isExistR1 = DB::select($isExist1);
				//echo "<pre>isExistR1=";print_r($isExistR1);die;
				if(count($isExistR1)>=1){
					//update wallpaper id with Future
					Image::where('id', $isExistR1[0]->id)->update(['prefType'=>"Past"]);
				}
			}
		}
	}
	
	//delete wallpaperId from all records of wallpapertypes table
	public function delSelwallpaperIdFromAllRecordsTbl($wallpaperId){
		//from upcoming_wallpaper_ids
		$upcomingquery = DB::table('wallpapertypes')
		 ->select('id','upcoming_wallpaper_ids')
         ->whereRaw('FIND_IN_SET("'.$wallpaperId.'",upcoming_wallpaper_ids)')
         ->get();
		//echo "<pre>upcomingquery=";print_r($upcomingquery);//die;
		if(count($upcomingquery)>0){
			foreach($upcomingquery as $upKey=>$upVal){
				if( strpos($upVal->upcoming_wallpaper_ids, ',') !== false ) {//echo "comma Found";
					$futureArr = explode(",",$upVal->upcoming_wallpaper_ids);
					if(!empty($futureArr)){
						//echo "<pre>futureArr=";print_r($futureArr);
						if (($key = array_search($wallpaperId, $futureArr)) !== false) {
							unset($futureArr[$key]);
							$futureArr = array_values($futureArr);
							//echo "<pre>futureArr%%%=";print_r($futureArr);die;
							if(!empty($futureArr)){
								$futureArrStr = implode(",",$futureArr);
							} else {
								$futureArrStr = "";
							}
						} else { 
							$futureArrStr = "";
						}
					} else { 
						$futureArrStr = "";
					}
				} else { //echo "comma not Found";
					$futureArrStr = "";
				}
				
				if($futureArrStr != ""){
					//updated Wallpapertype upcoming_wallpaper_ids field
					Wallpapertype::where('id', $upVal->id)->update(['upcoming_wallpaper_ids' => $futureArrStr]);
				}
				
			} //end foreach
		} //end if
		
		//from past_wallpaper_ids
		$pastquery = DB::table('wallpapertypes')
		 ->select('id','past_wallpaper_ids')
         ->whereRaw('FIND_IN_SET("'.$wallpaperId.'",past_wallpaper_ids)')
         ->get();
		//echo "<pre>pastquery=";print_r($pastquery);//die;
		if(count($pastquery)>0){
			foreach($pastquery as $pastKey=>$pastVal){
				if( strpos($pastVal->past_wallpaper_ids, ',') !== false ) {//echo "comma Found";
					$pastArr = explode(",",$pastVal->past_wallpaper_ids);
					if(!empty($pastArr)){
						//echo "<pre>pastArr=";print_r($pastArr);
						if (($key1 = array_search($wallpaperId, $pastArr)) !== false) {
							unset($pastArr[$key1]);
							$pastArr = array_values($pastArr);
							//echo "<pre>pastArr%%%=";print_r($pastArr);die;
							if(!empty($pastArr)){
								$pastArrStr = implode(",",$pastArr);
							} else {
								$pastArrStr = "";
							}
						} else { 
							$pastArrStr = "";
						}
					} else { 
						$pastArrStr = "";
					}
				} else { //echo "comma not Found";
					$pastArrStr = "";
				}
				if($pastArrStr != ""){
					//updated Wallpapertype upcoming_wallpaper_ids field
					Wallpapertype::where('id', $pastVal->id)->update(['past_wallpaper_ids' => $pastArrStr]);
				}
			} //end foreach
		} //end if
	}
	
	/**
	 * reorderPrefTypeAfterDelete
	 *
	 */
	public function reorderPrefTypeAfterDelete()
	{   
		//update future and past list in wallpapertypes table for latest week
		$this->updWallpaperTypeForLatestWeek();
	}
	
	/**
     * Remove the specified resource from storage.
     *
     * @param  \App\image  $image
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
		//echo $request->id."====".$request->prefOrder;die;
		$wallpaper = Image::where('id',$request->id)->where('prefOrder',$request->prefOrder)->delete();
		//$wallpaper = 1;
		if($wallpaper){
			//Get and update larger prefOrder Db for order reset
			$this->resetLargerPerforderValuesFromDb($request->id,$request->prefOrder);
			
			//delete wallpaperId from all records of wallpapertypes table
			$this->delSelwallpaperIdFromAllRecordsTbl($request->id);
			
			//reorder PrefType After Delete any record
			$this->reorderPrefTypeAfterDelete();
		}
		return Response()->json($wallpaper);
    }
	
	/**
     * Show the form for reorder the specified sequence.
     *
     * @param  \App\image  $image
     * @return \Illuminate\Http\Response
     */
	public function getPastFuturePrefOrderDb(){
		$completeArr = array();
		$completeArr =  DB::table('images')
					->select('id','prefOrder','prefType')
					->where('prefType','=', 'Past')
					->orwhere('prefType','=', 'Future')
					->orderBy('prefOrder', 'asc')
					->get();
		//echo "<pre>completeArr=";print_r($completeArr);die;	
		if(count($completeArr)>0){
			return $completeArr;
		} else {
			$completeArr = array();
		}
	}
	
	//get PrefType WRT PrefOrder
	public function getPrefTypeWRTPrefOrder($prefOrder){
		//echo "prefOrder=".$prefOrder;
		$preffArr =  DB::table('images')
					->select('prefType')
					->where('prefOrder','=',$prefOrder )
					->first();
		//echo "<pre>preffArr=";print_r($preffArr);die;	
		if(!empty($preffArr)){
			return $preffArr->prefType;
		} else {
			return '';
		}
	}
	
	/**
     * update future and past list in wallpapertypes table for latest week
     *
     * @param  \App\image  $image
     * @return \Illuminate\Http\Response
     */
	public function updWallpaperTypeForLatestWeek(){
		$latestWeekInfo = DB::table('wallpapertypes')
		->select('id','week_num','upcoming_wallpaper_ids','past_wallpaper_ids')
        ->orderBy('id', 'desc')
		->first();
		//echo "<pre>latestWeekInfo=";print_r($latestWeekInfo);#die;
		if(!empty($latestWeekInfo)){
			$week_num = $latestWeekInfo->week_num;//die;
			//get past wallpaper info
			$pastwallpaperInfo = DB::table('images')
			->select('id','prefOrder','prefType')
			->where('prefType', 'Past')
			->orderBy('prefOrder', 'asc')
			->get();
			//echo "<pre>pastwallpaperInfo=";print_r($pastwallpaperInfo);//die;
			if(count($pastwallpaperInfo)>0){
				$pastArr = array();
				foreach($pastwallpaperInfo as $kk=>$vv){
					$pastArr[] = $vv->id;
				}
				if(!empty($pastArr)){
					$pastStr = implode(",",$pastArr);
				} else {
					$pastStr = "";
				}
			} else {
				$pastStr = "";
			}
			//echo "<pre>pastStr=";print_r($pastStr);#die;
			
			//get future wallpaper info
			$futurewallpaperInfo = DB::table('images')
			->select('id','prefOrder','prefType')
			->where('prefType', 'Future')
			->orderBy('prefOrder', 'asc')
			->get();
			//echo "<pre>futurewallpaperInfo=";print_r($futurewallpaperInfo);//die;
			if(count($futurewallpaperInfo)>0){
				$futueArr = array();
				foreach($futurewallpaperInfo as $kk1=>$vv1){
					$futueArr[] = $vv1->id;
				}
				if(!empty($futueArr)){
					$futureStr = implode(",",$futueArr);
				} else {
					$futureStr = "";
				}
			} else {
				$futureStr = "";
			}
			//echo "<pre>futureStr=";print_r($futureStr);die;
			//updated Wallpapertypefuture and past filed for lated week
			Wallpapertype::where('week_num', $week_num)
			->update(['past_wallpaper_ids' => $pastStr,'upcoming_wallpaper_ids' => $futureStr]);
		}
	}
	
	/**
     * Show the form for reorder the specified sequence.
     *
     * @param  \App\image  $image
     * @return \Illuminate\Http\Response
     */
	 
	/*public function getFutureSequenceNoWRTWeek($weekNum){
		$futureArr = array();
		////////////////////////
		////////////////////////
		//for future
		$futureTot = 3;
		$maxF = $weekNum*$futureTot;
		//Min Val of Current week = Max Val of Previous week
		$minF1 = ($weekNum-1)*$futureTot;
		$minF = $minF1+1;
		for($i=$minF;$i<=$maxF;$i++){
			array_push($futureArr,$i);
		}
		//echo "<pre>futureArr=";print_R($futureArr);
		return $futureArr;
	}


	public function getPastSequenceNoWRTWeek($weekNum){
		$pastArr = array();
		///////////////////////
		///////////////////////
		//For past- get future sequence of previous week
		if($weekNum == 1){
			$pastArr = array();
		} else {
			$previousWeek = $weekNum-1;
			$futureTot1 = 3;
			$maxP = $previousWeek*$futureTot1;
			//Min Val of Current week = Max Val of Previous week
			$minP1 = ($previousWeek-1)*$futureTot1;
			$minP = $minP1+1;
			for($j=$minP;$j<=$maxP;$j++){
				array_push($pastArr,$j);
			}
			array_shift($pastArr);
		}
		//echo "<pre>pastArr=";print_R($pastArr);die;
		return $pastArr;
	}*/
	
    //Get Latest Week 'week_num'
	public function getLatestWeekNum(){
		$latestWeekInfo = "SELECT id,week_num,upcoming_wallpaper_ids,past_wallpaper_ids FROM wallpapertypes order by id desc limit 1"; #echo $latestWeekInfo;
		$result = DB::select($latestWeekInfo);
		if (count($result)> 0) {
			#echo "<pre>result=";print_r($result);die;
			return $result;//$result[0]->week_num; //week
		} else {
			return array();
		}
	}
	
	/**
     * Show the form for reorder the specified sequence.
     *
     * @param  \App\image  $image
     * @return \Illuminate\Http\Response
     */
	public function reorderSequence(Request $request)
    {   
		//Get Future pref order no 
		$futurePrefNoArr = DB::table('images')
		->select('id','prefOrder','prefType')
        ->where('prefType','=', 'Future')
		->orderBy('prefOrder', 'asc')
        ->get();
		#echo "<pre>futurePrefNoArr=";print_r($futurePrefNoArr);die;
		if(count($futurePrefNoArr)>0){ //die('iff');
			$futureSerialNoArr = array();
			foreach($futurePrefNoArr as $fKey=>$fVal){
				array_push($futureSerialNoArr,$fVal->prefOrder);
			}
			#echo "<pre>futureSerialNoArr=";print_r($futureSerialNoArr);#die;
		}
		
		//Get Past pref order no
		$pastPrefNoArr = DB::table('images')
		->select('id','prefOrder','prefType')
        ->where('prefType','=', 'Past')
		->orderBy('prefOrder', 'asc')
        ->get();
		if(count($pastPrefNoArr)>0){ //die('iff');
			$pastSerialNoArr = array();
			foreach($pastPrefNoArr as $pKey=>$pVal){
				array_push($pastSerialNoArr,$pVal->prefOrder);
			}
			#echo "<pre>pastSerialNoArr=";print_r($pastSerialNoArr);die;
		}
		
		#echo "<pre>futureSerialNoArr@@@=";print_r($futureSerialNoArr);
		#echo "<pre>pastSerialNoArr@@@=";print_r($pastSerialNoArr);
		
		if(!empty($request->res) && count($request->res)>0){
			foreach($request->res as $key=>$val){
				//update image preference order
				Image::where('id', $val['wallpaperId'])->update(['prefOrder' => $val['NewPosition']]);
			}
			//update image PrefType="" from all 
			$updArr = array('prefType'=>'') ;
			Image::where('prefType', 'Future')->orwhere('prefType','Past')->update($updArr);
			
			#echo "<pre>futureSerialNoArr=";print_r($futureSerialNoArr);
			#echo "<pre>pastSerialNoArr=";print_r($pastSerialNoArr);die;
			//update future prefernce with new values
			if(!empty($futureSerialNoArr)){
				foreach($futureSerialNoArr as $fSk=>$fSv){
					//check sequences are exist in table or not
					$isExist = "SELECT id,prefOrder FROM images where prefOrder='".$fSv."'";
					$isExistR = DB::select($isExist);
					//echo "<pre>isExistR=";print_r($isExistR);die;
					if(count($isExistR)>=1){
						//update wallpaper id with Future
						Image::where('id', $isExistR[0]->id)->update(['prefType'=>"Future"]);
					}
				}
			}
			
			//update past prefernce with new values
			if(!empty($pastSerialNoArr)){
				foreach($pastSerialNoArr as $pSk=>$pSv){
					//check sequences are exist in table or not
					$isExist1 = "SELECT id,prefOrder FROM images where prefOrder='".$pSv."'";
					$isExistR1 = DB::select($isExist1);
					//echo "<pre>isExistR1=";print_r($isExistR1);die;
					if(count($isExistR1)>=1){
						//update wallpaper id with Future
						Image::where('id', $isExistR1[0]->id)->update(['prefType'=>"Past"]);
					}
				}
			}
				
			//update future and past list in wallpapertypes table for latest week
			$this->updWallpaperTypeForLatestWeek();
			return Response()->json();
		}
	}
}
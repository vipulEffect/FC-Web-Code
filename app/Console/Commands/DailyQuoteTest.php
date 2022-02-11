<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use DB;
use App\Image,App\Wallpapertype;
use Illuminate\Http\Request;

class DailyQuoteTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:dailyTest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron For Weekly wallpaper change at every 15 min.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }  

    /**
     * Execute the console command.
     *
     * @return int
     */
	
	//Get Latest Week info from wallpapertypes table
	public function getLatestWeekInfo(){
		$latestWeekInfo = "SELECT * FROM wallpapertypes order by id desc limit 1"; #echo $latestWeekInfo;
		$result = DB::select($latestWeekInfo);
		if (count($result)> 0) {
			$latestWeekArr = array();
			foreach($result as $kk=>$row) {
				$latestWeekArr['week_num'] = $row->week_num;
				$latestWeekArr['upcoming_wallpaper_ids'] = $row->upcoming_wallpaper_ids;
				$latestWeekArr['past_wallpaper_ids'] = $row->past_wallpaper_ids;
				$latestWeekArr['creation_date'] = $row->created_at;
			}
			return $latestWeekArr;
		} else {
			return array();
		}
	}
	
	//Get active wallpapers list from images table
	public function getActiveWallapersList(){
		$activeWallpaperInfo = "SELECT id FROM images order by prefOrder desc"; #echo $activeWallpaperInfo;
		$result = DB::select($activeWallpaperInfo);
		//echo "<pre>result=";print_R($result);die;
		if (count($result)> 0) {
			$activeWallListArr = array();
			foreach($result as $kk=>$row) {
				$activeWallListArr[] = $row->id; //wallpapers ids
			}
			return $activeWallListArr;
		} else {
			return array();
		}
	}
	
	//Get Future Week Lists Till Now from wallpapertypes table
	public function getFutureWeekListsTillNow(){
		$allWeekInfo = "SELECT upcoming_wallpaper_ids FROM wallpapertypes order by id";
		$result = DB::select($allWeekInfo);
		//echo "<pre>result=";print_R($result);die;
		if (count($result)> 0) {
			$allWeekArr = array();
			foreach($result as $kk=>$row) {
				$allWeekArr[] = $row->upcoming_wallpaper_ids; //wallpapers ids
			}
			#echo "<pre>allWeekArr=";print_r($allWeekArr);
			$allWeekArr = array_unique($allWeekArr);
			#echo "<pre>allWeekArr=";print_r($allWeekArr);die;
			$allWeekStr = implode(',', $allWeekArr);
			#echo "<pre>";print_r($allWeekStr);die;
			return $allWeekStr;
		} else {
			return array();
		}
	}
	
	//Is future order images are availble for next week or not from images table
	public function isNext3OrderImagesAvailableForFuture(){
		$allWeekInfo = "SELECT prefOrder FROM images where prefType='Future' order by prefOrder desc limit 1";
		$result = DB::select($allWeekInfo);
		#echo "<pre>result=";print_R($result);die;
		if (count($result)> 0) {
			$lastFutureOrder = $result[0]->prefOrder; //last future wallpaper order
			$uptoFutureOrder = $lastFutureOrder+3;
			
			//check this order image is present or not 
			$isExistNextOrder = "SELECT id,prefOrder FROM images where prefOrder='".$uptoFutureOrder."' ";
			$isExistNextResult = DB::select($isExistNextOrder);
			#echo "<pre>isExistNextResult=";print_R($isExistNextResult);die;
			if (count($isExistNextResult)> 0) {
				return 1; //images for future order is present
			} else {
				return 2; //images for future order is not present
			}
		} else {
			return 2; //images for future order is not present
		}
	}
	
	//get Next 3 Order Images For Future from images table
	public function getNext3OrderImagesForFuture(){
		//latest future order number
		$latestPerfOrder = "SELECT prefOrder FROM images where prefType='Future' order by prefOrder desc limit 1";
		$latestPerfOrderResult = DB::select($latestPerfOrder);
		#echo "<pre>latestPerfOrderResult=";print_R($latestPerfOrderResult);die;
		if (count($latestPerfOrderResult)> 0) {
			$lastFutureOrder = $latestPerfOrderResult[0]->prefOrder; //last future wallpaper order
			$uptoFutureOrder = $lastFutureOrder+3;
			
			$start = $lastFutureOrder+1;
			$last = $uptoFutureOrder;
			$futureWallpaperArr = array();
			for($i=$start;$i<=$last;$i++){
				#echo $i."<br/>";
				//check this order image is present or not 
				$isExistNextOrder = "SELECT id,prefOrder FROM images where prefOrder='".$i."' ";
				$isExistNextResult = DB::select($isExistNextOrder);
				#echo "<pre>isExistNextResult=";print_R($isExistNextResult);
				if (count($isExistNextResult)> 0) {
					$nextWallpapeperId = $isExistNextResult[0]->id;
					array_push($futureWallpaperArr,$nextWallpapeperId);
				} else {
					$futureWallpaperArr = array();
				}
			}
			#echo "<pre>futureWallpaperArr=";print_R($futureWallpaperArr);die;
		} else {
			$futureWallpaperArr = array();
		}
		return $futureWallpaperArr;
	}
	
	
	//Get future and past wallpaper list for next weeks
	public function getFuturePastWallListForNextWeeks($futureListTillNow,$future_list,$past_list,$activeWallapersList,$week_num){
		
		$exResult = array();
		//future list of all weeks till now
		if($futureListTillNow !=""){
			$future_list_arr_till_now = array_unique(explode(",",$futureListTillNow));
			$future_list_arr_till_now = array_values($future_list_arr_till_now);
		} else {
			$future_list_arr_till_now = array();
		}
		//future list of current week
		if($future_list !=""){
			$future_list_arr = array_unique(explode(",",$future_list));
			$future_list_arr = array_values($future_list_arr);
		} else {
			$future_list_arr = array();
		}
		
		if($past_list !=""){
			$past_list_arr = array_unique(explode(",",$past_list));
			$past_list_arr = array_values($past_list_arr);
		} else {
			$past_list_arr = array();
		}
		
		if(!empty($activeWallapersList)){
			$active_list_arrR = array_reverse($activeWallapersList);
		} else {
			$active_list_arrR = array();
		}
		
		
		/*echo "<pre>future_list_arr_till_now=";print_R($future_list_arr_till_now);
		echo "<pre>future_list_arr=";print_R($future_list_arr);
		echo "<pre>past_list_arr=";print_R($past_list_arr);
		echo "<pre>active_list_arrR=";print_R($active_list_arrR);
		echo "week_num=".$week_num;*/
		
		//Is future order images are availble for next week or not from images table
		$next3Orders = $this->isNext3OrderImagesAvailableForFuture();
		#echo "<pre>next3Orders=";print_R($next3Orders);#die;
		if($next3Orders == 2 ){ //images for future order is not present
			//Get Latest
			$exResult['future'] = implode(",",$future_list_arr);
			$exResult['futureArr'] = $future_list_arr;
			
			$exResult['past'] = implode(",",$past_list_arr);
			$exResult['pastArr'] = $past_list_arr;
		} else { //images for future order is present
			//get Next 3 Order Images For Future from images table
			$getNext = $this->getNext3OrderImagesForFuture();
			#echo "<pre>getNext=";print_R($getNext);die;
			if(!empty($getNext)){
				//Get Latest
				$exResult['future'] = implode(",",$getNext);
				$exResult['futureArr'] = $getNext;
				
				$exResult['past'] = implode(",",array_values(array_slice($future_list_arr, -2, 2, true)));
				$exResult['pastArr'] = array_values(array_slice($future_list_arr, -2, 2, true));
			} else {
				//Get Latest
				$exResult['future'] = implode(",",$future_list_arr);
				$exResult['futureArr'] = $future_list_arr;
				
				$exResult['past'] = implode(",",$past_list_arr);
				$exResult['pastArr'] = $past_list_arr;
			}
		}
		return $exResult;
	}
	
	//select those which r past and future status in images table
	//update them with blank status
	public function getWallpapersOFPastFutureStatus(){
		$lastBeforeWeekInfo = "SELECT id FROM images where prefType='Past' OR prefType='Future'";
		$result = DB::select($lastBeforeWeekInfo);
		if (count($result)> 0) {
			$latestWeekArr = array();
			foreach($result as $kk=>$row) {
				//updated Wallpapertype upcoming_wallpaper_ids field
				DB::table('images')->where('id',$row->id)->update(['prefType' => '', 'updated_at' => now()]);
			}
		} 
	}
	
    public function handle()
    {   #die('wallpaper-change-test-at-every-15min');
		//Get Latest Week info from wallpapertypes table
		$latestWeekInfo = $this->getLatestWeekInfo();
		#echo "<pre>latestWeekInfo=";print_R($latestWeekInfo);#die;
		if(!empty($latestWeekInfo)){
			//Get active wallpapers list from images table
			$activeWallapersList = $this->getActiveWallapersList();
			#echo "<pre>activeWallapersList=";print_R($activeWallapersList);die;
			if(!empty($activeWallapersList)){
				$startdate = strtotime("Monday");//Monday
				$currentDate = date("Y-m-d");
				$cronWeekDate = date("Y-m-d", $startdate);
				//echo $startdate."==".$currentDate."==".$cronWeekDate;
				/*if ( strtotime($currentDate) != strtotime($cronWeekDate) ) { 
					die('not-equal');
				} else { die('equal');*/
					//Get Future Week Lists Till Now from wallpapertypes table
					$futureListTillNow = $this->getFutureWeekListsTillNow();
					#echo "<pre>futureListTillNow=";print_R($futureListTillNow);#die;
					//Get future and past wallpaper list for next weeks
					$ListArr = $this->getFuturePastWallListForNextWeeks($futureListTillNow,$latestWeekInfo['upcoming_wallpaper_ids'],$latestWeekInfo['past_wallpaper_ids'],$activeWallapersList,$latestWeekInfo['week_num']);
					#echo "<pre>ListArr=";print_R($ListArr);die;
					//insert into wallpapertypes table
					if(!empty($ListArr)){
						$nextWeek = $latestWeekInfo['week_num']+1;
						$data = array(
							"week_num"=> $nextWeek,
							"upcoming_wallpaper_ids"=> $ListArr['future'],
							"past_wallpaper_ids"=> $ListArr['past'],
							"created_at" => now(),
							"updated_at" => now(),
						);
						$ins55 = DB::table('wallpapertypes')->insert($data);#die;
						if($ins55){
							//Update 'appusers' table field- 'user_weekly_wallpaper_change_access' 
							DB::table('appusers')->update(['user_weekly_wallpaper_change_access' => '1','isWallpaperSelected' => 0, 'updated_at' => now()]);
							
							//1-select those which r past and future status in images table
							//update them with blank status
							$this->getWallpapersOFPastFutureStatus();
							
							//2- update futureArr wallpaperId(5,6,7) with prefType="Future" for next week
							if(!empty($ListArr['futureArr'])){
								foreach($ListArr['futureArr'] as $futureKey=>$futureVal){
									DB::table('images')->where('id',$futureVal)->update(['prefType' => 'Future', 'updated_at' => now()]);
								}
							}
							
							//3- update pastArr wallpaperId(3,4) with prefType="Past" for next week
							if(!empty($ListArr['pastArr'])){
								foreach($ListArr['pastArr'] as $pastKey=>$pastVal){
									DB::table('images')->where('id',$pastVal)->update(['prefType' => 'Past', 'updated_at' => now()]);
								}
							}
						}
					}
				//}
			} else {
				echo "No active wallpaper exist in Db";
			}
		} else {
			echo "No future or past wallpaper is set for week in Db";
		} 
	}
}
<?php
/**
 * 获得系统年份数组
 */
function getSystemYearArr(){
	$year_arr = array('2009'=>'2009','2010'=>'2010','2011'=>'2011','2012'=>'2012','2014'=>'2014','2014'=>'2014','2015'=>'2015','2016'=>'2016','2017'=>'2017','2018'=>'2018','2019'=>'2019','2020'=>'2020');
	return $year_arr;
}
/**
 * 获得系统月份数组
 */
function getSystemMonthArr(){
	$month_arr = array('1'=>'01','2'=>'02','3'=>'03','4'=>'04','5'=>'05','6'=>'06','7'=>'07','8'=>'08','9'=>'09','10'=>'10','11'=>'11','12'=>'12');
	return $month_arr;
}
/**
 * 获得系统周数组
 */
function getSystemWeekArr(){
	$week_arr = array('1'=>'周一','2'=>'周二','3'=>'周三','4'=>'周四','5'=>'周五','6'=>'周六','7'=>'周日');
	return $week_arr;
}

/**
 * 获取某年的第一天
 */
function getYearFirstDay($year) {
	return gmstr2time($year.'-01-01 00:00:00');
}
/**
 * 获取某年的最后一天
 */
function getYearLastDay($year){
    return gmstr2time($year.'-12-31 23:59:59');
}

/**
 * 获取某月的第一天
 */
function getMonthFirstDay($year, $month) {
	return gmstr2time($year.'-'.$month.'-01 00:00:00');
}
/**
 * 获取某月的最后一天
 */
function getMonthLastDay($year, $month){
    return gmstr2time($year.'-'.$month.'-01'." +1 month") - 1;
}
/**
 * 获得系统某月的周数组，第一周不足的需要补足
 */
function getMonthWeekArr($year, $month){
	
	//该月第一天
	$firstday = gmstr2time("{$year}-{$month}-01 00:00:00");
	
	//该月的第一周有几天
	$firstweekday = (7 - local_date('N', $firstday) + 1);
	
	//计算该月第一个周一的时间
	$starttime = $firstday - 3600 * 24 * (7 - $firstweekday);
	
	//该月的最后一天
	$lastday = getMonthLastDay($year, $month);
	
	//该月的最后一周有几天
	$lastweekday = local_date('N', $lastday);
	
	//该月的最后一个周末的时间
	$endtime = $lastday - 3600 * 24 * $lastweekday;
	
	//每周时间长度
	$step = 3600 * 24 * 7;
	$week_arr = array();
	for ($i = $starttime; $i < $endtime; $i = $i + $step){
		$week_arr[] = array(
			'key'=>local_date('Y-m-d',$i).'|'.local_date('Y-m-d',$i+$step-1), 
			'val'=>local_date('Y-m-d',$i).'~'.local_date('Y-m-d',$i+$step-1)
		);
	}
	
	return $week_arr;
}
/**
 * 获取本周的开始时间和结束时间
 */
function getWeek_SdateAndEdate($current_time){
    $current_time = gmstr2time(local_date('Y-m-d',$current_time));
	//local_date('N', $current_time); // 1（表示星期一）到 7（表示星期天）
	$return_arr['sdate'] = local_date('Y-m-d', $current_time - 86400 * (local_date('N', $current_time) - 1));
	$return_arr['edate'] = local_date('Y-m-d', $current_time + 86400 * (7 - local_date('N', $current_time) + 1) - 1);
	return $return_arr;
}

/**
  * 查询每月的周数组
*/
function getweekofmonth()
{
   $year = $_GET['y'];
   $month = $_GET['m'];
   if(!$year || !$month)
   {
	  $this->json_error('error');
	  return;
   }
   $week_arr = getMonthWeekArr($year, $month);
   $this->json_result($week_arr);
}
	
	// 格式化时间
function formatTime($search_arr = array())
{
 	//天
  	if(!$search_arr['search_time'])
	{
  		$search_arr['search_time'] = local_date('Y-m-d', gmtime());
   	}
    $search_arr['day']['search_time'] = gmstr2time($search_arr['search_time']);//搜索的时间

	// 年
   	if(!$search_arr['searchweek_year'])
	{
  		$search_arr['searchweek_year'] = local_date('Y', gmtime());
   	} 
	// 月
   	if(!$search_arr['searchweek_month'])
	{
   		$search_arr['searchweek_month'] = local_date('m', gmtime());
  	}
	// 周
	if(!$search_arr['searchweek_week'])
	{
   		$searchweek_weekarr = getWeek_SdateAndEdate(gmtime());
       	$search_arr['searchweek_week'] = implode('|', $searchweek_weekarr);
   		$searchweek_week_edate_m = local_date('m', gmstr2time($searchweek_weekarr['edate']));
     	if($searchweek_week_edate_m <> $search_arr['searchweek_month'])
		{
     		$search_arr['searchweek_month'] = $searchweek_week_edate_m;
   		}
  	}
  	$weekcurrent_year = $search_arr['searchweek_year'];
   	$weekcurrent_month = $search_arr['searchweek_month'];
  	$weekcurrent_week = $search_arr['searchweek_week'];
    $search_arr['week']['current_year'] = $weekcurrent_year;
   	$search_arr['week']['current_month'] = $weekcurrent_month;
    $search_arr['week']['current_week'] = $weekcurrent_week;

   	// 年
 	if(!$search_arr['searchmonth_year'])
	{
     	 $search_arr['searchmonth_year'] = local_date('Y', gmtime());
  	}
	// 月
  	if(!$search_arr['searchmonth_month'])
	{
      $search_arr['searchmonth_month'] = local_date('m', gmtime());
    }
   	$monthcurrent_year = $search_arr['searchmonth_year'];
   	$monthcurrent_month = $search_arr['searchmonth_month'];
   	$search_arr['month']['current_year'] = $monthcurrent_year;
  	$search_arr['month']['current_month'] = $monthcurrent_month;

   	return $search_arr;
}
<?php

function alert_flash($levelErr,$text){

    $levelErr ?? 'danger';
    $text ?? 'พบปัญหาหรือไม่? | Found A Problem?';

    $strAlert = "";
    $strAlert .= "<div class=\"note note-".$levelErr."\" role=\"alert\">";
    $strAlert .= "<i class=\"bi bi-ban\"  style=\"font-size: 1.75rem; color:rgba(99, 0, 0, 1);\" >";
    $strAlert .=  htmlspecialchars($text) . "</div>";
    
    return $strAlert;
}

function truncate_text(string $text, int $maxLength = 200): string {
    if (mb_strlen($text) > $maxLength) {
        return mb_substr($text, 0, $maxLength) . '...';
    }
    return $text;
}

function systemBooking_error_handler($error_no, $error_msg , $err_file , $err_line)
{	

	$env_sess =  base64_encode(trim($_COOKIE['PHPSESSID'] ?? ''));
	$env_file = base64_encode(trim($err_file)); 

	if(!file_exists(LOG_PATH)){	  mkdir(LOG_PATH, 0775, true);	}
	
	$date_time = date('h:i:s');
    $msg_error =  $date_time." :: Error Description: [{$error_msg}] \n";
    $msg_error .= "Error number:[{$error_no}] [{$err_file}] [{$err_line}] \r\n\n ";
	file_put_contents(LOG_PATH."/error_".date("Y-m-d").".txt", $msg_error,FILE_APPEND);	
}

function base64url_encode($data, $pad = null) {
    $data = str_replace(array('+', '/'), array('-', '_'), base64_encode($data));
    if (!$pad) {
        $data = rtrim($data, '=');
    }
    return $data;
}

function base64url_decode($data) {
    return base64_decode(str_replace(array('-', '_'), array('+', '/'), $data));
}

function base256_readingData($data,$KEY) {
  	$hash256  =  base64_encode("::".base64_encode($data));
	$hashSession_256  = ($KEY."::".base64_encode($hash256));
	return $hashSession_256 ;
}

function base256_recodeData($enData,$KEY) {
	$recode_258  = base64_encode($enData);
	$recode_257  =  base64_decode("::".base64_encode($recode_258));
	$recode_256 = $recode_257;	
  	//$recode_256  =  base64_decode("::".base64_encode($recode_257));
	return $recode_256;
}
//- --- --- -------------------

function base256_codingData($data) {

	$encodedData = str_replace(' ','_',$data);	
	$hash_chars=rtrim($encodedData);
	var_dump($hash_chars);

	$hash256 = array ( [ 
		'Iam_Name' => $data ,
		'Ref_Name' => $encodedData ,
		'Con_Name' => $hash_chars
	] );	

	session_start( [ 
		'Iam_Name' => $data ,
		'Ref_Name' => $encodedData ,
		'Con_Name' => $hash_chars
	] );	
	
	$envComplete = base64_decode(str_replace(array('-', '_'), array('+', '/'), $hash256));
	
    return $envComplete;
}

  
// ----------------------------

function math_discost($price_discount,$price_total,$price){
	$view = "";

	if($price_total>0 && $price_discount>0 && $price>0){
		$bath_price_full = number_format($price,2,".",",");
		$bath_price_discount = number_format($price_discount,2,".",",");
		$bath_price_total = number_format($price_total,2,".",","); 	

		$view .= "<p class=\"btn btn btn-warning\" style=\"size:12px;margin:10px;\"> ราคาปกติ <del>".$bath_price_full."</del>.- </p>";
		$view .= "<p class=\"btn btn btn-danger\" style=\"size:14px;margin:10px;\"> ส่วนลด ".$bath_price_discount.".-</p>";
		$view .= "<p class=\"btn btn-block btn-success\" style=\"size:14px;margin:10px;width:95%;\">".$bath_price_total." บาท </p>";
	}	
	elseif($price_total>0 && $price_discount>0){
		$bath_price_discount = number_format($price_discount,2,".",",");
		$bath_price_total = number_format($price_total,2,".",","); 	
		$view .= "<p class=\"btn btn-link btn-sm\" style=\"size:14px;margin:10px;\"> ส่วนลด ".$bath_price_discount." บาท </p>";
		$view .= "<p class=\"btn btn-sm btn-success\" style=\"size:14px;margin:10px;\">".$bath_price_total." บาท </p>";
	}	
	elseif($price>0){
		$view .= "<h5 class='btn btn-lg btn-success' style='margin:10px;'>".number_format($price_total,2,".",",")." บาท </h5>";
	}
	else{
		$view .= "<button type=\"button\" class=\"btn btn-primary\" style='margin:10px;'>เปิดรายละเอียดเพิ่มเติม</button>";
	}
	return $view;
}

function box_price($price){
    $priceBath = $price ?? '0';
	$string_row = "<p class=\"btn btn-sm btn-warning\" style=\"size:12px;\"> ราคา  ". $price." บาท</p>";	
	return $string_row;
}

function box_discost($price_discount){
    $priceBath = $price_discount ?? '0';
	$string_row = "<p class=\"btn btn-sm btn-light\" style='size:12px;'><del>ส่วนลด  ".$priceBath." บาท </del></p>";	
	return $string_row;
}

function box_price_sale($price , $price_discount , $price_total){

	if($price_total>=0.01 && $price_discount>=0.01 && $price>=0.01){

		$bath_price_full = number_format($price,2,".",",");
		$bath_price_discount = number_format($price_discount,2,".",",");
		$bath_price_total = number_format($price_total,2,".",","); 	
		
		$string_row = "<p class=\"btn btn btn-warning\" style=\"size:12px;margin:10px;\"> ราคาปกติ <del>".$bath_price_full."</del>.- </p>
		<p class=\"btn btn btn-danger\" style=\"size:14px;margin:10px;\"> ส่วนลด ".$bath_price_discount.".-</p>
		<p class=\"btn btn-block btn-success\" style=\"size:14px;margin:10px;width:95%;\">".$bath_price_total." บาท </p>";

	}
	elseif($price_total>1){	
			$string_row = "<div class=\"btn-group\">
					<button type=\"button\" class=\"btn btn-info\" style=\"width:200px;\"> จากราคาปกติ ".$price."</button>   
					<div class=\"btn-group\">
					<button type=\"button\" class=\"btn btn-primary dropdown-toggle\" data-toggle=\"dropdown\">".$price_total."</button>
					<div class=\"dropdown-menu\">
						<a class=\"dropdown-item\" href=\"#info\"> ติดต่อสอบถาม  </a>
						<a class=\"dropdown-item\" href=\"#register\"> แจ้งรับสิทธิ ยืนยันตัวตน </a>
					</div>
				</div>
			</div>";
	}else{
		$string_row = "<a class=\"btn btn-success \" href=\"#info\">ลงทะเบียน</a>";	
	}
	return $string_row;
}


function base64_encode_url($string) {
	if($_SERVER['HTTP_HOST']=="127.0.0.1"){
		return str_replace(['+','/','='], ['-','_',''], base64_encode($string));
	}	
}

function base64_decode_url($string) {
if($_SERVER['HTTP_HOST']=="127.0.0.1"){
    return base64_decode(str_replace(['-','_'], ['+','/'], $string));
	}
}

function func_DateThai($strDate){
		$strYear = date("Y",strtotime($strDate))+543;
		$strMonth= date("n",strtotime($strDate));
		$strDay= date("j",strtotime($strDate));
		$strHour= date("H",strtotime($strDate));
		$strMinute= date("i",strtotime($strDate));
		$strSeconds= date("s",strtotime($strDate));
		$strMonthCut = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
		$strMonthThai=$strMonthCut[$strMonth];
		return "$strDay $strMonthThai $strYear, $strHour:$strMinute";
}


function func_Date_NextWeek($strDate){

	$log_datetime=strtotime($strDate);					    // เวลาใน ฐานข้อมูล
	$nextWeek = time() + (7 * 24 * 60 * 60);		// 7 days; 24 hours; 60 mins; 60 secs	
	$next_15Time = time() + (14 * 60);		// 14 mins; 60 secs	
	$next_time=strtotime($next_15Time, $log_datetime);	 
	$nowww_Time = time();
	$int_date_betaween = ($nowww_Time-$log_datetime);
	$min_time = (($int_date_betaween)/60);
	print  $nowww_Time ." - ". $log_datetime." = ".$int_date_betaween."<br>";
	print "มีระยะเวลาห่างกัน ".$min_time." นาที <br>";
						
}


function LMhash($string)
{
    $string = strtoupper(substr($string,0,14));

    $p1 = LMhash_DESencrypt(substr($string, 0, 7));
    $p2 = LMhash_DESencrypt(substr($string, 7, 7));

    return strtoupper($p1.$p2);
}

function LMhash_DESencrypt($string)
{
    $key = array();
    $tmp = array();
    $len = strlen($string);

    for ($i=0; $i<7; ++$i)
        $tmp[] = $i < $len ? ord($string[$i]) : 0;

    $key[] = $tmp[0] & 254;
    $key[] = ($tmp[0] << 7) | ($tmp[1] >> 1);
    $key[] = ($tmp[1] << 6) | ($tmp[2] >> 2);
    $key[] = ($tmp[2] << 5) | ($tmp[3] >> 3);
    $key[] = ($tmp[3] << 4) | ($tmp[4] >> 4);
    $key[] = ($tmp[4] << 3) | ($tmp[5] >> 5);
    $key[] = ($tmp[5] << 2) | ($tmp[6] >> 6);
    $key[] = $tmp[6] << 1;
   
    $is = mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($is, MCRYPT_RAND);
    $key0 = "";
   
    foreach ($key as $k)
        $key0 .= chr($k);
    $crypt = mcrypt_encrypt(MCRYPT_DES, $key0, "KGS!@#$%", MCRYPT_MODE_ECB, $iv);

    return bin2hex($crypt);
}

function isValid_input(string $input): bool
{
    // ^                  - เริ่มต้นด้วยตัวอักษร อนุญาตให้มีแค่ a-z, A-Z, และ 0-9 ต้องมีความยาวอย่างน้อย 8 ตัวอักษรขึ้นไป จบด้วยตัวอักษร
    // [a-zA-Z0-9]       - อนุญาตให้มีแค่ a-z, A-Z, และ 0-9
    // {6,}               - ต้องมีความยาวอย่างน้อย 6 ตัวอักษรขึ้นไป
    // $                  - 
	
    $pattern = '/^[a-zA-Z0-9]{6,}$/';
    
    return preg_match($pattern, $input) === 1;
}

/**
 * ตรวจสอบรหัสผ่านว่าตรงตามเงื่อนไขหรือไม่
 * (ขั้นต่ำ 8 ตัว, ประกอบด้วย a-z, A-Z, 0-9 เท่านั้น)
 * @param string $password รหัสผ่านที่ต้องการตรวจสอบ
 * @return bool
 */
function isValidPassword(string $password): bool
{
    // ^                  - เริ่มต้นด้วยตัวอักษร อนุญาตให้มีแค่ a-z, A-Z, และ 0-9 ต้องมีความยาวอย่างน้อย 8 ตัวอักษรขึ้นไป จบด้วยตัวอักษร
    // [a-zA-Z0-9]       - อนุญาตให้มีแค่ a-z, A-Z, และ 0-9
    // {8,}               - ต้องมีความยาวอย่างน้อย 8 ตัวอักษรขึ้นไป
    // $                  - สิ้นสุดสตริง
    $pattern = '/^[a-zA-Z0-9]{8,}/';
    
    return preg_match($pattern, $password) === 1;
}

?>
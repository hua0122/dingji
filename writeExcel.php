<?php
date_default_timezone_set('Asia/Shanghai'); 

ob_end_clean();
      header("content-type:text/html;charset='utf-8'");
	  require_once('phpexcel/PHPExcel.php');
	  require_once('phpexcel/PHPExcel/IOFactory.php'); 
	  $objPHPExcel=new PHPExcel();
	  $iofactory=new PHPExcel_IOFactory();
	  
	  //获得数据  ---一般是从数据库中获得数据
	  /*$data=array(
		0=>array('id'=>2013,'name'=>'张某某','age'=>21),
		1=>array('id'=>201,'name'=>'EVA','age'=>21),
		2=>array('id'=>2016,'name'=>'ceshi','age'=>21)
	  );*/
	  $con=mysqli_connect("localhost","wuliao","wuliao123456","wuliao");
	  if(!$con){
		  exit("连接数据库失败！");
	  }
	  mysqli_query($con,"set names utf8");
	  
	  $sql="select sent_borrow.id,sent_borrow.cid,sent_borrow.num,sent_borrow.snum,sent_borrow.time,sent_borrow.returntime,sent_borrow.status,sent_goods.name,sent_goods.model,sent_goods.library,sent_person.username,sent_department.title from sent_borrow left join sent_goods on sent_borrow.cid = sent_goods.cid left join sent_person on sent_borrow.person_id = sent_person.uid left join sent_department on sent_department.id = sent_person.department_id";
	  $res=mysqli_query($con,$sql);
	  while( $row = mysqli_fetch_assoc($res) ){
		   if($row['status']==2){
			   $row['status']="已还清";
		   }elseif($row['status']==1){
			   $row['status']="已归还部分";
		   }
		   elseif($row['status']==0){
			   $row['status']="未归还";
		   }
		   
			$data[]=$row;
	  }	  
	
	
	  //设置excel列名
	  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','物料名称');
	  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','物料编号');
	  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','物料型号');
	  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1','借用数量');
	  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1','借用部门');
	  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F1','借用时间');
	  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1','借用人');
	  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H1','物资库位');
	  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I1','归还时间');
	  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J1','剩余归还数量');
	  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K1','归还状态');
	  
	  //把数据循环写入excel中
	  foreach($data as $key => $value){
		   $key+=2;
		 $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$key,$value['name']);
		 $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$key,$value['cid']);
		 $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$key,$value['model']);
		 $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$key,$value['num']);
		 $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$key,$value['title']);
		 $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$key,$value['time']);
		 $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$key,$value['username']);
		 $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$key,$value['library']);
		 $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$key,$value['returntime']);
		 $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$key,$value['snum']);
		 $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$key,$value['status']);
		 
	  }
	  
	  global $objPHPExcel;
	  global $iofactory;
 
  //导出excel并下载
  function xiazaiExcel($data){  
   global $objPHPExcel;
   global $iofactory;  
  //导出代码
   $objPHPExcel->getActiveSheet() -> setTitle('借用记录');
   $objPHPExcel-> setActiveSheetIndex(0);
   $objWriter = $iofactory -> createWriter($objPHPExcel, 'Excel2007');
   
   $filename = date("YmdHis").'borrow.xlsx';
   header('Content-Type: application/vnd.ms-excel');
   header('Content-Type: application/octet-stream');
   header('Content-Disposition: attachment; filename="' . $filename . '"');
   header('Cache-Control: max-age=0');
   $objWriter -> save('php://output');
	  
  }
 

	  xiazaiExcel($data);
  
  
?>
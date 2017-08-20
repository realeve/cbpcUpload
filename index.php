<?php    	
	
	function handleDir(){
		$year = date('Y');
		$month = date('m');
		$content = "./assets/$year/$month";
		$pathImg = "$content/image/";
		$pathFile = "$content/file/";
		$pathVideo = "$content/video/";
		$pathAudio = "$content/audio/";
		$pathWebp = "$content/webp/";
		
		if(!is_dir($pathImg)){
			$res = mkdir($pathImg,0777,true);
		}
		if(!is_dir($pathFile)){
			$res = mkdir($pathFile,0777,true);
		}
		if(!is_dir($pathVideo)){
			$res = mkdir($pathVideo,0777,true);
		}
		if(!is_dir($pathAudio)){
			$res = mkdir($pathAudio,0777,true);
		}
		if(!is_dir($pathWebp)){
			$res = mkdir($pathWebp,0777,true);
		}
	}
	
	function createNonceStr($length = 32) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
		$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	
	function handlePost(){
		$year = date('Y');
		$month = date('m');
		$content = "./assets/$year/$month";
		$pathImg = "$content/image/";
		$pathFile = "$content/file/";
		$pathVideo = "$content/video/";
		$pathAudio = "$content/audio/";
		$pathWebp = "$content/webp/";
	  header("Content-type: application/json");
	  // 100M大小限制
	  if ($_FILES["file"]["size"] < 1024*1024*100){
		if ($_FILES["file"]["error"] > 0)
		{
		  echo '{"status":"0","msg":"文件类型或大小错误"}';
		}
		else{
		  $file = $_FILES["file"];
		  $name = $file["name"];
		  $filename = $file["name"];
		  if(strpos($name,'.')==-1){
			$arr = explode('/',$file['type']);
			$fileType = '.'.$arr[count($arr)-1];
		  }else{
			$arr = explode('.',$filename);
			$fileType = '.'.$arr[count($arr)-1];
			$arr[count($arr)-1]='';
			$filename = implode('',$arr);
		  }
		  
		  //随机值          
		  // $filename = time().'_'.(microtime()*1000000).'_'.$filename;
		  // $filename = base64_encode($filename);
		  //base64中的'/'不能作为文件名内容
		  // $filename = str_replace("/",'-',$filename); 
		  $filename = time().'_'.(microtime()*1000000) . createNonceStr();
		  
		  $fileType = str_replace("image/svg",'svg',$fileType);
		  
		  //图片文件处理：1.获取宽高;2.转换为webp
		  if(strripos($file["type"],'image')>-1){
			$size = getimagesize($file["tmp_name"]);
			$return['width'] = $size[0];
			$return['height'] = $size[1];
			$distFile = $pathImg.$filename.$fileType;
		  }else if(strripos($file["type"],'audio')>-1){           
			$return['width'] = 0;
			$return['height'] = 0;
			$distFile = $pathAudio.$filename.$fileType;
		  }else if(strripos($file["type"],'video')>-1){           
			$return['width'] = 0;
			$return['height'] = 0;
			$distFile = $pathVideo.$filename.$fileType;
		  }else{           
			$return['width'] = 0;
			$return['height'] = 0;
			$distFile = $pathFile.$filename.$fileType;
		  }
		  
		  move_uploaded_file($file["tmp_name"],$distFile); 
		  $return['size'] = round($file["size"] / 1024,2)+'kb';
		  $return['type'] = $file["type"];
		  $return['url'] = $pathFile.$filename.$fileType;
			
		  //图片文件处理：1.获取宽高;2.转换为webp
		  if($return['width']){         
			//apache GD库默认对webp处理有较多BUG，转用 imagick方案
			// 此处需使用绝对路径，需要根据实际目录做设置
			$imgageDir = $_SERVER['DOCUMENT_ROOT'] .'/upload/'."assets/$year/$month/";
			
			$srcFile = $imgageDir.'image/'.$filename.$fileType;  
			$thumbFile = $imgageDir.'image/thumb_'.$filename.$fileType;
			
			$distFile = $imgageDir.'webp/'.$filename.'.webp';          
			$thumbWebpFile = $imgageDir.'webp/thumb_'.$filename.'.webp';
			
			$image = new Imagick($srcFile);			
			// $image->stripImage();//去掉exif等信息，如果是新闻网站则不应去掉
			$image->setImageFormat('webp');
			$image->setImageCompression(Imagick::COMPRESSION_JPEG); 
			$image->setImageCompressionQuality(80); 
			//转换效果与谷歌官方 cwebp -q 80 input.jpg -o oubput.webp 接近
			$image->writeImage($distFile);
			
			//生成Webp缩略图
			$image->thumbnailImage(360,null); 
			$image->writeImage($thumbWebpFile); 
			
			// 生成普通缩略图
			$image = new Imagick($srcFile);	
			$image->thumbnailImage(360,null); 
			$image->writeImage($thumbFile); 			
			
			// 不删除原图片
			// unlink($srcFile);
			
			$size = filesize($distFile);
			$return['size'] = round($size / 1024,2)+'kb';
			$return['type'] = 'images/webp';
			$return['url'] = $pathWebp.$filename.'.webp';
		  } 
		  
		  $return['status'] = 1;
		  $return['msg'] = '上传成功';
		  $return['name'] = $name;
		  $return['url'] = str_replace('./','/',$return['url']);
		  echo json_encode($return);
		}
	  }
	}
	
	function handleGet(){
	  header("Content-type: application/json");
	  if(isset($_GET['name'])){
		$filename = '.'.$_GET['name'];
		if(file_exists($filename)){
		  unlink($filename);
		  $return['status'] = 1;
		  $return['msg'] = '文件删除成功'; 
		}else{
		  $return['status'] = 0;
		  $return['msg'] = '文件'.$filename.'不存在';
		}            
	  }else{
		  $return['status'] = 0;
		  $return['msg'] = '请求参数错误';
	  }
	  
	  if(isset($_GET['callback'])){
		echo $_GET['callback'].'('.json_encode($return).')';
	  }else{
		echo json_encode($return);
	  }  
	}
	
	function handleErr(){
	  header("Content-type: application/json");
	  $return['status'] = 0;
	  $return['msg'] = '上传文件失败';
	  echo json_encode($return);
	}
	
  function init(){
		$requestType = $_SERVER['REQUEST_METHOD'];
		// 指定允许其他域名访问 // 正式布署请设置为前端资源主站
		header('Access-Control-Allow-Origin:*');
		// 响应类型
		header('Access-Control-Allow-Methods:GET,POST,PUT,OPTIONS');
		header('Access-Control-Allow-Headers:x-requested-with,content-type');   
		handleDir();
		if($requestType == "OPTIONS"){   
		  header('Access-Control-Allow-Credentials:true');
		  header('Access-Control-Max-Age:1728000');
		  header('Content-Type:text/plain charset=UTF-8');
		  header('Content-Length: 0',true);
		  header("status: 204"); 
		  header("HTTP/1.0 204 No Content");
		}else if($requestType == "POST"){  
		  handlePost();
		}else if($requestType == "GET"){     
		  handleGet();		  
		}else{
		  handleErr();
		}
	}
	init();
?>
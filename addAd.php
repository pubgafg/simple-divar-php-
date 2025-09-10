<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD']!=='POST') {
    header('Location: public/index.php');
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = trim($_POST['price'] ?? '');
$errors = [];

if ($title==='' || $description==='' || $price==='') $errors[]='لطفاً همه فیلدها را پر کنید.';
if (!is_numeric($price)||(int)$price<0) $errors[]='قیمت معتبر نیست.';

$imagePath=null;
if (!empty($_FILES['image']['name'])) {
    $file=$_FILES['image'];
    $maxSize=5*1024*1024;
    $allowedMime=['image/jpeg','image/png','image/gif','image/webp'];

    if($file['error']!==UPLOAD_ERR_OK) $errors[]='خطا در آپلود فایل.';
    elseif($file['size']>$maxSize) $errors[]='حجم فایل بیش از حد مجاز است (حداکثر 5MB).';
    else {
        $finfo=finfo_open(FILEINFO_MIME_TYPE);
        $mime=finfo_file($finfo,$file['tmp_name']);
        finfo_close($finfo);
        if(!in_array($mime,$allowedMime)) $errors[]='فایل انتخابی تصویر نیست یا فرمتش پشتیبانی نمی‌شود.';
        else {
            if(!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR,0755,true);
            $ext=pathinfo($file['name'],PATHINFO_EXTENSION);
            $unique=time().'-'.bin2hex(random_bytes(6)).'.'.$ext;
            $dest=UPLOAD_DIR.$unique;
            if(!move_uploaded_file($file['tmp_name'],$dest)) $errors[]='خطا در ذخیره فایل.';
            else $imagePath=UPLOAD_URI.$unique;
        }
    }
}

if(!empty($errors)){
    session_start();
    $_SESSION['form_errors']=$errors;
    $_SESSION['old']=['title'=>$title,'description'=>$description,'price'=>$price];
    header('Location: public/index.php');
    exit;
}

$mysqli=new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
if($mysqli->connect_errno) die('خطا در اتصال به دیتابیس: '.$mysqli->connect_error);

$sql="INSERT INTO ads (title, description, price, image) VALUES (?, ?, ?, ?)";
$stmt=$mysqli->prepare($sql);
$priceInt=(int)$price;
$stmt->bind_param('ssds',$title,$description,$priceInt,$imagePath);

if($stmt->execute()){
    session_start();
    $_SESSION['success']='آگهی با موفقیت ثبت شد.';
    header('Location: public/index.php');
    exit;
} else {
    if($imagePath){ $full=UPLOAD_DIR.basename($imagePath); if(file_exists($full)) unlink($full); }
    session_start();
    $_SESSION['form_errors']=['خطا در ذخیره آگهی.'];
    header('Location: public/index.php');
    exit;
}

<?php
require_once __DIR__ . '/../config.php';
session_start();

$errors = $_SESSION['form_errors'] ?? [];
$success = $_SESSION['success'] ?? '';
$old = $_SESSION['old'] ?? ['title'=>'','description'=>'','price'=>''];
unset($_SESSION['form_errors'],$_SESSION['success'],$_SESSION['old']);

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if($mysqli->connect_errno) die('خطا در اتصال به دیتابیس: '.$mysqli->connect_error);

$q = trim($_GET['q'] ?? '');
if($q!==''){
    $like = '%'.$mysqli->real_escape_string($q).'%';
    $stmt=$mysqli->prepare("SELECT id,title,description,price,image,created_at FROM ads WHERE title LIKE ? OR description LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param('ss',$like,$like);
    $stmt->execute();
    $res=$stmt->get_result();
} else {
    $res=$mysqli->query("SELECT id,title,description,price,image,created_at FROM ads ORDER BY created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
<meta charset="utf-8">
<title>سایت آگهی ساده</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{font-family:Tahoma,direction:rtl;background:#f5f5f5;margin:0;padding:20px;}
.container{max-width:900px;margin:0 auto;}
.card{background:#fff;padding:16px;border-radius:8px;margin-bottom:16px;box-shadow:0 2px 6px rgba(0,0,0,0.06);}
input,textarea,button{width:100%;padding:10px;margin-top:8px;border-radius:6px;border:1px solid #ccc;box-sizing:border-box;}
.ad{display:flex;gap:12px;align-items:flex-start;border-bottom:1px solid #eee;padding:12px 0;}
.ad img{width:160px;height:110px;object-fit:cover;border-radius:6px;}
.title{font-weight:bold;font-size:18px;}
.price{color:#d6336c;font-weight:bold;margin-top:6px;}
.search-row{display:flex;gap:8px;align-items:center;}
@media(max-width:600px){.ad{flex-direction:column}.search-row{flex-direction:column}}
</style>
</head>
<body>
<div class="container">
<h1 style="text-align:center">سایت آگهی ساده</h1>

<div class="card">
<h3>ثبت آگهی جدید</h3>
<?php if($success):?><div style="color:green;margin-bottom:8px;"><?=htmlspecialchars($success)?></div><?php endif;?>
<?php if(!empty($errors)):?><div style="color:#a00;margin-bottom:8px;"><ul><?php foreach($errors as $err):?><li><?=htmlspecialchars($err)?></li><?php endforeach;?></ul></div><?php endif;?>

<form action="../addAd.php" method="post" enctype="multipart/form-data">
<input type="text" name="title" placeholder="عنوان آگهی" required value="<?=htmlspecialchars($old['title']??'')?>">
<textarea name="description" placeholder="توضیحات" rows="4" required><?=htmlspecialchars($old['description']??'')?></textarea>
<input type="number" name="price" placeholder="قیمت (تومان)" required value="<?=htmlspecialchars($old['price']??'')?>">
<input type="file" name="image" accept="image/*">
<button type="submit">ثبت آگهی</button>
</form>
</div>

<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;">
<h3>جستجو و لیست آگهی‌ها</h3>
<div style="width:50%;">
<form method="get" style="display:flex;gap:8px;" class="search-row">
<input type="text" name="q" placeholder="جستجو در عنوان یا توضیحات" value="<?=htmlspecialchars($q)?>">
<button type="submit">جستجو</button>
</form>
</div></div>

<div id="adsList">
<?php if($res && $res->num_rows>0):?>
<?php while($ad=$res->fetch_assoc()):?>
<div class="ad">
<?php if(!empty($ad['image'])):?><img src="<?=htmlspecialchars($ad['image'])?>" alt="عکس آگهی"><?php endif;?>
<div>
<div class="title"><?=htmlspecialchars($ad['title'])?></div>
<div style="margin-top:6px;color:#444;"><?=nl2br(htmlspecialchars($ad['description']))?></div>
<div class="price"><?=number_format((int)$ad['price'])?> تومان</div>
<div style="color:#777;font-size:12px;margin-top:6px;"><?=htmlspecialchars($ad['created_at'])?></div>
</div></div>
<?php endwhile;?>
<?php else:?><p>آگهی‌ای یافت نشد.</p><?php endif;?>
</div>
</div></div>
</body>
</html>

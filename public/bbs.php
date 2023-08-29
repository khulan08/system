<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

if (isset($_POST['body'])) {
  
  $image_filename = null;
  if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
   
    if (preg_match('/^image\//', $_FILES['image']['type']) !== 1) {
    
      header("HTTP/1.1 302 Found");
      header("Location: ./bbs.php");
    }

    
    $pathinfo = pathinfo($_FILES['image']['name']);
    $extension = $pathinfo['extension'];
    
    $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;
    $filepath = '/var/www/upload/image/' . $image_filename;
    move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
  }

  // insertする
  $insert_sth = $dbh->prepare("INSERT INTO bbs (body, image_filename) VALUES (:body, :image_filename)");
  $insert_sth->execute([
    ':body' => $_POST['body'],
    ':image_filename' => $image_filename,
  ]);

  header("HTTP/1.1 302 Found");
  header("Location: ./bbs.php");
  return;
}
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// 1ページあたりの行数を決める
$count_per_page = 10;

// ページ数に応じてスキップする行数を計算
$skip_count = $count_per_page * ($page - 1);

// hogehogeテーブルの行数を SELECT COUNT で取得
$count_sth = $dbh->prepare('SELECT COUNT(*) FROM bbs;');
$count_sth->execute();
$count_all = $count_sth->fetchColumn();
if ($skip_count >= $count_all) {
    // スキップする行数が全行数より多かったらおかしいのでエラーメッセージ表示し終了
    print('このページは存在しません!');
    return;
}

$select_sth = $dbh->prepare('SELECT * FROM bbs ORDER BY created_at DESC LIMIT :count_per_page OFFSET :skip_count');
$select_sth->bindParam(':count_per_page', $count_per_page, PDO::PARAM_INT);
$select_sth->bindParam(':skip_count', $skip_count, PDO::PARAM_INT);
$select_sth->execute();

?>

<html style="">

<head>
	<title>Web掲示板サービス</title>
</head>
<body style="font-size: 14px;">
<h1 style="color: #1c6ea4;text-align: center;text-shadow: 2px 2px 2px black;">Web掲示板サービス</h1>
<form method="POST" action="./bbs.php" enctype="multipart/form-data" style="margin: 2em; padding-bottom:2em;outline: 5px dotted #1c6ea4; outline-offset:0;border-radius:10px; text-align: center;">
	<text style="font-weight: 700;font-size: 20px;">内容：</text><br>
	<textarea name="body"></textarea>
  <div style="margin: 1em 0;">
    <text style="font-weight: 700; font-size: 20px; align:center;">画像:</text><br>
		<input type="file" accept="image/*" name="image" id="imageInput" style="justify-content: center;">
  </div>
  <button type="submit" style="color:blue;font-weight: 700;font-size:20px; border-radius:10px;cursor:pointer;border: 2px solid; ">送信</button>
</form>
<div style="width: 100%; text-align: center; padding-bottom: 1em; border-bottom: 1px solid #ccc; margin-bottom: 0.5em">
  <?= $page ?>ページ目
  (全 <?= floor($count_all / $count_per_page) + 1 ?>ページ中)

  <div style="display: flex; justify-content: space-between; margin-bottom: 2em;">
    <div>
      <?php if($page > 1): // 前のページがあれば表示 ?>
        <a href="?page=<?= $page - 1 ?>">前のページ</a>
      <?php endif; ?>
    </div>
    <div>
      <?php if($count_all > $page * $count_per_page): // 次のページがあれば表示 ?>
        <a href="?page=<?= $page + 1 ?>">次のページ</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php foreach($select_sth as $entry): ?>
  <dl style="font-size:16px;margin: 2em; padding-bottom: 2em; outline: 5px dotted #1C6EA4;outline-offset: 0px;border-radius:5px">
    <dt style="font-weight:700;">ID</dt>
    <dd>
				<a href="bbs_view.php?id=<?= $entry['id'] ?>"><?= $entry['id'] ?></a>
		</dd>
    <dt style="font-weight: 700;">日時</dt>
    <dd><?= $entry['created_at'] ?></dd>
    <dt style="font-weight: 700;">内容</dt>
    <dd>
      <?= nl2br(htmlspecialchars($entry['body']))  ?>
      <?php if(!empty($entry['image_filename'])):  ?>
      <div>
        <img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;">
      </div>
      <?php endif; ?>
    </dd>
  </dl>
<?php endforeach ?>

</html>

<script>
    document.addEventListener("DOMContentLoaded", ()=>{
        const imageInput = document.getElementById("imageInput");
        imageInput.addEventListener("change", ()=> {
            if(imageInput.files.length < 1){
                return;
            }
            if(imageInput.files[0].size > 5 * 1024 * 1024){

                alert("5MB以下のファイルを選択してください！");
                imageInput.value = "";
            }
        });
    });
</script>
</body>
<style type="text/css">
*{background-color: #fff;}

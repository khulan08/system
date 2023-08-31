<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

if (!isset($_GET['id'])) {
  return; 
}
$id = intval($_GET['id']); 
$select_sth = $dbh->prepare('SELECT * FROM bbs WHERE id = :id');
$select_sth->bindParam(':id', $id, PDO::PARAM_INT);
$select_sth->execute();
$entry = $select_sth->fetch();

if (!$entry) {
  
  return; 
}
?>

<a href="bbs.php" style="font-size:22px; ">一覧に戻る</a>

<dl style="margin: 2em; padding-bottom:2em; outline: 5px dotted #1c6ea4; outline-offset: 0px; border-radius: 5px; ">
  <dt  style="font-weight:700;">ID</dt>
  <dd><?= $entry['id'] ?></dd>
  <dt  style="font-weight:700;">日時</dt>
  <dd><?= $entry['created_at'] ?></dd>
  <dt  style="font-weight:700;">内容</dt>
  <dd>
		<?= nl2br(htmlspecialchars($entry['body']))  ?>
		<?php if(!empty($entry['image_filename'])): ?>
		<div>
			<img src="/image/<?= $entry['image_filename'] ?>" style="max-height:10em;">
		</div> 
		<?php endif ?>
	</dd>
</dl>

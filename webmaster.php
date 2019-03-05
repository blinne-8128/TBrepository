<!DOCTYPE html>
<html>

    <head>

		<meta charset = "UTF-8">

		<title>WEB_MASTER</title>

	</head>

    <body>

        <h1>WEB_MASTER</h1>

		<br>

		<?php if($_SERVER["HTTP_REFERER"]=="http://ユーザー名/toppage.php" or $_SERVER["HTTP_REFERER"]=="http://ユーザー名/webmaster.php"): ?>

		<?php

			$filename1 = "account.php"; //phpファイル名
			$filename2 = "photodiary.php"; //phpファイル名(カレンダー用)
			$tablename1 = "account"; //テーブル名
			$tablename2 = "photodiary"; //テーブル名(カレンダー用)
			$folder = "files"; //画像フォルダ名

			//MySQLに接続
			$dsn = 'データベース名';
			$user = 'ユーザー名';
			$password = 'パスワード';
			$pdo = new PDO($dsn,$user,$password,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

			$table = $_POST['table'];
			$delete = $_POST['delete'];
			$image_Path = $folder."/*"; //全ての画像ファイル
			$php = "photodiary_id=*";

			if($table == "account"){
				$sql = "DROP TABLE $tablename1";
				$stmt = $pdo -> query($sql);
				$sql = "CREATE TABLE IF NOT EXISTS $tablename1" 
				."("."id INT NOT NULL AUTO_INCREMENT PRIMARY KEY," //ID
				."name CHAR(20) UNIQUE NOT NULL," //ユーザー名
				."password CHAR(20) NOT NULL," //パスワード
				."friend CHAR(100) NOT NULL".");";//閲覧可能ユーザー
				$stmt = $pdo -> query($sql);
			}

			if($table == "photodiary"){
				$sql = "DROP TABLE $tablename2";
				$stmt = $pdo -> query($sql);
				$sql = "CREATE TABLE IF NOT EXISTS $tablename2" 
				."("."id INT NOT NULL," //ユーザーID
				."date INT NOT NULL," //日付
				."ext char(5) NOT NULL," //拡張子
				."size INT NOT NULL," //サイズ
				."name char(50) NOT NULL" .");";//ファイル名
				$stmt = $pdo -> query($sql);
			}

			if($delete == "image"){
				foreach(glob($image_Path) as $val){
					unlink($val); //画像ファイルの削除
				}
			}

			if($delete == "php"){
				foreach(glob($php) as $val){
					unlink($val); //phpファイルの削除
				}
			}


			echo "<h3>アカウント情報</h3>";

			//アカウント表示
			$sql = "SELECT * FROM $tablename1 ORDER BY id";
			$stmt = $pdo -> query($sql);
			$results = $stmt -> fetchAll();
			foreach($results as $row){
				echo "[".$row['id']."] name: ".$row['name'].' | password: '.$row['password']."<br>";
			}
			echo "<br><br>";

			echo "<h3>PHP情報</h3>";

			//PHPファイル表示
			foreach(glob($php) as $val){
				echo $val."<br>";
			}
			echo "<br><br>";

			echo "<h3>カレンダー情報</h3>";

			//カレンダー表示
			$sql = "SELECT * FROM $tablename2 ORDER BY id";
			$stmt = $pdo -> query($sql);
			$results = $stmt -> fetchAll();
			foreach($results as $row){
				echo "[".$row['id']."] date: ".$row['date'].' | name: '.$row['name']."<br>";
			}
			echo "<br><br>";

			echo "<h3>写真情報</h3>";

			//写真表示
			foreach(glob($image_Path) as $val){
				echo $val."<br>";
			}
			echo "<br><br>";

		?>

		<h3>削除フォーム</h3>

		<form method="post" action="">
			<select name="table">
			<option value="account">アカウントテーブル</option>
			<option value="photodiary">カレンダーテーブル</option>
			</select>
			<br><br>
			<input type="submit" value="リセットする">
		</form>

		<br><br>

		<form method="post" action="">
			<input type="radio" name="delete" value="php"> PHPファイル
			<input type="radio" name="delete" value="image"> 写真
			<br><br>
			<input type="submit" value="全削除する">
		</form>

		<?php else: ?>

		<h3>エラー：ログインしてください。</h3>

		<?php endif; ?>

		<br><br>

		<A HREF = toppage.php>トップページ</A>

    </body>
</html>
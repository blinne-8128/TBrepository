<!DOCTYPE html>
<html>

    <head>

		<meta charset = "UTF-8">

		<title>掲示板</title>

	</head>

    <body>

        <h1>掲示板</h1>

		<br>

		<?php

			$filename = 'bbs.php'; //phpファイル名
			$tablename = 'テーブル名'; //テーブル名

			//MySQLに接続
			$dsn = 'データベース名';
			$user = 'ユーザー名';
			$password = 'パスワード';
			$pdo = new PDO($dsn,$user,$password,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));


			//テーブルの作成
			$sql = "CREATE TABLE IF NOT EXISTS $tablename" 
			."("."id INT,"."name char(32),"."comment TEXT,"."date DATETIME,"."password char(32)".");";
			$stmt = $pdo -> query($sql);


			//変数の定義
			$sql = "SELECT COUNT(*) FROM $tablename";
			$stmt = $pdo -> query($sql);
			$results = $stmt -> fetchAll();
			foreach($results as $row){
				$id = 1 + $row[0]; //投稿番号
			}
			$name = $_POST['name']; //名前
			$comment = $_POST['comment']; //コメント
			$date = date("Y/m/d H:i:s"); //時間
			$password = $_POST['password']; //パスワード
			$editcheck = $_POST['editcheck']; //送信-編集モード切り替え

			$edit = $_POST['edit']; //編集対象番号
			$editpassword = $_POST['editpassword']; //編集対象パスワード

			$delete = $_POST['delete']; //削除対象番号
			$deletepassword = $_POST['deletepassword']; //削除対象パスワード

			$sql = "SELECT * FROM $tablename ORDER BY id";
			$stmt = $pdo -> query($sql);
			$results = $stmt -> fetchAll();

			if(!empty($edit) or !empty($delete)) {

				foreach($results as $row){

					if($row['id'] == $edit and $row['password'] == $editpassword){
						$edit2 = $row['id']; //編集対象番号(チェック用)
						$editname = $row['name']; //編集対象名前(表示用)
						$editcomment = $row['comment']; //編集対象コメント(表示用)
						$editpassword2 = $row['password']; //編集対象パスワード(表示用)
					}

					if($row['id'] == $delete and $row['password'] == $deletepassword){
						$delete2 = $row['id']; //削除対象番号(チェック用)
					}
				}
			}


		?>


        <h3>送信フォーム　<?php if(!empty($edit2)){echo "※　編集モード => [".$edit2."]　※";} ?></h3>

		<form method="post" action="<?php echo $filenamet; ?>">
			name:<br>
        	<input type="text" name="name" size="32" value="<?php if(empty($editname)){echo "名前";}else{echo $editname;} ?>">
			<input type="hidden" name="editcheck" value="<?php echo $edit2; ?>">
			<br>
			comments:<br> 
			<input type="text" name="comment" size="80" value="<?php if(empty($editcomment)){echo "コメント";}else{echo $editcomment;} ?>">
        	<br>
			password:<br>
        	<input type="text" name="password" size="32" value="<?php if(!empty($editpassword2)){echo $editpassword2;} ?>">
			<br><br>
        	<input type="submit" value="送信する" >　<input type="reset" value="リセット" >
			<br>
        </form>


		<br>


		<h3>編集フォーム</h3>

		<form method="post" action="<?php echo $filenamet; ?>">
			編集対象番号:
        	<input type="text" name="edit" size="4">
			　/　password:
        	<input type="text" name="editpassword" size="32">
			<br><br>
        	<input type="submit" value="編集する" >　<input type="reset" value="リセット" >
			<br>
        </form>


		<br>


		<h3>削除フォーム</h3>

		<form method="post" action="<?php echo $filenamet; ?>">
			削除対象番号:
        	<input type="text" name="delete" size="4">
			　/　password:
        	<input type="text" name="deletepassword" size="32">
			<br><br>
        	<input type="submit" value="削除する" >　<input type="reset" value="リセット" >
			<br>
        </form>


		<br>


		<?php

			if(empty($name) or empty($comment)){ //名前なしorコメントなし

				if(empty($edit) and empty($delete)){ //送信フォーム
					echo "[プログラム] 名前とコメントを入力してください"."<br>";
				}

			}else{ //名前ありandコメントあり


				//コメント送信
				if(empty($editcheck)){
					$sql = "INSERT INTO $tablename (id,name,comment,date,password) VALUES (:id,:name,:comment,:date,:password)";
					$stmt = $pdo -> prepare($sql);
					$stmt -> bindParam(':id',$id,PDO::PARAM_INT);
					$stmt -> bindParam(':name',$name,PDO::PARAM_STR);
					$stmt -> bindParam(':comment',$comment,PDO::PARAM_STR);
					$stmt -> bindParam(':date',$date,PDO::PARAM_STR);
					$stmt -> bindParam(':password',$password,PDO::PARAM_STR);
					$stmt -> execute();
					echo "[プログラム] コメントを送信しました"."<br>";


				//コメント編集
				}else{

					$sql = "SELECT * FROM $tablename ORDER BY id";
					$stmt = $pdo -> query($sql);
					$results = $stmt -> fetchAll();
					foreach($results as $row){

						if($row['id'] == $editcheck){ //編集対象
							$sql = "UPDATE $tablename SET name=:name,comment=:comment,date=:date,password=:password WHERE id=:id";
							$stmt = $pdo -> prepare($sql);
							$stmt -> bindParam(':id',$row['id'],PDO::PARAM_INT);
							$stmt -> bindParam(':name',$name,PDO::PARAM_STR);
							$stmt -> bindParam(':comment',$comment,PDO::PARAM_STR);
							$stmt -> bindParam(':date',$row['date'],PDO::PARAM_STR);
							$stmt -> bindParam(':password',$password,PDO::PARAM_STR);
							$stmt -> execute();
						}
					}
					echo "[プログラム] コメントを編集しました"."<br>";
				}
			}

			if(!empty($edit) and !empty($edit2)){ //編集対象ありandパスワード一致
				echo "[プログラム] コメントを編集します"."<br>";
			}

			if(!empty($edit) and empty($edit2)){ //編集対象なしorパスワード不一致
				echo "[プログラム] 編集対象番号かパスワードが違います"."<br>";
			}


			//コメント削除
			if(!empty($delete2)){

					$sql = "SELECT * FROM $tablename ORDER BY id";
					$stmt = $pdo -> query($sql);
					$results = $stmt -> fetchAll();
					foreach($results as $row){

						if($row['id'] == $delete2){ //削除対象
							$sql = "DELETE FROM $tablename WHERE id=:id";
							$stmt = $pdo->prepare($sql);
							$stmt -> bindParam(':id',$row['id'],PDO::PARAM_INT);
							$stmt -> execute();
							echo "[プログラム] コメントを削除しました"."<br>";
						}
					}

					foreach($results as $row){
						if($row['id'] > $delete2){ //番号変更
							$sql = "UPDATE $tablename SET id=:id-1 WHERE id=:id";
							$stmt = $pdo -> prepare($sql);
							$stmt -> bindParam(':id',$row['id'],PDO::PARAM_INT);
							$stmt -> execute();
						}
					}

			}

			if(!empty($delete) and empty($delete2)){ //削除対象なしorパスワード不一致
				echo "[プログラム] 削除対象番号かパスワードが違います"."<br>";
			}

		?>


		<br><hr><br>

		<h3>コメント一覧</h3>

		<?php

			//コメント表示
			$sql = "SELECT * FROM $tablename ORDER BY id";
			$stmt = $pdo -> query($sql);
			$results = $stmt -> fetchAll();
			foreach($results as $row){
				echo "[".$row['id']."] name: ".$row['name'].' | date: '.$row['date']."<br><br>";
				echo $row['comment']."<br><br>";
			}


		?>

    </body>
</html>

<!DOCTYPE html>
<html>

    <head>

		<meta charset = "UTF-8">

		<title>ACCOUNT</title>

	</head>

    <body>

        <h1>ACCOUNT</h1>

		<br>

		<?php

			$filename = "account.php"; //phpファイル名
			$filename2 = "photodiary.php"; //phpファイル名(カレンダー用)
			$tablename = "account"; //テーブル名
			$tablename2 = "photodiary"; //テーブル名(カレンダー用)
			$folder = "files"; //画像フォルダ名

			//MySQLに接続
			$dsn = 'データベース名';
			$user = 'ユーザー名';
			$password = 'パスワード';
			$pdo = new PDO($dsn,$user,$password,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

			//テーブルの作成
			$sql = "CREATE TABLE IF NOT EXISTS $tablename" 
			."("."id INT NOT NULL AUTO_INCREMENT PRIMARY KEY," //ID
			."name CHAR(20) UNIQUE NOT NULL," //ユーザー名
			."password CHAR(20) NOT NULL," //パスワード
			."friend CHAR(100) NOT NULL".");";//閲覧可能ユーザー
			$stmt = $pdo -> query($sql);


			//POST作成データ
			$name = $_POST['name']; //名前
			$password = $_POST['password']; //パスワード
			$id_edit_check = $_POST['id_edit_check']; //送信-編集モード切り替え

			//POST編集データ
			$name_edit = $_POST['name_edit']; //編集対象番号
			$password_edit = $_POST['password_edit']; //編集対象パスワード

			//POST削除データ
			$name_delete = $_POST['name_delete']; //削除対象ユーザー名
			$password_delete = $_POST['password_delete']; //削除対象パスワード

			$friend = ""; //閲覧可能ユーザー
			$samename = 1; //ユーザー名の重複

			//名前とパスワードの一致確認
			$sql = "SELECT * FROM $tablename ORDER BY id";
			$stmt = $pdo -> query($sql);
			$results = $stmt -> fetchAll();

			foreach($results as $row){

				if(!empty($name_edit) or !empty($name_delete)) { //POST編集or削除フォーム

					if($row['name'] == $name_edit and $row['password'] == $password_edit){ //編集
						$id_edit = $row['id']; //編集対象ID
					}

					if($row['name'] == $name_delete and $row['password'] == $password_delete){ //削除
						$id_delete = $row['id']; //削除対象ID
					}

				}else{

					if($row['id'] != $id_edit_check and $row['name'] == $name){
						$samename ++;
					}
				}
			}

			//アカウント情報処理
			if(empty($name) or empty($password)){ //名前なしorパスワードなし

				if(empty($name_edit) and empty($name_delete) and !empty($id_edit_check)){ //送信フォーム
					echo "エラー：名前とパスワードを入力してください。<br>";
				}

			}else{ //名前ありandパスワードあり

				if($samename < 2){

					if(strpos("a".$name."a", '.') == false){ //ユーザー名に . が使われていない。

						//アカウント作成
						if($id_edit_check == "no"){
							$sql = "INSERT INTO $tablename (name,password,friend) VALUES (:name,:password,:friend)";
							$stmt = $pdo -> prepare($sql);
							$stmt -> bindParam(':name',$name,PDO::PARAM_STR);
							$stmt -> bindParam(':password',$password,PDO::PARAM_STR);
							$stmt -> bindParam(':friend',$friend,PDO::PARAM_STR);
							$stmt -> execute();

							$sql = "SELECT * FROM $tablename ORDER BY id";
							$stmt = $pdo -> query($sql);
							$results = $stmt -> fetchAll();
							foreach($results as $row){
								if($row['name'] == $name){
									$id = $row['id'];
								}
							}
							$filename3 = "photodiary_id=".$id.".php";
							$i = 1;
							if(file_exists($filename2)){$lines = file($filename2);}

							if(file_exists($filename2)){foreach($lines as $line){

								if($i == 39){ //$id= の行
									$line_id = '$id = '.$id.';';
									$fp = fopen($filename3,'a');
									fwrite($fp, $line_id."\n");
									fclose($fp);
								}else{
									$fp = fopen($filename3,'a');
									fwrite($fp, $line);
									fclose($fp);
								}
								$i++;
							}}

							echo "アカウントを作成しました。<br>";
							echo "<A HREF = photodiary_id=$id.php>カレンダーページ</A><br>";


						//アカウント編集
						}else{

							$sql = "SELECT * FROM $tablename ORDER BY id";
							$stmt = $pdo -> query($sql);
							$results = $stmt -> fetchAll();
							foreach($results as $row){

								if($row['id'] == $id_edit_check){ //編集対象
									$sql = "UPDATE $tablename SET name=:name,password=:password WHERE id=:id";
									$stmt = $pdo -> prepare($sql);
									$stmt -> bindParam(':id',$row['id'],PDO::PARAM_INT);
									$stmt -> bindParam(':name',$name,PDO::PARAM_STR);
									$stmt -> bindParam(':password',$password,PDO::PARAM_STR);
									$stmt -> execute();

									echo "アカウントを編集しました。<br>";
									echo "<A HREF = photodiary_id={$row['id']}.php>カレンダーページ</A><br>";
								}
							}
						}

					}else{
						echo "エラー：ユーザー名に . は使えません。<br>";
					}

				}else{
					echo "エラー：そのユーザー名はすでに使われています。<br>";
				}
			}

			if(!empty($name_edit) and !empty($password_edit)){ //編集対象ありandパスワード一致
				echo "アカウントを編集します。<br>";
			}

			if(!empty($name_edit) and empty($password_edit)){ //編集対象なしorパスワード不一致
				echo "エラー：ユーザー名またはパスワードが違います"."<br>";
			}


			//アカウント削除
			if(!empty($id_delete)){

				//カレンダー情報
				unlink("photodiary_id=".$id_delete.".php");

				$sql = "SELECT * FROM $tablename2 ORDER BY date";
				$stmt = $pdo -> query($sql);
				$results = $stmt -> fetchAll();
				foreach($results as $row){

					if($row['id'] == $id_delete){ //削除対象
						unlink($folder."/image.".$row['name']);
						unlink($folder."/thumb.".$row['name']);
						$sql = "DELETE FROM $tablename2 WHERE id=:id";
						$stmt = $pdo->prepare($sql);
						$stmt -> bindParam(':id',$row['id'],PDO::PARAM_INT);
						$stmt -> execute();
					}
				}

				//アカウント情報
				$sql = "SELECT * FROM $tablename ORDER BY id";
				$stmt = $pdo -> query($sql);
				$results = $stmt -> fetchAll();
				foreach($results as $row){

					if($row['id'] == $id_delete){ //削除対象
						$sql = "DELETE FROM $tablename WHERE id=:id";
						$stmt = $pdo->prepare($sql);
						$stmt -> bindParam(':id',$row['id'],PDO::PARAM_INT);
						$stmt -> execute();
					}
				}
				echo "アカウントを削除しました。<br>";
			}

			if(!empty($name_delete) and empty($id_delete)){ //削除対象なしorパスワード不一致
				echo "エラー：ユーザー名またはパスワードが違います。<br>";
			}

		?>

		<br>

        <h3>アカウント作成フォーム　<?php if(!empty($edit2)){echo "※　編集モード";} ?></h3>

		<form method="post" action="">
			ユーザー名:<br>
        	<input type="text" name="name" size="20" maxlength="20" value="<?php if(!empty($id_edit)){echo $name_edit;} ?>">
			<input type="hidden" name="id_edit_check" value="<?php if(!empty($id_edit)){echo $id_edit;}else{echo 'no';} ?>">
			<br>
			パスワード:<br>
        	<input type="password" name="password" size="20" maxlength="20" value="<?php if(!empty($id_edit)){echo $password_edit;} ?>">
			<br><br>
        	<input type="submit" value="作成する" >　<input type="reset" value="リセット" >
			<br>
        </form>
		※ユーザー名、パスワードは20文字以下で入力してください。<br>
		※ユーザー名に . は使えません。<br>


		<br>


		<h3>アカウント編集フォーム</h3>

		<form method="post" action="">
			ユーザー名:<br>
        	<input type="text" name="name_edit" size="20" maxlength="20">
			<br>
			パスワード:<br>
        	<input type="password" name="password_edit" size="20" maxlength="20">
			<br><br>
        	<input type="submit" value="編集する" >　<input type="reset" value="リセット" >
			<br>
        </form>


		<br>


		<h3>アカウント削除フォーム</h3>

		<form method="post" action="">
			ユーザー名:<br>
        	<input type="text" name="name_delete" size="20" maxlength="20">
			<br>
			パスワード:<br>
        	<input type="password" name="password_delete" size="20" maxlength="20">
			<br><br>
        	<input type="submit" value="削除する" >　<input type="reset" value="リセット" >
			<br>
        </form>

		<br><br>

		<A HREF = toppage.php>トップページ</A>

    </body>
</html>
<!DOCTYPE html>
<html>

    <head>

		<meta charset = "UTF-8">

		<title>TOP_PAGE</title>

	</head>

    <body>

        <h1>TOP_PAGE</h1>

		<?php

			$filename = "toppage.php"; //phpファイル名
			$tablename = "account"; //テーブル名

			//MySQLに接続
			$dsn = 'データベース名';
			$user = 'ユーザー名';
			$password = 'パスワード';
			$pdo = new PDO($dsn,$user,$password,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));


			//POSTログインデータ
			$name = $_POST['name']; //名前
			$password = $_POST['password']; //パスワード


			//名前とパスワードの一致確認
			$sql = "SELECT * FROM $tablename ORDER BY id";
			$stmt = $pdo -> query($sql);
			$results = $stmt -> fetchAll();

			if(!empty($name)) {

				foreach($results as $row){ //ユーザー名ありandパスワード一致

					if($row['name'] == $name and $row['password'] == $password){
						header("location: photodiary_id={$row['id']}.php");
					}
				}

				if(empty($judge)) { //ユーザー名なしorパスワード不一致
					echo "エラー：ユーザー名またはパスワードが違います。<br>";
				}
			}

		?>

		<br><br>

        <h3>ログインフォーム</h3>

		<form method="post" action="">
			ユーザー名:<br>
        	<input type="text" name="name" size="20" maxlength="20">
			<br>
			パスワード:<br>
        	<input type="password" name="password" size="20" maxlength="20">
			<br><br>
        	<input type="submit" value="ログインする" >　<input type="reset" value="リセット" >
			<br>
        </form>

		<br><br>

		<A HREF = account.php>アカウントページ</A>

    </body>
</html>
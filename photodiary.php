<!DOCTYPE html>
<html>

    <head>

		<meta charset = "UTF-8">

		<title>PHOTO_DIARY</title>

	</head>

    <body>

        <h1>PHOTO_DIARY</h1>

		<?php

			$filename = "photodiary.php"; //phpファイル名
			$tablename = "photodiary"; //テーブル名
			$tablename2 = "account"; //テーブル名(アカウント用)
			$folder = "files"; //画像フォルダ名

			//MySQLに接続
			$dsn = 'データベース名';
			$user = 'ユーザー名';
			$password = 'パスワード';
			$pdo = new PDO($dsn,$user,$password,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

			//テーブルの作成
			$sql = "CREATE TABLE IF NOT EXISTS $tablename" 
			."("."id INT NOT NULL," //ユーザーID
			."date INT NOT NULL," //日付
			."ext char(5) NOT NULL," //拡張子
			."size INT NOT NULL," //サイズ
			."name char(50) NOT NULL" .");";//ファイル名
			$stmt = $pdo -> query($sql);

			//ユーザー情報
			$id = 0;
				$sql = "SELECT * FROM $tablename2 ORDER BY id";
				$stmt = $pdo -> query($sql);
				$results = $stmt -> fetchAll();
				foreach($results as $row){
					if($row['id'] == $id){
						$username = $row['name']; //ユーザー名
					}
				}
			echo "<h3>[user] ".$username."</h3>";

			//FILEデータ
			$name_original = $_FILES['picture']['name']; //ファイル名
			$size = $_FILES['picture']['size']; //ファイルサイズ
			$tmp = $_FILES['picture']['tmp_name']; //テンポラリファイル名

			//POST送信データ
			$year_send = $_POST['year_send'];
			$month_send = $_POST['month_send'];
			$day_send = $_POST['day_send'];
			$date_send = sprintf('%04d',$year_send).sprintf('%02d',$month_send).sprintf('%02d',$day_send);
			$send = $_POST['date'];

			//POST削除データ
			$year_delete = $_POST['year_delete'];
			$month_delete = $_POST['month_delete'];
			$day_delete = $_POST['day_delete'];
			$date_delete = sprintf('%04d',$year_delete).sprintf('%02d',$month_delete).sprintf('%02d',$day_delete);

			//現在
			$year_now = date(Y);
			$month_now = date(m);
			$day_now = date(d);
			$date_now = date(Ymd);
			$month_now_int = (int)$month_now; //整数型(送信フォーム用)
			$day_now_int = (int)$day_now; //整数型(送信フォーム用)

			//POST送信の対象日
			if($send == "now"){ //今日
				$year = $year_now;
				$month = $month_now;
				$day = $day_now;
				$date = $date_now;
				$lastday = date('Ymd', mktime(0, 0, 0, $month_now+1, 0, $year_now)); //最終日
			}else{ //指定日
				$year = $year_send;
				$month = $month_send;
				$day = $day_send;
				$date = $date_send;
				$lastday = date('Ymd', mktime(0, 0, 0, $month_send+1, 0, $year_send)); //最終日
			}

			//変数の定義
			$ext = pathinfo($name_original,PATHINFO_EXTENSION); //拡張子
			$moji = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPUQRSTUVWXYZ';
			$random = substr(str_shuffle($moji), 0, 20);
			$name = $random.".".$ext; //ファイル名(random.ext)
			$image_Path = $folder."/image.".$name; //絶対パス
			$thumb_Path = $folder."/thumb.".$name; //絶対パス(サムネイル)

			//画像データをMySQLに挿入
			if (is_uploaded_file($tmp)){ //送信データが存在

				if($date<=$date_now and $date<=$lastday){ //日付が未来でないand存在する

					if($ext == "jpg" or $ext == "jpeg" or $ext == "gif" or $ext == "png"){ //拡張子が正しい

						if(move_uploaded_file($tmp,$image_Path)){ //データをフォルダへ移動
						    chmod($image_Path,0777); //権限変更777
							$edit = "no"; //編集データチェック用変数

							//サムネイル作成
							list($origin_w, $origin_h) = getimagesize($image_Path);

							$ratio = $origin_w / $origin_h;

							if($ratio>=1){ //横長
								$thumb_w = 200;
								$thumb_h = 200 / $ratio;
							}else{ //縦長
								$thumb_w = 200 * $ratio;
								$thumb_h = 200;
							}

							$thumb = imagecreatetruecolor($thumb_w, $thumb_h);

							if ($ext == "jpg" or $ext == "jpeg"){ //JPEG
							$origin = imagecreatefromjpeg($image_Path);
							imagecopyresized($thumb, $origin,0,0,0,0,$thumb_w,$thumb_h,$origin_w,$origin_h);
							imagejpeg($thumb,$thumb_Path);

							}elseif($ext == "gif"){ //GIF
							$origin = imagecreatefromgif($image_Path);
							imagecopyresized($thumb, $origin,0,0,0,0,$thumb_w,$thumb_h,$origin_w,$origin_h);
							imagegif($thumb,$thumb_Path);

							}elseif($ext == "png"){ //PNG
							$origin = imagecreatefrompng($image_Path);
							imagecopyresized($thumb, $origin,0,0,0,0,$thumb_w,$thumb_h,$origin_w,$origin_h);
							imagepng($thumb,$thumb_Path);
							}

							chmod($thumb_Path,0777); //権限変更777
							imagedestroy($origin); //データを廃棄
							imagedestroy($thumb);

							//データ編集
							$sql = "SELECT * FROM $tablename ORDER BY date";
							$stmt = $pdo -> query($sql);
							$results = $stmt -> fetchAll();
							foreach($results as $row){

								if($row['id'] == $id and $row['date'] == $date){ //編集対象
									unlink($folder."/image.".$row['name']);
									unlink($folder."/thumb.".$row['name']);
									$sql = "UPDATE $tablename SET ext=:ext,size=:size,name=:name WHERE date=:date";
									$stmt = $pdo -> prepare($sql);
									$stmt -> bindParam(':date',$row['date'],PDO::PARAM_INT);
									$stmt -> bindParam(':ext',$ext,PDO::PARAM_STR);
									$stmt -> bindParam(':size',$size,PDO::PARAM_INT);
									$stmt -> bindParam(':name',$name,PDO::PARAM_STR);
									$stmt -> execute();
									$edit = "yes";
									echo $year."年".$month."月".$day."日のファイルを更新しました。";
								}
							}

							//データ投稿
							if($edit == "no"){ //編集データなし
								$sql = "INSERT INTO $tablename (id,date,ext,size,name) VALUES (:id,:date,:ext,:size,:name)";
								$stmt = $pdo -> prepare($sql);
								$stmt -> bindParam(':id',$id,PDO::PARAM_INT);
								$stmt -> bindParam(':date',$date,PDO::PARAM_INT);
								$stmt -> bindParam(':ext',$ext,PDO::PARAM_STR);
								$stmt -> bindParam(':size',$size,PDO::PARAM_INT);
								$stmt -> bindParam(':name',$name,PDO::PARAM_STR);
								$stmt -> execute();
								echo $year."年".$month."月".$day."日のファイルを送信しました。";
							}

						}else{ //データをフォルダへ移動不可
							echo "エラー：ファイルを送信できません。";
						}
					}else{ //拡張子が正しくない
						echo "エラー：".$ext."形式のファイルは送信できません。";
					}
				}else{ //日付が未来or存在しない
					echo "エラー：指定した日付のファイルは送信はできません。";
				}

			}else{ //送信データが存在しない

				//データ削除
				if(!empty($year_delete)){
					$delete = "no"; //削除データチェック用変数
					$sql = "SELECT * FROM $tablename ORDER BY date";
					$stmt = $pdo -> query($sql);
					$results = $stmt -> fetchAll();
					foreach($results as $row){

						if($row['id'] == $id and $row['date'] == $date_delete){ //削除対象
							unlink($folder."/image.".$row['name']);
							unlink($folder."/thumb.".$row['name']);
							$sql = "DELETE FROM $tablename WHERE date=:date";
							$stmt = $pdo->prepare($sql);
							$stmt -> bindParam(':date',$row['date'],PDO::PARAM_INT);
							$stmt -> execute();
							$delete = "yes";
							echo $year_delete."年".$month_delete."月".$day_delete."日のファイルを削除しました。";
						}
					}

					if($delete == "no"){ //削除データなし
						echo "エラー：指定した日付のファイルがありません。";
					}
				}
			}



			//カレンダー用データ
			if(empty($_POST['year_ca'])){ //表示フォームなし
				$year_ca = $year_now;
				$month_ca = $month_now;

			}else{ //表示フォームあり
				$year_ca = $_POST['year_ca'];
				$month_ca = $_POST['month_ca'];
			}

			$month_ca_int = (int)$month_ca; //整数型(名前用配列のため)

			$lastday_ca = date('j', mktime(0, 0, 0, $month_ca+1, 0, $year_ca)); //最終日

			//名前用配列
			$month_name = array('','January','February','March','April','May','June','July','August','September','October','November','December'); //月名
			$week_name = array('Sun','Mon','Tue','Wed','Thu','Fri','Sat'); //曜日名

		?>

		<?php if($_SERVER["HTTP_REFERER"]=="http://ユーザー名/toppage.php" or $_SERVER["HTTP_REFERER"]=="http://ユーザー名/account.php" or $_SERVER["HTTP_REFERER"]=="http://ユーザー名/photodiary_id=$id.php"): ?>

		<h3>カレンダー表示フォーム</h3>
		<form method='POST' action=''>
			<select name='year_ca'>
		        <?php 
					for($year_select=2018; $year_select<=$year_now; $year_select++){
						if($year_select == $year_now){
							echo "<option selected> ".$year_select." </option>";
						}else{
							echo "<option> ".$year_select." </option>";
					}	}
				?>
			</select>年
			<select name='month_ca'>
		        <?php 
					for($month_select=1; $month_select<=12; $month_select++){
						if($month_select == $month_now_int){
							echo "<option selected> ".$month_select." </option>";
						}else{
							echo "<option> ".$month_select." </option>";
					}	}
				?>
			</select>月
			<br><br>
			<input type='submit' value='表示する'>
		</form>

		<?php
			echo "<h1> $year_ca <br> $month_name[$month_ca_int] </h1>";
		?>

		<table>

		    <tr>
		        <?php for($i=0; $i<=6; $i++): ?>
					<td><?php echo $week_name[$i]; ?></td>
				<?php endfor; ?>
		    </tr>
		 
		    <tr>

		<?php

			//カレンダー表示
			for($day_ca=1; $day_ca <= $lastday_ca; $day_ca ++){ //日
				$week_ca = date('w', mktime(0, 0, 0, $month_ca, $day_ca, $year_ca)); //曜日番号

				if($day_ca==1){

					for($before=1; $before<=$week_ca; $before++){ //一日より前
						echo "<td> </td>";
					}
				}

				$date_ca = sprintf('%04d',$year_ca).sprintf('%02d',$month_ca).sprintf('%02d',$day_ca); //カレンダー用日付
				$image_ca = "non"; //画像チェック用変数

				$sql = "SELECT * FROM $tablename ORDER BY date";
				$stmt = $pdo -> query($sql);
				$results = $stmt -> fetchAll();
				foreach($results as $row){
					if($row['id'] == $id and $row['date'] == $date_ca){ //画像チェック
						$image_ca = $folder."/image.".$row['name'];
						$thumb_ca = $folder."/thumb.".$row['name'];
					}
				}

				if($image_ca == "non"){ //画像が存在しない日
					echo "<td>".$day_ca."</td>";
				}else{ //画像が存在する日
					echo "<td><A HREF = $image_ca TARGET=_brank><IMG SRC = $thumb_ca WIDTH = 120 HEIGHT = 120></A></td>";
				}

				if($week_ca == 6){ //土曜日
					echo "</tr><tr>"; //行変更
				}

				if($day_ca == $lastday_ca){

					for($after=1; $after<=6-$week_ca; $after++){ //末日より後
						echo "<td> </td>";
					}
				}
			}

		?>

		    </tr>

		</table>

		<style type="text/css">

		table th,
		table td {
		    border: 1px solid #CCCCCC;
		    text-align: center;
		    width :120px;
			height : 120px;
		}

		</style>

		<br><br>

		<h3>画像投稿フォーム</h3>

		<form method="post" action="" enctype="multipart/form-data">
			画像ファイル:<br>
			<input type="file" name="picture" size=30>
			<br>
			送信する日付:<br>
			<input type="radio" name="date" value="now" checked> 現在
			<input type="radio" name="date" value="choice"> 指定する
			<br>
			<select name='year_send'>
		        <?php 
					for($year_select=2018; $year_select<=$year_now; $year_select++){
						if($year_select == $year_now){
							echo "<option selected> ".$year_select." </option>";
						}else{
							echo "<option> ".$year_select." </option>";
					}	}
				?>
			</select>年
			<select name='month_send'>
		        <?php 
					for($month_select=1; $month_select<=12; $month_select++){
						if($month_select == $month_now_int){
							echo "<option selected> ".$month_select." </option>";
						}else{
							echo "<option> ".$month_select." </option>";
					}	}
				?>
			</select>月
			<select name='day_send'>
		        <?php 
					for($day_select=1; $day_select<=31; $day_select++){
						if($day_select == $day_now_int){
							echo "<option selected> ".$day_select." </option>";
						}else{
							echo "<option> ".$day_select." </option>";
					}	}
				?>
			</select>日
			<br><br>
			<input type="submit" value="送信する">
		</form>
		※送信できるファイル形式はJPEG,GIF,PNGのみです。<br>
		※画像の形は正方形推奨です。<br>
		※日付の重複したデータは自動的に上書きされます。<br>
		※データを送信できる日付は今日より前のみです。

		<br><br>

		<h3>画像削除フォーム</h3>

		<form method="post" action="">
			削除する日付:<br>
			<select name='year_delete'>
		        <?php 
					for($year_select=2018; $year_select<=$year_now; $year_select++){
						if($year_select == $year_now){
							echo "<option selected> ".$year_select." </option>";
						}else{
							echo "<option> ".$year_select." </option>";
					}	}
				?>
			</select>年
			<select name='month_delete'>
		        <?php 
					for($month_select=1; $month_select<=12; $month_select++){
						if($month_select == $month_now_int){
							echo "<option selected> ".$month_select." </option>";
						}else{
							echo "<option> ".$month_select." </option>";
					}	}
				?>
			</select>月
			<select name='day_delete'>
		        <?php 
					for($day_select=1; $day_select<31; $day_select++){
						if($day_select == $day_now_int){
							echo "<option selected> ".$day_select." </option>";
						}else{
							echo "<option> ".$day_select." </option>";
					}	}
				?>
			</select>日
			<br><br>
			<input type="submit" value="削除する">
		</form>

		<?php else: ?>

		<h3>ログインしてください。</h3>

		<?php endif; ?>

		<br><br>

		<A HREF = toppage.php>トップページ</A><br><br>

		<A HREF = account.php>アカウントページ</A>

    </body>
</html>
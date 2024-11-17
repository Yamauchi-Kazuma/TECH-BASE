<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>掲示板</title>
    <style>
        /* body全体のスタイル */
        body {
            position: relative;
            margin: 0;
            padding: 0;
            overflow: hidden;
            overflow-y: auto; /* 縦方向のスクロール可能 */
        }

        /* 背景画像の設定 */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-image: url('dog.jpg'); /* 犬の画像を指定 */
            background-size: 100vw 100vh; /* 画面全体にフィット */
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.45; /* 透明度を調整*/
            z-index: -1; /* 背景画像が背面に配置されるように設定 */
        }
    </style>
</head>
<body>
    <?php
    //入力欄の初期値
    $namefirst='';
    $commentfirst='';
    $edit_id = ''; 
    $Pw = '';
    $passwordError = '';
    $deleteError = '';
    $editError = '';
    // DB接続設定（一応）
    $dsn = 'mysql:dbname=tb260322db;host=localhost';
    $user = 'tb-260322';
    $password = 'g6DutGypmg';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
     
    //テーブルを作成（一応）
    $sql = "CREATE TABLE IF NOT EXISTS tbtest"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name CHAR(32),"
    . "comment TEXT,"
    . "time DATETIME,"
    . "password CHAR(32)"  // パスワード用のカラム
    .");"; 
    
    $stmt = $pdo->query($sql);
    
    //コメントフォームの処理
    if (isset($_POST["submit1"])) {// フォームが送信されたか確認
        //データ未入力は弾く
        if (!empty($_POST["str1"]) && !empty($_POST["str2"]) && !empty($_POST["str5"])) {
            $name = $_POST["str1"]; 
            $comment = $_POST["str2"];
            $Pw = $_POST["str5"];
            $time = date("Y/m/d H:i:s");
            
            if (!empty($_POST["edit_id"])) { // 編集モードの場合
                $id = $_POST["edit_id"];
                $sql = 'UPDATE tbtest SET name=:name, comment=:comment, time=:time WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $comment, PDO::PARAM_STR); 
                $stmt->bindParam(':time', $time, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            } else { // 新規投稿モードの場合
                $sql = "INSERT INTO tbtest (name, comment, time, password) VALUES (:name, :comment, :time, :password)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $comment, PDO::PARAM_STR); 
                $stmt->bindParam(':time', $time, PDO::PARAM_STR);
                $stmt->bindParam(':password', $Pw, PDO::PARAM_STR);
                $stmt->execute();
            }
        }elseif(!empty($_POST["str1"]) && !empty($_POST["str2"]) && empty($_POST["str5"])) {
            $passwordError = "パスワードを設定してください";
        }
    }
    //削除フォームの処理
    if (isset($_POST["submit2"])) { // 削除フォームが送信されたか確認
        if (!empty($_POST["str3"]) && !empty($_POST["str5"])) { // パスワードが入力されているか確認
            $id = $_POST["str3"]; 
            $password_input = $_POST["str5"]; 
            
            // 投稿のパスワードを取得
            $sql = 'SELECT password FROM tbtest WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result && $result['password'] === $password_input) { // パスワードが正しいか確認
                $sql = 'DELETE FROM tbtest WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
                //実行
                $stmt->execute();
            }else {
                $deleteError = "パスワードが間違っています";
            }
        }
    }
    
    //編集フォームの処理
    if (isset($_POST["submit3"])) { // 編集フォームが送信されたか確認
        if (!empty($_POST["str4"]) && !empty($_POST["str5"])) {
            $id = $_POST["str4"]; 
            $password_input = $_POST["str5"];
            
             // 投稿のパスワードを取得
            $sql = 'SELECT password FROM tbtest WHERE id=:id'; 
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();

            if ($result && $result['password'] === $password_input) { // パスワードが正しいか確認
                $sql = 'SELECT * FROM tbtest WHERE id=:id'; 
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute(); 
                $results = $stmt->fetchAll();  

                // ループして、取得したデータを表示
                foreach ($results as $row) {
                    $namefirst = $row['name'];
                    $commentfirst = $row['comment'];
                    $edit_id = $row['id'];
                }   
            }else {
                $editError = "パスワードが間違っています";
            }   
        }
    }
    ?>
<h1 style="text-align: center;">掲示板
<form action="" method="post">
    <input type="text" name="str1" placeholder="名前" value="<?= $namefirst ?>">
    <input type="text" name="str2" placeholder="コメント" value="<?= $commentfirst ?>">
    <input type="hidden" name="edit_id" value="<?= $edit_id?>">
    <input type="text" name="str5" placeholder="新規投稿パスワード">
    <span style="color: red; font-size:16px;"><?= $passwordError ?></span>
    <input type="submit" name="submit1" value="送信">
</form>

<form action="" method="post">
    <input type="text" name="str3" placeholder="削除対象番号">
    <input type="text" name="str5" placeholder="削除投稿パスワード">
    <input type="submit" name="submit2" value="削除">
    <span style="color: red; font-size:16px;"><?= $deleteError ?></span>
</form>

<form action="" method="post">
    <input type="text" name="str4" placeholder="編集対象番号">
    <input type="text" name="str5" placeholder="編集投稿パスワード">
    <input type="submit" name="submit3" value="編集">
    <span style="color: red; font-size:16px;"><?= $editError ?></span>
</form><br></h1>

    <?php
        //SELECTでこれまでの投稿を表示
    $sql = 'SELECT * FROM tbtest';
    //query()メソッドでSQL文を実行し、結果を取得
    $stmt = $pdo->query($sql);
    //全ての結果を配列形式で取得
    $results = $stmt->fetchAll(); 
    //ループして、取得したデータを表示
    foreach ($results as $row){
        //$rowの中にテーブルのカラム名が入る
        // 投稿を囲むためのdiv
        echo '<div style="border: 3px solid #ccc; border-radius: 8px; padding: 10px; margin: 10px auto; width: 30%; background-color: rgba(255, 255, 255, 0.9);">'; 
        echo '<span style="font-size:16px;">'; // ここから小さい文字
        echo '&nbsp;'.'&nbsp;'.'&nbsp;'.'<span style="color:dimgray;">'.$row['id'].'&nbsp;:&nbsp;'; //投稿番号
        echo '<span style="color:green;">'.$row['name'].'</span>  '; //名前
        echo '<span style="color:dimgray;">: '.$row['time'].'&nbsp;<br>'; //投稿日時
        echo '&nbsp;&nbsp;&nbsp;'. $row['comment'].'</span>'; //コメント前にスペース
        echo '</span>'; // 小さい文字終了
        echo '</div>'; // 投稿を囲むdiv終了
    }
    ?>
    
</body>
</html>
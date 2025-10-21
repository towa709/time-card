## プロジェクト概要
このプロジェクトは Laravel を用いた勤怠管理アプリです。  
一般ユーザーは会員登録・ログイン後に出勤/退勤/休憩の打刻を行うことができ、  
管理者は承認・修正などを行えます。  

## 環境構築
**Dockerビルド**
1. `git clone git@github.com:towa709/time-card.git`
2. `cd time_card`
3. DockerDesktopアプリを立ち上げる
4. `docker-compose up -d --build`

 上記の手順は任意の作業ディレクトリで実行可能です。  
   例: Linux/WSL 環境では `/home/ユーザー名/coachtech/time-card`、  
   Windows 環境では `C:\Users\ユーザー名\coachtech\time-card` など。

**Laravel環境構築**
1. `docker-compose exec php bash`
2. `composer install`
3. '.env.example'ファイルを コピーして'.env'を作成し、DBの設定を変更
4. `cp .env.example .env`

**注意**
初回ビルド及び.envコピー後、`src/` ディレクトリが root 権限になりますので、以下を必ずプロジェクトのルートディレクトリで実行して権限を修正してから保存してください。  
```bash
sudo chown -R $(whoami):$(whoami) .
```
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

MAIL_FROM_ADDRESS=example@test.com
MAIL_FROM_NAME="time-card App"

```

5. アプリケーションキーの作成
``` bash
docker-compose exec php bash
php artisan key:generate
```

6. マイグレーションの実行
``` bash
docker-compose exec php bash
php artisan migrate --seed
```
※これでマイグレーションとデータ投入は完了です

---

### ⚠️ トラブルシューティング：初回アクセス時に「419 Page Expired」が表示される場合

環境構築直後に http://localhost/login へアクセスすると、
セッション関連の不整合により「419 | Page Expired」が表示される場合があります。

その場合は、以下のコマンドを実行して修正してください。
``` bash
docker-compose exec php bash
php artisan session:table
php artisan migrate
php artisan config:clear
php artisan cache:clear
php artisan view:clear
chmod -R 777 storage bootstrap/cache
exit
```
また、💡 .env 設定確認（参考）

以下の設定が含まれていることを確認してください：
``` text
SESSION_DRIVER=database
SESSION_DOMAIN=localhost
```

変更した場合は必ず設定を再読み込みしてください。
```bash
php artisan config:clear
```

7.  アクセス時に Permission denied エラーが出る場合は以下を実行してください。（http://localhost）
```bash
docker-compose exec php bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

8. テスト用データベースの作成  
テストは `laravel_test_db` データベースを使用します。  
初回のみ以下を実行して DB を作成してください。

```bash
docker-compose exec mysql bash
mysql -u root -p
```

MySQL コンソールに入ったら以下を入力：
```bash
CREATE DATABASE laravel_test_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; 
EXIT;
```
これでテスト用 DB が準備されます。

9. テストの実行
```bash
php artisan test --env=testing
```


### 実施内容

⚠️ キャッシュに関する注意

同じ PC で フリマアプリ と 勤怠アプリ を切り替えて利用する場合、
ブラウザのキャッシュが残って以前の CSS が反映されることがあります。
その際は Ctrl + Shift + R（スーパーリロード） を実行してください。

## ER図

![ER図](docs/er-diagram-v2.png)

## URL
- 開発環境：http://localhost
- phpMyAdmin: http://localhost:8080
- MailHog: http://localhost:8025

## 使用技術
- Laravel 12
- PHP 8.2
- MySQL 8.0
- Docker / docker-compose
- Nginx
- MailHog
- phpMyAdmin

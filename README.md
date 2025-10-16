# Happy — ポジティブ習慣 & 辞めた癖トラッカー

**Happy** は、Laravel 12 をベースにしたアプリケーションで、禁煙・禁酒・毎日歩くなどの習慣を管理するツールです。  
UI は **Livewire + DaisyUI** を使っています。

---

## 🚀 主な機能

- 習慣（ポジティブ or 辞めた癖）を追加・停止・再開
- **連続日数（streak）** の自動計算
- **節約金額** の計算（習慣が金額に紐づく場合）
- カレンダー表示：成功日（緑）、停止日（赤）、今日のハイライト
- 多言語対応（日本語・英語・フランス語）
- 管理画面でユーザー／習慣／期間の CRUD 操作

---

## 🧱 技術スタック

| 技術 | 内容 |
|------|------|
| Laravel 12.x | フレームワーク本体 |
| PHP 8.2 以上 | 必要な拡張：zip, intl, fileinfo |
| Livewire 3 | フロントの反応性 UI |
| Tailwind CSS + DaisyUI | UI デザイン、テーマ |
| SQLite / MySQL | DB（環境により） |

---

## ⚙ インストール手順

1. リポジトリをクローン
    ```
    git clone https://github.com/QuentinKeravec/happy.git
    cd happy
    ```

2. PHP 依存ライブラリをインストール
    ```
    composer install
    ```
   **注意**：拡張 `zip`、`intl`、`fileinfo` を有効化しておくこと。

3. フロントエンド依存をインストール
    ```
    npm install
    npm run dev
    ```

4. 環境設定
    ```
    cp .env.example .env
    php artisan key:generate
    ```

5. マイグレーション & シーディング
    ```
    php artisan migrate
    php artisan db:seed
    ```

6. 管理者ユーザー設定  
   users テーブルに `is_admin` カラムを追加  
   Tinker で自分のユーザーを管理者に設定：
    ```php
    \App\Models\User::where('email','あなたのメール')->update(['is_admin'=>true]);
    ```

---

## 💻 起動方法

```bash
php artisan serve
npm run dev

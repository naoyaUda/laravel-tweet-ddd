# Laravel-tweet-ddd

-   twitter 風アプリケーションを DDD(ドメイン駆動設計)のアーキテクチャに乗せて実装するプロジェクト

<p align="center">
<a href="https://github.com/naoyaUda/laravel-tweet-ddd/actions"><img src="https://github.com/naoyaUda/laravel-tweet-ddd/workflows/build-and-test/badge.svg" alt="Build Status"></a>
<a href="https://github.com/naoyaUda/laravel-tweet-ddd/actions"><img src="https://github.com/naoyaUda/laravel-tweet-ddd/workflows/deploy/badge.svg" alt="Build Status"></a>
<a href="https://dependabot.com"><img src="https://api.dependabot.com/badges/status?host=github&repo=naoyaUda/laravel-tweet-ddd" alt="Deppendabot Status"></a>
</p>

## Overview

-   DDD のコンテキストによる実装
-   オーバーエンジニアリングを許容する
    -   あくまで個人的な技術アウトプットの場のため

## Environments

-   Laravel 7.x
-   PHP 7.4.4
-   Mysql 5.7
-   Redis 5.0.7
-   MinIO
    -   S3 の Mock Storage として使用
-   Docker
-   node.js(asset ビルド)
    -   パッケージ管理: yarn

## Infrastructure

-   このアプリケーションのインフラ構成は[こちらのリポジトリ](https://github.com/naoyaUda/laravel-tweet-ddd-infrastructure)で terraform で管理しています

## Architecture

### Onion Architecture

#### Architecture Overview

<p align="center">
    <img width="55%" src="https://user-images.githubusercontent.com/43739514/76864285-30bf7380-68a4-11ea-8003-76ee28a99a1e.png">
</p>

#### Workflow sequence

-   e.g. Create New Entity
    ![LayerSequence](https://user-images.githubusercontent.com/43739514/76810548-db4a7e80-6831-11ea-9fed-71350d1e0140.png)

### Directory tree

```text
├── domain // Pure Domain Knowledge
│   ├── Base // Base Abstract Classes
│   ├── Application // Application Utility services
│   │   └── Contract
|   |       ├── Uuid
|   |       └── Transaction
│   ├── Model // Domain Model layer
│   │   ├── Entity
|   |   |   ├── Base
|   |   |   └── Tweet
│   │   ├── ValueObject
|   |   |   ├── Base
|   |   |   └── Tweet
│   │   └── DTO
|   |       ├── Base
|   |       └── Tweet
│   ├── Query // Belongs to ApplicationService, Abstract CQRS Query, not included concrete implementation
│   ├── Repository // Belongs to ApplicationService layer, not included concrete implementation
│   │   └── Contract
|   |       └── Tweet
│   └── UseCase // Belongs to ApplicationService layer, Accomplish use-case
|── infrastructure // Concrete Implementations. Should implement ApplicationService interface e.g RDBMS, HTTP Clients...
│   ├── Application // Concrete Utility Application services
|   |   ├── Uuid
|   |   └── Transaction
│   ├── Query // Concrete CQRS Query
|   └── Repository // Concrete Repository
|       ├── Base
│       │   └── InMemoryRepository // for testing
|       └── Tweet
├── app // Laravel app
│   ├── Console
│   ├── Eloquent
│   ├── Exceptions
│   ├── Http
│   │   ├── Actions // For ADR pattern
│   │   │   ├── Frontend
│   │   │   └── Backend
│   │   ├── Responders // For ADR pattern
│   │   │   ├── Frontend
│   │   │   └── Backend
│   │   └── Middleware
│   ├── Providers
│   └── View
│       └── Components
│           └── Partial
├── bootstrap
│   └── cache
├── config
├── database
│   ├── factories
│   ├── migrations
│   └── seeds
├── docker // configs with using docker-compose
│   ├── mysql
│   │   └── init
│   ├── nginx
│   └── php
├── docs
│   └── architecture
├── public
├── resources
│   ├── js
│   │   └── components
│   ├── lang
│   │   └── en
│   ├── sass
│   └── views
│       ├── components
│       │   └── partial
│       ├── frontend
│       ├── backend
│       └── layouts
├── routes
├── storage
│   ├── app
│   │   └── public
│   ├── framework
│   └── logs
└── tests
    ├── Browser // for E2E
    │   ├── Frontend
    │   ├── Backend
    │   ├── Pages
    │   ├── console
    │   └── screenshots
    ├── Helper // test helpers
    │   ├── Domain
    │   └── Utils
    ├── Feature
    └── Unit
```

### ADR

-   [Link](http://pmjones.io/adr/)

### Testing Architecture

-   Unit Test と E2E Test を用いて動作を担保する。
    -   Unit Test: phpunit
    -   E2E Test: Laravel Dusk
-   全体のテスト設計比率としては、 `UnitTest:E2E = 8:2`程度を想定する。
    -   実装依存する E2E テストは壊れやすい為、比率を少なくしている。

#### Unit Test Policy

-   UseCase 単位 = 1Action に対する test を実装する。
    -   test 自体が Entity、ValueObject、DomainService が持つドメイン知識のドキュメントになる。
    -   UseCase 程度の粒度に対して動作を担保すれば、test 設計コストに対する coverage を最大化できると判断している。
    -   また、特定のロジックに対して細かく test を書きすぎると、コード全体の保守性が下がっていく為。
-   純粋な Domain 知識に対して test を行う為、infrastructure を全て隠蔽し、各 interface に対して test を行う。
    -   Laravel の DI コンテナを利用して、entity の永続化先を全て InMemory に差し替えることで上記を解決する。
    -   Testing 用 DB の存在を意識せずに、test 実装が可能になる。
    -   参考 `infrastructure/Repository/Base/InMemoryRepository.php`

```php
class InMemoryTweetRepository extends InMemoryRepository implements TweetRepository
{
    /**
     * save Tweet entity.
     *
     * @param Tweet $tweet
     * @return void
     */
    public function save(Tweet $tweet): void
    {
        $this->saveInMemory($tweet);
    }
}
```

#### E2E Test Policy

-   infrastructure レベルで期待する動作が担保されているかを検査する。
    -   e.g. RDBMS, S3, Redis, etc...
    -   unit test で抽象化された Domain を検査済みの為、具象レベルでの担保はこちらで行う。

## Installation

-   docker を予めインストールしてください。

-   clone

```bash
git clone https://github.com/naoyaUda/laravel-tweet-ddd.git
```

-   install

```bash
# コンテナのビルド、依存パッケージのインストール、DBの初期化を行う
make install

# permission変更
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

## How to

-   各種操作コマンドは Makefile に記述しています。以下コマンドですべてのターゲットを確認できます。

```bash
make help
```

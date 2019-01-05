=== wp-tmkm-amazon ===
Contributors: mtdesigninfo
Tags: amazon, affiliate
Requires at least: 3.0.1
Tested up to: 3.5
Stable tag: 1.5.2
License: MIT License

wp-tmkm-amazon is plug-in which generates the affiliate code of an Amazon associate easily.  

== Description ==

amazon.co.jpの商品へのリンクを記事中に簡単に貼れるプラグインです。
「amazonアソシエイトプログラム」にも対応しています。

※ともかめ様製作の"wp-tmkm-amazon"をメンテナンスしています。

# ECS4.0 に対応しています。
# PHP4.x 以上で動作します。ただし Keith Devens.com の PHP XML Library が必要（ 同梱しています ）。
# [tmkm-amazon]ASIN[/tmkm-amazon] または <?php tmkm_amazon_view('ASIN'); ?> という記述で動作します。
# LGPL で提供されている Lite.php および Open Source License で提供されている xml.php を同梱しています。
# 記事およびページ投稿／編集画面での Amazon 検索が可能です。

== Installation ==

# 1. ダウンロードした zip ファイルを解凍します。
     フォルダ内の"wp-tmkmk-amazon-function.php"ファイルにキャッシュフォルダの指定があります。
     ご自身の環境に合わせてキャッシュフォルダを指定してください(どうやら絶対アドレスでないとダメっぽい)。
     デフォルトは"/tmp/"です。
# 2. wp-tmkm-amazon フォルダを wp-content/plugins フォルダに転送します。
# 3. 管理画面から wp-tmkm-amazon を有効化します。
# 4. 管理画面にある「設定」画面内の「Wp_Tmkm_Amazon」メニューで、ご自分の
     アソシエイトID / Access Key ID / Secret Access Key
     を入力し、保存します。
     そのほか管理画面では、リンクウィンドウの挙動や詳細表示を設定できます。
# 5. 各テーマの php ファイル、もしくは記事本文中に以下を記載します。
　　　PHP 関数として呼び出す場合	...	<?php tmkm_amazon_view('ASIN'); ?>　：テーマファイルに記述
　　　記事本文中にコードを書く場合	...	[tmkm-amazon]ASIN[/tmkm-amazon]
　　　記事本文中の場合、入力画面の下の方に検索窓が表示されています。
　　　そこに掲載したい商品名を記述して検索すると商品写真と上記タグが表示されますので、そのタグをコピーし、
　　　本文中へペーストしてください。ペーストされた位置に商品が表示されます。

## 記事本文中で PHP コードを実行できるプラグインを導入していれば、PHP 関数として呼び出すこともできます。
## 書籍の場合、ASIN に 10 桁および 13 桁の ISBN を使用できます。
## 同梱の amazon_noimg.png と amazon_noimg_small.png を差し替えれば、商品画像がないときの代替画像を好きなものにできます。

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

2013/7/21
　PHP 5.4で動作しないことがあったため、修正
2013/7/17
　WordPress Plugin Directory公開用にドキュメントを整備し、公開
2012/12/5
　"HOME画面に適用するか"オプションが誤動作していたため、修正
2012/12/1
　商品選択画面で続けて違う商品が検索できないバグを修正
　商品選択画面で「次ページ」「前ページ」機能が動作していなかったのを修正
2012/11/27
　動作していなかったキャッシュ機能を修正
2011/9/5
　トップページの表示速度が遅くなる場合があるので、オプション対応にて修正
　※オプションに"HOME画面で適用するかどうか"の項目を追加
2009/8/22
  カテゴリを指定して日本語で検索した場合、検索結果が表示されなかったのを修正
2009/7/3
  Access Key ID, Secret Access Keyを管理画面から入力出来るように修正
  Thanks : みやび / hiromasa.zone :o)
2009/6/18
  改変版公開
  Amazon Web Serviceの利用方法改定に伴う変更

== Upgrade Notice ==

== Arbitrary section ==

Special Thanks: Keith Devens.com (http://keithdevens.com/software/phpxml)
Special Thanks: websitepublisher.net (http://www.websitepublisher.net/article/aws-php/)
Special Thanks: hiromasa.zone :o) (http://hiromasa.zone.ne.jp/)
Special Thanks: みやび ( http://shinonon-web.net/ )
Special Thanks: PEAR :: Package :: Cache_Lite (http://pear.php.net/package/Cache_Lite)

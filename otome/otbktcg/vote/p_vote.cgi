#!/usr/local/bin/perl

require './jcode.pl';

#--------------------------------------
$ver="PluralVote v5.1";# (投票システム)
#--------------------------------------
# Copyright(C) りゅういち
# E-Mail:ryu@cj-c.com
# W W W :http://www.cj-c.com/
#--------------------------------------

#--- 初期設定 ------------------*

# 同じようにいくつでも増やせます。
# [ ]内の数字を使いCGIにアクセスするとその設定ファイルで動作します。
# $set[12] の設定ファイルを使う場合: http://www.xxx.com/cgi-bin/p_vote.cgi?no=12
$set[0]="./set.cgi";
$set[1]="./set1.cgi";

# 禁止文字列 タグ使用の場合は禁止タグも入力OK 同じようにいくつでも指定可能
@NW=('死ね','<html>','<script');

#--- 設定ここまで---------------*
&time_;
&d_code_;
if($no eq ""){$no=0;}
if($set[$no]){unless(-e $set[$no]){&er_('設定ファイルが無いです!');}else{require"$set[$no]";}}
else{&er_('設定ファイルがCGIに設定されてません!');}
$nf="<input type=hidden name=no value=$no>\n";
if($mode eq "all") { &all_; }
if($mode eq "vote"){&right_;}
if($mode eq "edit"){ &edit_;}
if($mode eq "hen") { &hen_; }
if($mode eq "h_w") { &h_w_; }
if($mode eq "s_d") { &s_d_; }
if($mode eq "man") { &man_; }
&html_;

#
# [トップページ]
#
sub html_ {
unless(-e $log){&l_m($log);}
open(DB,"$log") || &er_("Can't open $log");
@lines = <DB>;
close(DB);

&hed_;
print "<center>\n";
if($t_img eq ""){print"<font color=$tcolor face=\"$tface\" size=$tsize>$title</font>\n";}
elsif ($t_img ne "") {print "<img src=\"$t_img\" width=$twid height=$thei>\n";}

$total=0;
foreach $line (@lines) {
	($namber,$vote,$count) = split(/<>/,$line);
	$total+=$count;
	$count{$vote} = $count;
	$namb{$vote}  = $namber;
}
print <<"_HTML_";
<hr width=80%>
□<a href="$backurl"> HOME</a>　
□<a href="$cgi_f?mode=man&no=$no"> HELP</a>
<hr width=80%><table><tr><td>$com
</td></tr></table><hr width=80%>
<form action=$cgi_f method=$met>$date現在の総投票数: <b>$total</b><br>
<input type=hidden name=mode value="vote">$nf
<table border=0><tr bgcolor=$ttb>
<th>ランク</th><th>投票</th><th>項目</th><th>投票数</th><th>グラフ</th></tr>
_HTML_
if($rorc){$rcbox="checkbox";}else{$rcbox="radio";}
$rank1=0; $rank2=1; $count_tmp=0; $K=0;
foreach (sort { ($count{$b} <=> $count{$a}) || ($a cmp $b)} keys(%count)) {
	($count{$_} == $count_tmp) || ($rank1 = $rank2);
	if($total > 0){
		$point{$_}=($count{$_}/$total)*100;
		$point{$_}=sprintf("%2.1f",$point{$_});
		if($rank1==1){$bwid=$point{$_};$bwid{$_}=$kw;}
		else{$bwid{$_}=int(($point{$_}*$kw)/$bwid);}
	}else{$point{$_}="0";}
	if(($max_v && $total >= $max_v)||($p_co==1 && $cooks==3)||($max_d && $DATE>=$max_d)){
	$r_b="<br>";$s_c="<br>";$endcom="<h3>投票は終了しました。</h3>";
	}else{
	$r_b="<input type=$rcbox name=namber value=\"$namb{$_}\">";
	$s_c="<input type=submit value='チェック項目に投票'>";
	}
	if($K){$COL="$str";$K=0;}else{$COL="$bg";$K=1;}
print <<"_HTML_";
<tr bgcolor=$COL><th>$rank1位</th><th>$r_b</th>
<td align=center> $_ </td><th>$count{$_}</th>
<td><img src="$bar" width="$bwid{$_}" height="$bhei">
<small>$point{$_}\%</small></td></tr>
_HTML_
	$count_tmp = $count{$_};
	$rank2++;
	}
if($tag){$tac="可";}else{$tac="不可";}
if($edit == 1){
	print "<tr><th colspan=5>$s_c</th></tr></table></form>";
	if(($max_v && $total >= $max_v)||($p_co==1 && $cooks==3)||($max_d && $DATE>=$max_d)){
	print"<br>";
	}else{
	print <<"_HTML_";
<form action=$cgi_f method=$met>$nf
項目追加(最高$max個まで/タグ$tac)<br>
<input type=hidden name=mode value="vote">
<input type=text name="vote" size=15 maxlength=$vmax>
<input type=submit value="項目追加"></form>
_HTML_
	}
}elsif($edit == 0){print "<tr><th colspan=5>$s_c</th></tr></table></form>\n";}
print "$endcom\n";
print <<"_HTML_";
<br><div align=right>
<form action=$cgi_f method=$met><input type=hidden name=mode value="edit">$nf
<input type=password name="pass" size=8><input type=submit value="管理用"></form></div>
_HTML_

&foot_;
}
#
# [マニュアル]
#
sub man_ {
if($edit==1){
	$ecom="だれでも自由に増やせます。\n";$eco="追加できます。\n";
	$ec="<li>関係ない投票項目は\予\告\なく\削\除\されます。</li>\n";
	$e ="<li><b>半角カナは使用禁止</b>。文字化けの原因になります。</li>\n";
}else{$ecom="追加できません。\n";$eco="増える\可\能\性があります。\n";}
if($cooks==1){$comc="2重投票できません。\n";}
elsif($cooks==0){$comc="2重投票できますが、\n";}
elsif($cooks==2){$comc="同じ項目への連続投票はできません。\n";}
&hed_;
print <<"_HTML_";
□<a href="$cgi_f?no=$no">BACK</a>
<center><table width=90\%><tr><th bgcolor="$ttb">$title の使い方</th></tr></table>
<table width=65% align=center bgcolor=$k_back><tr><td><ul type="square">
<li>このランクシステム(以下VOTES)は投票したい項目をチェックしボタンを押し投票します。</li>
<li>このVOTESは投票項目を<b>$ecom</b></li>
<li>投票項目は<b>最大$maxコ</b>まで$eco</li>
$ec$e
<li>このVOTESは、<b>$comc</b>モラルある投票をしましょう。</li>
</ul></td></tr></table></center>
_HTML_
&foot_;
}
#
# [ヘッダ表示]
#
sub hed_ {
print "Content-type: text/html\n\n";
print <<"_HTML_";
<html><head>
<STYLE TYPE="text/css">
<!--
A:link   { text-decoration:none; }
A:visited{ text-decoration:none; }
A:hover  { color:$ie_c; text-decoration:underline; }
BODY,TD,TH{ font-family:"$k_font"; font-size:$k_size; }
-->
</STYLE>
<META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=Shift_JIS">
<title>$title</title>
</head><!--$ver-->
_HTML_
print "<body text=$text link=$link vlink=$vlink bgcolor=$bg";
if ($back ne "") { print " background=\"$back\">";} elsif ($back eq "") { print ">";}
print <<"_HEAD_";
<!--ヘッダ広告タグ挿入位置▽-->

<!--△ここまで-->
_HEAD_
}
#
# [フッタ表示]
#
sub foot_ {
print <<"_HTML_";
<!--著作権表\示 削除不可-->
<hr width=90\%>
<center>- <a href="http://www.cj-c.com/" target=_top>Plural Vote</a> -</center>
<!--フッタ広告タグ挿入位置▽-->

<!--△ここまで-->
_HTML_
	print "</body></html>\n";
	exit;
}
#
# [フォームデコード]
#
sub d_code_ {
if ($ENV{'REQUEST_METHOD'} eq "POST") {read(STDIN, $buffer, $ENV{'CONTENT_LENGTH'});}
else { $buffer = $ENV{'QUERY_STRING'}; }

@pairs = split(/&/,$buffer);
foreach $pair (@pairs) {
	($name, $value) = split(/=/, $pair);
	$value =~ tr/+/ /;
	$value =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;
	&jcode'convert(*value,'sjis');

	$value =~ s/</\&lt\;/g;
	$value =~ s/>/\&gt\;/g;
	$value =~ s/\"/\&quot\;/g;
	$value =~ s/<>/\&lt\;\&gt\;/g;
	$value =~ s/<!--(.|\n)*-->//g;
foreach(0..$#NW){if(index($value,$NW[$_]) >= 0){&er_("「$NW[$_]」は使用できません!");}}
	$FORM{$name} = $value;
	if($name eq 'namber'){ push(@v_,$value); }
	if ($name eq 'p_co') { push(@d_,$value); }
}
$vote = $FORM{'vote'};
$namber=$FORM{'namber'};
$mode = $FORM{'mode'};
$cook = $FORM{'cook'};
$p_co = $FORM{'p_co'};
$no   = $FORM{'no'};
}
#
# [書き込み処理]
#
sub right_ {
$OK=0;
if($FORM{'pass'} eq $pass){$OK=1;}
if($cooks==1 && $OK==0){&get_; if($cook){&er_("投票は一回のみです!");}}
$addr = $ENV{'REMOTE_ADDR'};
if ($lock == 1) {$lockfile = "$lock1"; &lock_;}

open(DB,"$log") || &er_("Can't open $log");
@lines = <DB>;
close(DB);

if($tag){
	$vote=~ s/\&quot\;/\"/g;
	$vote=~ s/\&lt\;/</g;
	$vote=~ s/\&gt\;/>/g;
}
@new = ();
$k = 0;
foreach $line (@lines) {
	$line =~ s/\n//g;
	($nam,$votes,$count,$ip) = split(/<>/,$line);
	$ip =~ s/\n//g;
	foreach $namber (@v_) {
		if ($nam eq "$namber") {
		if($cooks==2 && $OK==0){if($addr eq "$ip"){ &er_("連続投票不可!"); }}
		$count++;
		$line = "$nam<>$votes<>$count<>$addr<>";
		$k = 1;
		}
	}
if ($vote eq "$votes") { &er_("同一項目名が存在します！"); }
push (@new,"$line\n");
}
if($k == 0){ 
@lines = reverse(@lines);
($nam0,$vo0,$cnt0,$ip0) = split(/<>/,$lines[0]);
if($nam0 eq "") { $namber=0; }
$namber = $nam0 + 1; $LINE=@lines;
if($LINE > $max){&er_("これ以上項目を増やせません！");}
if($vote eq ""){ &er_("項目名がありません!"); }
if($OK){$C=0;}else{$C=1;}
push (@new,"$namber<>$vote<>$C<>$addr<>\n"); 
}

open (DB,">$log");
print DB @new;
close(DB);

if(-e $lockfile){ unlink($lockfile); }
if($cooks==1 && $OK==0){ &set_; }
if($FORM{'pass'} eq "$pass"){ &edit_; }
if($cooks==3){$p_co=1;}
}
#
# [ファイルロック]
#
sub lock_ {
$k = 0;
foreach (1 .. 5) {
	unless (-e $lockfile) {
	open(LOCK,">$lockfile");
	close(LOCK);
	$k = 1;
	last;
	} else {sleep(1);}
}
if ($k == 0) { &er_("LOCK is BUSY"); }
}
#
# [時刻の取得]
#
sub time_ {
	$ENV{'TZ'} = "JST-9";
	($sec,$min,$hour,$mday,$mon,$year,$wday) = localtime(time);
	$year=$year+1900;
	$mon++;
	if ($mon  < 10) { $mon  = "0$mon";  }
	if ($mday < 10) { $mday = "0$mday"; }
	if ($hour < 10) { $hour = "0$hour"; }
	if ($min  < 10) { $min  = "0$min";  }
	if ($sec  < 10) { $sec  = "0$sec";  }
	$week = ('日','月','火','水','木','金','土') [$wday];
$date="$year\/$mon\/$mday\($week\) $hour\:$min\:$sec";
$DATE="$year$mon$mday";
}
#
# [クッキー発行]
#
sub set_ { 
($secg,$ming,$hourg,$mdayg,$mong,$yearg,$wdayg,$ydayg,$isdstg) = gmtime(time + $cday*24*60*60);
	$yearg += 1900;
	if ($secg  < 10) { $secg  = "0$secg";  }
	if ($ming  < 10) { $ming  = "0$ming";  }
	if ($hourg < 10) { $hourg = "0$hourg"; }
	if ($mdayg < 10) { $mdayg = "0$mdayg"; }
$month = ('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec')[$mong];
$youbi = ('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')[$wdayg];
$date_gmt = "$youbi, $mdayg\-$month\-$yearg $hourg:$ming:$secg GMT";
$cook="cook\:1";
print "Set-Cookie: $CN=$cook; expires=$date_gmt\n";
}
#
# [クッキー取得]
#
sub get_ { 
	$cookies = $ENV{'HTTP_COOKIE'};
	@pairs = split(/;/,$cookies);
	foreach $pair (@pairs) {
		($name, $value) = split(/=/, $pair);
		$name =~ s/ //g;
		$DUMMY{$name} = $value;
	}
	@pairs = split(/,/,$DUMMY{"$CN"});
	foreach $pair (@pairs) {
		($name, $value) = split(/:/, $pair);
		$COOKIE{$name} = $value;
	}
	$cook = $COOKIE{'cook'};

	if ($FORM{'cook'}) { $cook = $FORM{'cook'}; }
}
#
# [管理画面]
#
sub edit_ {
if ($FORM{'pass'} ne "$pass") { &er_("パスワードが違います!"); }
if ($p_co) {
open(DB,"$log");
@lines = <DB>;
close(DB);
@COMS=@lines;
@CAS = ();
	foreach $COMS (@COMS) {
	$COMS =~ s/\n//g;
	($nam,$vo,$cou,$ip) = split(/<>/,$COMS);
		foreach $p_co (@d_) {if ($p_co eq $nam) {$COMS = "";}}
		if($COMS eq ""){ $n=""; }else{ $n="\n"; }
		push (@CAS,"$COMS$n");
	}
open (DB,">$log");
print DB @CAS;
close(DB);
}
open(DB,"$log");
@lines = <DB>;
close(DB);
if (-s $log) {$size = -s $log;} else {$size=0;}
&hed_;
print <<"_HTML_";
□<a href="$cgi_f?no=$no"> BACK</a>
<center><table width=90\%><tr><th bgcolor="$ttb">管理モード</th></tr></table>
現在のログサイズ：$size バイト
<table><tr><td>
□ 削除したい項目にチェックを入れ「削 除」を押して下さい。<br>
□ ナンバーをクリックするとその項目について編集できます。
</td></tr></table>
<form action="$cgi_f" method=$met>$nf
<input type=hidden name=mode value="edit">
<input type=hidden name=pass value="$FORM{'pass'}">
<table border=1><tr bgcolor=$ttb>
<th>チェック</th><th>ナンバー</th><th>項目名</th><th>カウント</th></tr>
_HTML_

foreach $line (@lines) {
($namber,$vote,$count,$ip) = split(/<>/,$line);
print <<"_HTML_";
<tr><th><input type=checkbox name=p_co value="$namber"></th>
<th><a href="$cgi_f?mode=hen&namber=$namber&pass=$FORM{'pass'}&no=$no">No.$namber</a></th>
<th>$vote</th><th>$count</th></tr>
_HTML_
}
print <<"_HTML_";
</table><br>
<input type=submit value="削 除">
<input type=reset value="リセット">
</form><br>
<hr width=90%><form action=$cgi_f method=$met>$nfログの初期化
<input type=hidden name=pass value="$FORM{'pass'}">
<input type=hidden name=mode value="s_d"><input type=submit value="初期化">
</form>
_HTML_
if($namber eq ""){ $namber=0; }
if($tag){$tac="可";}else{$tac="不可";}
$namber=$namber+1;
print <<"_HTML_";
<hr width=90\%><form action=$cgi_f method=$met>$nf
・ここから追加すれば連続投票扱いになりません。<br><br>
項目を増やす事ができます。(最高$max個まで/タグ$tac)<br>
<input type=hidden name=mode value="vote">
<input type=text name="vote" size=15 maxlength=$vmax>
<input type=hidden name=namber value="$namber">
<input type=hidden name=pass value="$FORM{'pass'}">
<input type=submit value="項目追加"></form>
_HTML_
print "</center>\n";
&foot_;
}
#
# [記事編集]
#
sub hen_ {
if ($FORM{'pass'} ne "$pass") { &er_("パスワードが違います!"); }
open(DB,"$log");
@lines = <DB>;
close(DB);

@new = ();
$flag = 0;
foreach $line (@lines) {
($nam,$vo,$co,$ip) = split(/<>/,$line);
$ip =~ s/\n//g;
	if ($namber eq "$nam") {
		if($tag){
		$vo=~ s/\"/\&quot\;/g;
		$vo =~ s/</\&lt\;/g;
		$vo =~ s/>/\&gt\;/g;
		}
	&hed_;
	print <<"_HTML_";
<form action="$cgi_f" method=$met><input type=hidden name=pass value="$FORM{'pass'}">
<input type=hidden name=mode value=edit>$nf<input type=submit value="戻 る"></form>
<center><table width=90\% bgcolor=$ttb><tr><th>ナンバー[$namber] の編集</th></tr></table>
□ すべての項目のカウント数を 0 にできます。
<form action="$cgi_f" method="$met">$nf
<input type=hidden name=pass value="$FORM{'pass'}">
<input type=hidden name=mode value=h_w>
<input type=hidden name=namber value=$namber>
<table><tr><td><b>項目</b></td><td>
/<input type=text name=vote value="$vo" size=30></td></tr>
<tr><td><b>カウント数</b></td><td>
/<input type=text name="count" value="$co" size=4></td></tr>
</td></tr><tr><td colspan=2 align=center><input type=submit value="編 集">
<input type=reset value=リセット></td></tr></table></form></center>
_HTML_
&foot_;
	}
}
}
#
# [編集内容置換]
#
sub h_w_ {
if($FORM{'count'} eq "") { &er_("カウント数が未入力!"); }
if($FORM{'vote'} eq "") { &er_("項目名が未入力!"); }
if($FORM{'pass'} ne "$pass") { &er_("パスワードが違います!"); }
open(DB,"$log");
@lines = <DB>;
close(DB);

if($tag){
$vote=~ s/\&quot\;/\"/g;
$vote=~ s/\&lt\;/</g;
$vote=~ s/\&gt\;/>/g;
}
@new = ();
$flag = 0;
foreach $line (@lines) {
	$line =~ s/\n//g;
	($knam,$kvo,$kco,$kip) = split(/<>/,$line);
	if ($namber eq "$knam") {
		$line = "$namber<>$vote<>$FORM{'count'}<>$kip<>";
		$flag = 1;
	}
	push(@new,"$line\n");
}
if ($flag == 0) { &er_("編集ナンバーが不正です!"); }
if ($flag == 1) {
	open (DB,">$log");
	print DB @new;
	close(DB);
}
&edit_;
}
#
# [項目一括削除]
#
sub s_d_ {
if ($FORM{'pass'} ne "$pass") { &er_("パスワードが違います!"); }
	open(DB,">$log");
	printf DB "";
	close(DB);
&edit_;
}
#
# [エラー処理]
#
sub er_ {
if (-e $lockfile) { unlink($lockfile); }
	&hed_;
	print "<center>ERROR! - $_[0]</center><br>\n";
	&foot_;
}
#
# [設定ファイルチェック]
#
sub all_ {
&hed_;
$T=@set;
print"<h3>$T個の設定ファイルがCGIに設定済み</h3><hr>";
foreach (0..$#set){
if($set[$_]){
	unless(-e $set[$_]){print"<b>$_)$set[$_]が無いです</b><br>";}
	else{require "$set[$_]";
		print"<b>$_)<a href=\"$cgi_f?no=$_\">$title</a></b><br>";
		if(-e $log){print"　ログ:○";}else{print"　ログ:×";}
	}
print"<br><br>"
}
}
&foot_;
}
#
# [ログ生成]
#
sub l_m {
open(DB,">$_[0]") || &er_("Can't write $_[0]");
print DB "";
close(DB);

chmod(0666,"$_[0]");
}

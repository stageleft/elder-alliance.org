#!/usr/local/bin/perl

require './jcode.pl';

#--------------------------------------
$ver="PluralVote v5.1";# (���[�V�X�e��)
#--------------------------------------
# Copyright(C) ��イ����
# E-Mail:ryu@cj-c.com
# W W W :http://www.cj-c.com/
#--------------------------------------

#--- �����ݒ� ------------------*

# �����悤�ɂ����ł����₹�܂��B
# [ ]���̐������g��CGI�ɃA�N�Z�X����Ƃ��̐ݒ�t�@�C���œ��삵�܂��B
# $set[12] �̐ݒ�t�@�C�����g���ꍇ: http://www.xxx.com/cgi-bin/p_vote.cgi?no=12
$set[0]="./set.cgi";
$set[1]="./set1.cgi";

# �֎~������ �^�O�g�p�̏ꍇ�͋֎~�^�O������OK �����悤�ɂ����ł��w��\
@NW=('����','<html>','<script');

#--- �ݒ肱���܂�---------------*
&time_;
&d_code_;
if($no eq ""){$no=0;}
if($set[$no]){unless(-e $set[$no]){&er_('�ݒ�t�@�C���������ł�!');}else{require"$set[$no]";}}
else{&er_('�ݒ�t�@�C����CGI�ɐݒ肳��Ă܂���!');}
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
# [�g�b�v�y�[�W]
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
��<a href="$backurl"> HOME</a>�@
��<a href="$cgi_f?mode=man&no=$no"> HELP</a>
<hr width=80%><table><tr><td>$com
</td></tr></table><hr width=80%>
<form action=$cgi_f method=$met>$date���݂̑����[��: <b>$total</b><br>
<input type=hidden name=mode value="vote">$nf
<table border=0><tr bgcolor=$ttb>
<th>�����N</th><th>���[</th><th>����</th><th>���[��</th><th>�O���t</th></tr>
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
	$r_b="<br>";$s_c="<br>";$endcom="<h3>���[�͏I�����܂����B</h3>";
	}else{
	$r_b="<input type=$rcbox name=namber value=\"$namb{$_}\">";
	$s_c="<input type=submit value='�`�F�b�N���ڂɓ��['>";
	}
	if($K){$COL="$str";$K=0;}else{$COL="$bg";$K=1;}
print <<"_HTML_";
<tr bgcolor=$COL><th>$rank1��</th><th>$r_b</th>
<td align=center> $_ </td><th>$count{$_}</th>
<td><img src="$bar" width="$bwid{$_}" height="$bhei">
<small>$point{$_}\%</small></td></tr>
_HTML_
	$count_tmp = $count{$_};
	$rank2++;
	}
if($tag){$tac="��";}else{$tac="�s��";}
if($edit == 1){
	print "<tr><th colspan=5>$s_c</th></tr></table></form>";
	if(($max_v && $total >= $max_v)||($p_co==1 && $cooks==3)||($max_d && $DATE>=$max_d)){
	print"<br>";
	}else{
	print <<"_HTML_";
<form action=$cgi_f method=$met>$nf
���ڒǉ�(�ō�$max�܂�/�^�O$tac)<br>
<input type=hidden name=mode value="vote">
<input type=text name="vote" size=15 maxlength=$vmax>
<input type=submit value="���ڒǉ�"></form>
_HTML_
	}
}elsif($edit == 0){print "<tr><th colspan=5>$s_c</th></tr></table></form>\n";}
print "$endcom\n";
print <<"_HTML_";
<br><div align=right>
<form action=$cgi_f method=$met><input type=hidden name=mode value="edit">$nf
<input type=password name="pass" size=8><input type=submit value="�Ǘ��p"></form></div>
_HTML_

&foot_;
}
#
# [�}�j���A��]
#
sub man_ {
if($edit==1){
	$ecom="����ł����R�ɑ��₹�܂��B\n";$eco="�ǉ��ł��܂��B\n";
	$ec="<li>�֌W�Ȃ����[���ڂ�\�\\��\�Ȃ�\��\��\����܂��B</li>\n";
	$e ="<li><b>���p�J�i�͎g�p�֎~</b>�B���������̌����ɂȂ�܂��B</li>\n";
}else{$ecom="�ǉ��ł��܂���B\n";$eco="������\��\�\\��������܂��B\n";}
if($cooks==1){$comc="2�d���[�ł��܂���B\n";}
elsif($cooks==0){$comc="2�d���[�ł��܂����A\n";}
elsif($cooks==2){$comc="�������ڂւ̘A�����[�͂ł��܂���B\n";}
&hed_;
print <<"_HTML_";
��<a href="$cgi_f?no=$no">BACK</a>
<center><table width=90\%><tr><th bgcolor="$ttb">$title �̎g����</th></tr></table>
<table width=65% align=center bgcolor=$k_back><tr><td><ul type="square">
<li>���̃����N�V�X�e��(�ȉ�VOTES)�͓��[���������ڂ��`�F�b�N���{�^�����������[���܂��B</li>
<li>����VOTES�͓��[���ڂ�<b>$ecom</b></li>
<li>���[���ڂ�<b>�ő�$max�R</b>�܂�$eco</li>
$ec$e
<li>����VOTES�́A<b>$comc</b>���������铊�[�����܂��傤�B</li>
</ul></td></tr></table></center>
_HTML_
&foot_;
}
#
# [�w�b�_�\��]
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
<!--�w�b�_�L���^�O�}���ʒu��-->

<!--�������܂�-->
_HEAD_
}
#
# [�t�b�^�\��]
#
sub foot_ {
print <<"_HTML_";
<!--���쌠�\\�� �폜�s��-->
<hr width=90\%>
<center>- <a href="http://www.cj-c.com/" target=_top>Plural Vote</a> -</center>
<!--�t�b�^�L���^�O�}���ʒu��-->

<!--�������܂�-->
_HTML_
	print "</body></html>\n";
	exit;
}
#
# [�t�H�[���f�R�[�h]
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
foreach(0..$#NW){if(index($value,$NW[$_]) >= 0){&er_("�u$NW[$_]�v�͎g�p�ł��܂���!");}}
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
# [�������ݏ���]
#
sub right_ {
$OK=0;
if($FORM{'pass'} eq $pass){$OK=1;}
if($cooks==1 && $OK==0){&get_; if($cook){&er_("���[�͈��݂̂ł�!");}}
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
		if($cooks==2 && $OK==0){if($addr eq "$ip"){ &er_("�A�����[�s��!"); }}
		$count++;
		$line = "$nam<>$votes<>$count<>$addr<>";
		$k = 1;
		}
	}
if ($vote eq "$votes") { &er_("���ꍀ�ږ������݂��܂��I"); }
push (@new,"$line\n");
}
if($k == 0){ 
@lines = reverse(@lines);
($nam0,$vo0,$cnt0,$ip0) = split(/<>/,$lines[0]);
if($nam0 eq "") { $namber=0; }
$namber = $nam0 + 1; $LINE=@lines;
if($LINE > $max){&er_("����ȏ㍀�ڂ𑝂₹�܂���I");}
if($vote eq ""){ &er_("���ږ�������܂���!"); }
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
# [�t�@�C�����b�N]
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
# [�����̎擾]
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
	$week = ('��','��','��','��','��','��','�y') [$wday];
$date="$year\/$mon\/$mday\($week\) $hour\:$min\:$sec";
$DATE="$year$mon$mday";
}
#
# [�N�b�L�[���s]
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
# [�N�b�L�[�擾]
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
# [�Ǘ����]
#
sub edit_ {
if ($FORM{'pass'} ne "$pass") { &er_("�p�X���[�h���Ⴂ�܂�!"); }
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
��<a href="$cgi_f?no=$no"> BACK</a>
<center><table width=90\%><tr><th bgcolor="$ttb">�Ǘ����[�h</th></tr></table>
���݂̃��O�T�C�Y�F$size �o�C�g
<table><tr><td>
�� �폜���������ڂɃ`�F�b�N�����u�� ���v�������ĉ������B<br>
�� �i���o�[���N���b�N����Ƃ��̍��ڂɂ��ĕҏW�ł��܂��B
</td></tr></table>
<form action="$cgi_f" method=$met>$nf
<input type=hidden name=mode value="edit">
<input type=hidden name=pass value="$FORM{'pass'}">
<table border=1><tr bgcolor=$ttb>
<th>�`�F�b�N</th><th>�i���o�[</th><th>���ږ�</th><th>�J�E���g</th></tr>
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
<input type=submit value="�� ��">
<input type=reset value="���Z�b�g">
</form><br>
<hr width=90%><form action=$cgi_f method=$met>$nf���O�̏�����
<input type=hidden name=pass value="$FORM{'pass'}">
<input type=hidden name=mode value="s_d"><input type=submit value="������">
</form>
_HTML_
if($namber eq ""){ $namber=0; }
if($tag){$tac="��";}else{$tac="�s��";}
$namber=$namber+1;
print <<"_HTML_";
<hr width=90\%><form action=$cgi_f method=$met>$nf
�E��������ǉ�����ΘA�����[�����ɂȂ�܂���B<br><br>
���ڂ𑝂₷�����ł��܂��B(�ō�$max�܂�/�^�O$tac)<br>
<input type=hidden name=mode value="vote">
<input type=text name="vote" size=15 maxlength=$vmax>
<input type=hidden name=namber value="$namber">
<input type=hidden name=pass value="$FORM{'pass'}">
<input type=submit value="���ڒǉ�"></form>
_HTML_
print "</center>\n";
&foot_;
}
#
# [�L���ҏW]
#
sub hen_ {
if ($FORM{'pass'} ne "$pass") { &er_("�p�X���[�h���Ⴂ�܂�!"); }
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
<input type=hidden name=mode value=edit>$nf<input type=submit value="�� ��"></form>
<center><table width=90\% bgcolor=$ttb><tr><th>�i���o�[[$namber] �̕ҏW</th></tr></table>
�� ���ׂĂ̍��ڂ̃J�E���g���� 0 �ɂł��܂��B
<form action="$cgi_f" method="$met">$nf
<input type=hidden name=pass value="$FORM{'pass'}">
<input type=hidden name=mode value=h_w>
<input type=hidden name=namber value=$namber>
<table><tr><td><b>����</b></td><td>
/<input type=text name=vote value="$vo" size=30></td></tr>
<tr><td><b>�J�E���g��</b></td><td>
/<input type=text name="count" value="$co" size=4></td></tr>
</td></tr><tr><td colspan=2 align=center><input type=submit value="�� �W">
<input type=reset value=���Z�b�g></td></tr></table></form></center>
_HTML_
&foot_;
	}
}
}
#
# [�ҏW���e�u��]
#
sub h_w_ {
if($FORM{'count'} eq "") { &er_("�J�E���g����������!"); }
if($FORM{'vote'} eq "") { &er_("���ږ���������!"); }
if($FORM{'pass'} ne "$pass") { &er_("�p�X���[�h���Ⴂ�܂�!"); }
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
if ($flag == 0) { &er_("�ҏW�i���o�[���s���ł�!"); }
if ($flag == 1) {
	open (DB,">$log");
	print DB @new;
	close(DB);
}
&edit_;
}
#
# [���ڈꊇ�폜]
#
sub s_d_ {
if ($FORM{'pass'} ne "$pass") { &er_("�p�X���[�h���Ⴂ�܂�!"); }
	open(DB,">$log");
	printf DB "";
	close(DB);
&edit_;
}
#
# [�G���[����]
#
sub er_ {
if (-e $lockfile) { unlink($lockfile); }
	&hed_;
	print "<center>ERROR! - $_[0]</center><br>\n";
	&foot_;
}
#
# [�ݒ�t�@�C���`�F�b�N]
#
sub all_ {
&hed_;
$T=@set;
print"<h3>$T�̐ݒ�t�@�C����CGI�ɐݒ�ς�</h3><hr>";
foreach (0..$#set){
if($set[$_]){
	unless(-e $set[$_]){print"<b>$_)$set[$_]�������ł�</b><br>";}
	else{require "$set[$_]";
		print"<b>$_)<a href=\"$cgi_f?no=$_\">$title</a></b><br>";
		if(-e $log){print"�@���O:��";}else{print"�@���O:�~";}
	}
print"<br><br>"
}
}
&foot_;
}
#
# [���O����]
#
sub l_m {
open(DB,">$_[0]") || &er_("Can't write $_[0]");
print DB "";
close(DB);

chmod(0666,"$_[0]");
}

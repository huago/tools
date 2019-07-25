#!/usr/bin/expect
#0.自动登录脚本
#1.安装zssh 		$ brew install zssh
#2.安装expect 		$ brew install expect
#3.增加可执行权限	$ chmod a+x autologin.sh
#4.执行			$ ./autologin.sh
spawn zssh username@host -p port
set password "xxx"
expect {
	"Password>:" {send "$password\n";}
	"Else text:" {send "other text\n"}
}
interact

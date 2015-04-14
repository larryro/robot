# ~/.bashrc: executed by bash(1) for non-login shells.
# see /usr/share/doc/bash/examples/startup-files (in the package bash-doc)
# for examples

# If not running interactively, don't do anything
[ -z "$PS1" ] && return

# don't put duplicate lines in the history. See bash(1) for more options
# ... or force ignoredups and ignorespace
HISTCONTROL=ignoredups:ignorespace

# append to the history file, don't overwrite it
shopt -s histappend

# for setting history length see HISTSIZE and HISTFILESIZE in bash(1)
HISTSIZE=1000
HISTFILESIZE=2000

# check the window size after each command and, if necessary,
# update the values of LINES and COLUMNS.
shopt -s checkwinsize

# make less more friendly for non-text input files, see lesspipe(1)
[ -x /usr/bin/lesspipe ] && eval "$(SHELL=/bin/sh lesspipe)"

# set variable identifying the chroot you work in (used in the prompt below)
if [ -z "$debian_chroot" ] && [ -r /etc/debian_chroot ]; then
    debian_chroot=$(cat /etc/debian_chroot)
fi

# set a fancy prompt (non-color, unless we know we "want" color)
case "$TERM" in
    xterm-color) color_prompt=yes;;
esac

# uncomment for a colored prompt, if the terminal has the capability; turned
# off by default to not distract the user: the focus in a terminal window
# should be on the output of commands, not on the prompt
#force_color_prompt=yes

if [ -n "$force_color_prompt" ]; then
    if [ -x /usr/bin/tput ] && tput setaf 1 >&/dev/null; then
	# We have color support; assume it's compliant with Ecma-48
	# (ISO/IEC-6429). (Lack of such support is extremely rare, and such
	# a case would tend to support setf rather than setaf.)
	color_prompt=yes
    else
	color_prompt=
    fi
fi

if [ "$color_prompt" = yes ]; then
    PS1='${debian_chroot:+($debian_chroot)}\[\033[01;32m\]\u@\h\[\033[00m\]:\[\033[01;34m\]\w\[\033[00m\]\$ '
else
    PS1='${debian_chroot:+($debian_chroot)}\u@\h:\w\$ '
fi
unset color_prompt force_color_prompt

# If this is an xterm set the title to user@host:dir
case "$TERM" in
xterm*|rxvt*)
    PS1="\[\e]0;${debian_chroot:+($debian_chroot)}\u@\h: \w\a\]$PS1"
    ;;
*)
    ;;
esac

# enable color support of ls and also add handy aliases
if [ -x /usr/bin/dircolors ]; then
    test -r ~/.dircolors && eval "$(dircolors -b ~/.dircolors)" || eval "$(dircolors -b)"
    alias ls='ls --color=auto'
    #alias dir='dir --color=auto'
    #alias vdir='vdir --color=auto'

    alias grep='grep --color=auto'
    alias fgrep='fgrep --color=auto'
    alias egrep='egrep --color=auto'
fi

# some more ls aliases
alias ll='ls -alF'
alias la='ls -A'
alias l='ls -CF'

# Alias definitions.
# You may want to put all your additions into a separate file like
# ~/.bash_aliases, instead of adding them here directly.
# See /usr/share/doc/bash-doc/examples in the bash-doc package.

if [ -f ~/.bash_aliases ]; then
    . ~/.bash_aliases
fi

# enable programmable completion features (you don't need to enable
# this, if it's already enabled in /etc/bash.bashrc and /etc/profile
# sources /etc/bash.bashrc).
#if [ -f /etc/bash_completion ] && ! shopt -oq posix; then
#    . /etc/bash_completion
#fi
export JAVA_HOME=/usr/lib/jvm/jdk1.8.0_25
export JRE_HOME=${JAVA_HOME}/jre
export CLASSPATH=.:${JAVA_HOME}/lib:${JRE_HOME}/lib
export PATH=${JAVA_HOME}/bin:$PATHroot@iZ9412sf3j9Z:~# ls -al
total 1464
drwx------  7 root root    4096 Jan 20 14:46 .
drwxr-xr-x 24 root root    4096 Jan 20 14:32 ..
-rw-------  1 root root   33811 Jan 20 14:42 .bash_history
-rw-r--r--  1 root root    3106 Feb 20  2014 .bashrc
-rw-r--r--  1 root root   12288 Jan 20 14:26 .bashrc.swn
-rw-r--r--  1 root root   16384 Jan 20 14:21 .bashrc.swp
drwx------  2 root root    4096 Oct 25 16:48 .cache
drwxr-xr-x  3 root root    4096 Oct 27 10:21 .composer
drwxr-xr-x  3 root root    4096 Dec 10 18:13 ffmpeg_sources
-rw-r--r--  1 root root      37 Dec  3 20:40 file_list.txt
-rw-r--r--  1 root root  117935 Dec 20 14:21 flyhelper.sql
lrwxrwxrwx  1 root root      14 Dec  3 15:20 html -> /var/www/html/
-rw-r--r--  1 root root     343 Dec  3 20:47 less
-rw-------  1 root root    3693 Jan 19 16:27 .mysql_history
-rw-r--r--  1 root root       4 Jan 10 15:29 pid.file
-rw-r--r--  1 root root       3 Jan  4 10:58 pid.log
-rw-r--r--  1 root root     140 Feb 20  2014 .profile
-rw-------  1 root root    1024 Jan  4 18:36 .rnd
-rw-r--r--  1 root root      75 Nov  7 11:49 .selected_editor
-rw-r--r--  1 root root     757 Jan  4 18:36 server.crt
-rw-r--r--  1 root root     603 Jan  4 18:35 server.csr
-rw-r--r--  1 root root     963 Jan  4 18:34 server.key
-rw-r--r--  1 root root       0 Dec 30 11:43 show
-rw-r--r--  1 root root      37 Dec  3 20:41 sorted_file_lilst.txt
drwxr-xr-x  3 root root    4096 Dec 20 11:50 .subversion
-rw-r--r--  1 root root 1176900 Dec 24 18:41 test.log
-rw-r--r--  1 root root     351 Jan  8 12:03 test.php
-rw-r--r--  1 root root   25753 Nov  8 19:07 time.log
drwxr-xr-x  2 root root    4096 Nov  8 14:08 .vim
-rw-------  1 root root   13796 Jan 20 14:46 .viminfo
root@iZ9412sf3j9Z:~# cd /usr/lib/jv.bashrc.swn.bashrc.swp
-bash: cd: /usr/lib/jv.bashrc.swn.bashrc.swp: No such file or directory
root@iZ9412sf3j9Z:~# rm .bashrc.swp
root@iZ9412sf3j9Z:~# vi .bashrc
alias ll='ls -alF'
alias la='ls -A'
alias l='ls -CF'

# Alias definitions.
# You may want to put all your additions into a separate file like
# ~/.bash_aliases, instead of adding them here directly.
# See /usr/share/doc/bash-doc/examples in the bash-doc package.

if [ -f ~/.bash_aliases ]; then
    . ~/.bash_aliases
fi

# enable programmable completion features (you don't need to enable
# this, if it's already enabled in /etc/bash.bashrc and /etc/profile
# sources /etc/bash.bashrc).
#if [ -f /etc/bash_completion ] && ! shopt -oq posix; then
#    . /etc/bash_completion
#fi
export PATH=/home/develop/android-sdks/platform-tools/:$PATH

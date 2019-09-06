npm run build
rm  -R ./php/cache
rm  -R ./php/errors
ssh -T deb105013n2@skcvolleybal.nl -i /c/Users/jonat/.ssh/antagonist-ssh <<- 'END'
cd /home/deb105013n2/public_html/team-portal
shopt -s extglob
rm -R !("configuration.php")
shopt -u extglob
END
scp -i /c/Users/jonat/.ssh/antagonist-ssh -r dist/* deb105013n2@skcvolleybal.nl:~/public_html/team-portal
scp -i /c/Users/jonat/.ssh/antagonist-ssh -r php deb105013n2@skcvolleybal.nl:~/public_html/team-portal
curl https://www.skcvolleybal.nl/team-portal/php/interface.php?action=CompleteDailyTasks
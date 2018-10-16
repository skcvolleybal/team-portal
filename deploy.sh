cd /home/deb105013n2/public_html/scripts/team-portal
git pull
cd /home/deb105013n2/public_html/team-portal
rm * -R -v
cp ../scripts/team-portal/dist/* . -R
cd php
mkdir cache
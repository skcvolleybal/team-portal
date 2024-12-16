# TeamPortal

## Description:

TeamPortal supports the Team Task Coordinator of SKC (TeamTakenCo, bestuur) in making schedules for match days. SKC members are scheduled on match days to be emergency responders, referees, scorers, or for setting up and dismantling the hall. To ensure that members are available, members can log in to TeamPortal to provide their availability.

The schedules are sent to relevant people weekly via a Cron job.

TeamPortal uses the Nevobo RSS feed to retrieve matches relevant to SKC. Furthermore, it uses the internal WordPress database of the SKC website to link teams and members.

# Screenshots
![Mijn Overzicht](/.github/screenshots/mijn-overzicht.png "Mijn Overzicht")
![Tel/Fluit Beschikbaarheid](/.github/screenshots/tel-fluit-beschikbaarheid.png "Tel/Fluit Beschikbaarheid Opgeven")
![Barcie Planner](/.github/screenshots/barcie.png "Barcie Planner")
![Verzonden E-mails](/.github/screenshots/emails.png "Verzonden E-mails")

# Installation
Install is done using Docker. Use the start.sh/bat in the https://github.com/skcvolleybal/starthier repository. 

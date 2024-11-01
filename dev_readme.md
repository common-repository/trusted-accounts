#Deployment process

### 1. Run translation
- run ```wp i18n make-pot . languages/trusted-accounts.pot```
- open Poedit -> Datei durchsuchen -> *.po file
- open Übersetzung -> aus .POT Datei aktualisieren
- select generated /languages/trusted-accounts.pot file
- update translations and hit save to automatically generate .mo file

### 2. Prepare for deployment
- Update version in controller file
- Update stable version tag in readme file
- Update changelog in readme file

### 3. Deploy to Wordpress.com
- Go to svn local directory of Trusted Accounts Plugin
- Copy newer files to trunk folder
- Right click on folder and choose "commit"
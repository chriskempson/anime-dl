# anime-dl
Personal project, unsupported and undocumented so don't ask for help! ：P

## Description
Downloads Japanese media with subtitles for use with [Voracious](https://github.com/rsimmons/voracious). Hence this tool is only useful for those studying Japanese.

## Requirements
- Windows Subsystem for Linux with PHP and Composer
- Java for Selenium
- Chrome

## Run
Open 'resources/chromedriver.exe', open 'resources/start-selenium.bat', open a CLI and run 'php animedl.php source-website.com' for a listing of all available media or 'php animedl.php source-website.com "Media Name in English"' to download media with Japanese and English (if available) subtitles.
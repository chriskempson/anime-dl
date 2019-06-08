# anime-dl
Personal project, unsupported and undocumented so don't ask for help! ：P

![screenshot](screenshot.jpg)

## Description
Downloads Japanese media with subtitles for use with [Voracious](https://github.com/rsimmons/voracious). Hence this tool is only useful for those studying Japanese.

## Requirements
- Windows Subsystem for Linux with PHP and [Composer](https://getcomposer.org/) installed.
- Java for Selenium
- Chrome

## Installation
Clone this repo to a suitable directory and ensure the defined composer dependencies are installed by running Composer's [install](https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies) command.

## Running
Open `resources/chromedriver.exe`, open `resources/start-selenium.bat`, open a CLI and run `php animedl.php source-website.com` for a listing of all available media or `php animedl.php source-website.com "Media Name in Romaji"` to download media with Japanese and English (if available) subtitles.

Seach a list of available media with grep:

     php animedl.php animejpnsub.ezyro.com | grep Nihonjin
     
Download a title with English and Japanese subtitles ready for [Voracious](https://github.com/rsimmons/voracious):

    php animedl.php animejpnsub.ezyro.com "Nihonjin no Shiranai Nihongo"

# Keyakizaka46-Blog-archiver
Archieve Keyakizaka46 members' blog by PHP

## **❗❗ Your help is needed ❗❗**
## Installation
 1. Install [composer](https://getcomposer.org/)
 2. Install dependency
 `composer require stefangabos/zebra_curl`
 3. Modify `inc/config.inc.php`
 
 Note: 
 It is recommend to set the `webroot` (apache) or `root`(nginx) to 
 `/out/`
 
## Usage
* Execute `gen.php` by command line
* Open `/out/manual_update/index.html` by web browser for normal user
* Open `/out/manual_update/gen.php?pass=YOUR_BACKDOOR_PASS` by web browser, where `YOUR_BACKDOOR_PASS` is the value of `$backdoor_pass` in `inc/config.inc.php`

----------
## TODO
* Rewrite the code to fulfill the following goals
	* More adaptive toward change of HTML layout of feed
	* Object-oriented
	* Better API implementation
	* Performance optimization

----------
## License
Keyakizaka46-Blog-archiver Copyright (C) 2017 MilitaryRiotLab

Please refer to LICENSE

## Acknowledgement
#### This project re-distributes the following third party software.
* [PHP Simple HTML DOM Parser](https://sourceforge.net/projects/simplehtmldom/)
MIT License
* [Skeleton](https://github.com/dhg/Skeleton)
[MIT License](https://github.com/dhg/Skeleton/blob/master/LICENSE.md)
* [Zebra_cURL](https://github.com/stefangabos/Zebra_cURL)
[LGPL-3.0 License](https://github.com/stefangabos/Zebra_cURL/blob/master/license.txt)

echo off
cls
echo.
echo ######################################################
echo #                                                    #
echo #          S N A P T R A C - R E P O R T             #
echo #                                                    #
echo ######################################################
echo.
set PATH=%PATH%;%cd%\php5
php.exe process_json.php
pause
@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../phpmv/ubiquity-devtools/src/Ubiquity
php "%BIN_TARGET%" %*

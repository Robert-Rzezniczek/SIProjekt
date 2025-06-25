@echo off
setlocal ENABLEEXTENSIONS

set "RESULT_FILE=check_code.result.cache"
del /F /Q "%RESULT_FILE%"
type nul > "%RESULT_FILE%"

echo Installing dependencies...
call composer install --no-interaction >nul 2>&1
call composer require --dev friendsofphp/php-cs-fixer --no-interaction >nul 2>&1
call composer require --dev squizlabs/php_codesniffer --no-interaction >nul 2>&1
call composer require --dev escapestudios/symfony2-coding-standard --no-interaction >nul 2>&1
php vendor\bin\phpcs --config-set installed_paths vendor/escapestudios/symfony2-coding-standard >nul 2>&1
php vendor\bin\phpcs --config-set default_standard Symfony >nul 2>&1

del /F /Q .php-cs-fixer.dist.php >nul 2>&1
del /F /Q .php-cs-fixer.cache >nul 2>&1

echo Running php-cs-fixer...
php vendor\bin\php-cs-fixer fix src\ --rules=@Symfony,@PSR1,@PSR2,@PSR12 --dry-run -vvv

del /F /Q .php-cs-fixer.dist.php >nul 2>&1
del /F /Q .php-cs-fixer.cache >nul 2>&1

echo Running phpcs...
php vendor\bin\phpcs --standard=Symfony src\ --ignore=Kernel.php

echo Running debug:translation...
php bin\console debug:translation en --only-missing >> "%RESULT_FILE%" 2>&1
php bin\console debug:translation pl --only-missing >> "%RESULT_FILE%" 2>&1

echo Running DB schema and data fixtures...
php bin\console doctrine:schema:drop --no-interaction --full-database --force >> "%RESULT_FILE%" 2>&1
php bin\console doctrine:migrations:migrate --no-interaction >> "%RESULT_FILE%" 2>&1
php bin\console doctrine:fixtures:load --no-interaction >> "%RESULT_FILE%" 2>&1

echo Tear down...
php bin\console doctrine:schema:drop --no-interaction --full-database --force >nul 2>&1
rmdir /S /Q var >nul 2>&1
rmdir /S /Q vendor >nul 2>&1

endlocal

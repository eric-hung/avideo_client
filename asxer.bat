@REM VER.1
@REM Date: 2014-02-04
@REM Author: Eric Hung
@REM *****************************************************************************
@SETLOCAL
@REM Please configure variables as below:
@REM Notice: Never preserve blank characters in two sides of the assign-symbol "="
@SET PHP_EXEC="D:\portables\TWAMPd\ap\php-5.4\php.exe"
@SET DISPATCHER="D:\web_php\avideo_client\_dispatcher.php"
@REM *****************************************************************************
@REM Don't change any code line as below if no necessary
@IF [%1]==[] ( ECHO Only exactly 1 parameter required. ) ELSE ( 
%PHP_EXEC% -q %DISPATCHER% %1 )

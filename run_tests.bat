@echo off
echo Running PHPUnit tests...
vendor\bin\phpunit.bat --stop-on-failure > test_results.log 2>&1
echo Tests completed. Results saved to test_results.log
echo.
echo Last 30 lines of output:
echo ========================
powershell "Get-Content test_results.log | Select-Object -Last 30"

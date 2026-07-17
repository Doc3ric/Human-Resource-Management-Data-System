Set WinScriptHost = CreateObject("WScript.Shell")
WinScriptHost.Run Chr(34) & "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" & Chr(34) & " " & Chr(34) & "C:\laragon\www\Human-Resource-Management-Data-System\artisan" & Chr(34) & " schedule:run", 0
Set WinScriptHost = Nothing

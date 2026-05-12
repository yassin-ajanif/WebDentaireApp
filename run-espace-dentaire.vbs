' Launches Laravel (php artisan serve) with no console window, then opens the app in the default browser.
' Double-click "Open-Espace-Dentaire.bat" or create a Desktop shortcut to this .vbs file.

Option Explicit

Dim fso, sh, folder, phpExe, phpOverride, cmd

' If automatic detection fails, set your php.exe path here, e.g. "C:\xampp\php\php.exe"
phpOverride = ""

Set fso = CreateObject("Scripting.FileSystemObject")
Set sh = CreateObject("WScript.Shell")
folder = fso.GetParentFolderName(WScript.ScriptFullName)

If Len(phpOverride) > 0 Then
  phpExe = phpOverride
ElseIf fso.FileExists("C:\xampp\php\php.exe") Then
  phpExe = "C:\xampp\php\php.exe"
Else
  phpExe = "php"
End If

If phpExe <> "php" And Not fso.FileExists(phpExe) Then
  MsgBox "PHP introuvable :" & vbCrLf & phpExe & vbCrLf & vbCrLf & "Ouvrez run-espace-dentaire.vbs et renseignez phpOverride avec le chemin complet vers php.exe.", vbCritical, "Espace Dentaire"
  WScript.Quit 1
End If

If LCase(phpExe) = "php" Then
  cmd = "php artisan serve"
Else
  cmd = Chr(34) & phpExe & Chr(34) & " artisan serve"
End If

sh.CurrentDirectory = folder
' 0 = hidden window; False = do not wait (server keeps running)
sh.Run cmd, 0, False
WScript.Sleep 2500
sh.Run "http://127.0.0.1:8000", 1, False

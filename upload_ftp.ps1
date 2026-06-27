$ErrorActionPreference = 'Stop'
$user='if0_42200853'
$pass='Igdz7s4PORPYbr'
$ftpHost='ftpupload.net'
$base = "ftp://$ftpHost/htdocs/srtms"
$files = @(
    @{local='c:\xampp\htdocs\srtms\index.php'; remote='index.php'},
    @{local='c:\xampp\htdocs\srtms\assets\css\style.css'; remote='assets/css/style.css'},
    @{local='c:\xampp\htdocs\srtms\assets\images\photo1.svg'; remote='assets/images/photo1.svg'},
    @{local='c:\xampp\htdocs\srtms\assets\images\photo2.svg'; remote='assets/images/photo2.svg'},
    @{local='c:\xampp\htdocs\srtms\student\index.php'; remote='student/index.php'},
    @{local='c:\xampp\htdocs\srtms\student\transcript.php'; remote='student/transcript.php'},
    @{local='c:\xampp\htdocs\srtms\admin\login.php'; remote='admin/login.php'},
    @{local='c:\xampp\htdocs\srtms\lecturer\login.php'; remote='lecturer/login.php'}
)
$wc = New-Object System.Net.WebClient
$wc.Credentials = New-Object System.Net.NetworkCredential($user,$pass)

# Ensure remote directories exist (attempt MKD; ignore errors if already present)
$dirs = $files | ForEach-Object { Split-Path $_.remote -Parent } | Select-Object -Unique
foreach($d in $dirs){
    if([string]::IsNullOrEmpty($d)) { continue }
    $dirUri = $base + '/' + $d
    $dirUri = $dirUri -replace '\\','/'
    try{
        $req = [System.Net.FtpWebRequest]::Create($dirUri)
        $req.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $req.Credentials = New-Object System.Net.NetworkCredential($user,$pass)
        $resp = $req.GetResponse()
        $resp.Close()
        Write-Output "MKD: $dirUri"
    } catch {
        Write-Output "MKD (ignored): $dirUri -> $($_.Exception.Message)"
    }
}
foreach($f in $files){
    $local = $f.local
    $remote = $base + '/' + $f.remote
    $remote = $remote -replace '\\','/'
    if(-not (Test-Path $local)){
        Write-Output "MISSING: $local"
        continue
    }
    try{
        Write-Output "Uploading $local -> $remote"
        $wc.UploadFile($remote, $local)
        Write-Output "UPLOADED: $remote"
    } catch {
        Write-Output "ERROR: $remote -> $($_.Exception.Message)"
    }
}
Write-Output "Done."

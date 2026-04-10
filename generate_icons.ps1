Add-Type -AssemblyName System.Drawing

$srcPath  = 'C:\Users\Janssen\.gemini\antigravity\brain\ba618409-10dd-4073-a38a-87aee02d2f5f\psau_parking_launcher_1775804025304.png'
$basePath = 'c:\Users\Janssen\Documents\GitHub\psau-security\psau_parking_system\android\app\src\main\res'

$sizes = @{
    'mipmap-mdpi'    = 48
    'mipmap-hdpi'    = 72
    'mipmap-xhdpi'   = 96
    'mipmap-xxhdpi'  = 144
    'mipmap-xxxhdpi' = 192
}

$src = [System.Drawing.Image]::FromFile($srcPath)

foreach ($folder in $sizes.Keys) {
    $size     = $sizes[$folder]
    $dir      = Join-Path $basePath $folder
    $destPath = $dir + '\ic_launcher.png'

    $bmp = New-Object System.Drawing.Bitmap($size, $size)
    $g   = [System.Drawing.Graphics]::FromImage($bmp)
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.DrawImage($src, 0, 0, $size, $size)
    $g.Dispose()
    $bmp.Save($destPath, [System.Drawing.Imaging.ImageFormat]::Png)
    $bmp.Dispose()

    Write-Host "Created $folder\ic_launcher.png ($size x $size px)"
}

$src.Dispose()
Write-Host "All done!"

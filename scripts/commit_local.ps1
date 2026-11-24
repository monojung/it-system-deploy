param(
    [string]$Message = "feat: polish official memo signature blocks and layout v2.15.6"
)

# Commit local changes using safe index file
$baseDir = "d:\Project\it-system"
Push-Location $baseDir

try {
    $env:GIT_INDEX_FILE = "$baseDir\.git\index_user"
    if (Test-Path "$baseDir\.git\index_user") {
        Remove-Item "$baseDir\.git\index_user" -Force -ErrorAction SilentlyContinue
    }
    if (Test-Path "$baseDir\.git\index.lock") {
        Remove-Item "$baseDir\.git\index.lock" -Force -ErrorAction SilentlyContinue
    }

    git read-tree HEAD
    git add -A

    $msg = $Message
    $tree = (git write-tree).Trim()
    $parent = (git rev-parse HEAD).Trim()
    $commit = (git commit-tree $tree -p $parent -m "$msg").Trim()

    [System.IO.File]::WriteAllText("$baseDir\.git\refs\heads\main", "$commit`n")
    [System.IO.File]::WriteAllText("$baseDir\.git\refs\remotes\origin\main", "$commit`n")
    Copy-Item -Path "$baseDir\.git\index_user" -Destination "$baseDir\.git\index" -Force -ErrorAction SilentlyContinue

    Write-Host "Committed successfully! Commit: $commit"
    git log -1 --oneline
} finally {
    Remove-Item env:GIT_INDEX_FILE -ErrorAction SilentlyContinue
    Pop-Location
}

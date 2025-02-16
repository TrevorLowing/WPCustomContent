# Deploy test script for WP Custom Content plugin
param (
    [switch]$NoVersionIncrement
)

$ErrorActionPreference = "Stop"

# Plugin paths
$sourceDir = $PSScriptRoot
$targetDir = "C:\Users\trevo\Local Sites\fedxio\app\public\wp-content\plugins\wp-custom-content"

# Version management
function Update-Version {
    $versionFile = Join-Path $sourceDir "version.json"
    if (Test-Path $versionFile) {
        $versionData = Get-Content $versionFile | ConvertFrom-Json
        $testCount = $versionData.test_count + 1
        $baseVersion = (Get-Content (Join-Path $sourceDir "wp-custom-content.php") | 
                       Select-String "Version:\s*(\d+\.\d+\.\d+)").Matches.Groups[1].Value
        
        $versionData.test_count = $testCount
        $versionData.test_version = "$baseVersion-test.$testCount"
        $versionData.last_test = [int][double]::Parse((Get-Date -UFormat %s))
        
        $versionData | ConvertTo-Json | Set-Content $versionFile
        
        Write-Host "Updated test version to: $($versionData.test_version)"
        return $versionData.test_version
    }
    return $null
}

# Deployment
try {
    Write-Host "Starting deployment..."

    # Update version if not skipped
    if (-not $NoVersionIncrement) {
        $newVersion = Update-Version
        if ($newVersion) {
            Write-Host "Test version incremented to: $newVersion"
        }
    }

    # Remove existing plugin
    if (Test-Path $targetDir) {
        Write-Host "Removing existing plugin..."
        Remove-Item -Recurse -Force $targetDir
    }

    # Copy plugin files
    Write-Host "Copying plugin files..."
    Copy-Item -Recurse -Force $sourceDir $targetDir

    Write-Host "Deployment completed successfully!"
} catch {
    Write-Host "Error during deployment: $_" -ForegroundColor Red
    exit 1
}

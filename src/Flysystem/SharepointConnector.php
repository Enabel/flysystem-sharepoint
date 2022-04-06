<?php

declare(strict_types=1);

namespace Enabel\Sharepoint\Flysystem;

use Enabel\Sharepoint\Microsoft\Authentication\AuthenticationService;
use Enabel\Sharepoint\Microsoft\Drive\DirectoryService;
use Enabel\Sharepoint\Microsoft\Drive\DriveService;
use Enabel\Sharepoint\Microsoft\Drive\FileService;
use Enabel\Sharepoint\Microsoft\Sharepoint\SharepointService;

class SharepointConnector
{
    private string $accessToken;

    private DriveService $drive;
    private FileService $file;
    private DirectoryService $directory;

    public function __construct(
        string $tenantId,
        string $clientId,
        string $clientSecret,
        string $sharepointSite
    ) {
        $authService = new AuthenticationService();
        $accessToken = $authService->getAccessToken($tenantId, $clientId, $clientSecret);
        $this->setAccessToken($accessToken);

        // Get siteId by site name
        $spSite = new SharepointService($accessToken);
        $sharepointHostname = $spSite->requestSharepointHostname();
        $siteId = $spSite->requestSiteIdBySiteName($sharepointHostname, $sharepointSite);

        // Get driveId by site
        $this->drive = new DriveService($accessToken);
        $driveId = $this->drive->requestDriveId($siteId);
        $this->drive->setDriveId($driveId);

        $this->directory = new DirectoryService($accessToken, $driveId);
        $this->file = new FileService($accessToken, $driveId);
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): SharepointConnector
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function getDrive(): DriveService
    {
        return $this->drive;
    }

    public function setDrive(DriveService $drive): SharepointConnector
    {
        $this->drive = $drive;
        return $this;
    }

    public function getFile(): FileService
    {
        return $this->file;
    }

    public function setFile(FileService $file): SharepointConnector
    {
        $this->file = $file;
        return $this;
    }

    public function getDirectory(): DirectoryService
    {
        return $this->directory;
    }

    public function setDirectory(DirectoryService $directory): SharepointConnector
    {
        $this->directory = $directory;
        return $this;
    }
}

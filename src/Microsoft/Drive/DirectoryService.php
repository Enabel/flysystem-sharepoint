<?php

declare(strict_types=1);

namespace Enabel\Sharepoint\Microsoft\Drive;

use Enabel\Sharepoint\Microsoft\ApiConnector;
use Exception;
use GuzzleHttp\RequestOptions;

class DirectoryService
{
    /** @var ApiConnector|null $apiConnector */
    private ?ApiConnector $apiConnector;

    /** @var string $driveId */
    private string $driveId;

    /**
     * @param string $accessToken
     * @param int $requestTimeout
     * @param bool $verify
     */
    public function __construct(
        string $accessToken,
        string $driveId,
        int    $requestTimeout = 60,
        bool   $verify = true
    )
    {
        $this->setApiConnector(new ApiConnector($accessToken, $requestTimeout, $verify));
        $this->setDriveId($driveId);
    }

    /**
     * @return ApiConnector|null
     */
    public function getApiConnector(): ?ApiConnector
    {
        return $this->apiConnector;
    }

    /**
     * @param ApiConnector|null $apiConnector
     *
     * @return DirectoryService
     */
    public function setApiConnector(?ApiConnector $apiConnector): DirectoryService
    {
        $this->apiConnector = $apiConnector;
        return $this;
    }

    /**
     * @return string
     */
    public function getDriveId(): string
    {
        return $this->driveId;
    }

    /**
     * @param string $driveId
     *
     * @return DirectoryService
     */
    public function setDriveId(string $driveId): DirectoryService
    {
        $this->driveId = $driveId;
        return $this;
    }


    /**
     * @param string|null $path
     * @param string|null $itemId
     * @param string|null $suffix
     * @return string
     * @throws Exception
     */
    private function getDirectoryBaseUrl(?string $path = '/', ?string $itemId = null, ?string $suffix = null): string
    {
        if ($path === null && $itemId === null) {
            throw new \Exception('Microsoft SP Drive Request: Not all the parameters are correctly set. ' . __FUNCTION__, 2311);
        }

        // /drives/{drive-id}/items/{item-id}
        // /drives/{drive-id}/root:/{item-path}
        // https://docs.microsoft.com/en-us/graph/api/driveitem-get?view=graph-rest-1.0&tabs=http
        if ($itemId !== null) {
            return sprintf('/v1.0/drives/%s/items/%s%s', $this->getDriveId(), $itemId, ($suffix ?? ''));
        }

        if ($path === '/' || $path === '') {
            return sprintf('/v1.0/drives/%s/items/root%s', $this->getDriveId(), ($suffix ?? ''));
        }

        $path = ltrim($path, '/');
        return sprintf('/v1.0/drives/%s/items/root:/%s%s', $this->getDriveId(), $path, ($suffix !== null ? ':'.$suffix : ''));
    }

    /**
     * List all items in a specific directory
     *
     * @param string|null $directory
     * @param string|null $itemId
     * @return array
     * @throws Exception
     */
    public function requestDirectoryItems(?string $directory = '/', ?string $itemId = null): array
    {
        $url = $this->getDirectoryBaseUrl($directory, $itemId, '/children');

        // /sites/{siteId}/drive
        $response = $this->apiConnector->request('GET', $url);


        if ( ! isset($response['value'])) {
            throw new \Exception('Microsoft SP Drive Request: Cannot parse the body of the sharepoint drive request. ' . __FUNCTION__, 2321);
        }

        return $response['value'];
    }


    /**
     * Read the directory metadata and so check if it exists
     *
     * @param string|null $directory
     * @param string|null $itemId
     * @return array
     * @throws Exception
     */
    public function requestDirectoryMetadata(?string $directory = null, ?string $itemId = null): ?array
    {
        $url = $this->getDirectoryBaseUrl($directory, $itemId);

        $response = $this->apiConnector->request('GET', $url);

        if (isset($response['error'], $response['error']['code']) && $response['error']['code'] === 'itemNotFound') {
            return null;
        }

        if ( ! isset($response['id'], $response['name'], $response['webUrl'])) {
            throw new \Exception('Microsoft SP Drive Request: Cannot parse the body of the sharepoint drive request. ' . __FUNCTION__, 2331);
        }

        return $response;
    }


    /**
     * @param string|null $directory
     * @param string|null $itemId
     * @return bool
     * @throws Exception
     */
    public function checkDirectoryExists(?string $directory = null, ?string $itemId = null): bool
    {
        $directoryMetaData = $this->requestDirectoryMetadata($directory, $itemId);

        if (isset($directoryMetaData['file'])) {
            throw new \Exception('Check for file exists but path is actually a directory', 2231);
        }

        return ($directoryMetaData !== null);
    }

    /**
     * @param string|null $directory
     * @param string|null $parentDirectoryId
     * @return array|null
     * @throws Exception
     */
    public function createDirectory(?string $directory = null, ?string $parentDirectoryId = null): ?array
    {
        if($directory === '/') {
            throw new \Exception('Cannot create the root directory, this already exists', 2351);
        }

        // Explode the path
        $parent = explode( '/', $directory);
        $directoryName = array_pop($parent);


        // build url to fetch the parentItemId if not provided
        if($parentDirectoryId === null) {
            $parentDirectoryMeta = $this->requestDirectoryMetadata(sprintf('/%s', ltrim(implode('/', $parent), '/')));
            if($parentDirectoryMeta === null) {
                throw new \Exception('Parent directory does not exists', 2352);
            }
            $parentDirectoryId = $parentDirectoryMeta['id'];
        }

        $url = $this->getDirectoryBaseUrl(null, $parentDirectoryId, '/children');

        // Build request
        $body = [
            'name' => $directoryName,
            'directory' => []
        ];

        try {
            $response = $this->apiConnector->request('POST', $url, [], [], null, [
                RequestOptions::JSON => $body
            ]);

            return $response;
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param string|null $directory
     * @param string|null $itemId
     * @return bool
     * @throws Exception
     */
    public function createDirectoryRecursive(?string $directory = null): ?array
    {
        $pathParts = explode("/", $directory);

        $buildPath = '';
        $parentDirectoryId = null;
        $createDirectoryResponse = null;
        foreach($pathParts as $path) {
            $buildPath .= $path;
            $directoryMeta = $this->requestDirectoryMetadata($buildPath);

            if($directoryMeta !== null) {
                $parentDirectoryId = $directoryMeta['id'];
                continue;
            }

            $createDirectoryResponse = $this->createDirectory($buildPath, $parentDirectoryId);
            if($createDirectoryResponse === null) {
                throw new \Exception(sprintf('Cannot create recursive the directory %s', $buildPath), 2361);
            }

            $parentDirectoryId = $createDirectoryResponse['id'];
        }

        return $createDirectoryResponse;
    }

    /**
     * @param string|null $directory
     * @param string|null $itemId
     * @return bool
     * @throws Exception
     */
    public function deleteDirectory(?string $directory = null, ?string $itemId = null): bool
    {
        $url = $this->getDirectoryBaseUrl($directory, $itemId);

        try {
            $this->apiConnector->request('DELETE', $url);
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }
}
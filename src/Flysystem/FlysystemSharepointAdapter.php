<?php

declare(strict_types=1);

namespace Enabel\Sharepoint\Flysystem;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;

class FlysystemSharepointAdapter implements FilesystemAdapter
{
    private SharepointConnector $connector;

    public function __construct(
        SharepointConnector $connector
    ) {
        $this->setConnector($connector);
    }

    public function getConnector(): SharepointConnector
    {
        return $this->connector;
    }

    public function setConnector(SharepointConnector $connector): FlysystemSharepointAdapter
    {
        $this->connector = $connector;
        return $this;
    }

    public function fileExists(string $path): bool
    {
        return $this->connector->getFile()->checkFileExists($path);
    }

    public function directoryExists(string $path): bool
    {
        return $this->connector->getDirectory()->checkDirectoryExists($path);
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $mimeType = $config->get('mimeType', 'text/plain');

        $this->connector->getFile()->writeFile($path, $contents, $mimeType);
    }

    /**
     * @param resource $contents
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        // TODO: Implement writeStream() method.
        throw new \Exception('Not implemented yet!');
    }

    public function read(string $path): string
    {
        return $this->connector->getFile()->readFile($path);
    }

    /**
     * @return resource
     * @throws \Exception
     */
    public function readStream(string $path)
    {
        // TODO: Implement readStream() method.
        throw new \Exception('Not implemented yet!');
    }

    public function delete(string $path): void
    {
        $this->connector->getFile()->deleteFile($path);
    }

    public function deleteDirectory(string $path): void
    {
        $this->connector->getDirectory()->deleteDirectory($path);
    }

    public function createDirectory(string $path, Config $config): void
    {
        $this->connector->getDirectory()->createDirectoryRecursive($path);
    }

    public function setVisibility(string $path, string $visibility): void
    {
        throw new \Exception('Not implemented');
    }

    public function visibility(string $path): FileAttributes
    {
        throw new \Exception('Not implemented');
    }

    public function mimeType(string $path): FileAttributes
    {
        //TODO: WIP
        $this->connector->getFile()->checkFileMimeType($path);
        throw new \Exception('Not implemented yet!');
    }

    public function lastModified(string $path): FileAttributes
    {
        //TODO: WIP
        $this->connector->getFile()->checkFileLastModified($path);
        throw new \Exception('Not implemented yet!');
    }

    public function fileSize(string $path): FileAttributes
    {
        //TODO: WIP
        $this->connector->getFile()->checkFileSize($path);
        throw new \Exception('Not implemented yet!');
    }

    public function listContents(string $path, bool $deep): iterable
    {
        return $this->connector->getDirectory()->requestDirectoryItems($path);
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $parent = explode('/', $destination);
        $fileName = array_pop($parent);

        // Create parent directories if not exists
        $parentDirectory = sprintf('/%s', ltrim(implode('/', $parent), '/'));

        $this->connector->getFile()->moveFile($source, $parentDirectory, $fileName);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $parent = explode('/', $destination);
        $fileName = array_pop($parent);

        // Create parent directories if not exists
        $parentDirectory = sprintf('/%s', ltrim(implode('/', $parent), '/'));

        $this->connector->getFile()->copyFile($source, $parentDirectory, $fileName);
    }
}

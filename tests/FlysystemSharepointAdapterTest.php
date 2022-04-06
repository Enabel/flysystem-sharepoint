<?php

namespace Enabel\Sharepoint\Tests;

use Enabel\Sharepoint\Flysystem\FlysystemSharepointAdapter;
use Enabel\Sharepoint\Flysystem\SharepointConnector;
use Enabel\Sharepoint\Microsoft\Drive\DirectoryService;
use Enabel\Sharepoint\Microsoft\Drive\DriveService;
use Enabel\Sharepoint\Microsoft\Drive\FileService;
use League\Flysystem\Config;
use PHPUnit\Framework\TestCase;

class FlysystemSharepointAdapterTest extends TestCase
{
    public function setUp(): void
    {
        $this->driveMock = $this->createMock(DriveService::class);
        $this->fileMock = $this->createMock(FileService::class);
        $this->directoryMock = $this->createMock(DirectoryService::class);
        $this->connectorMock = $this->createMock(SharepointConnector::class);

        $this->adapter = new FlysystemSharepointAdapter(
            $this->connectorMock
        );
    }

    public function testGetConnector() {

        $connector = $this->adapter->getConnector();

        $this->assertEquals($this->connectorMock, $connector);
    }

    public function testSetConnector() {

        $conn = $this->createMock(SharepointConnector::class);

        $adapter = $this->adapter->setConnector($conn);

        $this->assertEquals($conn, $adapter->getConnector());
    }

    public function testFileExists() {
        $path = '/test.txt';

        $this->fileMock->method('checkFileExists')
            ->with($path)
            ->willReturn(true);

        $this->connectorMock->method('getFile')
            ->willReturn($this->fileMock);

        $this->assertTrue($this->adapter->fileExists($path));
    }

    public function testDirectoryExists() {
        $path = '/test';

        $this->directoryMock->method('checkDirectoryExists')
            ->with($path)
            ->willReturn(true);

        $this->connectorMock->method('getDirectory')
            ->willReturn($this->directoryMock);

        $this->assertTrue($this->adapter->directoryExists($path));
    }

    public function testWrite() {
        $path = '/test';
        $content = 'testContent';

        $options = new Config();

        $this->fileMock->method('writeFile')
            ->with($path, $content, 'text/plain');

        $this->connectorMock->method('getFile')
            ->willReturn($this->fileMock);

        $void = $this->adapter->write($path, $content, $options);

        $this->assertEmpty($void);
    }

    public function testWriteWithOptions() {
        $path = '/test';
        $content = 'testContent';
        $mimeType = ['mimeType' => 'application/json'];
        $options = new Config($mimeType);

        $this->fileMock->method('writeFile')
            ->with($path, $content, $mimeType['mimeType']);

        $this->connectorMock->method('getFile')
            ->willReturn($this->fileMock);

        $void = $this->adapter->write($path, $content, $options);

        $this->assertEmpty($void);
    }
}
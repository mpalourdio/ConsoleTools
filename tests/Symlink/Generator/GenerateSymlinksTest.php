<?php
/*
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace ConsoleToolsTests\Symlink\Generator;

use ConsoleTools\Symlink\Generator\GenerateSymlinks;

class GenerateSymlinksTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceCanBeCreatedWithParams()
    {
        $output     = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput');
        $filesystem = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->getMock();
        $filesystem->expects($this->exactly(1))->method('exists')->willReturn(true);
        $instance = new GenerateSymlinks(
            $filesystem,
            [
                'source'      => 'rootPath',
                'destination' => __DIR__ . DIRECTORY_SEPARATOR . '../',
                'projectDirs' => ['*'],
            ],
            $output
        );

        $this->assertInstanceOf('ConsoleTools\Symlink\Generator\GenerateSymlinks', $instance);
    }

    /**
     * @expectedException \Exception
     */
    public function testWrongDestFolderThrowsException()
    {
        $filesystem = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->getMock();
        $filesystem->expects($this->exactly(1))->method('exists')->willReturn(false);
        new GenerateSymlinks($filesystem, ['source' => 'rootPath', 'destination' => 'destPath']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRootPathIsMandatory()
    {
        $filesystem = $this->getMock('Symfony\Component\Filesystem\Filesystem');
        new GenerateSymlinks($filesystem, ['source' => null]);
    }

    public function testCanTraverseDirsWithJoker()
    {
        $filesystem = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->getMock();
        $filesystem->expects($this->exactly(1))->method('exists')->willReturn(true);
        $output   = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput');
        $instance = new GenerateSymlinks(
            $filesystem,
            [
                'source'      => __DIR__ . DIRECTORY_SEPARATOR . '../',
                'destination' => __DIR__ . DIRECTORY_SEPARATOR . '../',
                'projectDirs' => ['*'],
            ],
            $output
        );

        $this->assertInternalType('array', $instance->getAllDirsToTraverse());
        $this->assertEquals('Commands', $instance->getAllDirsToTraverse()[0]);
        $this->assertEquals('Generator', $instance->getAllDirsToTraverse()[1]);
    }

    public function testCanTraverseDirs()
    {
        $filesystem = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->getMock();
        $filesystem->expects($this->exactly(1))->method('exists')->willReturn(true);
        $output   = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput');
        $instance = new GenerateSymlinks(
            $filesystem,
            [
                'source'      => __DIR__ . DIRECTORY_SEPARATOR . '../',
                'destination' => __DIR__ . DIRECTORY_SEPARATOR . '../',
                'projectDirs' => ['Linux'],
            ],
            $output
        );

        $this->assertInternalType('array', $instance->getAllDirsToTraverse());
        $this->assertEquals('Linux', $instance->getAllDirsToTraverse()[0]);
    }

    public function testGetProjectConfigNoJsonFound()
    {
        $filesystem = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->getMock();
        $filesystem->expects($this->at(0))->method('exists')->willReturn(true);
        $filesystem->expects($this->at(1))->method('exists')->willReturn(false);
        $output = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput');

        $instance = new GenerateSymlinks(
            $filesystem,
            [
                'source'      => __DIR__ . DIRECTORY_SEPARATOR . '../',
                'destination' => __DIR__ . DIRECTORY_SEPARATOR . '../',
                'projectDirs' => ['Linux'],
            ],
            $output
        );

        $this->assertFalse($instance->getProjectConfig('Linux'));
    }

    public function testGetProjectConfigJsonFound()
    {
        $filesystem = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->getMock();
        $filesystem->expects($this->at(0))->method('exists')->willReturn(true);
        $filesystem->expects($this->at(1))->method('exists')->willReturn(true);
        $output   = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput');
        $instance = new GenerateSymlinks(
            $filesystem,
            [
                'source'      => __DIR__ . DIRECTORY_SEPARATOR . '../../../tests',
                'destination' => __DIR__ . DIRECTORY_SEPARATOR . '../../../tests',
                'projectDirs' => ['assets'],
            ],
            $output
        );

        $this->assertInternalType('array', $instance->getProjectConfig('assets'));
    }

    public function testprepareSymlinksSourceDoesNotExist()
    {
        $filesystem = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->getMock();
        $filesystem->expects($this->at(0))->method('exists')->willReturn(true);
        $filesystem->expects($this->at(1))->method('exists')->willReturn(false);
        $output = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput');

        $instance = new GenerateSymlinks(
            $filesystem,
            [
                'source'      => __DIR__ . DIRECTORY_SEPARATOR . '../',
                'destination' => __DIR__ . DIRECTORY_SEPARATOR . '../',
                'projectDirs' => ['Linux'],
            ],
            $output
        );

        $this->assertTrue($instance->prepareSymlinks('Linux', [['source' => 'MacOS']]));
    }

    public function testprepareSymlinksSourceExists()
    {
        $filesystem = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->getMock();
        $filesystem->expects($this->at(0))->method('exists')->willReturn(true);
        $filesystem->expects($this->at(1))->method('exists')->willReturn(true);
        $output = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput');

        $instance = new GenerateSymlinks(
            $filesystem,
            [
                'source'      => __DIR__ . DIRECTORY_SEPARATOR . '../',
                'destination' => __DIR__ . DIRECTORY_SEPARATOR . '../',
                'projectDirs' => ['Linux'],
            ],
            $output
        );

        $this->assertTrue($instance->prepareSymlinks('Linux', [['source' => 'MacOS']]));
    }

    public function testCreateSymlinks()
    {
        $filesystem = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->getMock();
        $filesystem->expects($this->at(0))->method('exists')->willReturn(true);
        $filesystem->expects($this->at(1))->method('exists')->willReturn(true);
        $filesystem->expects($this->at(0))->method('symlink')->willReturn(null);
        $filesystem->expects($this->at(0))->method('remove')->willReturn(null);

        $output   = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput');
        $instance = new GenerateSymlinks(
            $filesystem,
            [
                'source'      => __DIR__ . DIRECTORY_SEPARATOR . '../',
                'destination' => __DIR__ . DIRECTORY_SEPARATOR . '../',
                'projectDirs' => ['Linux'],
            ],
            $output
        );

        $this->assertNull($instance->createSymlinks('foo', 'bar'));
    }

    public function testProcessCanBeRan()
    {
        $mock = $this
            ->getMockBuilder('ConsoleTools\Symlink\Generator\GenerateSymlinks')
            ->disableOriginalConstructor()
            ->setMethods(['getAllDirsToTraverse', 'getProjectConfig', 'prepareSymlinks'])
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getAllDirsToTraverse')
            ->willReturn(['firstDir', 'SecondDir']);

        $mock
            ->expects($this->any())
            ->method('getProjectConfig')
            ->willReturn(['build' => ['symlinks' => ['source' => 'source']]]);

        $mock
            ->expects($this->any())
            ->method('prepareSymlinks')
            ->withAnyParameters()
            ->willReturn(true);

        $this->assertNull($mock->process());
    }
}

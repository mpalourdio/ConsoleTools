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
        $output   = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput', []);
        $instance = new GenerateSymlinks('rootPath', '../', ['*'], $output);

        $this->assertInstanceOf('ConsoleTools\Symlink\Generator\GenerateSymlinks', $instance);
    }

    /**
     * @expectedException \Exception
     */
    public function testWrongDestFolderThrowsException()
    {
        new GenerateSymlinks('rootPath', 'destPath/');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRootPathIsMandatory()
    {
        new GenerateSymlinks(null, null);
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
            ->willReturn([]);

        $mock
            ->expects($this->any())
            ->method('getProjectConfig')
            ->willReturn([]);

        $mock
            ->expects($this->any())
            ->method('prepareSymlinks')
            ->willReturn(true);

        $this->assertNull($mock->process());
    }

    public function testCanTraverseDirs()
    {
        $output   = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput', []);
        $instance = new GenerateSymlinks('../', '../', ['*'], $output);

        $this->assertInternalType('array', $instance->getAllDirsToTraverse());
    }

    public function testGetProjectConfig()
    {
        $output   = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput', []);
        $instance = new GenerateSymlinks('../', '../', ['Linux'], $output);

        $this->assertFalse($instance->getProjectConfig('Linux'));
    }

    public function testNotStarDoesntTraverse()
    {
        $output   = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput', []);
        $instance = new GenerateSymlinks('../', '../', ['Symlink'], $output);

        $this->assertEquals('Symlink', $instance->getAllDirsToTraverse()[0]);
    }

    public function testprepareSymlinks()
    {
        $output   = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput', []);
        $instance = new GenerateSymlinks('../', '../', ['Linux'], $output);

        $this->assertTrue($instance->prepareSymlinks('Linux', [['source' => 'MacOS']]));
    }

    public function testCreateSymlinks()
    {
        $mock = $this
            ->getMockBuilder('ConsoleTools\Symlink\Generator\GenerateSymlinks')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('createSymlinks')
            ->withAnyParameters()
            ->willReturn(false);

        $this->assertFalse($mock->createSymlinks('foo', 'bar'));
    }
}

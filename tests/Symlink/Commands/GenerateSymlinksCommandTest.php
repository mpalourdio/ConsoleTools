<?php
/*
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace ConsoleToolsTests\Symlink\Commands;

use ConsoleTools\Symlink\Commands\GenerateSymlinksCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateSymlinksCommandTest extends \PHPUnit_Framework_TestCase
{
    private function getQuestionHelperMockSetToFalse()
    {
        $dialog = $this->getMock('Symfony\Component\Console\Helper\QuestionHelper', ['askConfirmation']);
        $dialog->expects($this->any())
            ->method('askConfirmation')
            ->will($this->returnValue(false));

        return $dialog;
    }

    public function testSymlinkMandatoryOption()
    {
        $application = new Application();
        $application->add(new GenerateSymlinksCommand());

        $command = $application->find('symlink:generate');


        $command->getHelperSet()->set($this->getQuestionHelperMockSetToFalse(), 'dialog');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '-s'      => 'root',
        ]);

        $this->assertRegExp('/Templates dir : root/', $commandTester->getDisplay());
    }

    /**
     * @expectedException \Exception
     */
    public function testNoRootThrowsException()
    {
        $application = new Application();
        $application->add(new GenerateSymlinksCommand());

        $command       = $application->find('symlink:generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
    }

    public function testSymlinkOneSpecificParam()
    {
        $application = new Application();
        $application->add(new GenerateSymlinksCommand());

        $command = $application->find('symlink:generate');
        $command->getHelperSet()->set($this->getQuestionHelperMockSetToFalse(), 'dialog');

        $symlink = $this
            ->getMockBuilder('ConsoleTools\Symlink\Generator\GenerateSymlinks')
            ->disableOriginalConstructor()
            ->getMock();

        $symlink->expects($this->any())
            ->method('process')
            ->will($this->returnValue(true));

        $symlink->expects($this->any())
            ->method('getAllDirsToTraverse')
            ->will($this->returnValue(true));

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '-s'      => 'root',
            '-p'      => ['linux'],
        ]);


        $this->assertRegExp('/linux/', $commandTester->getDisplay());
    }

    public function testSymlinkMoreThanOneParam()
    {
        $application = new Application();
        $application->add(new GenerateSymlinksCommand());

        $command = $application->find('symlink:generate');
        $command->getHelperSet()->set($this->getQuestionHelperMockSetToFalse(), 'dialog');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '-s'      => 'root',
            '-p'      => ['linux', 'gnu'],
        ]);

        $this->assertRegExp('/linux && gnu/', $commandTester->getDisplay());
    }
}

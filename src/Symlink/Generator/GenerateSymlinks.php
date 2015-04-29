<?php
/*
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace ConsoleTools\Symlink\Generator;

use DirectoryIterator;
use Prophecy\Exception\InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSymlinks
{
    private $templateDir;
    private $projectDir;
    private $output;
    const JSON_CONFIG_FILENAME = 'config.json';

    /**
     * @param                 $rootPath
     * @param string          $projectDir
     * @param OutputInterface $output
     */
    public function __construct($rootPath, $projectDir = '*', OutputInterface $output = null)
    {
        if (null === $rootPath) {
            throw new InvalidArgumentException('Vous devez spécifier la racine de Templates');
        }

        $this->projectDir  = $projectDir;
        $this->output      = $output;
        $this->templateDir = $rootPath;
    }

    /**
     * @return array
     */
    public function getAllDirsToTraverse()
    {
        $dir = new DirectoryIterator($this->templateDir);
        if ($this->projectDir === ['*']) {
            $allDirs = [];
            foreach ($dir as $fileinfo) {
                if ($fileinfo->isDir() &&
                    ! $fileinfo->isDot() &&
                    substr($fileinfo->getFilename(), 0, 1) != '.'
                ) {
                    $allDirs[] = $fileinfo->getFilename();
                }
            }
            sort($allDirs);
        } else {
            $allDirs = $this->projectDir;
        }

        return $allDirs;
    }

    /**
     * @param  $projectDir
     * @return array
     */
    public function getProjectConfig($projectDir)
    {
        $configFile =
            $this->templateDir . DIRECTORY_SEPARATOR . $projectDir . DIRECTORY_SEPARATOR . self::JSON_CONFIG_FILENAME;
        if (! file_exists($configFile)) {
            $this->output->writeln(
                '<error>config.json introuvable pour ' . $projectDir . '<error>'
            );

            return false;
        }

        return json_decode(file_get_contents($configFile), true);
    }

    /**
     * return void
     */
    public function process()
    {
        $projectDirs = $this->getAllDirsToTraverse();
        foreach ($projectDirs as $projectDir) {
            $config = $this->getProjectConfig($projectDir);
            if (false !== $config) {
                $this->createSymlinks($projectDir, $config['build']['symlinks'], $this->output);
            }
        }
    }

    /**
     * @param       $projectDir
     * @param array $symlinksToCreate
     */
    private function createSymlinks($projectDir, array $symlinksToCreate)
    {
        foreach ($symlinksToCreate as $symlink) {
            if (! isset($symlink['dest'])) {
                $symlink['dest'] = $symlink['source']; //source n'est pas mandatory
            }
            $symlinkToCreate = $this->templateDir . DIRECTORY_SEPARATOR . $symlink['dest'];

            //check if symlink exists
            if (is_link($symlinkToCreate)) {
                unlink($symlinkToCreate);
                $this->output->writeln(
                    '<question>' . $symlinkToCreate . ' -> A recréer </question>'
                );
            }

            symlink(
                $this->templateDir . DIRECTORY_SEPARATOR . $projectDir . DIRECTORY_SEPARATOR . $symlink['source'],
                $symlinkToCreate
            );

            $this->output->writeln(
                '<info>' . $symlinkToCreate . ' -> OK <info>'
            );
        }
    }
}

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
    private $projectDirs;
    private $output;
    const JSON_CONFIG_FILENAME = 'config.json';

    /**
     * @param string          $rootPath    The path of the directory where projects are stores
     * @param array           $projectDirs List of projects for which we want subfolders symlinks
     * @param OutputInterface $output      Writes messages to console
     */
    public function __construct($rootPath, $projectDirs = ['*'], OutputInterface $output = null)
    {
        if (null === $rootPath) {
            throw new InvalidArgumentException('Vous devez spécifier la racine de Templates');
        }

        $this->projectDirs = $projectDirs;
        $this->output      = $output;
        $this->templateDir = realpath($rootPath);
    }

    /**
     * Traverse and store all dirs from the specified root path
     *
     * @return array
     */
    public function getAllDirsToTraverse()
    {
        $dir = new DirectoryIterator($this->templateDir);
        if ($this->projectDirs === ['*']) {
            $allDirs = [];
            foreach ($dir as $fileinfo) {
                if ($fileinfo->isDir() &&
                    ! $fileinfo->isDot() &&
                    substr($fileinfo->getFilename(), 0, 1) != '.' &&
                    ! $fileinfo->isLink()
                ) {
                    $allDirs[] = $fileinfo->getFilename();
                }
            }
            sort($allDirs);
        } else {
            $allDirs = $this->projectDirs;
        }

        return $allDirs;
    }

    /**
     * Read content of config.json if exists for the current fetched folder
     *
     * @param  string $projectDir The current folder we want config from
     *
     * @return array
     */
    public function getProjectConfig($projectDir)
    {
        $configFile =
            $this->templateDir . DIRECTORY_SEPARATOR . $projectDir . DIRECTORY_SEPARATOR . self::JSON_CONFIG_FILENAME;
        if (! file_exists($configFile)) {
            $this->output->writeln('<error>config.json introuvable pour ' . $projectDir . '<error>');

            return false;
        }

        return json_decode(file_get_contents($configFile), true);
    }

    /**
     * Bootstrap the symlinks creation process
     *
     * @return void
     */
    public function process()
    {
        $projectDirs = $this->getAllDirsToTraverse();
        foreach ($projectDirs as $projectDir) {
            $config = $this->getProjectConfig($projectDir);
            if (false !== $config) {
                $this->prepareSymlinks($projectDir, $config['build']['symlinks']);
            }
        }
    }

    /**
     * Grabs and treats sources and destinations paths
     *
     * @param string $projectDir
     * @param array  $symlinksToCreate
     *
     * @return bool
     */
    public function prepareSymlinks($projectDir, array $symlinksToCreate)
    {
        foreach ($symlinksToCreate as $symlink) {
            if (! isset($symlink['dest'])) {
                $symlink['dest'] = $symlink['source']; //source n'est pas mandatory
            }
            $symlinkToCreate = $this->templateDir . DIRECTORY_SEPARATOR . $symlink['dest'];

            $sourceEntity =
                $this->templateDir . DIRECTORY_SEPARATOR . $projectDir . DIRECTORY_SEPARATOR . $symlink['source'];

            if (! is_dir($sourceEntity) && ! file_exists($sourceEntity)) {
                $this->output->writeln('<error>' . $sourceEntity . ' n\'existe pas<error>');
            } else {
                $this->createSymlinks($symlinkToCreate, $sourceEntity);
            }
        }

        return true;
    }

    /**
     * Delete The symlink if exists and re-create it
     *
     * @param string $symlinkToCreate The symlink to create
     * @param string $sourceEntity    The source from which create the symlink
     *
     * @return bool
     */
    public function createSymlinks($symlinkToCreate, $sourceEntity)
    {
        //check if symlink exists
        if (is_link($symlinkToCreate)) {
            unlink($symlinkToCreate);
            $this->output->writeln('<question>' . $symlinkToCreate . ' -> A recréer </question>');
        }

        if (symlink($sourceEntity, $symlinkToCreate)) {
            $this->output->writeln('<info>' . $symlinkToCreate . ' -> OK <info>');

            return true;
        }

        return false;
    }
}

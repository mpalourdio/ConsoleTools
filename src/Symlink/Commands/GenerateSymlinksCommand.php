<?php
/*
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace ConsoleTools\Symlink\Commands;

use ConsoleTools\Symlink\Generator\GenerateSymlinks;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class GenerateSymlinksCommand extends Command
{
    protected function configure()
    {
        $this->setName('symlink:generate')
            ->setDescription("Création des liens symboliques pour \\Templates")
            ->setDefinition([
                new InputOption(
                    'chemin',
                    'c',
                    InputOption::VALUE_REQUIRED,
                    'Chemin du répertoire de template si différent du répertoire de la console'
                ),
                new InputOption(
                    'project',
                    'p',
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    '"*" ou un/des répertoire(s) de projet',
                    ['*']
                ),
            ])
            ->setHelp(
                'Crée les symlinks pour les dossiers de templates d\'un ou des projets

Utilisation:

<info>app/console symlink:generate -p Gpecs -p Serac </info>
<info>app/console symlink:generate -c ../Templates </info>'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @throws \Exception
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $headerStyle = new OutputFormatterStyle('white', 'green', ['bold']);
        $output->getFormatter()->setStyle('header', $headerStyle);

        $project  = $input->getOption('project');
        $rootPath = $input->getOption('chemin');
        if ($rootPath === null) {
            throw new \Exception(
                'Vous devez spécifier la racine de Templates avec l\'option "-c" ou "--chemin"'
            );
        }

        $output->writeln(
            '<header>Génération des symlinks pour "' . implode(' && ', $project) . '" -> Templates dir : '
            . $rootPath
            . '</header>'
        );

        if ($this->getHelper('dialog')->askConfirmation($output, "Continuer? (y/n) ")) {
            $g = new GenerateSymlinks($output, $rootPath, $project);
            $g->process($output);
        }

        $output->writeln('<header>Fini ? Ca joue le chalet ou bien ?</header>');
    }
}

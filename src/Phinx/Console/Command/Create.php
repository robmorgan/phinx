<?php
/**
 * Phinx
 *
 * (The MIT license)
 * Copyright (c) 2014 Rob Morgan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated * documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package    Phinx
 * @subpackage Phinx\Console
 */
namespace Phinx\Console\Command;

use Phinx\Migration\CreationInterface;
use Phinx\Migration\Util;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Create extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('create')
            ->setDescription('Create a new migration')
            ->addArgument('name', InputArgument::REQUIRED, 'What is the name of the migration?')
            ->setHelp(sprintf(
                '%sCreates a new database migration%s',
                PHP_EOL,
                PHP_EOL
            ));

        // An alternative template.
        $this->addOption('template', 't', InputOption::VALUE_REQUIRED, 'Use an alternative template');

        // A classname to be used to gain access to the template content as well as the ability to
        // have a callback once the migration file has been created.
        $this->addOption('class', 'l', InputOption::VALUE_REQUIRED, 'Use a class implementing Phinx\Migration\CreationInterface to generate the template');
    }

    /**
     * Migrate the database.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var CreationInterface $creationClass
         */

        $this->bootstrap($input, $output);

        // get the migration path from the config
        $path = $this->getConfig()->getMigrationPath();

        if (!is_writeable($path)) {
            throw new \InvalidArgumentException(sprintf(
                'The directory "%s" is not writeable',
                $path
            ));
        }

        $path = realpath($path);
        $className = $input->getArgument('name');

        if (!Util::isValidMigrationClassName($className)) {
            throw new \InvalidArgumentException(sprintf(
                'The migration class name "%s" is invalid. Please use CamelCase format.',
                $className
            ));
        }

        // Compute the file path
        $fileName = Util::mapClassNameToFileName($className);
        $filePath = $path . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($filePath)) {
            throw new \InvalidArgumentException(sprintf(
                'The file "%s" already exists',
                $filePath
            ));
        }

        // Get the alternative template and static class options, but only allow one of them.
        $altTemplate = $input->getOption('template');
        $creationClassName = $input->getOption('class');
        if (!empty($altTemplate) && !empty($creationClassName)){
            throw new \InvalidArgumentException('Cannot use --template and --class');
        }

        // Verify the alternative template file's existence.
        if (!empty($altTemplate) &&!file_exists($altTemplate)){
            throw new \InvalidArgumentException(sprintf(
                'The alternative template file "%s" does not exist',
                $altTemplate
            ));
        }

        // Verify the static class exists and that it implements the required interface.
        if (!empty($creationClassName)) {
            if (!class_exists($creationClassName, true)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The class "%s" does not exist',
                        $creationClassName
                    )
                );
            }
            if (!is_subclass_of($creationClassName, '\Phinx\Migration\CreationInterface', true)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The class "%s" does not implement the required interface "\Phinx\Migration\CreationInterface"',
                        $creationClassName
                    )
                );
            }
        }

        // Determine the appropriate mechanism to get the template
        if (!empty($altTemplate)) {
            // Get the template from the alternative template filename.
            $contents = file_get_contents($altTemplate);
        } elseif (!empty($creationClassName)) {
            // Get the template from the creation class
            $creationClass = new $creationClassName();
            $contents = $creationClass->getMigrationTemplate();
        } else {
            // load the migration template
            $contents = file_get_contents(dirname(__FILE__) . '/../../Migration/Migration.template.php.dist');
        }

        // inject the class name
        $contents = str_replace('$className', $className, $contents);
        
        // inject the base class name
        $contents = str_replace('$baseClassName', $this->getConfig()->getMigrationBaseClassName(), $contents);
        
        if (false === file_put_contents($filePath, $contents)) {
            throw new \RuntimeException(sprintf(
                'The file "%s" could not be written to',
                $path
            ));
        }

        // Do we need to do the post creation call to the creation class?
        if (!empty($creationClassName)) {
            try {
                $creationClass->postMigrationCreation($filePath, $className, $this->getConfig()->getMigrationBaseClassName());
            }
            catch(Exception $ex){
                throw new \RuntimeException(sprintf(
                    'Problem calling $s->postMigrationCreation(), resulting in $s',
                    $creationClassName,
                    $ex->getMessage()
                ));
            }
        }

        $output->writeln('<info>created</info> .' . str_replace(getcwd(), '', $filePath));
    }
}

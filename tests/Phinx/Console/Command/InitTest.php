<?php

namespace Test\Phinx\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\StreamOutput;
use Phinx\Config\Config;
use Phinx\Console\Command\Init;

class InitTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $files = glob(sys_get_temp_dir() . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testConfigAndSchemaAreWritten()
    {
        $application = new \Phinx\Console\PhinxApplication('testing');
        $application->add(new Init());

        // setup dependencies
        $output = new StreamOutput(fopen('php://memory', 'a', false));

        $command = $application->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'path' => sys_get_temp_dir()
        ));

        $this->assertRegExp(
            '/created (.*)\/phinx.yml(.*)/',
            $commandTester->getDisplay()
        );
        $this->assertRegExp(
            '/created (.*)\/phinxlog.sql(.*)/',
            $commandTester->getDisplay()
        );

        $this->assertFileExists(
            sys_get_temp_dir() . '/phinx.yml',
            'Phinx configuration not existent'
        );
        $this->assertFileExists(
            sys_get_temp_dir() . '/phinxlog.sql',
            'Phinx migration schema not existent'
        );
    }

    public function testNameOfDefaultMigrationTableIsApplied()
    {
        $application = new \Phinx\Console\PhinxApplication('testing');
        $application->add(new Init());

        // setup dependencies
        $output = new StreamOutput(fopen('php://memory', 'a', false));

        $command = $application->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'path' => sys_get_temp_dir(),
            '--default-migration-table' => 'testname',
        ));

        $this->assertRegExp(
            '/created (.*)phinx.yml/',
            $commandTester->getDisplay()
        );
        $this->assertRegExp(
            '/created (.*)testname.sql/',
            $commandTester->getDisplay()
        );

        $this->assertFileExists(
            sys_get_temp_dir() . '/phinx.yml',
            'Phinx configuration not existent'
        );
        $this->assertFileExists(
            sys_get_temp_dir() . '/testname.sql',
            'Phinx migration schema not existent'
        );

        $this->assertRegExp(
            '/CREATE TABLE `testname`(.*)/',
            file_get_contents(sys_get_temp_dir() . '/testname.sql')
        );
        $this->assertRegExp(
            '/default_migration_table: testname(.*)/',
            file_get_contents(sys_get_temp_dir() . '/phinx.yml')
        );
    }

    /**
     * @expectedException              \InvalidArgumentException
     * @expectedExceptionMessageRegExp /The file "(.*)" already exists/
     */
    public function testThrowsExceptionWhenConfigFilePresent()
    {
        touch(sys_get_temp_dir() . '/phinx.yml');
        $application = new \Phinx\Console\PhinxApplication('testing');
        $application->add(new Init());

        // setup dependencies
        $output = new StreamOutput(fopen('php://memory', 'a', false));

        $command = $application->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'path' => sys_get_temp_dir()
        ));
    }
    /**
     * @expectedException              \InvalidArgumentException
     * @expectedExceptionMessageRegExp /The file "(.*)" already exists/
     */
    public function testThrowsExceptionWhenMigrationTableSchemaFilePresent()
    {
        touch(sys_get_temp_dir() . '/phinxlog.sql');
        $application = new \Phinx\Console\PhinxApplication('testing');
        $application->add(new Init());

        // setup dependencies
        $output = new StreamOutput(fopen('php://memory', 'a', false));

        $command = $application->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'path' => sys_get_temp_dir()
        ));
    }
}

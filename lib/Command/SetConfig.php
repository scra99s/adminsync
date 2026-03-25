<?php
declare(strict_types=1);
namespace OCA\AdminSync\Command;

use OCP\IConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetConfig extends Command {

    protected static $defaultName = 'adminsync:set';
    private IConfig $config;

    public function __construct(IConfig $config) {
        parent::__construct();
        $this->config = $config;
    }

    protected function configure(): void {
        $this
            ->setDescription('Set AdminSync configuration')
            ->addArgument('key', InputArgument::REQUIRED)
            ->addArgument('value', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->config->setAppValue('adminsync', $input->getArgument('key'), $input->getArgument('value'));
        $output->writeln("Set {$input->getArgument('key')} = {$input->getArgument('value')}");
        return Command::SUCCESS;
    }
}

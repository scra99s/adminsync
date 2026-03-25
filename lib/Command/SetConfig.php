<?php
namespace OCA\AdminSync\Command;

use OCP\IConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetConfig extends Command {

    private IConfig $config;

    public function __construct(IConfig $config) {
        parent::__construct();
        $this->config = $config;
    }

    protected function configure() {
        $this
            ->setName('adminsync:set')
            ->setDescription('Set adminsync config')
            ->addArgument('key', InputArgument::REQUIRED)
            ->addArgument('value', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $key = $input->getArgument('key');
        $value = $input->getArgument('value');

        $this->config->setAppValue('adminsync', $key, $value);

        $output->writeln("Set $key = $value");
        return Command::SUCCESS;
    }
}

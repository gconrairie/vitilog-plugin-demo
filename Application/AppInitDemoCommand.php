<?php

namespace App\Plugins\Demo\Application;

use App\Core\Installer\Application\Read\CheckConfigHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-demo',
    description: 'Initialize demo app'
)]
class AppInitDemoCommand extends Command
{
    public function __construct(
        private readonly AppInitDemoService $appInitDemoService,
        private readonly CheckConfigHandler $checkConfigHandler,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // init app
        $io->title('Initializing App');
        $this->appInitDemoService->init($io);

        // Check config
        $response = $this->checkConfigHandler->handle();
        foreach ($response['env'] as $key => $value) {
            $io->writeln($key . ': ' . ($value ? '✅' : '❌'));
        }
        foreach ($response['adminOptions'] as $key => $value) {
            $io->writeln($key . ': ' . ($value ? '✅' : '❌'));
        }

        return Command::SUCCESS;
    }
}
